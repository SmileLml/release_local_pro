<?php
/**
 * The control file of workflowfield module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowfield
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowfield extends control
{
    /**
     * Browse field list.
     *
     * @param  string $module
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function browse($module, $orderBy = 'order')
    {
        $flow   = $this->loadModel('workflow', 'flow')->getByModule($module);
        $fields = $this->workflowfield->getList($flow->module, $orderBy);

        if($flow->type == 'table')
        {
            /* If flow is table, filter the useless field.*/
            $disabledFields = $this->config->workflowfield->disabledFields['subTables'];
            foreach($fields as $key => $field)
            {
                if($disabledFields and strpos(",{$disabledFields},", ",{$field->field},") !== false) unset($fields[$key]);
            }
            $this->view->parent = $this->workflow->getByModule($flow->parent);
        }

        $this->view->title      = $this->lang->workflowfield->browse;
        $this->view->fields     = $fields;
        $this->view->flow       = $flow;
        $this->view->orderBy    = $orderBy;
        $this->view->editorMode = 'advanced';
        $this->view->moduleMenu = false;
        $this->display();
    }

    /**
     * Create a field.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function create($module)
    {
        if($_POST)
        {
            $result = $this->workflowfield->create($module);
            if(zget($result, 'result') == 'fail') return $this->send($result);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action')->create('workflowfield', $result, 'Created');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "module={$module}")));
        }

        unset($_SESSION['sqlVars']);

        $flow = $this->loadModel('workflow', 'flow')->getByModule($module);

        $datasources = $this->workflowfield->getDatasourcePairs($flow->type);
        if($flow->buildin && $flow->type != 'table') unset($datasources['prevModule']);

        $this->view->title       = $this->lang->workflowfield->create;
        $this->view->rules       = $this->loadModel('workflowrule', 'flow')->getPairs();
        $this->view->fields      = $this->workflowfield->getPairs($module);;
        $this->view->datasources = $datasources;
        $this->view->flow        = $flow;
        $this->display();
    }

    /**
     * Edit a field.
     *
     * @param  string $module
     * @param  int    $id
     * @access public
     * @return void
     */
    public function edit($module, $id)
    {
        if($_POST)
        {
            $result = $this->workflowfield->update($id);
            if(is_array($result)) return $this->send($result);
            if(!$result) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $this->inlink('browse', "module=$module")));
        }

        $flow  = $this->loadModel('workflow', 'flow')->getByModule($module);
        $field = $this->workflowfield->getByID($id, $mergeOptions = false);

        unset($_SESSION['sqlVars']);
        $_SESSION['sqlVars'] = (array)$field->sqlVars;

        $fields    = $this->workflowfield->getPairs($module);
        $fieldKeys = array_keys($fields);
        $index     = array_search($id, $fieldKeys);
        $index     = $index > 0 ? $index - 1 : $index;
        if($field->order != 0) $field->order = $fieldKeys[$index];
        unset($fields[$id]);

        $datasources = $this->workflowfield->getDatasourcePairs($flow->type);
        if($flow->buildin && $flow->type != 'table') unset($datasources['prevModule']);

        $this->view->title       = $this->lang->workflowfield->edit;
        $this->view->rules       = $this->loadModel('workflowrule', 'flow')->getPairs();
        $this->view->datasources = $datasources;
        $this->view->flow        = $flow;
        $this->view->fields      = $fields;
        $this->view->field       = $field;
        $this->display();
    }

    /**
     * Delete a field.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function delete($id)
    {
        $result = $this->workflowfield->delete($id);
        if(is_array($result)) return $this->send($result);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success'));
    }

    /**
     * Sort fields of a flow.
     *
     * @access public
     * @return void
     */
    public function sort()
    {
        if($_POST)
        {
            foreach($_POST as $id => $order)
            {
                $this->dao->update(TABLE_WORKFLOWFIELD)->set('order')->eq($order)->where('id')->eq($id)->exec();
            }

            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess));
        }
    }

    /**
     * Import fields to a flow.
     *
     * @param  string $module
     * @param  string $type
     * @access public
     * @return void
     */
    public function import($module, $type = 'flow')
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $file = $this->loadModel('file')->getUpload('files');
            if(empty($file)) return $this->send(array('result' => 'fail', 'message' => $this->lang->excel->error->noFile));
            $file = $file[0];

            $fileName = $this->file->savePath . $this->file->getSaveName($file['pathname']);
            move_uploaded_file($file['tmpname'], $fileName);

            $phpExcel  = $this->app->loadClass('phpexcel');
            $phpReader = new PHPExcel_Reader_Excel2007();
            if(!$phpReader->canRead($fileName))
            {
                $phpReader = new PHPExcel_Reader_Excel5();
                if(!$phpReader->canRead($fileName))
                {
                    unlink($fileName);
                    return $this->send(array('result' => 'fail', 'message' => $this->lang->excel->error->canNotRead));
                }
            }
            $this->session->set('importFile', $fileName);
            return $this->send(array('result' => 'success', 'locate' => (inlink('showImport', "module=$module&type=$type"))));
        }

        $this->view->title  = $this->lang->import;
        $this->view->module = $module;
        $this->display();
    }

    /**
     * Show import fields of a flow.
     *
     * @param  string $module
     * @param  string $type
     * @access public
     * @return void
     */
    public function showImport($module, $type = 'flow')
    {
        if(!$this->session->importFile) $this->locate(inlink('browse', "module=$module"));

        if($_POST)
        {
            $errors = $this->workflowfield->createFromImport($module);
            if($errors) return $this->send(array('result' => 'fail', 'message' => $errors));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "module=$module")));
        }

        $fields = array();
        foreach($this->config->workflowfield->excel->templateFields as $field)
        {
            $fields[$field] = $this->lang->workflowfield->$field;
        }
        
        $controlTypeList = $this->lang->workflowfield->controlTypeList;
        $datasourceList  = $this->workflowfield->getDatasourcePairs($type);;

        $flow = $this->loadModel('workflow', 'flow')->getByModule($module);
        if($flow->buildin && $type != 'table') unset($datasourceList['prevModule']);

        $fieldList       = $this->loadModel('file')->parseExcel($fields);
        foreach($fieldList as $key => $field)
        {
            if(empty($field->name) or empty($field->field))
            {
                unset($fieldList[$key]);
                continue;
            }

            $field->control    = zget(array_flip($controlTypeList), $field->control, 'input');
            $field->datasource = zget(array_flip($datasourceList), $field->datasource, 'custom');

            switch($field->control)
            {
                case 'textarea' :
                case 'multi-select' :
                case 'checkbox' :
                case 'richtext' :
                    $field->type   = 'text';
                    $field->length = 0;
                    break;
                case 'date' :
                    $field->type   = 'date';
                    $field->length = 0;
                    break;
                case 'datetime' :
                    $field->type   = 'datetime';
                    $field->length = 0;
                    break;
                case 'integer' :
                    $field->type   = 'mediumint';
                    $field->length = 0;
                    break;
                case 'decimal' :
                case 'formula' :
                    $field->type   = 'decimal';
                    $field->length = '10,2';
                    break;
                default :
                    $field->type   = 'varchar';
                    $field->length = 255;
            }
        }

        if(empty($fieldList))
        {
            unlink($this->session->importFile);
            unset($_SESSION['importFile']);
            echo js::alert($this->lang->excel->error->noData);
            die(js::locate(inlink('browse', "module=$module")));
        }

        $this->view->title       = $this->lang->workflowfield->showImport;
        $this->view->datasources = array('' => '') + $datasourceList;
        $this->view->fieldList   = $fieldList;
        $this->view->module      = $module;
        $this->view->modalWidth  = 1200;
        $this->display();
    }

    /**
     * Export fields template of a flow.
     *
     * @param  string $module
     * @param  string $type
     * @access public
     * @return void
     */
    public function exportTemplate($module, $type = 'flow')
    {
        if($_POST)
        {
            unset($this->lang->workflowfield->controlTypeList['label']);

            $fields = array();
            $rows   = array();
            foreach($this->config->workflowfield->excel->templateFields as $key)
            {
                $fields[$key] = $this->lang->workflowfield->$key;
                for($i = 0; $i < $this->post->num; $i++) $rows[$i][$key] = '';
            }

            $flow = $this->loadModel('workflow', 'flow')->getByModule($module);
            $datasourceList = $this->workflowfield->getDatasourcePairs($type);;
            if($flow->buildin && $type != 'table') unset($datasourceList['prevModule']);

            $data = new stdclass();
            $data->fields         = $fields;
            $data->kind           = 'workflowfield';
            $data->rows           = $rows;
            $data->title          = $this->lang->workflowfield->template;
            $data->customWidth    = $this->config->workflowfield->excel->customWidth;
            $data->controlList    = $this->lang->workflowfield->controlTypeList;
            $data->datasourceList = $datasourceList;
            $data->sysDataList    = $this->config->workflowfield->excel->listFields;
            $data->listStyle      = $this->config->workflowfield->excel->listFields;
            $data->help           = $this->lang->workflowfield->excel->tips . $this->lang->workflowfield->excel->defaultTip;

            $excelData = new stdclass();
            $excelData->dataList[] = $data;
            $excelData->fileName   = $this->lang->workflowfield->template;

            $this->app->loadClass('excel')->export($excelData, $this->post->fileType);
        }

        $this->display('file', 'exportTemplate');
    }

    /**
     * Set fields to display in another flow.
     *
     * @access public
     * @return void
     */
    public function setValue($module)
    {
        if($_POST)
        {
            $this->workflowfield->setValue();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $this->view->title       = $this->lang->workflowfield->setValue;
        $this->view->flow        = $this->loadModel('workflow', 'flow')->getByModule($module);
        $this->view->fields      = $this->workflowfield->getFieldPairs($module, 'all', false);
        $this->view->valueFields = $this->workflowfield->getValueFields($module);
        $this->view->module      = $module;
        $this->view->editorMode  = 'advanced';
        $this->display();
    }

    /**
     * Set fields to export.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function setExport($module)
    {
        if($_POST)
        {
            $this->workflowfield->setExport($module);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $flowPairs    = array();
        $fieldGroups  = array();
        $exportGroups = array();
        $flow         = $this->loadModel('workflow', 'flow')->getByModule($module);
        $subTables    = $this->loadModel('workflow', 'flow')->getPairs($module);
        $flowPairs    = array($flow->module => $flow->name) + $subTables;

        $fieldGroups[$module]  = $this->workflowfield->getFieldPairs($module, '0', false, 'canExport_desc, exportOrder, order, id');
        $exportGroups[$module] = $this->workflowfield->getExportFields($module, '0');

        if($subTables)
        {
            foreach($subTables as $subModule => $tableName)
            {
                $fieldGroups[$subModule]  = $this->workflowfield->getFieldPairs($subModule, '0', false, 'canExport_desc, exportOrder, order, id');
                $fieldGroups[$subModule]  = $this->workflowfield->filterUselessFields($fieldGroups[$subModule]);
                $exportGroups[$subModule] = $this->workflowfield->getExportFields($subModule, '0');
            }
        }

        $this->view->title        = $this->lang->workflowfield->setExport;
        $this->view->flow         = $flow;
        $this->view->flowPairs    = $flowPairs;
        $this->view->fieldGroups  = $fieldGroups;
        $this->view->exportGroups = $exportGroups;
        $this->view->editorMode   = 'advanced';
        $this->display();
    }

    /**
     * Set fields to search.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function setSearch($module)
    {
        if($_POST)
        {
            $this->workflowfield->setSearch($module);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $this->view->title        = $this->lang->workflowfield->setSearch;
        $this->view->flow         = $this->loadModel('workflow', 'flow')->getByModule($module);
        $this->view->fields       = $this->workflowfield->getFieldPairs($module, '0', false, 'canSearch_desc, searchOrder, order, id');
        $this->view->searchFields = $this->workflowfield->getSearchFields($module, '0');
        $this->view->module       = $module;
        $this->view->editorMode   = 'advanced';
        $this->display();
    }

    /**
     * Add a sql var.
     *
     * @access public
     * @return void
     */
    public function addSqlVar()
    {
        if($_POST)
        {
            $sqlVar = fixer::input('post')->get();

            $errors = array();
            if(empty($sqlVar->varName))     $errors['varName']     = sprintf($this->lang->error->notempty, $this->lang->workflowfield->varName);
            if(empty($sqlVar->showName))    $errors['showName']    = sprintf($this->lang->error->notempty, $this->lang->workflowfield->showName);
            if(empty($sqlVar->requestType)) $errors['requestType'] = sprintf($this->lang->error->notempty, $this->lang->workflowfield->requestType);
            if(isset($_SESSION['sqlVars'][$sqlVar->varName])) $errors['varName'] = sprintf($this->lang->error->unique, $this->lang->workflowfield->varName, $sqlVar->varName);
            if(!empty($errors)) return $this->send(array('result' => 'fail', 'message' => $errors));

            $sqlVars = zget($_SESSION, 'sqlVars', array());
            $sqlVars[$sqlVar->varName] = $sqlVar;
            $this->session->set('sqlVars', $sqlVars);
            return $this->send(array('result' => 'success', 'varName' => $sqlVar->varName));
        }

        $this->view->title = $this->lang->workflowfield->addVar;
        $this->display();
    }

    /**
     * Delete a sql var.
     *
     * @param  string $varName
     * @access public
     * @return void
     */
    public function delSqlVar($varName = '')
    {
        if(isset($_SESSION["sqlVars"][$varName]))
        {
            unset($_SESSION["sqlVars"][$varName]);
        }

        return $this->send(array('result' => 'success'));
    }

    /**
     * Build var control.
     *
     * @param  string $varName
     * @access public
     * @return void
     */
    public function buildVarControl($varName = '')
    {
        if(!isset($_SESSION["sqlVars"][$varName])) return;

        $sqlVar = $_SESSION["sqlVars"][$varName];
        $html   = "<span class='input-group-addon'>" . $sqlVar->showName . "</span>";
        if($sqlVar->requestType == 'input')
        {
            $html .= html::input("varValues[$varName]", $sqlVar->default, "class='form-control'");
        }
        elseif($sqlVar->requestType == 'date')
        {
            $html .= html::input("varValues[$varName]", $sqlVar->default, "class='form-control form-date'");
        }
        elseif($sqlVar->requestType == 'select')
        {
            $options = array();
            switch($sqlVar->selectList)
            {
            case 'order'    : $options = $this->loadModel('order', 'crm')->getPairs();
                break;
            case 'contract' : $options = $this->loadModel('contract', 'crm')->getPairs();
                break;
            case 'customer' : $options = $this->loadModel('customer', 'crm')->getPairs($relation = 'client');
                break;
            case 'provider' : $options = $this->loadModel('customer', 'crm')->getPairs($relation = 'provider');
                break;
            case 'contact'  : $options = $this->loadModel('contact', 'crm')->getPairs();
                break;
            case 'user'     : $options = $this->loadModel('user', 'sys')->getPairs('nodeleted,noforbidden');
                break;
            case 'dept'     : $options = array('' => '') + $this->loadModel('tree', 'sys')->getOptionMenu('dept');
                break;
            }
            $html .= html::select("varValues[$varName]", $options, $sqlVar->default, "class='form-control chosen'");
        }
        $html .= html::hidden("sqlVars[$varName]", helper::jsonEncode($sqlVar));
        $html .= "<span class='input-group-addon'>" . baseHTML::a(inlink('delSqlVar', "varName={$varName}"), "<i class='icon-close icon-large'></i>", "class='delSqlVar jsoner'") . "</span>";
        echo $html;
    }

    /**
     * Check length of a field when change its control type by ajax.
     *
     * @param  int     $id
     * @param  string  $control
     * @access public
     * @return string
     */
    public function ajaxCheckFieldLength($id, $control)
    {
        $field = $this->workflowfield->getByID($id);

        /* Init length to 256. */
        $oldLength = '256';
        $newLength = '256';
        foreach($this->config->workflowfield->lengthList as $length => $controls)
        {
            if(strpos($controls, ",$control,") !== false) $newLength = $length;
            if(strpos($controls, ",{$field->control},") !== false) $oldLength = $length;
        }

        if(($newLength < $oldLength)) echo $this->lang->workflowfield->tips->lengthNotice;
    }

    /**
     * Get field pairs by ajax.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function ajaxGetField($module)
    {
        $html = '<option></option>';
        if(!$module) die($html);

        $fields = $this->workflowfield->getList($module);
        foreach($fields as $field)
        {
            if($field->buildin or $field->readonly or strpos(',status,subStatus,', ",{$field->field},") !== false) unset($fields[$field->id]);
        }

        foreach($fields as $field)
        {
            $html .= "<option value='$field->field'>$field->name</option>";
        }

        die($html);
    }

    /**
     * Get option list of field value by ajax.
     *
     * @param  string  $module
     * @param  string  $field
     * @param  string  $value
     * @param  string  $elementName
     * @param  string  $elementID
     * @access public
     * @return string
     */
    public function ajaxGetFieldControl($module, $field, $value = '', $elementName = '', $elementID = '')
    {
        $html  = '';
        $value = $value ? urldecode(base64_decode($value)) : '';
        $name  = $elementName ? urldecode(base64_decode($elementName)) : 'values[]';
        $id    = $elementID ? "id='$elementID'" : "id='" . str_replace(array('[', ']'), '', $name) . "'";
        $field = $this->workflowfield->getByField($module, $field);

        if($field)
        {
            $options = $this->workflowfield->getFieldOptions($field, true, $value, '', $this->config->flowLimit);
            if($field->options == 'user')
            {
                $this->app->loadLang('workflowlayout', 'flow');
                $options = $this->lang->workflowlayout->default->user + $options;
            }
            elseif($field->options == 'dept')
            {
                $this->app->loadLang('workflowlayout', 'flow');
                $options = $this->lang->workflowlayout->default->dept + $options;
            }

            $data = "data-module='{$module}' data-field='{$field->field}'";
            if($field->control == 'select' or $field->control == 'radio')
            {
                $html = html::select($name, $options, $value, "$id class='form-control picker-select' $data");
            }
            elseif($field->control == 'multi-select' or $field->control == 'checkbox')
            {
                $name .= '[]';
                $html  = html::select($name, $options, $value, "$id class='form-control picker-select' multiple='multiple' $data");
            }
            else
            {
                $class = $field->control == 'date' ? 'form-date' : ($field->control == 'datetime' ? 'form-datetime' : '');
                $html  = html::input($name, $value, "$id class='form-control $class' autocomplete='off'");
            }
        }
        else
        {
            $html = html::input($name, $value, "$id class='form-control' autocomplete='off'");
        }

        echo $html;
    }

    /**
     * Get param options by ajax.
     *
     * @param  string $paramType
     * @param  string $value
     * @param  string $elementName
     * @param  string $elementID
     * @access public
     * @return void
     */
    public function ajaxGetParamOptions($paramType, $value = '', $elementName = '', $elementID = '')
    {
        $value = $value ? urldecode(base64_decode($value)) : '';
        $name  = $elementName ? urldecode(base64_decode($elementName)) : 'param[]';
        $id    = $elementID ? "id='$elementID'" : "id='" . str_replace(array('[', ']'), '', $name) . "'";

        $field = new stdclass();
        $field->type    = $paramType == 'dept' ? 'mediumint' : 'varchar';
        $field->control = 'select';
        $field->options = $paramType;

        $options = $this->workflowfield->getFieldOptions($field, true, $value, '', $this->config->flowLimit);

        echo html::select($name, $options, $value, "$id class='form-control picker-select' data-options='{$paramType}'");
    }

    /**
     * Ajax get defaultValue control.
     * 
     * @param  string $mode
     * @param  string $control 
     * @param  string $optionType 
     * @param  string $type 
     * @param  string $sql 
     * @param  string $sqlVars 
     * @param  string $elementName
     * @param  string $default 
     * @access public
     * @return string
     */
    public function ajaxGetDefaultControl($mode = 'quick', $control = '', $optionType = '', $type = '', $sql = '', $sqlVars = '', $elementName = '', $default = '')
    {
        $control = urldecode(base64_decode($control));
        $sql     = urldecode(base64_decode($sql));
        $default = urldecode(base64_decode($default));
        $name    = $elementName ? urldecode(base64_decode($elementName)) : 'default';

        if($control == 'radio')    $control = 'select';
        if($control == 'checkbox') $control = 'multi-select';

        $field = new stdclass();
        $field->options = $optionType;
        $field->type    = $type;
        $field->control = $control;
        $field->sql     = $sql;
        $field->sqlVars = $sqlVars ? $sqlVars : array();
        $field->default = $default;

        $options = $this->workflowfield->getFieldOptions($field, true, $default, '', $this->config->flowLimit);

        if($mode == 'quick')
        {
            if($optionType == 'user' or $optionType == 'dept')
            {
                $this->app->loadLang('workflowlayout', 'flow');
                $options = $this->lang->workflowlayout->default->{$optionType} + $options;
            }

            die(json_encode($options));
        }

        $html = '';
        if($control == 'select' or $control == 'radio')
        {
            $html = html::select($name, $options, '', "class='form-control'");
        }
        elseif($control == 'multi-select' or $control == 'checkbox')
        {
            $name .= '[]';
            $html  = html::select($name, $options, '', "class='form-control' multiple='multiple'");
        }

        echo $html;
    }

    /**
     * Search more default by ajax.
     *
     * @param  string $mode
     * @param  string $control
     * @param  string $optionType
     * @param  string $type
     * @param  string $sql
     * @param  string $sqlVars
     * @param  string $search
     * @param  int    $limit
     * @access public
     * @return void
     */
    public function ajaxGetMoreDefault($mode = 'quick', $control = '', $optionType = '', $type = '', $sql = '', $sqlVars = '', $search = '', $limit = 0)
    {
        $control = urldecode(base64_decode($control));
        $sql     = urldecode(base64_decode($sql));
        $search  = urldecode($search);

        if(!$limit) $limit = $this->config->searchLimit;
        if($control == 'radio')    $control = 'select';
        if($control == 'checkbox') $control = 'multi-select';

        $field = new stdclass();
        $field->options = $optionType;
        $field->type    = $type;
        $field->control = $control;
        $field->sql     = $sql;
        $field->sqlVars = $sqlVars ? $sqlVars : array();

        $options = $this->workflowfield->getFieldOptions($field, false, '', $search, $limit);

        if($mode == 'quick')
        {
            if($optionType == 'user' or $optionType == 'dept')
            {
                $this->app->loadLang('workflowlayout', 'flow');
                $options = $this->lang->workflowlayout->default->{$optionType} + $options;
            }

            die(json_encode($options));
        }

        $results = array();
        foreach($options as $key => $value)
        {
            $result = new stdclass();
            $result->text  = $value;
            $result->value = $key;

            $results[] = $result;
        }

        die(json_encode($results));
    }
}
