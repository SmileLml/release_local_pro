<?php
/**
 * The control file of workflowdatasource module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowdatasource
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowdatasource extends control
{
    /**
     * Browse datasource list.
     *
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->view->title       = $this->lang->workflowdatasource->browse;
        $this->view->datasources = $this->workflowdatasource->getList($orderBy, $pager);
        $this->view->users       = $this->loadModel('user')->getDeptPairs();
        $this->view->orderBy     = $orderBy;
        $this->view->pager       = $pager;
        $this->display();
    }

    /**
     * Create a datasource.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        if($_POST)
        {
            $id = $this->workflowdatasource->create();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action')->create('workflowdatasource', $id, 'Created');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse')));
        }

        $this->view->title = $this->lang->workflowdatasource->create;
        $this->view->apps  = $this->workflowdatasource->getApps();
        $this->display();
    }

    /**
     * Edit a datasource.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function edit($id)
    {
        if($_POST)
        {
            $changes = $this->workflowdatasource->update($id);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('workflowdatasource', $id, 'edited');
                $this->action->logHistory($actionID, $changes);
            }
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse')));
        }

        $datasource = $this->workflowdatasource->getByID($id);

        $this->view->title      = $this->lang->workflowdatasource->edit;
        $this->view->apps       = $this->workflowdatasource->getApps();
        $this->view->modules    = $datasource->type == 'system' ? $this->workflowdatasource->getAppModules($datasource->app) : '';
        $this->view->methods    = $datasource->type == 'system' ? $this->workflowdatasource->getModuleMethods($datasource->app, $datasource->module) : '';
        $this->view->fields     = $this->workflowdatasource->getViewFields($datasource->sql);
        $this->view->datasource = $datasource;
        $this->display();
    }

    /**
     * Delete a datasource.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function delete($id)
    {
        $datasource = $this->workflowdatasource->getByID($id);
        if($datasource->buildin) return $this->send(array('result' => 'success'));

        $this->workflowdatasource->delete($id);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success'));
    }

    /**
     * Get modules of an app by ajax.
     *
     * @param  string $app
     * @access public
     * @return void
     */
    public function ajaxGetAppModules($app)
    {
        $html    = '';
        $modules = $this->workflowdatasource->getAppModules($app);
        foreach($modules as $module => $label)
        {
            $html .= "<option value='{$module}'>{$label}</option>";
        }
        die($html);
    }

    /**
     * Get methods of a module by ajax or not.
     *
     * @param  string $app
     * @param  string $module
     * @access public
     * @return void | array
     */
    public function ajaxGetModuleMethods($app, $module)
    {
        $methods = $this->workflowdatasource->getModuleMethods($app, $module);
        die(helper::jsonEncode($methods));
    }

    /**
     * Get method comment by ajax.
     *
     * @param  string $app
     * @param  string $module
     * @param  string $method
     * @param  int    $isAjax
     * @access public
     * @return void | string
     */
    public function ajaxGetMethodComment($app, $module, $method, $isAjax = true)
    {
        $comment = $this->workflowdatasource->getMethodComments($app, $module, $method, $methodDescOnly = true);
        if(!$isAjax) return $comment;
        echo $comment;
    }

    /**
     * Get params of a method by ajax.
     *
     * @param  string $app
     * @param  string $module
     * @param  string $method
     * @access public
     * @return void | array
     */
    public function ajaxGetMethodParams($app, $module, $method)
    {
        $html = '';
        if(!empty($app) && !empty($module) && !empty($method))
        {
            $comment = $this->workflowdatasource->getMethodComments($app, $module, $method);
            $params  = $this->workflowdatasource->getDefaultParams($app, $module, $method);
            foreach($params as $name => $default)
            {
                $default   = is_array($default) || is_object($default) || is_null($default) ? '' : $default;
                $paramType = isset($comment['param'][$name]['type']) ? $comment['param'][$name]['type'] : '';
                $paramDesc = isset($comment['param'][$name]['desc']) ? $comment['param'][$name]['desc'] : '';

                $html .= "<div class='input-group'>";
                $html .= "<span class='input-group-addon'>" . $this->lang->workflowdatasource->param . "</span>";
                $html .= html::input("paramName[]", $name, "class='form-control' readonly='readonly' title='{$name}'");
                $html .= "<span class='input-group-addon fix-border'>" . $this->lang->workflowdatasource->paramType . "</span>";
                $html .= html::input("paramType[]", $paramType, "class='form-control' readonly='readonly' title='{$paramType}'");
                $html .= "<span class='input-group-addon fix-border'>" . $this->lang->workflowdatasource->desc . "</span>";
                $html .= html::input("paramDesc[]", $paramDesc, "class='form-control' readonly='readonly' title='{$paramDesc}'");
                $html .= "<span class='input-group-addon fix-border'>" . $this->lang->workflowdatasource->paramValue . "</span>";
                $html .= html::input("paramValue[]", $default, "class='form-control'");
                $html .= "</div>";
            }
        }
        echo $html;
    }

    /**
     * Check sql by ajax.
     *
     * @access public
     * @return void
     */
    public function ajaxCheckSql()
    {
        try
        {
            $fieldPairs = $this->workflowdatasource->getViewFields($this->post->sql);

            $options = '<option></option>';
            foreach($fieldPairs as $field => $value) $options .= "<option value='$value'>$value</option>";

            return $this->send(array('result' => 'success', 'options' => $options));
        }
        catch(PDOException $exception)
        {
            return $this->send(array('result' => 'fail', 'message' => $exception->getMessage()));
        }
    }
}
