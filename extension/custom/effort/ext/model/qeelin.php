<?php

/**
 * Batch create efforts.
 *
 * @param  date   $date
 * @param  string $account
 * @access public
 * @return void
 */
public function batchCreateForMyWork()
{
    $this->loadModel('task');
    $this->loadModel('action');

    $now        = helper::now();
    $efforts    = fixer::input('post')->get();
    $data       = array();
    $taskIDList = array();
    $today      = helper::today();
    $nonRDUser  = (!empty($_SESSION['user']->feedback) or !empty($_COOKIE['feedbackView'])) ? true : false;
    $consumedAll = $this->getAccountStatistics('', $efforts->date);

    foreach($efforts->id as $id => $num)
    {
        $isBug = strpos($efforts->objectType[$id], 'bug') !== false;
        if(empty($efforts->work[$id]) && empty($efforts->objectType[$id]) && (!$isBug && empty($efforts->execution[$num])) && empty($efforts->consumed[$id]) && empty($efforts->left[$num])) continue;

        if($efforts->objectType[$id] == 'task' and (empty($efforts->date) or helper::isZeroDate($efforts->date))) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->task->error->dateEmpty));

        $efforts->work[$id] = trim($efforts->work[$id]);

        if(empty($efforts->work[$id])) die(js::alert(sprintf($this->lang->effort->nowork, $efforts->id[$id])));

        $efforts->objectType[$id] = trim($efforts->objectType[$id]);
        if(empty($efforts->objectType[$id])) die(js::alert(sprintf($this->lang->effort->noObjectType, $efforts->id[$id])));

        if(strpos($efforts->objectType[$id], '_') === false) die(js::alert(sprintf($this->lang->effort->noFormatObjectType, $efforts->id[$id])));
        $pos = strpos($efforts->objectType[$id], '_');
        $efforts->objectID[$id]   = substr($efforts->objectType[$id], $pos + 1);
        $efforts->objectType[$id] = substr($efforts->objectType[$id], 0, $pos);
        $efforts->execution[$num] = !empty($efforts->execution[$num]) ? trim($efforts->execution[$num]) : 0;

        if($efforts->objectType[$id] == 'bug')
        {
            $effortBug = $this->loadModel('bug')->getByID($efforts->objectID[$id]);
            if(empty($effortBug->execution)) die(js::alert(sprintf($this->lang->effort->bugNoExecution, $efforts->id[$id])));
        }

        if(empty($efforts->execution[$num]) && $efforts->objectType[$id]) die(js::alert(sprintf($this->lang->effort->taskNoExecution, $efforts->id[$id])));

        $left = isset($efforts->left[$num]) ? $efforts->left[$num] : '';
        if(!empty($left) and !is_numeric($left)) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->isNumber));
        if(!empty($left) and $left < 0)          die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->notNegative));
        if($efforts->objectType[$id] == 'task' and !$nonRDUser and empty($left) and !is_numeric($left))  die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->notEmpty));

        if($efforts->objectType[$id] == 'bug')
        {
            $effortBug = $this->loadModel('bug')->getByID($efforts->objectID[$id]);
            if(!isset($effortBug->execution) or empty($effortBug->execution)) die(js::alert(sprintf($this->lang->effort->bugNoExecution, $efforts->id[$id])));
        }

        if(!is_numeric($efforts->consumed[$id])) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->consumed . $this->lang->effort->isNumber));
        $consumed = (float)$efforts->consumed[$id];
        if(empty($consumed)) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->consumed . $this->lang->effort->notEmpty));
        if($consumed < 0)    die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->consumed . $this->lang->effort->notNegative));

        $data[$id] = new stdclass();
        $data[$id]->vision    = $this->config->vision;
        $data[$id]->product   = ',0,';
        $data[$id]->execution = 0;
        $data[$id]->objectID  = 0;

        $data[$id]->date       = $efforts->date;
        $data[$id]->consumed   = $efforts->consumed[$id];
        $data[$id]->account    = $this->app->user->account;
        $data[$id]->work       = $efforts->work[$id];
        $data[$id]->objectType = $efforts->objectType[$id];

        if($data[$id]->date > $now) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->notFuture));

        if($data[$id]->objectType == 'task')
        {
            $taskIDList[$efforts->objectID[$id]] = $efforts->objectID[$id];
            $data[$id]->left                     = (float)$left;
        }

        if(strpos($this->config->effort->createEffortType, $data[$id]->objectType) !== false)
        {
            $consumedAll += $data[$id]->consumed;
            if($consumedAll > $this->config->limitWorkHour) die(js::alert(sprintf($this->lang->effort->hoursConsumedTodayOverflowForALL, $efforts->date)));
        }

        $data[$id]->objectID = $efforts->objectID[$id];

        if($data[$id]->objectID != 0)
        {
            $relation = $this->action->getRelatedFields($data[$id]->objectType, $data[$id]->objectID);
            $data[$id]->product   = $relation['product'];
            $data[$id]->project   = (int)$relation['project'];
            $data[$id]->execution = (int)$relation['execution'];
        }

        if(!empty($efforts->execution[$num]))
        {
            $data[$id]->project   = $this->dao->select('project')->from(TABLE_EXECUTION)->where('id')->eq((int)$efforts->execution[$num])->fetch('project');
            $data[$id]->execution = (int)$efforts->execution[$num];
        }

        if((!empty($efforts->execution[$num])) && ($data[$id]->objectID == 0))
        {
            $products = $this->loadModel('product')->getProducts($efforts->execution[$num]);
            ksort($products);
            $data[$id]->product = ',' . join(',', array_keys($products)) . ',';
        }
    }

    $tasks = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in($taskIDList)->fetchAll('id');
    $executionTeams = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('task')->in($taskIDList)->orderBy('order')->fetchGroup('task');
    $lastDatePairs  = $this->dao->select('objectID,max(date) as date')->from(TABLE_EFFORT)
        ->where('objectID')->in($taskIDList)->andWhere('objectType')->eq('task')
        ->andWhere('deleted')->eq(0)
        ->groupBy('objectID')
        ->fetchPairs('objectID', 'date');

    $now    = helper::now();
    $errors = array();

    $this->loadModel('story');
    $this->loadModel('task');
    $changedTasks = array();

    $otherEffortFields = $this->config->effort->create->requiredFields;
    $requiredFields = explode(',', $this->config->effort->create->requiredFields);
    if(in_array('execution', $requiredFields)) unset($requiredFields[array_search('execution', $requiredFields)]);
    $bugEffortFields = implode(',', $requiredFields);
    foreach($data as $id => $effort)
    {
        $useRequriedFields = $effort->objectType == 'bug' ? $bugEffortFields : $otherEffortFields;
        $this->dao->insert(TABLE_EFFORT)->data($effort)->autoCheck()->batchCheck($useRequriedFields, 'notempty')->exec();
        if(dao::isError())
        {
            $errors[$id] = dao::getError();
            continue;
        }

        $effortID = $this->dao->lastInsertID();
        $this->action->create('effort', $effortID, 'created');

        if(isset($efforts->actionID[$id]))
        {
            $this->dao->update(TABLE_ACTION)->set('efforted')->eq(1)
                ->where('id')->le($efforts->actionID[$id])
                ->andWhere('actor')->eq($this->app->user->account)
                ->andWhere('objectType')->eq($effort->objectType)
                ->andWhere('objectID')->eq($effort->objectID)
                ->andWhere('date')->ge("$effort->date 00:00:00")
                ->andWhere('date')->le("$effort->date 23:59:59")
                ->exec();
        }

        if($effort->objectType == 'bug')
        {
            $this->dao->update(TABLE_BUG)->set('lastEditedDate')->eq($now)
                ->set('lastEditedBy')->eq($this->app->user->account)
                ->where('id')->eq($effort->objectID)
                ->exec();
        }

        if($effort->objectType != 'task' and $effort->objectType != 'custom')
        {
            $this->recordAction($effort->objectType, $effort->objectID, 'recordEstimate', $effort->work, $effort->consumed);
            continue;
        }

        if($effort->objectType == 'task')
        {
            $taskID = $effort->objectID;
            $task   = zget($tasks, $taskID, '');
            if(empty($task)) continue;

            $fromAction = false;
            if(!empty($_POST['actionID'][$id]))
            {
                $action = $this->dao->select('*')->from(TABLE_ACTION)->where('id')->eq($efforts->actionID[$id])->fetch();
                if(isset($action->action) and ($action->action == 'opened' or $action->action == 'edited')) $fromAction = true;
            }

            $newTask = clone $task;
            $newTask->consumed      += $effort->consumed;
            $newTask->lastEditedBy   = $this->app->user->account;
            $newTask->lastEditedDate = $now;
            if(helper::isZeroDate($task->realStarted)) $newTask->realStarted = $now;

            if(empty($lastDatePairs[$taskID]) or $lastDatePairs[$taskID] <= $effort->date)
            {
                $newTask->left = $effort->left;
                $lastDatePairs[$taskID] = $effort->date;
            }

            if(isset($executionTeams[$taskID]))
            {
                $extra = array('filter' => 'done');
                if(isset($effort->order)) $extra['order'] = $effort->order;
                $currentTeam = $this->task->getTeamByAccount($executionTeams[$taskID], $effort->account, $extra);
            }

            /* Fix for bug #1853. */
            if($fromAction)
            {
                $actionID = $this->action->create('task', $taskID, 'RecordEstimate', $effort->work, $effort->consumed);
            }
            elseif($newTask->left == 0 and ((empty($currentTeam) and strpos('done,pause,cancel,closed', $task->status) === false) or (!empty($currentTeam) and $currentTeam->status != 'done')))
            {
                $newTask->status         = 'done';
                $newTask->assignedTo     = $task->openedBy;
                $newTask->assignedDate   = $now;
                $newTask->finishedBy     = $this->app->user->account;
                $newTask->finishedDate   = $now;
                $actionID = $this->action->create('task', $taskID, 'Finished', $effort->work);
            }
            elseif($newTask->status == 'wait')
            {
                $newTask->status       = 'doing';
                $newTask->assignedTo   = $this->app->user->account;
                $newTask->assignedDate = $now;
                $actionID = $this->action->create('task', $taskID, 'Started', $effort->work);
            }
            elseif($newTask->left != 0 and strpos('done,pause,cancel,closed,pause', $task->status) !== false)
            {
                $newTask->status         = 'doing';
                $newTask->assignedTo     = $this->app->user->account;
                $newTask->finishedBy     = '';
                $newTask->canceledBy     = '';
                $newTask->closedBy       = '';
                $newTask->closedReason   = '';
                $newTask->finishedDate   = '0000-00-00 00:00:00';
                $newTask->canceledDate   = '0000-00-00 00:00:00';
                $newTask->closedDate     = '0000-00-00 00:00:00';
                $actionID = $this->action->create('task', $taskID, 'Activated', $effort->work);
            }
            else
            {
                $actionID = $this->action->create('task', $taskID, 'RecordEstimate', $effort->work, $effort->consumed);
            }

            /* Process multi-person task. Update consumed on team table. */
            if(isset($executionTeams[$taskID]))
            {
                if(!empty($currentTeam))
                {
                    $teamStatus = $effort->left == 0 ? 'done' : 'doing';
                    $this->dao->update(TABLE_TASKTEAM)->set('left')->eq($effort->left)->set("consumed = consumed + {$effort->consumed}")->set('status')->eq($teamStatus)->where('id')->eq($currentTeam->id)->exec();
                    if($task->mode == 'linear' and empty($effort->order)) $this->task->updateEstimateOrder($effortID, $currentTeam->order);
                    $currentTeam->consumed += $effort->consumed;
                    $currentTeam->left      = $effort->left;
                    $currentTeam->status    = $teamStatus;
                }

                $newTask = $this->task->computeHours4Multiple($task, $newTask, $executionTeams[$taskID]);
            }

            $this->dao->update(TABLE_ACTION)->set('efforted')->eq('1')->where('id')->eq($actionID)->exec();

            unset($newTask->subStatus);
            $changes = common::createChanges($task, $newTask, 'task');
            if($changes and !empty($actionID)) $this->action->logHistory($actionID, $changes);

            if($changes) $changedTasks[$taskID] = $taskID;
            $tasks[$taskID] = $newTask;
        }
    }

    $this->loadModel('common');
    $this->loadModel('programplan');
    foreach($changedTasks as $taskID)
    {
        $task = $tasks[$taskID];

        $this->dao->update(TABLE_TASK)->data($task)->where('id')->eq($taskID)->exec();
        if($task->parent > 0) $this->task->updateParentStatus($task->id);
        if($task->story) $this->story->setStage($task->story);

        if($task->parent > 0)
        {
            if($task->status == 'done') $this->task->updateParentStatus($task->id, $task->parent, 'done');
            $this->task->computeWorkingHours($task->parent);
        }

        $this->common->syncPPEStatus($taskID);
        $this->programplan->computeProgress($task->execution);
    }

    return $errors;
}

