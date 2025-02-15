<?php
include '../../control.php'; 
class mytesttask extends testtask{
        /**
     * Batch unlink cases.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function batchUnlinkCases($taskID)
    {
        if(isset($_POST['caseIDList']))
        {
            $this->dao->delete()->from(TABLE_TESTRUN)
                ->where('task')->eq((int)$taskID)
                ->andWhere('`case`')->in($this->post->caseIDList)
                ->exec();
            $this->loadModel('action');
            $oldtestTask = $this->dao->select('*')->from(TABLE_TESTTASK)->where('id')->eq((int)$taskID)->fetch();
            $count=0;
            foreach($_POST['caseIDList'] as $caseID) 
            {
            $case = $this->dao->select('*')->from(TABLE_CASE)->where('id')->eq((int)$caseID)->fetch();
            if($case->auto=='enable') $count = $count + 1;
            $this->action->create('case', $caseID, 'unlinkedfromtesttask', '', $taskID);
            }
            if((int)$oldtestTask->autocount-$count<=0&&$count!=0){
                $oldtestTask->color='';
                $oldtestTask->autocount=0; 
            }
            else{
                $oldtestTask->autocount=(int)$oldtestTask->autocount-$count;
            }
            $this->dao->update(TABLE_TESTTASK)->data($oldtestTask)
            ->autoCheck()
            ->where('id')->eq((int)$taskID)
            ->exec();  
        }

        die(js::locate($this->createLink('testtask', 'cases', "taskID=$taskID")));
    }
}
