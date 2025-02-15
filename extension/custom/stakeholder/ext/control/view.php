<?php

class mystakeholder extends stakeholder
{
    /**
     * User details.
     *
     * @access public
     * @param  int  $userID
     * @return void
     */
    public function view($userID = 0)
    {
        $user = $this->stakeholder->getByID($userID);
        $this->loadModel('project')->setMenu($user->objectID);

        $this->commonAction($userID, 'stakeholder');

        if(isset($user->objectType) && $user->objectType == 'project')
        {
            $project = $this->project->getByID($user->objectID);
            $this->view->canBeChanged = common::canModify('project', $project);
        }

        $this->view->title      = $this->lang->stakeholder->common . $this->lang->colon . $this->lang->stakeholder->view;
        $this->view->position[] = $this->lang->stakeholder->view;
        $this->view->user       = $user;
        $this->view->users      = $this->loadModel('user')->getTeamMemberPairs($this->session->project, 'project', 'nodeleted');
        $this->view->expects    = $this->stakeholder->getExpectByUser($userID);

        $this->display();
    }
}