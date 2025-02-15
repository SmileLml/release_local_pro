<?php
helper::importControl('attend');
class myAttend extends attend
{
    /**
     * Browse attend.
     *
     * @param  string $date
     * @param  bool   $company
     * @access public
     * @return void
     */
    public function browse($date = '', $company = false)
    {
        if($date == '' or strlen($date) != 6) $date = date('Ym');
        $currentYear  = substr($date, 0, 4);
        $currentMonth = substr($date, 4, 2);
        $startDate    = "{$currentYear}-{$currentMonth}-01";
        $endDate      = date('Y-m-d', strtotime("$startDate +1 month"));

        $dayNum    = (int)date('d', strtotime("$endDate -1 day"));
        $weekNum   = (int)ceil($dayNum / 7);
        $monthList = $this->attend->getAllMonth($type = $company ? 'company' : 'department');
        $yearList  = array_keys($monthList);

        /* Get deptList. */
        if($company)
        {
            $deptList = $this->loadModel('dept')->getPairs('', 'dept');
            $deptList[0] = '/';
        }
        else
        {
            $deptList = array();
            $depts    = $this->loadModel('dept')->getListByType('dept');

            if($this->app->user->admin) $user = $this->loadModel('user')->getById($this->app->user->account);

            foreach($depts as $dept)
            {
                /* Only the department manager and the super administrator of the department can see the department attendance. */
                if(strpos(",$dept->manager,", ",{$this->app->user->account},") === false and !$this->app->user->admin) continue;
                if($this->app->user->admin and $user->dept != $dept->id and strpos(",$dept->manager,", ",{$this->app->user->account},") === false) continue;

                /* Get family of current dept. */
                foreach($depts as $d)
                {
                    if(strpos($d->path, $dept->path) === 0) $deptList[$d->id] = $d->name;
                }
            }
        }

        /* Get attend. */
        $attends = array();
        if(!empty($deptList))
        {
            $dept = array_keys($deptList);
            $attends = $this->attend->getByDept($dept, $startDate, $endDate < helper::today() ? $endDate : helper::today());
        }

        $users    = $this->loadModel('user')->getList();
        $newUsers = array();
        foreach($users as $key => $user) $newUsers[$user->account] = $user;

        $this->view->title        = $this->lang->attend->department;
        $this->view->attends      = $attends;
        $this->view->dayNum       = $dayNum;
        $this->view->weekNum      = $weekNum;
        $this->view->currentYear  = $currentYear;
        $this->view->currentMonth = $currentMonth;
        $this->view->yearList     = $yearList;
        $this->view->monthList    = $monthList;
        $this->view->deptList     = $deptList;
        $this->view->users        = $newUsers;
        $this->view->company      = $company;
        $this->display();
    }
}
