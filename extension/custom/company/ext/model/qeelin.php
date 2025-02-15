<?php

/**
 * Get effort.
 *
 * @param  int    $parent
 * @param  date   $begin
 * @param  date   $end
 * @param  int    $product
 * @param  int    $project
 * @param  int    $execution
 * @param  string $account
 * @param  int    $showUser
 * @param  string $userType inside|outside
 *
 * @access public
 * @return array
 */
public function getEffortCustom($parent, $begin, $end, $product = 0, $project = 0,$execution = 0, $account ='', $showUser = 'all', $userType = '')
{
    $this->app->loadClass('date');
    $users = $this->loadModel('dept')->getDeptUserPairs($parent == 'all' ? 0 : $parent, '', '', 'withdeleted');
    if($account) $users = array($account => $account);

    $efforts = $this->dao->select('t1.*,t2.dept')->from(TABLE_EFFORT)->alias('t1')
        ->leftJoin(TABLE_USER)->alias('t2')->on('t1.account=t2.account')
        ->where('t1.date')->ge($begin)
        ->andWhere('t1.vision')->eq($this->config->vision)
        ->andWhere('t1.date')->le($end)
        ->andWhere('t1.deleted')->eq(0)
        ->beginIF($product)->andWhere('t1.product')->like("%,$product,%")->fi()
        ->beginIF($project)->andWhere('t1.project')->eq($project)->fi()
        ->beginIF($execution)->andWhere('t1.execution')->eq($execution)->fi()
        ->beginIF(!empty($users))->andWhere('t1.account')->in(array_keys($users))->fi()
        ->beginIF($userType)->andWhere('t2.type')->eq($userType)->fi()
        ->fetchAll();

    /* Set session. */
    $sql = explode('WHERE', $this->dao->get());
    $sql = explode('ORDER', $sql[1]);
    $this->session->set('effortReportCondition', $sql[0]);

    $actions  = array();
    foreach($efforts as $effort) $actions[$effort->account][] = $effort;

    $depts  = $this->dao->select('*')->from(TABLE_DEPT)->fetchAll();
    $effort = array();
    $selectedUser    = $account;
    $deptRelation[0] = 'current';
    if($parent == 'all')
    {
        foreach($depts as $dept)
        {
            $deptList = explode(',', $dept->path);
            $deptRelation[$dept->id] = $deptList[1];
        }

        $users = $this->dao->select('account, dept')->from(TABLE_USER)
            ->where('1=1')
            ->beginIF($this->config->vision)->andWhere("CONCAT(',', visions, ',')")->like("%,{$this->config->vision},%")->fi()
            ->beginIF($selectedUser)->andWhere('account')->eq($selectedUser)->fi()
            ->beginIF($userType)->andWhere('type')->eq($userType)->fi()
            ->fetchAll();
    }
    else
    {
        $parentDept = $this->loadModel('dept')->getById($parent);
        $childDepts = $this->dept->getAllChildId($parent);

        foreach($depts as $dept)
        {
            if(in_array($dept->id, $childDepts))
            {
                $deptList = explode(',', trim(str_replace($parentDept->path, '', $dept->path), ','));
                $deptRelation[$dept->id] = $dept->id == $parent ? 'current' : $deptList[0];
            }
        }

        $users = $this->dao->select('account, dept')->from(TABLE_USER)
            ->where('dept')->in($childDepts)
            ->beginIF($selectedUser)->andWhere('account')->eq($selectedUser)->fi()
            ->beginIF($userType)->andWhere('type')->eq($userType)->fi()
            ->fetchAll();
    }

    foreach($users as $user)
    {
        $account  = $user->account;
        $deptID   = zget($deptRelation, $user->dept);
        $showUser = strtolower($showUser);

        if($selectedUser and $selectedUser == $account)
        {
            if(!isset($effort[$deptID][$account])) $effort[$deptID][$account] = array();
        }
        else
        {
            if($showUser == 'notlogged' and isset($actions[$account])) continue;
            if($showUser == 'logged' and !isset($actions[$account])) continue;
            $effort[$deptID][$account] = array();
        }

        if(!isset($actions[$account])) continue;

        foreach($actions[$account] as $action)
        {
            $effort[$deptID][$account][$action->date][] = array('id' => $action->id, 'begin' => date::formatTime($action->begin), 'end' => date::formatTime($action->end), 'work' => $action->work, 'consumed' => $action->consumed);
        }
    }

    return $effort;
}