<?php
/**
 * The control file of workflowlayout module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowlayout
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowlayout extends control
{
    /**
     * Admin layout of an aciton.
     *
     * @param  string $module
     * @param  string $action
     * @param  string $mode
     * @access public
     * @return void
     */
    public function admin($module, $action, $mode = 'view')
    {
        if($this->server->request_method == 'POST')
        {
            $this->workflowlayout->save($module, $action);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('admin', "module=$module&action=$action")));
        }

        /* Check custom fields. */
        $currentFlow  = $this->loadModel('workflow', 'flow')->getByModule($module);
        $customFields = $this->loadModel('workflowfield', 'flow')->getCustomFields($module);
        if(!$customFields)
        {
            $this->view->title             = $this->lang->workflowlayout->admin;
            $this->view->module            = $module;
            $this->view->emptyCustomFields = true;
            die($this->display());
        }

        /* Process actions can admin layout and get fields created by action. */
        $actionFields = array();
        $actions      = $this->loadModel('workflowaction', 'flow')->getList($module);
        foreach($actions as $key => $actionObject)
        {
            $actionBy   = $actionObject->action . 'By';
            $actionDate = $actionObject->action . 'Date';

            $actionFields[$actionBy]   = $actionBy;
            $actionFields[$actionDate] = $actionDate;
        }

        /* Remove built-in fields when extend a built-in action. */
        $fields = $this->workflowaction->getFields($module, $action);
        $action = $this->workflowaction->getByModuleAndAction($module, $action);
        if($action && $action->buildin && $action->extensionType == 'extend')
        {
            foreach($fields as $key => $field)
            {
                if($field->buildin == '1') unset($fields[$key]);
            }
        }

        /* Set default position. */
        $defaultPositions = array();
        $defaultFields    = array_keys($this->config->workflowfield->default->fields);
        foreach($fields as $key => $field)
        {
            $defaultPositions[$field->field] = 'info';
            if(in_array($field->field, $defaultFields)) $defaultPositions[$field->field] = 'basic';
            if(in_array($field->field, $actionFields))  $defaultPositions[$field->field] = 'basic';
        }

        $flowPairs   = array();
        $subTables   = array();
        $prevModules = array();
        $flows       = $this->workflow->getList('browse', $type = '');  // Must assign a empty string to the parameter $type.
        $relations   = $this->loadModel('workflowrelation', 'flow')->getPrevList($module, 'prev');

        /* Process related datas. */
        foreach($flows as $flow)
        {
            /* Process flow pairs. */
            $flowPairs[$flow->module] = $flow->name;

            if(!$currentFlow->buildin && $flow->type == 'flow' && $flow->status == 'normal')
            {
                /* Process prev modules. */
                if(isset($relations[$flow->module]))
                {
                    $relation = $relations[$flow->module];
                    if(strpos(",$relation->actions,", ',many2one,') === false) $prevModules[$flow->module] = $this->workflowrelation->getLayoutFields($flow->module, $module, $action->action);
                }
            }

            /* Process sub tables. */
            if($flow->type == 'table' && $flow->parent == $module)
            {
                $subTables['sub_' . $flow->module] = $this->workflowaction->getFields($flow->module, $action->action);
            }
        }

        $positionList = zget($this->lang->workflowlayout->positionList, $action->method, array());
        if($action->method == 'view')
        {
            $blocks = json_decode($action->blocks);
            $positionList = $this->loadModel('workflowaction', 'flow')->getPositionList($blocks);
        }

        if($action->method == 'view')
        {
            $sortedFields = array();
            foreach($positionList as $positionKey => $positionName)
            {
                foreach($fields as $key => $field)
                {
                    if($field->position == $positionKey) $sortedFields[$key] = $field;
                }
            }

            foreach($fields as $key => $field)
            {
                if(!isset($positionList[$field->position])) $sortedFields[$key] = $field;
            }
        }

        $this->view->title            = $this->lang->workflowlayout->admin . ' - ' . $action->name;
        $this->view->fields           = $action->method == 'view' ? $sortedFields : $fields;
        $this->view->action           = $action;
        $this->view->rules            = $this->loadModel('workflowrule', 'flow')->getPairs();
        $this->view->flow             = $currentFlow; 
        $this->view->flowPairs        = $flowPairs;
        $this->view->subTables        = $subTables;
        $this->view->prevModules      = $prevModules;
        $this->view->defaultPositions = $defaultPositions;
        $this->view->positionList     = $positionList;
        $this->view->mode             = $mode;
        $this->view->modalWidth       = 1100;
        $this->display();
    }

    /**
     * Set block in right part.
     * 
     * @access public
     * @return void
     */
    public function block($module)
    {
        $action = $this->loadModel('workflowaction')->getByModuleAndAction($module, 'view');

        if($_POST)
        {
            $blocks = $action->blocks ? json_decode($action->blocks, true) : array();
            $this->workflowlayout->saveBlocks($module, $blocks);

            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $this->view->title  = $this->lang->workflowlayout->block;
        $this->view->action = $action;
        $this->display();
    }
}