/**
 * Create append link
 *
 * @param  string $objectType
 * @param  int    $objectID
 * @access public
 * @return string
 */
public function createAppendLink($objectType, $objectID)
{
    if(!in_array($objectType, $this->config->effort->allowCreateEffortLinkObject)) return false;
    if(!common::hasPriv('effort', 'createForObject')) return false;

    /* Determines whether an object is editable. */
    if($objectType == 'case') $objectType = 'testcase';
    $object = $this->loadModel($objectType)->getByID($objectID);
    if(!common::canBeChanged($objectType, $object)) return false;

    return html::a(helper::createLink('effort', 'createForObject', "objectType=$objectType&objectID=$objectID", '', true), "<i class='icon-green-effort-createForObject icon-time'></i> " . $this->lang->effort->common, '', "class='btn effort iframe' data-width='90%'");
}

/**
 * Get efforts by account.
 *
 * @param  array    $effortIDList
 * @param  string   $account
 * @access public
 * @return object
 */
public function getAccountEffort($effortIDList, $account = '')
{
    $efforts = $this->dao->select('*')->from(TABLE_EFFORT)
        ->where('id')->in($effortIDList)
        ->andWhere('deleted')->eq(0)
        ->beginIF(!empty($account))->andWhere('account')->eq($account)->fi()
        ->fetchAll('id');

    if(!empty($efforts))
    {
        $objectIdList = array();
        foreach($efforts as $effort)
        {
            if(strpos($this->config->effort->createEffortType, $effort->objectType) === false)
            {
                dao::$errors = $this->lang->effort->batchEditEffortTypeError;
                break;
            }
            $objectIdList[$effort->objectType][$effort->objectID] = $effort->objectID;
        }
        if(dao::isError()) return false;

        list($objectTypeList, $todos) = $this->getEffortTitles($objectIdList);
        $objectTypeList['user']       = $this->loadModel('user')->getPairs('noletter');

        foreach($efforts as $effort)
        {
            $objectType = $effort->objectType;
            $objectID   = $effort->objectID;
            $key = $objectType . '_' . $objectID;
            $typeList[$key] = isset($objectTypeList[$objectType][$objectID]) ? "[$key]:" . $objectTypeList[$objectType][$objectID] : '';
            if($objectType != 'custom' and isset($objectTypeList[$objectType][$objectID]))
            {
                $typeList[$key] = strtoupper($objectType) . $objectID . ':' . $objectTypeList[$objectType][$objectID];
            }
            if($objectType == 'todo' and isset($todo) and isset($objectTypeList[$todo->type]))
            {
                $todo = $todos[$objectID];
                $typeList[$key] = strtoupper($objectType) . $objectID . ':' . $objectTypeList[$todo->type][$objectID];
            }
        }

        $vision  = $this->config->vision;
        if(strpos($this->config->effort->createEffortType, 'story') !== false)
        {
            $stories = $this->dao->select('id,title')->from(TABLE_STORY)->where('assignedTo')->eq($account)->andWhere('deleted')->eq('0')->andWhere('vision')->eq($vision)->fetchAll();
            foreach($stories as $story)
            {
                $key = 'story_' . $story->id;
                $typeList[$key] = '[S]' . $story->id . ':' . $story->title;
            }
        }

        $tasks = $this->dao->select('id,name')->from(TABLE_TASK)->where('assignedTo')->eq($account)->andWhere('deleted')->eq('0')->andWhere('vision')->eq($vision)->fetchAll();
        foreach($tasks as $task)
        {
            $key = 'task_' . $task->id;
            $typeList[$key] = '[T]' . $task->id . ':' . $task->name;
        }

        if($vision != 'lite')
        {
            $bugs = $this->dao->select('id,title')->from(TABLE_BUG)->where('assignedTo')->eq($account)->andWhere('deleted')->eq('0')->fetchAll();
            foreach($bugs as $bug)
            {
                $key = 'bug_' . $bug->id;
                $typeList[$key] = '[B]' . $bug->id . ':' . $bug->title;
            }
        }

        $efforts['typeList'] = $typeList;
    }
    return $efforts;
}

