<?php
function checktesttask($taskID)
{
    $autocases = $this->dao->select('t2.*')->from(TABLE_TESTRUN)->alias('t1')
        ->leftJoin(TABLE_CASE)->alias('t2')->on('t1.case = t2.id')
        ->leftJoin(TABLE_STORY)->alias('t3')->on('t2.story = t3.id')
        ->where('t1.task')->eq((int)$taskID)
        ->andWhere('t2.deleted')->eq(0)
        ->andWhere('t2.auto')->eq('enable')
        ->fetchAll('id');
    $count = count($autocases);
    $oldtestTask = $this->dao->select('*')->from(TABLE_TESTTASK)->where('id')->eq((int)$taskID)->fetch();
    $oldtestTask->autocount = $count;
    if ($count == 0) {
        $oldtestTask->color = '';
    }
    $this->dao->update(TABLE_TESTTASK)->data($oldtestTask)
        ->autoCheck()
        ->where('id')->eq((int)$taskID)
        ->exec();
    return $count;
}
