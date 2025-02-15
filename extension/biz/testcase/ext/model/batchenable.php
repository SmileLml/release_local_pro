<?php
function batchEnable($caseIdList, $auto)
    {
        $now     = helper::now();
        $actions = array();
        $this->loadModel('action');
        $case = new stdClass();
        $case->lastEditedBy   = $this->app->user->account;
        $case->lastEditedDate = $now;
        $case->auto          = $auto;
        $this->dao->update(TABLE_CASE)->data($case)->autoCheck()->where('id')->in($caseIdList)->exec();
		$this->dao->update(TABLE_CASE)->data($case)->autoCheck()->where('fromCaseID')->in($caseIdList)->exec();
    }