/**
 * Get actions.
 *
 * @param  int    $date
 * @param  int    $account
 * @param  string $objectType
 * @param  int    $objectID
 * @access public
 * @return array
 */
public function getActions($date, $account, $objectType = '', $objectID = '')
{
    /* Get all actions. */
    $date = is_numeric($date) ? substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) : $date;
    $dateLength = strlen($date);
    $allActions = $this->dao->select('*')->from(TABLE_ACTION)
        ->where('actor')->eq($account)
        ->andWhere('vision')->eq($this->config->vision)
        ->andWhere("(LEFT(`date`, $dateLength) = '$date')")
        ->beginIF(!empty($objectType) && strpos($objectType, ',') !== false)->andWhere('objectType')->in($objectType)->fi()
        ->beginIF(!empty($objectType) && strpos($objectType, ',') === false)->andWhere('objectType')->eq($objectType)->fi()
        ->beginIF(!empty($objectID))->andWhere('objectID')->eq($objectID)->fi()
        ->andWhere('efforted')->eq(0)
        ->orderBy('id_desc')
        ->limit(30)
        ->fetchAll('id');

    /* Init vars. */
    $taskIdList              = array();
    $bugIdList               = array();
    $executionTask           = array();
    $executionBug            = array();
    $closedExectuions        = array();
    $projectClosedExectuions = array();
    $projectClosedProducts   = array();
    $projectClosed           = array();
    if(empty($this->config->CRExecution)) $closedExectuions = $this->loadModel('execution')->getIdList(0, 'closed');
    $deletedExecutions = $this->dao->select('id')->from(TABLE_EXECUTION)->where('deleted')->eq('1')->fetchPairs('id');
    $deletedProducts   = $this->dao->select('id')->from(TABLE_PRODUCT)->where('deleted')->eq('1')->fetchPairs('id');
    if(isset($this->config->CRProject) && empty($this->config->CRProject))
    {
        $projectClosedExectuions = $this->loadModel('execution')->getIdListByProjectClosedStatus();
        $projectClosedProducts   = $this->loadModel('product')->getIdListByProjectClosedStatus();
        $projectClosed           = $this->loadModel('project')->getIdListByProjectClosedStatus();
    }

    foreach($allActions as $id => $action)
    {
        if($action->objectType == 'task')
        {
            if(isset($closedExectuions[$action->execution]) or isset($deletedExecutions[$action->execution]) or isset($projectClosedExectuions[$action->execution]))
            {
                unset($allActions[$id]);
                continue;
            }

            $taskIdList[$action->objectID] = $action->objectID;
        }
        elseif($action->objectType == 'bug' or $action->objectType == 'story' or $action->objectType == 'testtask' or $action->objectType == 'feedback')
        {
            if($action->objectType == 'bug')
            {
                $actionProducts = trim($action->product, ',');
                if(isset($projectClosedProducts[$actionProducts]))
                {
                    unset($allActions[$id]);
                    continue;
                }

                if(isset($action->execution) && $action->execution && isset($projectClosedExectuions[$action->execution]))
                {
                    unset($allActions[$id]);
                    continue;
                }

                if(isset($action->project) && $action->project && isset($projectClosed[$action->project]))
                {
                    unset($allActions[$id]);
                    continue;
                }
            }
            $actionProducts = array_filter(explode(',', $action->product));
            if(empty($deletedProducts) or empty($actionProducts)) continue;

            $checkProductDeleted = true;
            foreach($actionProducts as $actionProduct)
            {
                if(!isset($deletedProducts[$actionProduct]))
                {
                    $checkProductDeleted = false;
                    break;
                }
            }
            if($checkProductDeleted)
            {
                unset($allActions[$id]);
                continue;
            }

            $bugIdList[$action->objectID] = $action->objectID;
        }
    }
    $taskIdList  = $this->dao->select('id')->from(TABLE_TASK)->where('`id`')->in($taskIdList)->andWhere('deleted')->eq(0)->andWhere('mode')->ne('linear')->fetchPairs('id', 'id');
    $teams       = $this->dao->select('task,account')->from(TABLE_TASKTEAM)->where('task')->in($taskIdList)->fetchGroup('task', 'account');
    $parentTasks = $this->dao->select('id,name')->from(TABLE_TASK)->where('`id`')->in($taskIdList)->andWhere('parent')->eq(-1)->fetchGroup('id', 'name');

    $actions     = array();
    $executions  = array();
    $beforeID    = 0;
    $dealActions = array();
    $parents     = $this->dao->select('distinct parent,parent')->from(TABLE_EXECUTION)->where('type')->eq('stage')->andWhere('grade')->gt(1)->andWhere('deleted')->eq(0)->fetchPairs();

    $bugIdList  = $this->dao->select('id')->from(TABLE_BUG)->where('`id`')->in($bugIdList)->andWhere('deleted')->eq(0)->fetchPairs('id', 'id');

    foreach($allActions as $id => $action)
    {
        /* Remove started or finished or multiple or parent or deleted task. */
        if($action->objectType == 'task' and ($action->action == 'started' or $action->action == 'finished')) continue;
        if($action->objectType == 'task' and isset($parentTasks[$action->objectID])) continue;
        if($action->objectType == 'task' and !isset($taskIdList[$action->objectID])) continue;
        if($action->objectType == 'task' and !isset($teams[$action->objectID][$account])) continue;
        if($action->objectType == 'bug'  and !isset($bugIdList[$action->objectID])) continue;
        if(!empty($parents[$action->execution])) continue;

        if(isset($dealActions[$action->objectType][$action->objectID])) continue;

        if(isset($this->lang->effort->objectTypeList[$action->objectType]))
        {
            $work = $this->getWork($action->objectType, $action->objectID);

            $key      = $action->objectType . '_' . $action->objectID;
            $objectID = $action->objectID;
            if(!isset($work[$objectID])) continue;
            $typeList[$key] = '[' . zget($this->lang->effort->objectTypeList, $action->objectType, $action->objectType) . ']' . $objectID . ':' . $work[$objectID];
            $action->work   = $this->lang->effort->deal . $this->lang->effort->objectTypeList[$action->objectType] . ' : ' . $work[$objectID];

            $beforeID = $id;
            unset($action->product);

            $actions[$id] = $action;
            // $executions[$action->execution] = $action->execution;
            if($action->objectType == 'task') $executionTask[$key] = $action->execution; // Fix bug #1581.
            if($action->objectType == 'bug') $executionBug[$key] = $action->execution; // Fix bug #16446.
            $dealActions[$action->objectType][$action->objectID] = true;
        }
    }

    if(isset($this->lang->effort->objectTypeList['story']) && strpos($objectType, 'story') !== false)
    {
        $stories = $this->dao->select('t1.id,t1.title')->from(TABLE_STORY)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product=t2.id')
            ->where('t1.assignedTo')->eq($this->app->user->account)
            ->andWhere('t1.deleted')->eq('0')
            ->andWhere('t2.deleted')->eq('0')
            ->andWhere('t1.vision')->eq($this->config->vision)
            ->orderBy('id_desc')
            ->fetchAll();

        foreach($stories as $story)
        {
            $key = 'story_' . $story->id;
            $typeList[$key] = "[{$this->lang->effort->objectTypeList['story']}]" . $story->id . ':' . $story->title;
        }
    }

    /* Get tasks and remove multiple or parent tasks. */
    $tasks = $this->dao->select('t1.id,t1.execution,t1.name,t1.parent')->from(TABLE_TASK)->alias('t1')
        ->leftJoin(TABLE_EXECUTION)->alias('t2')->on('t1.execution=t2.id')
        ->where('t1.assignedTo')->eq($this->app->user->account)
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t2.deleted')->eq('0')
        ->andWhere('t1.vision')->eq($this->config->vision)
        ->orderBy('id_desc')
        ->fetchAll();

    foreach($tasks as $task)
    {
        if($task->parent < 0) continue;
        if(isset($closedExectuions[$task->execution]) || isset($projectClosedExectuions[$task->execution])) continue;

        $key                          = 'task_' . $task->id;
        $typeList[$key]               = "[{$this->lang->effort->objectTypeList['task']}]" . $task->id . ':' . $task->name;
        $executionTask[$key]          = $task->execution;
        $executions[$task->execution] = $task->execution;
    }

    if(isset($this->lang->effort->objectTypeList['bug']) && strpos($objectType, 'bug') !== false)
    {
        $bugs = $this->dao->select('t1.id,t1.title,t1.execution,t1.product,t1.project')->from(TABLE_BUG)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product=t2.id')
            ->where('t1.assignedTo')->eq($this->app->user->account)
            ->andWhere('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq(0)
            ->orderBy('id_desc')
            ->fetchAll();

        foreach($bugs as $bug)
        {
            $bugProduct = trim($bug->product, ',');
            if(isset($projectClosedProducts[$bugProduct])) continue;

            if(isset($bug->execution) && $bug->execution && isset($projectClosedExectuions[$bug->execution])) continue;

            if(isset($bug->project)   && $bug->project   && isset($projectClosed[$bug->project])) continue;

            $key                         = 'bug_' . $bug->id;
            $typeList[$key]              = "[{$this->lang->effort->objectTypeList['bug']}]" . $bug->id . ':' . $bug->title;
            $executionBug[$key]          = $bug->execution;
            $executions[$bug->execution] = $bug->execution;
        }
    }

    $actions['typeList'] = isset($typeList) ? $typeList : array();
    $executions = $this->loadModel('execution')->getByIdList($executions);
    $projects   = $this->loadModel('project')->getPairsByModel('all');

    foreach($executions as $execution)
    {
        $executionPrefix = isset($projects[$execution->project]) ? $projects[$execution->project] . '/' : '';
        $actions['executions'][$execution->id] = $executionPrefix . $execution->name;
    }

    if(isset($executionTask)) $actions['executionTask'] = $executionTask;
    if(isset($executionBug)) $actions['executionBug'] = $executionBug;

    return $actions;
}

