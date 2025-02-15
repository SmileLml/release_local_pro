<?php
class qeelinEffort extends effortModel
{
    public function batchCreate()
    {
        $taskIdList = array();
        $lefts      = array();
        foreach($this->post->objectType as $i => $objectType)
        {
            if(empty($this->post->work[$i])) continue;

            if($objectType == 'task')
            {
                $objectID = $this->post->objectID[$i];
                $taskIdList[$objectID] = $objectID;
                $lefts[$objectID]      = $this->post->left[$i];
            }
        }

        $this->loadModel('task');
        $tasks = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in($taskIdList)->fetchAll('id');
        foreach($tasks as $task)
        {
            if($task->status == 'wait')
            {
                $message = $this->task->checkDepend($task->id, 'begin');
                if($message) die(js::alert($message));
            }
            if(isset($lefts[$task->id]) and $lefts[$task->id] == 0 and strpos('done,cancel,closed', $task->status) === false)
            {
                $message = $this->task->checkDepend($task->id, 'end');
                if($message) die(js::alert($message));
            }
        }

        $this->loadModel('task');
        $this->loadModel('action');

        $now        = helper::now();
        $efforts    = fixer::input('post')->get();
        $data       = array();
        $taskIDList = array();
        $today      = helper::today();
        $nonRDUser  = (!empty($_SESSION['user']->feedback) or !empty($_COOKIE['feedbackView'])) ? true : false;
        $consumedDate = [];
        foreach($efforts->id as $id => $num)
        {
            if(strpos($efforts->objectType[$id], '_') !== false)
            {
                $pos = strpos($efforts->objectType[$id], '_');
                $efforts->objectID[$id]   = substr($efforts->objectType[$id], $pos + 1);
                $efforts->objectType[$id] = substr($efforts->objectType[$id], 0, $pos);
            }
            elseif(empty($efforts->objectID[$id]))
            {
                $efforts->objectType[$id] = 'custom';
                $efforts->objectID[$id]   = 0;
            }

            if(!empty($efforts->work[$id]) or !empty($efforts->consumed[$id]))
            {
                if($efforts->objectType[$id] == 'task' and (empty($efforts->dates[$num]) or helper::isZeroDate($efforts->dates[$num])) and (empty($efforts->date) or helper::isZeroDate($efforts->date))) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->task->error->dateEmpty));
                if($efforts->objectType[$id] == 'task' and isset($efforts->dates[$num]) and $efforts->dates[$num] > $today) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->task->error->date));

                $efforts->work[$id] = trim($efforts->work[$id]);
                if(empty($efforts->work[$id]))           die(js::alert(sprintf($this->lang->effort->nowork, $efforts->id[$id])));
                if(!is_numeric($efforts->consumed[$id])) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->consumed . $this->lang->effort->isNumber));

                $consumed = (float)$efforts->consumed[$id];
                if(empty($consumed)) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->consumed . $this->lang->effort->notEmpty));
                if($consumed < 0)    die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->consumed . $this->lang->effort->notNegative));

                $left = isset($efforts->left[$num]) ? $efforts->left[$num] : '';
                if(!empty($left) and !is_numeric($left)) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->isNumber));
                if(!empty($left) and $left < 0)          die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->notNegative));
                if($efforts->objectType[$id] == 'task' and !$nonRDUser and empty($left) and !is_numeric($left))  die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->notEmpty));
                if($efforts->objectType[$id] == 'bug')
                {
                    $effortBug = $this->loadModel('bug')->getByID($efforts->objectID[$id]);
                    if(!isset($effortBug->execution) or empty($effortBug->execution)) die(js::alert(sprintf($this->lang->effort->bugNoExecution, $efforts->id[$id])));
                }
                $data[$id] = new stdclass();
                $data[$id]->vision    = $this->config->vision;
                $data[$id]->product   = ',0,';
                $data[$id]->execution = 0;
                $data[$id]->objectID  = 0;

                $data[$id]->date       = isset($efforts->dates[$id]) ? $efforts->dates[$id] : $efforts->date;
                $data[$id]->consumed   = $efforts->consumed[$id];
                $data[$id]->account    = $this->app->user->account;
                $data[$id]->work       = $efforts->work[$id];
                $data[$id]->objectType = $efforts->objectType[$id];
                if(isset($efforts->order[$id])) $data[$id]->order = $efforts->order[$id];

                if($data[$id]->date > $now) die(js::alert($this->lang->effort->common . $efforts->id[$id] . ' : ' . $this->lang->effort->left . $this->lang->effort->notFuture));

                if(isset($consumedDate[$data[$id]->date]))
                {
                    $consumedDate[$data[$id]->date] += $data[$id]->consumed;
                }
                else
                {
                    $consumedDate[$data[$id]->date] = $data[$id]->consumed;
                }
                if($data[$id]->objectType == 'task')
                {
                    $taskIDList[$efforts->objectID[$id]] = $efforts->objectID[$id];
                    $data[$id]->left = (float)$left;
                }

                if($data[$id]->objectType != 'custom') $data[$id]->objectID = $efforts->objectID[$id];

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
        }

        foreach($consumedDate as $dateID => $dateConsumed)
        {
            if($this->loadModel('effort')->getAccountStatistics('', $dateID) + $dateConsumed > $this->config->limitWorkHour)
            {
                die(js::alert(sprintf($this->lang->effort->hoursConsumedTodayOverflowForALL, $dateID)));
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
}
