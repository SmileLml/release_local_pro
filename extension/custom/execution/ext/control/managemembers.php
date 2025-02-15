<?php
  
class myexecution extends execution
{
    /**
     * Manage members of the execution.
     *
     * @param  int    $executionID
     * @param  int    $team2Import    the team to import.
     * @param  int    $dept
     * @access public
     * @return void
     */
    public function manageMembers($executionID = 0, $team2Import = 0, $dept = 0)
    {
        if(!empty($_POST))
        {
            $this->execution->manageMembers($executionID);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action')->create('team', $executionID, 'managedTeam');
            $link = $this->session->teamList ? $this->session->teamList : $this->createLink('execution', 'team', "executionID=$executionID");
            return $this->send(array('message' => $this->lang->saveSuccess, 'result' => 'success', 'locate' => $link));
        }

        /* Load model. */
        $this->loadModel('user');
        $this->loadModel('dept');

        $execution = $this->execution->getById($executionID);
        $users     = $this->user->getPairs('noclosed|nodeleted|devfirst');
        $roles     = $this->user->getUserRoles(array_keys($users));
        $deptUsers = empty($dept) ? array() : $this->dept->getDeptUserPairs($dept);

        $currentMembers = $this->execution->getTeamMembers($executionID);
        $members2Import = $this->execution->getMembers2Import($team2Import, array_keys($currentMembers));
        $teams2Import   = $this->loadModel('personnel')->getCopiedObjects($executionID, 'sprint', true);
        $teams2Import   = array('' => '') + $teams2Import;

        /* Append users for get users. */
        $appendUsers = array();
        foreach($currentMembers as $member) $appendUsers[$member->account] = $member->account;
        foreach($members2Import as $member) $appendUsers[$member->account] = $member->account;
        foreach($deptUsers as $deptAccount => $userName) $appendUsers[$deptAccount] = $deptAccount;

        $users = $this->user->getPairs('noclosed|nodeleted|devfirst', $appendUsers);
        $roles = $this->user->getUserRoles(array_keys($users));

        /* Set menu. */
        $this->execution->setMenu($execution->id);
        if(!empty($this->config->user->moreLink)) $this->config->moreLinks["accounts[]"] = $this->config->user->moreLink;

        if($execution->type == 'kanban') $this->lang->execution->copyTeamTitle = str_replace($this->lang->execution->common, $this->lang->execution->kanban, $this->lang->execution->copyTeamTitle);

        $title      = $this->lang->execution->manageMembers . $this->lang->colon . $execution->name;
        $position[] = html::a($this->createLink('execution', 'browse', "executionID=$executionID"), $execution->name);
        $position[] = $this->lang->execution->manageMembers;
        $project    = $this->loadModel('project')->getById($execution->project);

        $this->view->title          = $title;
        $this->view->position       = $position;
        $this->view->project        = $project;
        $this->view->execution      = $execution;
        $this->view->users          = $users;
        $this->view->deptUsers      = $deptUsers;
        $this->view->roles          = $roles;
        $this->view->dept           = $dept;
        $this->view->depts          = array('' => '') + $this->loadModel('dept')->getOptionMenu();
        $this->view->currentMembers = $currentMembers;
        $this->view->members2Import = $members2Import;
        $this->view->teams2Import   = $teams2Import;
        $this->view->team2Import    = $team2Import;

        $this->view->canBeChanged   = common::canModify('execution', $execution, $this->view->project);

        $this->display();
    }
}