/**
 * Get join executions
 *
 * @param  string status
 * @param  int    limit
 * @access public
 * @return array
 */
public function getJoinExecution($status = 'all', $limit = 20)
{
    $executions = $this->dao->select('t1.id as id, t1.name as name, t2.name as project, t1.multiple, t1.type, t1.grade, t1.path')->from(TABLE_EXECUTION)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project=t2.id')
        ->leftJoin(TABLE_TEAM)->alias('t3')->on('t1.id=t3.root')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t1.type')->in($this->config->vision == 'lite' ? 'kanban' : 'stage,sprint,kanban')
        ->andWhere('t3.type')->eq('execution')
        ->andWhere('t3.account')->eq($this->app->user->account)
        ->beginIF($this->config->vision)->andWhere('t1.vision')->eq($this->config->vision)->fi()
        ->beginIF($status == 'noclosed')->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF(isset($this->config->CRProject) && empty($this->config->CRProject))->andWhere('t2.status')->ne('closed')->fi()
        ->orderBy('t3.join_desc, t3.id_desc')
        ->beginIF(!empty($limit))->limit($limit)->fi()
        ->fetchAll('id');

    $this->app->loadLang('project');
    $executionPairs = array();
    $parents        = $this->dao->select('distinct parent,parent')->from(TABLE_EXECUTION)->where('type')->eq('stage')->andWhere('grade')->gt(1)->andWhere('deleted')->eq(0)->fetchPairs();
    foreach($executions as $id => $execution)
    {
        if(!empty($parents[$execution->id])) continue;

        if($execution->type == 'stage' and $execution->grade > 1)
        {
            $parentExecutions = $this->dao->select('id,name')->from(TABLE_EXECUTION)->where('id')->in(trim($execution->path, ','))->andWhere('type')->in('stage,kanban,sprint')->orderBy('grade')->fetchPairs();
            $execution->name  = implode('/', $parentExecutions);
        }

        $executionPairs[$id] = $execution->project . '/' . $execution->name;
        if(empty($execution->multiple)) $executionPairs[$id] = $execution->project . "({$this->lang->project->disableExecution})";
    }

    return $executionPairs;
}

