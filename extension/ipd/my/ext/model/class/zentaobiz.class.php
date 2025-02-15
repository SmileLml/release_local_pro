<?php
class zentaobizMy extends myModel
{
    /**
     * Get reviewing attends
     *
     * @param  array    $allDeptList
     * @param  array    $managedDeptList
     * @access public
     * @return array
     */
    public function getReviewingAttends($allDeptList, $managedDeptList)
    {
        $this->loadModel('attend');
        $account  = $this->app->user->account;
        $deptList = array();
        if(!empty($this->config->attend->reviewedBy) and $this->config->attend->reviewedBy == $account) $deptList = $allDeptList;
        if(empty($this->config->attend->reviewedBy)) $deptList = $managedDeptList;

        $deptList = array_keys($deptList);
        if($this->app->user->admin) $deptList = '';
        if(!$this->app->user->admin and empty($deptList)) return array();
        return $this->attend->getWaitAttends($deptList);
    }

    /**
     * Get reviewing leaves.
     *
     * @param  array  $allDeptList
     * @param  array  $managedDeptList
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getReviewingLeaves($allDeptList, $managedDeptList, $orderBy = 'status')
    {
        $account    = $this->app->user->account;
        $deptList   = array();
        $leaves     = array();
        $reviewedBy = $this->loadModel('leave')->getReviewedBy();
        if($reviewedBy and $reviewedBy == $account) $deptList = $allDeptList;
        if(!$reviewedBy) $deptList = $managedDeptList;

        $deptList = array_keys($deptList);
        if($this->app->user->admin) $deptList = '';
        if(!$this->app->user->admin and empty($deptList)) return array();
        $leaves = $this->leave->getList('browseReview', $year = '', $month = '', '', $deptList, $status = 'wait,pass', $orderBy);
        foreach($leaves as $id => $leave)
        {
            if(!$this->leave->isClickable($leave, 'review')) unset($leaves[$id]);
        }
        return $leaves;
    }

    /**
     * Get reviewing overtimes.
     *
     * @param  array  $allDeptList
     * @param  array  $managedDeptList
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getReviewingOvertimes($allDeptList, $managedDeptList, $orderBy = 'status')
    {
        $account    = $this->app->user->account;
        $deptList   = array();
        $overtimes  = array();
        $reviewedBy = $this->loadModel('overtime')->getReviewedBy();
        if($reviewedBy and $reviewedBy == $account) $deptList = $allDeptList;
        if(!$reviewedBy) $deptList = $managedDeptList;

        $deptList = array_keys($deptList);
        if($this->app->user->admin) $deptList = '';
        if(!$this->app->user->admin and empty($deptList)) return array();
        $overtimes = $this->overtime->getList('browseReview', $year = '', $month = '', '', $deptList, $status = 'wait', $orderBy);
        foreach($overtimes as $id => $overtime)
        {
            if(!$this->overtime->isClickable($overtime, 'review')) unset($overtimes[$id]);
        }
        return $overtimes;
    }

    /**
     * Get reviewing makeups.
     *
     * @param  array  $allDeptList
     * @param  array  $managedDeptList
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getReviewingMakeups($allDeptList, $managedDeptList, $orderBy = 'status')
    {
        $account    = $this->app->user->account;
        $deptList   = array();
        $makeups    = array();
        $reviewedBy = $this->loadModel('makeup')->getReviewedBy();
        if($reviewedBy and $reviewedBy == $account) $deptList = $allDeptList;
        if(!$reviewedBy) $deptList = $managedDeptList;

        $deptList = array_keys($deptList);
        if($this->app->user->admin) $deptList = '';
        if(!$this->app->user->admin and empty($deptList)) return array();
        $makeups = $this->makeup->getList('browseReview', $year = '', $month = '', '', $deptList, $status = 'wait', $orderBy);
        foreach($makeups as $id => $makeup)
        {
            if(!$this->makeup->isClickable($makeup, 'review')) unset($makeups[$id]);
        }
        return $makeups;
    }

    /**
     * Get reviewing lieus.
     *
     * @param  array  $allDeptList
     * @param  array  $managedDeptList
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getReviewingLieus($allDeptList, $managedDeptList, $orderBy = 'status')
    {
        $account    = $this->app->user->account;
        $deptList   = array();
        $lieus      = array();
        $reviewedBy = $this->loadModel('lieu')->getReviewedBy();
        if($reviewedBy and $reviewedBy == $account) $deptList = $allDeptList;
        if(!$reviewedBy) $deptList = $managedDeptList;

        $deptList = array_keys($deptList);
        if($this->app->user->admin) $deptList = '';
        if(!$this->app->user->admin and empty($deptList)) return array();
        $lieus = $this->lieu->getList('browseReview', $year = '', $month = '', '', $deptList, $status = 'wait', $orderBy);
        foreach($lieus as $id => $lieu)
        {
            if(!$this->lieu->isClickable($lieu, 'review')) unset($lieus[$id]);
        }
        return $lieus;
    }
}
