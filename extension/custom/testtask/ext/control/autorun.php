<?php
class mytesttask extends testtask
{
    /**
     * autorun.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function autorun($taskID)
    {
        $curtasktest = $this->dao->select('*')->from(TABLE_TESTTASK)->where('id')->eq((int)$taskID)->fetch();
        if (!empty($_POST)) {
            if (empty($_POST['testmachine'])) {
                return $this->send(array('result' => 'fail', 'message' => "请选择测试机"));
            }
            $this->testtask->createautorun($taskID);
            if (dao::isError()) die(js::error(dao::getError()));
            if (isonlybody()) echo js::closeModal('parent.parent', 'this');
            $this->send(array('result' => 'success', 'message' => "创建成功"));
        }
        include_once('autotest.php');
        $ops = array();
        $testmachineList = array(1 => "测试机1", 2 => "测试机2");
        $testmachineList[$myre->module] = $myre->module;
        $count = $this->testtask->checktesttask($taskID);
        $this->view->testmachineList = $testmachineList;
        $this->view->count = $count;
        $this->view->testtask = $curtasktest;
        $this->display();
    }
}