/**
 * Get effort list of a user.
 *
 * @param  date   $begin
 * @param  date   $end
 * @param  string $account
 * @param  int    $product
 * @param  int    $execution
 * @param  int    $dept
 * @param  string $orderBy
 * @param  object $pager
 * @param  int    $project
 * @access public
 * @return void
 */
public function getList($begin, $end, $account = '', $product = 0, $execution = 0, $dept = 0, $orderBy = 'date_desc', $pager = null, $project = 0, $userType = '')
{
    $orderBy = empty($orderBy) ? 'date_desc' : $orderBy;
    $efforts = array();
    $users   = array();
    if($dept)   $users = $this->loadModel('dept')->getDeptUserPairs($dept, '', '', 'withdeleted');
    if($account)$users = array($account => $account);

    $efforts = $this->dao->select('t1.*,t2.dept,t3.status as projectStatus')->from(TABLE_EFFORT)->alias('t1')
        ->leftJoin(TABLE_USER)->alias('t2')->on('t1.account=t2.account')
        ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t1.project=t3.id')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t1.vision')->eq($this->config->vision)
        ->beginIF($begin)->andWhere("t1.date")->ge($begin)->fi()
        ->beginIF($end)->andWhere("t1.date")->le($end)->fi()
        ->beginIF($users or $dept)->andWhere('t1.account')->in(array_keys($users))->fi()
        ->beginIF($product)->andWhere('t1.product')->like("%,$product,%")->fi()
        ->beginIF($project)->andWhere('t1.project')->eq($project)->fi()
        ->beginIF($execution)->andWhere('t1.execution')->eq($execution)->fi()
        ->beginIF($userType)->andWhere('t2.type')->eq($userType)->fi()
        ->orderBy($orderBy)
        ->page($pager)
        ->fetchAll();

    /* Set session. */
    $sql = explode('WHERE', $this->dao->get());
    $sql = explode('ORDER', $sql[1]);
    $this->session->set('effortReportCondition', $sql[0]);
    $requestSource = $this->app->rawModule == 'company' && $this->app->rawMethod == 'effort';
    $objectIdList  = array();
    foreach($efforts as $effort) $objectIdList[$effort->objectType][$effort->objectID] = $effort->objectID;
    list($objectTypeList, $todos) = $this->getEffortTitles($objectIdList, $requestSource);

    foreach($efforts as $effort)
    {
        if(isset($objectTypeList[$effort->objectType]))
        {
            $effort->taskDesc = '';
            if($requestSource && $effort->objectType == 'task')
            {
                $taskList            = $objectTypeList[$effort->objectType];
                $effort->objectTitle = isset($taskList[$effort->objectID]) ? $taskList[$effort->objectID]->name : '';
                $effort->taskDesc    = isset($taskList[$effort->objectID]) ? $taskList[$effort->objectID]->desc : '';
            }
            else
            {
                $title               = $objectTypeList[$effort->objectType];
                $effort->objectTitle = zget($title, $effort->objectID, '');
            }

            if($effort->objectType == 'todo' and isset($todos[$effort->objectID]))
            {
                $todo = $todos[$effort->objectID];
                $effort->objectTitle = $todo->name;
                if(isset($objectTypeList[$todo->type])) $effort->objectTitle = zget($objectTypeList[$todo->type], $todo->idvalue, '');
            }
            if($effort->objectType == 'case') $effort->objectType = 'testcase';
        }
    }
    return $efforts;
}

