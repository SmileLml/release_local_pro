<?php
helper::importControl('effort');
class myeffort extends effort
{
    public function batchCreate($date = 'today', $userID = '')
    {
        if(!empty($_POST))
        {
            if($this->app->tab == 'my') $this->effort->batchCreateForMyWork();
            else $this->effort->batchCreate();
            if(dao::isError()) die(js::error(dao::getError()));
            if(isonlybody())   die(js::closeModal('parent.parent', '', "function(){if(typeof(parent.parent.refreshCalendar) == 'function'){parent.parent.refreshCalendar()}else{parent.parent.location.reload(true)}}"));
            die(js::locate($this->createLink('my', 'effort'), 'parent'));
        }

        if($date == 'today') $date   = date(DT_DATE1, time());
        if($userID == '')    $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;
        $actions = $this->effort->getActions($date, $account, $this->config->effort->createEffortType);

        /* Fix bug #18282. */
        $efforts = $this->effort->getList($date, $date, $account);
        foreach($actions as $key => $action)
        {
            if(!isset($action->objectType) or !isset($action->objectID)) continue;
            foreach($efforts as $effort)
            {
                if($effort->objectType == $action->objectType and $effort->objectID == $action->objectID) unset($actions[$key]);
            }
        }

        $typeList = array();
        if(isset($actions['typeList'])) $typeList += $actions['typeList'];

        $executionTask = array();
        $executionBug  = array();
        if(isset($actions['executionTask'])) $executionTask += $actions['executionTask'];
        if(isset($actions['executionBug'])) $executionBug += $actions['executionBug'];

        $status = $this->config->CRExecution ? 'all' : 'noclosed';

        $appendExecutions = empty($actions['executions']) ? array() : $actions['executions'];

        $maxCount      = 50;
        $joinExecution = $this->effort->getJoinExecution($status, $maxCount);

        $recentlyExecution = array();
        if($maxCount - count($joinExecution) > 0) $recentlyExecution = $this->effort->getRecentlyExecutions($status, $maxCount - count($joinExecution), array_keys($joinExecution));

        unset($actions['typeList']);
        unset($actions['executionTask']);
        unset($actions['executionBug']);
        unset($actions['executions']);
        $taskIDs = array();
        foreach($executionTask as $taskID => $execution)
        {
            $taskIDs[] = explode('_', $taskID)[1];
        }
        foreach($actions as $actionID => $action)
        {
            if($action->objectType == 'task') $taskIDs[] = $action->objectID;
        }
        $tasks = $this->loadModel('task')->getByList($taskIDs);
        foreach($tasks as $task)
        {
            $task->oldConsumed = $task->consumed;
        }
        $this->view->title              = $this->lang->my->common . $this->lang->colon . $this->lang->effort->create;
        $this->view->position[]         = $this->lang->effort->create;
        $this->view->date               = !is_numeric($date) ? $date : substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        $this->view->actions            = $actions;
        $this->view->typeList           = array('' => '') + $typeList;
        $this->view->executions         = array('' => '') + $joinExecution + $recentlyExecution + $appendExecutions;
        $this->view->executionTask      = $executionTask;
        $this->view->executionBug       = $executionBug;
        $this->view->tasks              = $tasks;
        $this->view->hoursConsumedToday = $this->loadModel('effort')->getAccountStatistics('', $date);
        $this->display();
    }
}
