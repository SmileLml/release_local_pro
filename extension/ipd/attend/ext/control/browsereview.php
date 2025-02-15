<?php
helper::importControl('attend');
class myAttend extends attend
{
    /**
     * browse review list.
     *
     * @param  int    $dept
     * @access public
     * @return void
     */
    public function browseReview($dept = '')
    {
        $attends  = array();
        $deptList = $this->loadModel('dept')->getPairs('', 'dept');
        $deptList['0'] = '/';
        /* Get deptments managed by me. */
        if($this->app->user->admin == 'super' or (!empty($this->config->attend->reviewedBy) && $this->config->attend->reviewedBy == $this->app->user->account))
        {
            $attends = $this->attend->getWaitAttends();
        }
        else
        {
            $managedDepts = $this->loadModel('dept')->getDeptManagedByMe($this->app->user->account);
            if($managedDepts) $attends = $this->attend->getWaitAttends(array_keys($managedDepts));
        }

        /* Get users info. */
        $users    = $this->loadModel('user')->getList('all');
        $newUsers = array();
        foreach($users as $key => $user) $newUsers[$user->account] = $user;

        $this->view->title    = $this->lang->attend->review;
        $this->view->users    = $newUsers;
        $this->view->attends  = $attends;
        $this->view->deptList = $deptList;
        $this->display();
    }
}
