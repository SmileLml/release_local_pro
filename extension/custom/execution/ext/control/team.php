<?php

class myexecution extends execution
{
    /**
     * Browse team of a execution.
     *
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function team($executionID = 0)
    {
        $this->app->session->set('teamList', $this->app->getURI(true), 'execution');

        $execution   = $this->commonAction($executionID);
        $executionID = $execution->id;
        $deptID      = $this->app->user->admin ? 0 : $this->app->user->dept;

        $title      = $execution->name . $this->lang->colon . $this->lang->execution->team;
        $position[] = html::a($this->createLink('execution', 'browse', "executionID=$executionID"), $execution->name);
        $position[] = $this->lang->execution->team;

        $this->view->title        = $title;
        $this->view->position     = $position;
        $this->view->deptUsers    = $this->loadModel('dept')->getDeptUserPairs($deptID, 'id');

        $this->view->canBeChanged = common::canModify('execution', $execution, $this->view->project);

        $this->display();
    }
}