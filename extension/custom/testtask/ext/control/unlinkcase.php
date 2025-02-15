<?php
class mytesttask extends testtask
{
    /**
     * Remove a case from test task.
     *
     * @param  int    $rowID
     * @access public
     * @return void
     */
    public function unlinkCase($rowID, $confirm = 'no')
    {
        if ($confirm == 'no') {
            die(js::confirm($this->lang->testtask->confirmUnlinkCase, $this->createLink('testtask', 'unlinkCase', "rowID=$rowID&confirm=yes")));
        } else {
            $response['result']  = 'success';
            $response['message'] = '';

            $testRun = $this->dao->select('task,`case`')->from(TABLE_TESTRUN)->where('id')->eq((int)$rowID)->fetch();
            $case = $this->dao->select('*')->from(TABLE_CASE)->where('id')->eq((int)$testRun->case)->fetch();
            if ($case->auto == 'enable') {
                $oldtestTask = $this->dao->select('*')->from(TABLE_TESTTASK)->where('id')->eq((int)$testRun->task)->fetch();
                if ((int)$oldtestTask->autocount == 1) {
                    $oldtestTask->autocount = 0;
                    $oldtestTask->color = '';
                    $this->dao->update(TABLE_TESTTASK)->data($oldtestTask)
                        ->autoCheck()
                        ->where('id')->eq((int)$testRun->task)
                        ->exec();
                }
                else{
                    $oldtestTask->autocount = (int)$oldtestTask->autocount-1;
                    $this->dao->update(TABLE_TESTTASK)->data($oldtestTask)
                    ->autoCheck()
                    ->where('id')->eq((int)$testRun->task)
                    ->exec();
                }
            }
            $this->dao->delete()->from(TABLE_TESTRUN)->where('id')->eq((int)$rowID)->exec();
            if (dao::isError()) {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
            }
            $this->loadModel('action')->create('case', $testRun->case, 'unlinkedfromtesttask', '', $testRun->task);
            return $this->send($response);
        }
    }
}