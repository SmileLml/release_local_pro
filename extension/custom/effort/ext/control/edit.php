<?php

class myeffort extends effort
{
    /**
     * Edit a effort.
     *
     * @param  int    $effortID
     * @access public
     * @return void
     */
    public function edit($effortID)
    {
        if(!empty($_POST))
        {
            $changes = $this->effort->update($effortID);
            if(dao::isError()) die(js::error(dao::getError()));
            if($changes)
            {
                $actionID = $this->loadModel('action')->create('effort', $effortID, 'edited');
                $this->action->logHistory($actionID, $changes);
            }

            if(dao::isError()) die(js::error(dao::getError()));
            if(isonlybody())   die(js::reload('parent.parent'));

            $url = $this->session->effortList ? $this->session->effortList : inlink('view', "effortID=$effortID");
            die(js::locate($url, 'parent'));
        }

        /* Judge a private effort or not, If private, die. */
        $effort = $this->effort->getById($effortID);

        if($effort->objectType === 'bug') $effort->execution = $this->dao->select('execution')->from(TABLE_BUG)->where('id')->eq($effort->objectID)->fetch('execution');


        $url = $this->session->effortList ? $this->session->effortList : inlink('view', "effortID=$effortID");
        if(isset($this->config->effort->createEffortType) && strpos( $this->config->effort->createEffortType, $effort->objectType) === false) die(js::alert($this->lang->effort->batchEditEffortTypeError) . js::locate($url, 'parent'));

        $effort->date       = (int)$effort->date == 0 ? $effort->date : substr($effort->date, 0, 4) . '-' . substr($effort->date, 4, 2) . '-' . substr($effort->date, 6, 2);
        $projectCloseFilter = isset($this->config->CRProject) && empty($this->config->CRProject);
        $executions         = $this->loadModel('execution')->getPairs(0, 'all', 'noclosed|leaf' . ($projectCloseFilter ? '|projectclosefilter' : ''));

        /* Get the id of the latest date effort. */
        $recentDateID = 0;
        if($effort->objectType === 'task')
        {
            $recentDateID = $this->dao->select('*')->from(TABLE_EFFORT)->where('objectType')->eq('task')->andWhere('objectID')->eq($effort->objectID)->andWhere('deleted')->eq(0)->orderBy('`date` desc,`id` desc')->limit(1)->fetch('id');
            $executions   = $this->execution->getPairs($effort->project, 'all', 'noclosed|leaf' . ($projectCloseFilter ? '|projectclosefilter' : ''));
            $this->view->task = $this->loadModel('task')->getById($effort->objectID);
        }

        $this->view->canBeChanged = true;

        /* Add project name. */
        if($effort->project)
        {
            $project = $this->loadModel('project')->getById($effort->project);
            foreach($executions as $id => $name)
            {
                $executions[$id] = $project->name . $name;
                if(empty($project->multiple)) $executions[$id] = $project->name . "({$this->lang->project->disableExecution})";
            }

            $this->view->project = $project;
            $this->view->canBeChanged = common::canModify('project', $project);
        }

        $objectName = zget($this->lang->effort->objectTypeList, $effort->objectType);
        if($effort->objectType == 'task')
        {
            $object     = $this->dao->findById($effort->objectID)->from(TABLE_TASK)->fetch();
            $objectName = $object->name;
            $this->view->consumed = $object->consumed;
            $this->view->estimate = $object->estimate;
        }
        if($effort->objectType == 'bug')   $objectName = $this->dao->findById($effort->objectID)->from(TABLE_BUG)->fetch('title');

        if($effort->execution)
        {
            $execution = $this->execution->getByID($effort->execution);
            if(!empty($execution->status) and $execution->status == 'closed') $executions += array($execution->id => $execution->name);
        }

        /* Remove duplicate case. */
        unset($this->lang->effort->objectTypeList['testcase']);

        $this->view->title         = $this->lang->my->common . $this->lang->colon . $this->lang->effort->edit;
        $this->view->position[]    = $this->lang->effort->edit;
        $this->view->products      = $this->loadModel('product')->getPairs();
        $this->view->executions    = $executions;
        $this->view->objectName    = $objectName;
        $this->view->effort        = $effort;
        $this->view->recentDateID  = $recentDateID;
        $this->view->hoursConsumed = $this->loadModel('effort')->getAccountStatistics('', $effort->date);
        $this->display();
    }
}
