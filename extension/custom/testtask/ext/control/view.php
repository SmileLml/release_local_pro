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
    public function view($taskID)
    {
        /* Get test task, and set menu. */
        $taskID = (int)$taskID;
        $task   = $this->testtask->getById($taskID, true);
        if (!$task) {
            if (defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => '404 Not found'));
            die(js::error($this->lang->notFound) . js::locate($this->createLink('qa', 'index')));
        }

        /* When the session changes, you need to query the related products again. */
        if ($this->session->project != $task->project) $this->view->products = $this->products = $this->product->getProductPairsByProject($task->project);
        $this->session->project = $task->project;
        $autoruns = $this->dao->select('t2.*,t1.*, t2.version as caseVersion,t3.title as storyTitle,t2.status as caseStatus')->from(TABLE_TESTRUN)->alias('t1')
        ->leftJoin(TABLE_CASE)->alias('t2')->on('t1.case = t2.id')
        ->leftJoin(TABLE_STORY)->alias('t3')->on('t2.story = t3.id')
        ->where('t1.task')->eq($task->id)
        ->andWhere('t2.auto')->eq('enable')
        ->andWhere('t2.deleted')->eq(0)
        ->fetchAll('id');
        $count = count($autoruns);
        $oldtestTask = $this->dao->select('*')->from(TABLE_TESTTASK)->where('id')->eq((int)$taskID)->fetch();
        $oldtestTask->autocount = $count;
		if($oldtestTask->color != 'red'){
        $oldtestTask->color = 'green';
		}
        if ($count == 0) {
            $oldtestTask->color = '';
        }
        $this->dao->update(TABLE_TESTTASK)->data($oldtestTask)
        ->autoCheck()
        ->where('id')->eq((int)$taskID)
        ->exec();
        $productID = $task->product;
        $buildID   = $task->build;
        if ($task->color == 'red') {
            $actionID = $this->loadModel('action')->create('testtask', $taskID, 'autofind', '');
            $notify = new stdclass();
            include_once('autotest.php');
            $lefttime = 1000;
            $autotest = new autotest();
            $ops = array('zentao_task'=>(int)$taskID);
            $lefttime = $autotest->getleft($ops);
            $notify->objectType  = 'message';
            $notify->action      = $actionID;
            $notify->toList      = "," . $this->app->user->account . ",";
            $notify->data        = "当前测试单执行完成还需".$lefttime['lefttime'].'s';
            $notify->status      = 'wait';
            $notify->createdBy   = $this->app->user->account;
            $notify->createdDate = helper::now();
            $this->dao->insert(TABLE_NOTIFY)->data($notify)->exec();
        }
        $build   = $this->loadModel('build')->getByID($buildID);
        $stories = array();
        $bugs    = array();

        if ($build) {
            $stories = $this->dao->select('*')->from(TABLE_STORY)->where('id')->in($build->stories)->fetchAll();
            $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'story');

            $bugs    = $this->dao->select('*')->from(TABLE_BUG)->where('id')->in($build->bugs)->fetchAll();
            $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'bug');
        }

        if ($this->app->tab == 'project') {
            $this->loadModel('project')->setMenu($task->project);
        } elseif ($this->app->tab == 'execution') {
            $this->loadModel('execution')->setMenu($task->execution);
        } elseif ($this->app->tab == 'qa') {
            $this->loadModel('qa')->setMenu($this->products, $productID, $task->branch, $taskID);
        }

        $this->executeHooks($taskID);

        $this->view->title      = "TASK #$task->id $task->name/" . $this->products[$productID];
        $this->view->position[] = html::a($this->createLink('testtask', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[] = $this->lang->testtask->common;
        $this->view->position[] = $this->lang->testtask->view;

        $this->view->productID       = $productID;
        $this->view->task            = $task;
        $this->view->users           = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->actions         = $this->loadModel('action')->getList('testtask', $taskID);
        $this->view->build           = $build;
        $this->view->testreportTitle = $this->dao->select('title')->from(TABLE_TESTREPORT)->where('id')->eq($task->testreport)->fetch('title');
        $this->view->stories         = $stories;
        $this->view->bugs            = $bugs;
        $this->display();
    }
}