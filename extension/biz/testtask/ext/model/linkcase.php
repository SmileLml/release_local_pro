<?php
    /**
     * Link cases.
     *
     * @param  int    $taskID
     * @param  string $type
     * @access public
     * @return void
     */
    function linkCase($taskID, $type)
    {
        if($this->post->cases == false) return;
        $postData = fixer::input('post')->get();

        if($type == 'bybuild') $assignedToPairs = $this->dao->select('`case`, assignedTo')->from(TABLE_TESTRUN)->where('`case`')->in($postData)->fetchPairs('case', 'assignedTo');
        $oldtestTask = $this->dao->select('*')->from(TABLE_TESTTASK)->where('id')->eq((int)$taskID)->fetch();
        $count=0;
        // $newtestTask = $oldtestTask;
        foreach($postData->cases as $caseID)
        {
            $case = $this->dao->select('*')->from(TABLE_CASE)->where('id')->eq((int)$caseID)->fetch();
            if($case->auto=='enable'){
                $count = $count + 1;
            }
            $row = new stdclass();
            $row->task       = $taskID;
            $row->case       = $caseID;
            $row->version    = $postData->versions[$caseID];
            $row->assignedTo = '';
            $row->status     = 'normal';
            if($type == 'bybuild') $row->assignedTo = zget($assignedToPairs, $caseID, '');
            $this->dao->replace(TABLE_TESTRUN)->data($row)->exec();

            /* When the cases linked the testtask, the cases link to the project. */
            if($this->app->tab != 'qa')
            {
                $lastOrder = (int)$this->dao->select('*')->from(TABLE_PROJECTCASE)->where('project')->eq($projectID)->orderBy('order_desc')->limit(1)->fetch('order');
                $project   = $this->app->tab == 'project' ? $this->session->project : $this->session->execution;

                $data = new stdclass();
                $data->project = $project;
                $data->product = $this->session->product;
                $data->case    = $caseID;
                $data->version = 1;
                $data->order   = ++ $lastOrder;
                $this->dao->replace(TABLE_PROJECTCASE)->data($data)->exec();
                $this->loadModel('action')->create('case', $caseID, 'linked2testtask', '', $taskID);
            }
        }
        if($count!=0&&$oldtestTask->color！="red"){
        $oldtestTask->autocount = (int)$oldtestTask->autocount+$count;
        $oldtestTask->color = 'green';
        $this->dao->update(TABLE_TESTTASK)->data($oldtestTask)
        ->autoCheck()
        ->where('id')->eq((int)$taskID)
        ->exec();}
    }