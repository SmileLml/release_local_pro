<?php
/**
 * The control file of workflowlinkage module of ZDOO.
 *
 * @copyright   Copyright 2009-2018 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowlinkage
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowlinkage extends control
{
    /**
     * Browse linkage list.
     *
     * @param  int    $action
     * @access public
     * @return void
     */
    public function browse($action)
    {
        $action = $this->loadModel('workflowaction', 'flow')->getByID($action);

        $this->view->title      = $this->lang->workflowlinkage->browse;
        $this->view->flow       = $this->loadModel('workflow', 'flow')->getByModule($action->module);
        $this->view->fields     = $this->loadModel('workflowaction', 'flow')->getFields($action->module, $action->action);
        $this->view->actions    = $this->workflowaction->getList($action->module);
        $this->view->action     = $action;
        $this->view->modalWidth = 1100;
        $this->display();
    }

    /**
     * Create a linkage.
     *
     * @param  int    $action
     * @access public
     * @return void
     */
    public function create($action)
    {
        if($_POST)
        {
            $this->workflowlinkage->create($action);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
                
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "action=$action")));
        }

        $action = $this->loadModel('workflowaction', 'flow')->getByID($action);

        $this->view->title  = $action->name . $this->lang->minus . $this->lang->workflowlinkage->create ;
        $this->view->flow   = $this->loadModel('workflow', 'flow')->getByModule($action->module);
        $this->view->fields = array('' => '') + $this->loadModel('workflowlayout', 'flow')->getFields($action->module, $action->action);
        $this->view->action = $action;
        $this->display();
    }

    /**
     * Edit a linkage.
     *
     * @param  int    $action
     * @param  int    $key
     * @access public
     * @return void
     */
    public function edit($action, $key)
    {
        if($_POST)
        {
            $this->workflowlinkage->update($action, $key);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
                
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "action=$action")));
        }

        $action = $this->loadModel('workflowaction', 'flow')->getByID($action);

        $this->view->title  = $this->lang->workflowlinkage->edit;
        $this->view->flow   = $this->loadModel('workflow', 'flow')->getByModule($action->module);
        $this->view->fields = array('' => '') + $this->loadModel('workflowlayout', 'flow')->getFields($action->module, $action->action);
        $this->view->action = $action;
        $this->view->key    = $key;
        $this->display();
    }

    /**
     * Delete a linkage.
     *
     * @param  int    $action
     * @param  int    $key
     * @access public
     * @return void
     */
    public function delete($action, $key)
    {
        $this->workflowlinkage->delete($action, $key);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success'));
    }
}