/**
 * Get effort titles.
 *
 * @param  array  $objectIdList
 * @access public
 * @return array
 */
public function getEffortTitles($objectIdList, $taskDesc = false)
{
    $this->app->loadConfig('action');
    $todos = array();
    $objectTypeList = array();
    foreach($objectIdList as $objectType => $idList)
    {
        $table = zget($this->config->objectTables, $objectType, '');
        $field = zget($this->config->action->objectNameFields, $objectType, '');
        if($table and $field)
        {
            if($objectType == 'task' && $taskDesc) $objectTypeList[$objectType] = $this->dao->select("`id`, `name`, `desc`")->from($table)->where('id')->in($idList)->fetchAll('id');
            else $objectTypeList[$objectType] = $this->dao->select("id,$field")->from($table)->where('id')->in($idList)->fetchPairs('id', $field);
            if($objectType == 'todo')
            {
                $todos = $this->dao->select('*')->from(TABLE_TODO)->where('id')->in($idList)->fetchAll('id');
                $todoLinkedObject = array();
                foreach($todos as $todo)
                {
                    if(!empty($todo->idvalue)) $todoLinkedObject[$todo->type][$todo->idvalue] = $todo->idvalue;
                }
                if($todoLinkedObject)
                {
                    foreach($todoLinkedObject as $linkedType => $linkedIdList)
                    {
                        $table = zget($this->config->objectTables, $linkedType, '');
                        $field = zget($this->config->action->objectNameFields, $linkedType, '');
                        if($table and $field)
                        {
                            $linkedObjects = $this->dao->select("id,$field")->from($table)->where('id')->in($linkedIdList)->fetchPairs('id', $field);
                            if(!isset($objectTypeList[$linkedType])) $objectTypeList[$linkedType] = array();
                            $objectTypeList[$linkedType] += $linkedObjects;
                        }
                    }
                }
            }
        }
    }
    return array($objectTypeList, $todos);
}

/**
 * Parse date
 *
 * @param  string $date
 * @access public
 * @return array
 */
public function parseDate($date)
{
    $this->app->loadClass('date');
    if($date == 'today')
    {
        $begin = date('Y-m-d', time());
        $end   = $begin;
    }
    elseif($date == 'yesterday')
    {
        $begin = date::yesterday();
        $end   = $begin;
    }
    elseif($date == 'thisweek')
    {
        extract(date::getThisWeek());
    }
    elseif($date == 'lastweek')
    {
        extract(date::getLastWeek());
    }
    elseif($date == 'thismonth')
    {
        extract(date::getThisMonth());
    }
    elseif($date == 'lastmonth')
    {
        extract(date::getLastMonth());
    }
    elseif($date == 'all')
    {
        $end   = date("Y-m-d");
        if(date('m-d') == '02-29') $begin = date("Y-m-d" , strtotime("-1 year -1 day"));
        else $begin = date("Y-m-d" , strtotime("-1 year"));
    }
    elseif(is_array($date))
    {
        list($begin, $end) = $date;
    }
    else
    {
        $begin = $date;
        $end   = $date;
    }
    return array(substr($begin, 0, 10), substr($end, 0, 10));
}

/**
 * Get recently executions
 *
 * @param  string status
 * @param  int    limit
 * @param  array  filterID
 * @access public
 * @return array
 */
public function getRecentlyExecutions($status = 'all', $limit = 20, $filterID = array())
{
    if(!empty($filterID))
    {
        $executionID = array_diff(explode(',', $this->app->user->view->sprints), $filterID);
    }
    else
    {
        $executionID = $this->app->user->view->sprints;
    }

    $executions = $this->dao->select('t1.id as id, t1.name as name, t2.name as project, t1.multiple, t1.type, t1.grade, t1.path')->from(TABLE_EXECUTION)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project=t2.id')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t1.type')->in($this->config->vision == 'lite' ? 'kanban' : 'stage,sprint,kanban')
        ->beginIF(!$this->app->user->admin)->andWhere('t1.id')->in($executionID)->fi()
        ->beginIF($this->config->vision)->andWhere('t1.vision')->eq($this->config->vision)->fi()
        ->beginIF($status == 'noclosed')->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF(isset($this->config->CRProject) && empty($this->config->CRProject))->andWhere('t2.status')->ne('closed')->fi()
        ->orderBy('id_desc')
        ->beginIF(!empty($limit))->limit($limit)->fi()
        ->fetchAll('id');

    $this->app->loadLang('project');
    $executionPairs = array();
    $parents        = $this->dao->select('distinct parent,parent')->from(TABLE_EXECUTION)->where('type')->eq('stage')->andWhere('grade')->gt(1)->andWhere('deleted')->eq(0)->fetchPairs();
    foreach($executions as $id => $execution)
    {
        if(!empty($parents[$execution->id])) continue;

        if($execution->type == 'stage' and $execution->grade > 1)
        {
            $parentExecutions = $this->dao->select('id,name')->from(TABLE_EXECUTION)->where('id')->in(trim($execution->path, ','))->andWhere('type')->in('stage,kanban,sprint')->orderBy('grade')->fetchPairs();
            $execution->name  = implode('/', $parentExecutions);
        }

        $executionPairs[$id] = $execution->project . '/' . $execution->name;
        if(empty($execution->multiple)) $executionPairs[$id] = $execution->project . "({$this->lang->project->disableExecution})";
    }

    return $executionPairs;
}

