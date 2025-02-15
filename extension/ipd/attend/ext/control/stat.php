<?php
class attend extends control
{
    /**
     * Browse stat of attend.
     *
     * @param  string  $month
     * @param  string  $mode
     * @access public
     * @return void
     */
    public function stat($month = '', $mode = '')
    {
        if(!$month or (strlen($month) != 4 && strlen($month) != 6)) $month = date('Ym');
        $this->app->loadConfig('user');
        $currentYear  = substr($month, 0, 4);
        $currentMonth = substr($month, 4, 2);
        $users        = $this->loadModel('user')->getDeptPairs('noclosed,noempty,noforbidden' . ( (isset($this->config->user->showDeleted) and $this->config->user->showDeleted) ? '' : ',nodeleted'));

        $stat = $this->attend->getStat($month);
        if(!empty($stat))
        {
            $mode = $mode ? $mode : 'view';
            $this->app->loadLang('leave');
            $this->app->loadLang('overtime');
        }
        else
        {
            $mode = 'edit';

            $stat = $this->attend->computeStat($currentYear, $currentMonth, $users);
        }

        $deletedUser = $this->dao->select('*')->from(TABLE_USER)->where('deleted')->eq(1)->fetchPairs('account', 'account');
        foreach($stat as $account => $item)
        {
            if(in_array($account, $deletedUser) and $item->actual == 0) unset($stat[$account]);
        }

        $monthList = $this->attend->getAllMonth($type = 'stat');
        $yearList  = array_keys($monthList);

        $this->view->title        = $this->lang->attend->stat;
        $this->view->waitReviews  = $this->attend->checkWaitReviews($month);
        $this->view->mode         = $mode;
        $this->view->stat         = $stat;
        $this->view->month        = $month;
        $this->view->currentYear  = $currentYear;
        $this->view->currentMonth = $currentMonth;
        $this->view->yearList     = $yearList;
        $this->view->monthList    = $monthList;
        $this->view->users        = $users;
        $this->display();
    }
}
