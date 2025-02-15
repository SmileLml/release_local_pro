<?php
class attend extends control
{
    public function detail($date = '', $deptID = 0, $account = '')
    {
        if($_POST)
        {
            $deptID  = $this->post->dept;
            $account = $this->post->account;
            $date    = str_replace('-', '', $this->post->date);
        }

        if($date == '' or strlen($date) != 6) $date = date('Ym');
        $currentYear  = substr($date, 0, 4);
        $currentMonth = substr($date, 4, 2);

        $deptList = array('') + $this->loadModel('dept')->getPairs(0, 'dept');
        $userList = $this->loadModel('user')->getDeptPairs('noclosed,nodeleted,noforbidden', $deptID);

        /* Sort data. */
        asort($deptList);
        asort($userList);

        $attends = $this->attend->getDetailAttends($date, $account, $deptID);

        $this->session->set('attendDeptID', $deptID);
        $this->session->set('attendAccount', $account);

        $fileName = '';
        if($deptID)
        {
            $dept = zget($deptList, $deptID, '');
            if($dept) $fileName .= $dept . ' - ';
        }
        if($account)
        {
            $user = zget($userList, $account, '');
            if($user) $fileName .= $user . ' - ';
        }
        $fileName .= $currentYear;
        if($this->app->clientLang != 'en') $fileName .= $this->lang->year;
        $fileName .= $currentMonth;
        if($this->app->clientLang != 'en') $fileName .= $this->lang->month;
        $fileName .= $this->lang->attend->detail;

        $this->view->title        = $this->lang->attend->detail;
        $this->view->dept         = $deptID;
        $this->view->account      = $account;
        $this->view->date         = "{$currentYear}-{$currentMonth}-01";
        $this->view->currentYear  = $currentYear;
        $this->view->currentMonth = $currentMonth;
        $this->view->deptList     = $deptList;
        $this->view->userList     = $userList;
        $this->view->attends      = $attends;
        $this->view->fileName     = $fileName;
        $this->display();
    }
}