/**
 * Print cell.
 *
 * @param  object $col
 * @param  object $effort
 * @param  string $mode
 * @param  array  $executions
 * @access public
 * @return void
 */
public function printCellCustom($col, $effort, $mode = 'datatable', $executions = array())
{
    $canView  = common::hasPriv('effort', 'view');
    $account  = $this->app->user->account;
    $id       = $col->id;
    if($col->show)
    {
        $class = '';
        $title = '';
        if($id == 'work') $title = " title='{$effort->work}'";
        if($id == 'objectType' and isset($effort->objectTitle)) $title = " title='{$effort->objectTitle}'";

        if($id == 'work' or $id == 'objectType') $class .= ' c-name';

        if($id == 'product')
        {
            static $products;
            if(empty($products)) $products = $this->loadModel('product')->getPairs('', 0, '', 'all');

            $effort->productName = '';
            $effortProducts      = explode(',', trim($effort->product, ','));
            foreach($effortProducts as $productID) $effort->productName .= zget($products, $productID, '') . ' ';
            $title = " title='{$effort->productName}'";
        }

        if($id == 'execution')
        {
            $effort->executionName = zget($executions, $effort->execution, '');
            $title = " title='{$effort->executionName}'";
        }

        if($id == 'project')
        {
            static $projects;
            if(empty($projects)) $projects = $this->loadModel('project')->getPairsByProgram();
            $effort->projectName = !empty($effort->project) && isset($projects[$effort->project]) ? '#' . $effort->project . ' ' . $projects[$effort->project] : '';
            $title = " title='{$effort->projectName}'";
        }

        if($id == 'projectStatus')
        {
            static $projects;
            if(empty($projects)) $projects = $this->loadModel('project')->getPairsByProgram();
            $this->loadModel('project');
            $effort->projectStatus = !empty($effort->project) && isset($projects[$effort->project]) ? zget($this->lang->project->statusList, $effort->projectStatus, '') : '';
            $title = " title='{$effort->projectStatus}'";
        }

        if($id == 'dept')
        {
            static $depts;
            if(empty($depts)) $depts = $this->loadModel('dept')->getOptionMenu();
            $effort->deptName = zget($depts, $effort->dept, '');
            $title = " title='{$effort->deptName}'";
        }

        if($id == 'taskDesc')
        {
            $effort->taskDesc = strip_tags($effort->taskDesc);
            $title = " title='{$effort->taskDesc}'";
        }

        echo "<td class='c-{$id}" . $class . "'" . $title . ">";
        switch($id)
        {
        case 'id':
            if($this->app->getModuleName() == 'my')
            {
                echo html::checkbox('effortIDList', array($effort->id => sprintf('%03d', $effort->id)));
            }
            else
            {
                printf('%03d', $effort->id);
            }
            break;
        case 'date':
            echo $effort->date;
            break;
        case 'account':
            static $users;
            if(empty($users)) $users = $this->loadModel('user')->getPairsCustom();
            echo str_replace('(deleted)','&nbsp;<span class="label label-danger">' . $this->lang->user->dimission . '</span>', zget($users, $effort->account));
            break;
        case 'dept':
            echo $effort->deptName;
            break;
        case 'work':
            echo $canView ? html::a(helper::createLink('effort', 'view', "id=$effort->id&from=my", '', true), $effort->work, '', "class='iframe'") : $effort->work;
            break;
        case 'consumed':
            echo $effort->consumed;
            break;
        case 'left':
            echo $effort->objectType == 'task' ? $effort->left : '';
            break;
        case 'objectType':
            if($effort->objectType != 'custom')
            {
                $viewLink = helper::createLink($effort->objectType, 'view', "id=$effort->objectID");
                $objectTitle = zget($this->lang->effort->objectTypeList, $effort->objectType, strtoupper($effort->objectType)) . " #{$effort->objectID} " . $effort->objectTitle;
                echo common::hasPriv($effort->objectType, 'view') ? html::a($viewLink, $objectTitle) : $objectTitle;
            }
            break;
        case 'product':
            echo $effort->productName;
            break;
        case 'execution':
            echo $effort->executionName;
            break;
        case 'project':
            echo $effort->projectName;
            break;
        case 'projectStatus':
            echo $effort->projectStatus;
            break;
        case 'taskDesc':
            $effort->taskDesc = mb_substr($effort->taskDesc, 0, 15);
            echo $effort->taskDesc;
            break;
        case 'actions':
            common::printIcon('effort', 'edit',   "id=$effort->id", $effort, 'list', '', '', 'iframe', true);
            common::printIcon('effort', 'delete', "id=$effort->id", $effort, 'list', 'trash', 'hiddenwin');
            break;
        }
        echo '</td>';
    }
}

/**
 * update a effort.
 *
 * @param  int    $effortID
 * @access public
 * @return void
 */
