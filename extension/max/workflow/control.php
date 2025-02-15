<?php
/**
 * The control file of workflow module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflow
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflow extends control
{
    /**
     * Browse userdefined flows.
     *
     * @param  string $mode         browse | bysearch
     * @param  string $status       wait | normal | pause
     * @param  string $app
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browseFlow($mode = 'browse', $status = 'normal', $app = '', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Build search form. */
        $this->config->workflow->search['actionURL'] = inlink('browseFlow', 'mode=bysearch');
        $this->config->workflow->search['params']['app']['values'] = array('' => '') + $this->workflow->getApps();
        $this->loadModel('search')->setSearchParams($this->config->workflow->search);

        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->session->set('workflowList', $this->app->getURI());

        $flows = $this->workflow->getList($mode, 'flow', $status, '', $app, $orderBy, $pager);
        foreach($flows as $flow)
        {
            $flow->newVersion = $this->workflow->getVersionPairs($flow);
        }

        $this->view->title      = $this->lang->workflow->browseFlow;
        $this->view->apps       = $this->workflow->getApps();
        $this->view->flows      = $flows;
        $this->view->mode       = $mode;
        $this->view->status     = $status;
        $this->view->currentApp = $app;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->display();
    }

    /**
     * Browse userdefined tables.
     *
     * @param  string $parent
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browseDB($parent = '', $table = '', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $tables     = $this->workflow->getList('browse', 'table', '', $parent, '', $orderBy, $pager);
        $tablePairs = $this->workflow->getPairs($parent, 'table');

        if($tables && (!$table || !isset($tablePairs[$table]))) $table = current($tables)->module;

        $subTableTipsReaders = zget($this->config->workflow, 'subTableTipsReaders', ',');
        if(strpos($subTableTipsReaders, ",{$this->app->user->account},") === false)
        {
            $this->loadModel('setting')->setItem('system.flow.workflow.subTableTipsReaders', $subTableTipsReaders . $this->app->user->account . ',');
        }

        $fields = array();
        if($table)
        {
            $fields = $this->loadModel('workflowfield', 'flow')->getList($table);
            /* If flow is table, filter the useless field.*/
            $disabledFields = $this->config->workflowfield->disabledFields['subTables'];
            foreach($fields as $key => $field)
            {
                if($disabledFields and strpos(",{$disabledFields},", ",{$field->field},") !== false) unset($fields[$key]);
            }
        }

        $this->view->title               = $this->lang->workflow->browseDB;
        $this->view->tables              = $tables;
        $this->view->fields              = $fields;
        $this->view->flow                = $this->workflow->getByModule($parent);
        $this->view->currentTable        = $this->workflow->getByModule($table);
        $this->view->subTableTipsReaders = $subTableTipsReaders;
        $this->view->orderBy             = $orderBy;
        $this->view->pager               = $pager;
        $this->view->editorMode          = 'advanced';
        $this->display();
    }

    /**
     * Create a flow or table.
     *
     * @param  string $type
     * @param  string $parent
     * @param  string $app
     * @access public
     * @return void
     */
    public function create($type = 'flow', $parent = '', $app = '')
    {
        if($type == 'table')
        {
            $this->lang->workflow->create = $this->lang->workflowtable->create;
            $this->lang->workflow->module = $this->lang->workflowtable->module;
            $this->lang->workflow->name   = $this->lang->workflowtable->name;
        }

        if($_POST)
        {
            $flowID = $this->workflow->create();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $flow = $this->workflow->getByID($flowID);

            $this->workflow->createFields($flow);    // Create table and default fields.
            $this->workflow->createActions($flow);   // Create default actions.
            $this->workflow->createLabels($flow);    // Create default labels.

            if(!empty($this->config->openedApproval) && $this->post->approval == 'enabled')
            {
                $this->workflow->createApprovalRelation($flow);
                $this->workflow->createApprovalObject($this->post->approvalFlow, $flow->module);
            }

            if(dao::isError())
            {
                $errors = dao::getError();

                $this->workflow->delete($flowID);

                return $this->send(array('result' => 'fail', 'message' => $errors));
            }

            $this->loadModel('action')->create('workflow', $flowID, 'Created');

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload', 'module' => $flow->module));
        }

        $this->view->title       = $this->lang->workflow->create;
        $this->view->apps        = $this->workflow->getApps();
        $this->view->type        = $type;
        $this->view->parent      = $parent;
        $this->view->currentApp  = $app;
        $this->view->modalWidth  = 600;
        if($type == 'flow' && !empty($this->config->openedApproval)) $this->view->approvalFlows = $this->loadModel('approvalflow')->getPairs('workflow');
        $this->display();
    }

    /**
     * Copy a flow to create a new one.
     *
     * @param  string $source
     * @access public
     * @return void
     */
    public function copy($source)
    {
        if($_POST)
        {
            $flowID = $this->workflow->copy($source);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action')->create('workflow', $flowID, 'Created');

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $this->view->title      = $this->lang->workflow->copy;
        $this->view->source     = $this->workflow->getByModule($source);
        $this->view->apps       = $this->workflow->getApps();
        $this->view->modalWidth = 600;
        $this->display();
    }

    /**
     * Edit a flow or table.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function edit($id)
    {
        $flow = $this->workflow->getByID($id);
        if($_POST)
        {
            $this->loadModel('entry');

            $oldFlow = $flow;
            if($oldFlow->status == 'normal' && $oldFlow->app != 'flow') $entry = $this->entry->getByCode($oldFlow->app);

            $changes = $this->workflow->update($id);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('workflow', $id, 'Edited');
                if($changes) $this->action->logHistory($actionID, $changes);
            }

            $newFlow  = $this->workflow->getByID($id);
            if($newFlow->status == 'normal' && $newFlow->app != 'flow') $entry = $this->entry->getByCode($newFlow->app);

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload', 'entryID' => !empty($entry) ? $entry->id : 0));
        }

        if($flow->type == 'table')
        {
            $this->lang->workflow->edit   = $this->lang->workflowtable->edit;
            $this->lang->workflow->module = $this->lang->workflowtable->module;
            $this->lang->workflow->name   = $this->lang->workflowtable->name;
        }

        $this->view->title      = $this->lang->workflow->edit;
        $this->view->apps       = $this->workflow->getApps();
        $this->view->menus      = $this->workflow->getAppMenus($flow->app, $flow->module);
        $this->view->flow       = $flow;
        $this->view->modalWidth = 600;
        $this->display();
    }

    /**
     * Backup a flow.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function backup($module)
    {
        $result = $this->workflow->backup($module);
        if(!$result) return $this->send(array('result' => 'fail', 'message' => implode("\n", dao::getError())));

        return $this->send(array('result' => 'success', 'message' => $this->lang->workflow->upgrade->backupSuccess));
    }

    /**
     * Upgrade a flow.
     *
     * @param  string $module
     * @param  string $step
     * @param  string $toVersion
     * @param  string $mode         install | upgrade
     * @access public
     * @return void
     */
    public function upgrade($module, $step = 'start', $toVersion = '', $mode = '')
    {
        if($step == 'start')
        {
            if($toVersion)
            {
                $this->locate(inlink('upgrade', "module=$module&step=confirm&toVersion=$toVersion"));
            }
            else
            {
                $flow = $this->workflow->getByModule($module);

                $this->view->title    = $this->lang->workflow->upgrade->selectVersion;
                $this->view->versions = $this->workflow->getVersionPairs($flow);
                $this->view->flow     = $flow;
            }
        }
        elseif($step == 'confirm')
        {
            if($mode)
            {
                $this->locate(inlink('upgrade', "module=$module&step=result&toVersion=$toVersion&mode=$mode"));
            }
            else
            {
                $sqls = $this->workflow->compare($module, $toVersion);
                //if(!$sqls) $this->locate(inlink('upgrade', "module=$module&step=result&toVersion=$toVersion&mode=upgrade"));

                $this->view->title = $this->lang->workflow->upgrade->confirm;
                $this->view->sqls  = implode("\n", $sqls);
            }
        }
        elseif($step == 'result')
        {
            $result = $this->workflow->backup($module);
            if($result)
            {
                $this->view->result = $this->workflow->$mode($module, $toVersion);
            }
            else
            {
                $this->view->result = array('result' => 'fail', 'errors' => dao::getError());
            }
        }

        $this->view->module     = $module;
        $this->view->step       = $step;
        $this->view->toVersion  = $toVersion;
        $this->view->mode       = $mode;
        $this->view->modalWidth = 400;
        $this->display();
    }

    /**
     * View a flow or table.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function view($id)
    {
        $flow = $this->workflow->getByID($id);
        if($flow->type == 'table')
        {
            $this->lang->workflow->view   = $this->lang->workflowtable->view;
            $this->lang->workflow->module = $this->lang->workflowtable->name;
        }

        $this->view->title = $this->lang->workflow->view;
        $this->view->apps  = $this->workflow->getApps();
        $this->view->users = $this->loadModel('user')->getDeptPairs();
        $this->view->flow  = $flow;
        $this->display();
    }

    /**
     * Workflow editor flowchart page for quick mode
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function flowchart($module)
    {
        $this->app->loadLang('workflowfield');
        $this->app->loadModuleConfig('workflowaction', 'flow');

        $this->view->title      = $this->lang->workfloweditor->flowchart;
        $this->view->flow       = $this->workflow->getByModule($module);
        $this->view->editorMode = 'quick';

        $this->display();
    }

    /**
     * Workflow editor ui page for quick mode.
     *
     * @param  string $module
     * @param  string $action
     * @access public
     * @return void
     */
    public function ui($module, $action = 'create')
    {
        if($_POST)
        {
            $result = $this->workflow->saveLayout($module, $action);
            return $this->send($result);
        }

        $flow    = $this->workflow->getByModule($module);
        $fields  = $this->loadModel('workflowaction', 'flow')->getFields($module, $action, false);
        $actions = $this->dao->select('action, name, type')->from(TABLE_WORKFLOWACTION)
            ->where('module')->eq($module)
            ->andWhere('open')->ne('none')
            ->andWhere('`virtual`')->eq(0)
            ->andWhere('status')->eq('enable')
            ->orderBy('order_asc')
            ->fetchAll('action');

        $this->loadModel('workflowlayout', 'flow');
        $disabledFields = in_array($action, $this->config->workflowaction->defaultActions) ? zget($this->config->workflowlayout->disabledFields, $action, '') : $this->config->workflowlayout->disabledFields['custom'];
        foreach($fields as $key => $field)
        {
            if(strpos(",{$disabledFields},", ",{$field->field},") !== false or strpos($field->field, 'sub_') === 0) unset($fields[$key]);
        }

        if($action == 'view') $this->app->loadLang('action');

        foreach($fields as $field) $field->optionsData = $this->workflowaction->getRealOptions($field);

        $this->view->title         = $this->lang->workfloweditor->uiDesign;
        $this->view->flow          = $flow;
        $this->view->editorMode    = 'quick';
        $this->view->notEmptyRule  = $this->loadModel('workflowrule', 'flow')->getByTypeAndRule('system', 'notempty');
        $this->view->actions       = $actions;
        $this->view->currentAction = zget($actions, $action, $actions['create']);
        $this->view->fields        = $fields;
        $this->view->datasources   = $this->loadModel('workflowfield', 'flow')->getDatasourcePairs($flow->type);
        $this->view->rules         = $this->loadModel('workflowrule', 'flow')->getPairs();

        $this->display();
    }

    /**
     * Release a flow.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function release($id)
    {
        $flow   = $this->workflow->getByID($id);
        if($_POST)
        {
            $this->loadModel('entry');

            $oldFlow = $flow;
            if($oldFlow->status == 'normal' && $oldFlow->app != 'flow') $entry = $this->entry->getByCode($oldFlow->app);

            $changes = $this->workflow->release($id);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            if($changes)
            {
                $action = $this->loadModel('action')->create('workflow', $id, 'created');
                $this->action->logHistory($action, $changes);
            }

            $newFlow  = $this->workflow->getByID($id);
            if($newFlow->status == 'normal' && $newFlow->app != 'flow') $entry = $this->entry->getByCode($newFlow->app);

            $entries = '';
            if($this->post->createApp) $entries = $this->entry->getJSONEntries();

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browseFlow'), 'entryID' => !empty($entry) ? $entry->id : 0, 'entries' => $entries));
        }

        $errors = $this->workflow->checkFieldAndLayout($flow->module);
        if(!empty($errors))
        {
            $this->view->errors     = $errors;
            $this->view->modalWidth = 660;
        }
        else
        {
            $this->view->apps       = $this->workflow->getApps();
            $this->view->menus      = $this->workflow->getAppMenus($flow->app, $flow->module);
            $this->view->flow       = $flow;
            $this->view->modalWidth = 500;
        }

        $this->view->title = $this->lang->workflow->release;
        $this->display();
    }

    /**
     * Deactivate flow.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function deactivate($id)
    {
        $this->dao->update(TABLE_WORKFLOW)->set('status')->eq('pause')->where('id')->eq($id)->exec();
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success'));
    }

    /**
     * Activate flow.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function activate($id)
    {
        $this->dao->update(TABLE_WORKFLOW)->set('status')->eq('normal')->where('id')->eq($id)->exec();
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success'));
    }

    /**
     * Delete a flow or table.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function delete($id)
    {
        $this->workflow->delete($id);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success', 'message' => $this->lang->deleteSuccess, 'locate' => 'reload'));
    }

    /**
     * Set js.
     *
     * @param  int    $id
     * @param  string $type     flow | action
     * @access public
     * @return void
     */
    public function setJS($id, $type = 'flow')
    {
        if($_POST)
        {
            $this->workflow->setJS($id, $type);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $closeModal = $type == 'action' ? 'true' : '';
            $reload     = $type == 'flow' ? 'reload' : '';
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => $closeModal, 'locate' => $reload));
        }

        if($type == 'action')
        {
            $action = $this->loadModel('workflowaction')->getByID($id);
            $flow   = $this->workflow->getByModule($action->module);
        }
        else
        {
            $flow = $this->workflow->getByID($id);
        }

        $table = $type == 'flow' ? TABLE_WORKFLOW : TABLE_WORKFLOWACTION;

        $this->view->title      = $this->lang->workflow->setJS;
        $this->view->js         = $this->dao->select('js')->from($table)->where('id')->eq($id)->fetch('js');
        $this->view->type       = $type;
        $this->view->id         = $id;
        $this->view->flow       = $flow;
        $this->view->editorMode = 'advanced';
        $this->display();
    }

    /**
     * Set css.
     *
     * @param  int    $id
     * @param  string $type     flow | action
     * @access public
     * @return void
     */
    public function setCSS($id, $type = 'flow')
    {
        if($_POST)
        {
            $this->workflow->setCSS($id, $type);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $closeModal = $type == 'action' ? 'true' : '';
            $reload     = $type == 'flow' ? 'reload' : '';
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => $closeModal, 'locate' => $reload));
        }

        if($type == 'action')
        {
            $action = $this->loadModel('workflowaction')->getByID($id);
            $flow   = $this->workflow->getByModule($action->module);
        }
        else
        {
            $flow = $this->workflow->getByID($id);
        }

        $table = $type == 'flow' ? TABLE_WORKFLOW : TABLE_WORKFLOWACTION;

        $this->view->title      = $this->lang->workflow->setCSS;
        $this->view->css        = $this->dao->select('css')->from($table)->where('id')->eq($id)->fetch('css');
        $this->view->type       = $type;
        $this->view->id         = $id;
        $this->view->flow       = $flow;
        $this->view->editorMode = 'advanced';
        $this->display();
    }

    /**
     * Set fulltext search of a flow.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function setFulltextSearch($id)
    {
        if($_POST)
        {
            $this->workflow->setFulltextSearch($id);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $flow = $this->workflow->getByID($id);

        $this->view->title      = $this->lang->workflow->fullTextSearch->common;
        $this->view->fields     = array('') + $this->loadModel('workflowfield', 'flow')->getFieldPairs($flow->module);
        $this->view->flow       = $flow;
        $this->view->editorMode = 'advanced';
        $this->display();
    }

    /**
     * Set approval.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function setApproval($module)
    {
        if(empty($this->config->openedApproval)) $this->locate(inlink('browseflow'));

        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $result = $this->workflow->setApproval($module);
            if($result['result'] != 'success') return $this->send($result);

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }
        $this->app->loadLang('workflowrelation');

        $this->view->title         = $this->lang->workflow->setApproval;
        $this->view->flow          = $this->loadModel('workflow', 'flow')->getByModule($module);
        $this->view->approvalFlows = $this->loadModel('approvalflow')->getPairs('workflow');
        $this->view->approvalFlow  = $this->dao->select('flow')->from(TABLE_APPROVALFLOWOBJECT)->where('objectType')->eq($module)->fetch('flow');
        $this->view->editorMode    = 'advanced';
        $this->display();
    }

    /**
     * Build index for full text search.
     *
     * @param  string $module
     * @param  int    $lastID
     * @access public
     * @return void
     */
    public function buildIndex($module, $lastID = 0)
    {
        $flow = $this->workflow->getByModule($module);
        if($flow->buildin or !$flow->titleField) return $this->send(array('result' => 'finished', 'message' => $this->lang->workflow->error->buildIndexFail));

        $result = $this->loadModel('search')->buildAllIndex($module, $lastID);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        if(isset($result['finished']) and $result['finished'])
        {
            return $this->send(array('result' => 'finished', 'message' => $this->lang->search->buildSuccessfully));
        }
        else
        {
            return $this->send(array('result' => 'unfinished', 'message' => sprintf($this->lang->search->buildResult, zget($this->lang->searchObjects, $module, $module), $result['count']), 'next' => inlink('buildIndex', "module={$module}&lastID={$result['lastID']}")));
        }
    }

    /**
     * Save flowchart by ajax.
     *
     * @param  string    $module
     * @access public
     * @return void
     */
    public function ajaxSaveFlowchart($module)
    {
        $chartItems = $this->workflow->processFlowchart($this->post->data);

        $this->loadModel('workflowaction', 'flow')->saveActions($module, $chartItems);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        $this->workflow->saveFlowchart($module, $chartItems);

        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'flowchart' => helper::jsonEncode($chartItems)));
    }

    /**
     * Get menus of an app by ajax.
     *
     * @param  string   $app
     * @param  string   $exclude    The exclude menu, separated by comma.
     * @access public
     * @return string
     */
    public function ajaxGetAppMenus($app, $exclude = '')
    {
        $html  = '';
        $menus = $this->workflow->getAppMenus($app, $exclude);
        foreach($menus as $key => $label)
        {
            $html .= "<option value='{$key}'>{$label}</option>";
        }
        die($html);
    }
}
