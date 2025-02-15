<?php

class myproject extends project
{
    /**
     * Browse team of a project.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function team($projectID = 0)
    {
        $this->session->set('teamList', $this->app->getURI(true), 'project');

        $this->app->loadLang('execution');
        $this->project->setMenu($projectID);

        $project = $this->project->getById($projectID);
        $deptID  = $this->app->user->admin ? 0 : $this->app->user->dept;

        $this->view->title        = $project->name . $this->lang->colon . $this->lang->project->team;
        $this->view->project      = $project;
        $this->view->projectID    = $projectID;
        $this->view->teamMembers  = $this->project->getTeamMembers($projectID);
        $this->view->deptUsers    = $this->loadModel('dept')->getDeptUserPairs($deptID, 'id');

        $this->view->canBeChanged = common::canModify('project', $project);

        $this->display();
    }
}