public function update($effortID)
{
    $today     = helper::today();
    $now       = helper::now();

    $oldEffort = $this->getById($effortID);
    $effort    = fixer::input('post')
        ->setDefault('account', $oldEffort->account)
        ->cleanInt('objectID')
        ->join('product', ',')
        ->get();

    if(!empty($effort->product)) $effort->product = ',' . $effort->product . ',';

    if($effort->objectType == 'task')
    {
        $this->app->loadLang('task');
        if(helper::isZeroDate($effort->date)) die(js::alert($this->lang->task->error->dateEmpty));
        if($effort->date > $today) die(js::alert($this->lang->task->error->date));
        if(empty($effort->execution)) die(js::alert($this->lang->task->error->taskNoExecution));
    }

    if($effort->objectType == 'bug')
    {
        $effort->execution = $this->dao->select('execution')->from(TABLE_BUG)->where('id')->eq($effort->objectID)->fetch('execution');
        if(empty($effort->execution)) die(js::alert($this->lang->effort->bugNotExecution));
    }

    if($effort->consumed <= 0) die(js::alert(sprintf($this->lang->error->gt, $this->lang->effort->consumed, '0')));
    if($effort->left < 0)      die(js::alert($this->lang->effort->left . $this->lang->effort->notNegative));

    if($effort->date > helper::now()) die(js::alert($this->lang->effort->notFuture));
    $oldEffortDate = is_numeric($oldEffort->date) ? substr($oldEffort->date, 0, 4) . '-' . substr($oldEffort->date, 4, 2) . '-' . substr($oldEffort->date, 6, 2) : $oldEffort->date;
    if($oldEffortDate == $effort->date)
    {
        if($effort->consumed - $oldEffort->consumed + $this->getAccountStatistics('', $effort->date) > $this->config->limitWorkHour) die(js::alert(sprintf($this->lang->effort->hoursConsumedTodayOverflowForALL, $effort->date)));
    }
    else
    {
        if($effort->consumed + $this->getAccountStatistics('', $effort->date) > $this->config->limitWorkHour) die(js::alert(sprintf($this->lang->effort->hoursConsumedTodayOverflowForALL, $effort->date)));
    }
    $this->dao->update(TABLE_EFFORT)->data($effort)
        ->autoCheck()
        ->batchCheck($this->config->effort->edit->requiredFields, 'notempty')
        ->where('id')->eq($effortID)
        ->exec();

    if(!dao::isError())
    {
        if($effort->objectType != 'task') $this->recordAction($effort->objectType, $effort->objectID, 'editEstimate', $effort->work, $effort->consumed - $oldEffort->consumed);

        if($effort->objectType == 'bug')
        {
            $this->dao->update(TABLE_BUG)->set('lastEditedDate')->eq($now)
                ->set('lastEditedBy')->eq($this->app->user->account)
                ->where('id')->eq($effort->objectID)
                ->exec();
        }

        $changes = common::createChanges($oldEffort, $effort);
        if($changes) $this->changeTaskConsumed($effort, 'add', $oldEffort);
        if($oldEffort->objectType == 'task' and $oldEffort->objectID != $effort->objectID) $this->changeTaskConsumed($oldEffort, 'delete');
        return $changes;
    }
}

public function getAccountStatistics($account = '', $date = 'today')
{
    if($date == 'today') $date  = date(DT_DATE1, time());
    if($account == '') $account = $this->app->user->account;
    $dateLength = strlen($date);
    $efforts = $this->dao->select('consumed')->from(TABLE_EFFORT)
        ->where('deleted')->eq(0)
        ->andWhere('account')->eq($account)
        ->beginIF(is_numeric($date))->andWhere('date')->ge($date)->fi()
        ->beginIF(is_numeric($date))->andWhere('date')->le($date)->fi()
        ->beginIF(!is_numeric($date))->andWhere("(LEFT(`date`, $dateLength) = '$date')")->fi()
        ->andWhere("objectType")->in($this->config->effort->createEffortType)
        ->fetchAll();

    $consumed = 0;
    foreach($efforts as $effort) $consumed += $effort->consumed;

    return $consumed;
}

public function printCellExt($col, $effort, $mode = 'datatable', $executions = array())
{
    $canView  = common::hasPriv('effort', 'view');
    $account  = $this->app->user->account;
    $id       = $col->id;
    if($col->show)
    {
        $class = '';
        $title = '';
        if($id == 'work') $title = " title='{$effort->work}'";
        if($id == 'objectType' and isset($effort->objectTitle)) $title = " title='{$effort->objectTitle}'";

        if($id == 'work' or $id == 'objectType') $class .= ' c-name';

        if($id == 'product')
        {
            static $products;
            if(empty($products)) $products = $this->loadModel('product')->getPairs('', 0, '', 'all');

            $effort->productName = '';
            $effortProducts      = explode(',', trim($effort->product, ','));
            foreach($effortProducts as $productID) $effort->productName .= zget($products, $productID, '') . ' ';
            $title = " title='{$effort->productName}'";
        }

        if($id == 'execution')
        {
            $effort->executionName = zget($executions, $effort->execution, '');
            $title = " title='{$effort->executionName}'";
        }

        if($id == 'project')
        {
            static $projects;
            if(empty($projects)) $projects = $this->loadModel('project')->getPairsByProgram();
            $effort->projectName = zget($projects, $effort->project, '');
            $title = " title='{$effort->projectName}'";
        }

        if($id == 'dept')
        {
            static $depts;
            if(empty($depts)) $depts = $this->loadModel('dept')->getOptionMenu();
            $effort->deptName = zget($depts, $effort->dept, '');
            $title = " title='{$effort->deptName}'";
        }

        echo "<td class='c-{$id}" . $class . "'" . $title . ">";
        switch($id)
        {
        case 'id':
            if($this->app->getModuleName() == 'my')
            {
                echo html::checkbox('effortIDList', array($effort->id => sprintf('%03d', $effort->id)));
            }
            else
            {
                printf('%03d', $effort->id);
            }
            break;
        case 'date':
            echo $effort->date;
            break;
        case 'account':
            static $users;
            if(empty($users)) $users = $this->loadModel('user')->getPairs('noletter');
            echo zget($users, $effort->account);
            break;
        case 'dept':
            echo $effort->deptName;
            break;
        case 'work':
            echo $canView ? html::a(helper::createLink('effort', 'view', "id=$effort->id&from=my", '', true), $effort->work, '', "class='iframe'") : $effort->work;
            break;
        case 'consumed':
            echo $effort->consumed;
            break;
        case 'left':
            echo $effort->objectType == 'task' ? $effort->left : '';
            break;
        case 'objectType':
            if($effort->objectType != 'custom')
            {
                $viewLink = helper::createLink($effort->objectType, 'view', "id=$effort->objectID");
                $objectTitle = zget($this->lang->effort->objectTypeList, $effort->objectType, strtoupper($effort->objectType)) . " #{$effort->objectID} " . $effort->objectTitle;
                echo common::hasPriv($effort->objectType, 'view') ? html::a($viewLink, $objectTitle) : $objectTitle;
            }
            break;
        case 'product':
            echo $effort->productName;
            break;
        case 'execution':
            echo $effort->executionName;
            break;
        case 'project':
            echo $effort->projectName;
            break;
        case 'actions':
            common::printIcon('effort', 'edit',   "id=$effort->id", $effort, 'list', '', '', 'iframe', true, "data-width='90%'");
            common::printIcon('effort', 'delete', "id=$effort->id", $effort, 'list', 'trash', 'hiddenwin');
            break;
        }
        echo '</td>';
    }
}
