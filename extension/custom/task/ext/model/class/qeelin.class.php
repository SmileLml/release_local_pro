<?php
class qeelinTask extends taskModel
{
    public function start($taskID, $extra = '')
    {
        $message = $this->checkDepend($taskID, 'begin');
        if($message) die(js::alert($message));

        if($this->post->left == 0)
        {
            $task = $this->getById($taskID);
            $lastMember = array();
            if($task->mode == 'linear') $lastMember = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('task')->eq($taskID)->orderBy('order desc')->limit(1)->fetch();
            if(empty($lastMember) or $lastMember->account == $this->app->user->account)
            {
                $message = $this->checkDepend($taskID, 'end');
                if($message) die(js::alert($message));
            }
        }

        $extra = str_replace(array(',', ' '), array('&', ''), $extra);
        parse_str($extra, $output);

        $oldTask = $this->getById($taskID);
        if(!empty($oldTask->team))
        {
            $currentTeam = $this->getTeamByAccount($oldTask->team);
            if($currentTeam and $this->post->consumed < $currentTeam->consumed) dao::$errors['consumed'] = $this->lang->task->error->consumedSmall;
            if($currentTeam and $currentTeam->status == 'doing' and $oldTask->status == 'doing') dao::$errors[] = $this->lang->task->error->alreadyStarted;
        }
        else
        {
            if($this->post->consumed < $oldTask->consumed) dao::$errors['consumed'] = $this->lang->task->error->consumedSmall;
            if($this->loadModel('effort')->getAccountStatistics() + $this->post->consumed - $oldTask->consumed > $this->config->limitWorkHour) dao::$errors[] = $this->lang->effort->hoursConsumedTodayOverflowForTask;
            if($oldTask->status == 'doing') dao::$errors[] = $this->lang->task->error->alreadyStarted;
        }
        if(dao::isError()) return false;

        $editorIdList = $this->config->task->editor->start['id'];
        if($this->app->getMethodName() == 'restart') $editorIdList = $this->config->task->editor->restart['id'];
        $now  = helper::now();
        $task = fixer::input('post')
            ->add('id', $taskID)
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setDefault('status', 'doing')
            ->setIF($oldTask->assignedTo != $this->app->user->account, 'assignedDate', $now)
            ->stripTags($editorIdList, $this->config->allowedTags)
            ->removeIF(!empty($oldTask->team), 'consumed,left')
            ->remove('comment')->get();

        $task = $this->loadModel('file')->processImgURL($task, $editorIdList, $this->post->uid);
        if($this->post->left == 0)
        {
            if(isset($task->consumed) and $task->consumed == 0) return dao::$errors[] = sprintf($this->lang->error->notempty, $this->lang->task->consumed);
            if(empty($oldTask->team))
            {
                $task->status       = 'done';
                $task->finishedBy   = $this->app->user->account;
                $task->finishedDate = $now;
                $task->assignedTo   = $oldTask->openedBy;
            }
        }

        /* Record consumed and left. */
        $estimate = new stdclass();
        $estimate->date     = helper::today();
        $estimate->task     = $taskID;
        $estimate->consumed = zget($_POST, 'consumed', 0);
        $estimate->left     = zget($_POST, 'left', 0);
        $estimate->work     = zget($task, 'work', '');
        $estimate->account  = $this->app->user->account;
        $estimate->consumed = (!empty($oldTask->team) and $currentTeam) ? $estimate->consumed - $currentTeam->consumed : $estimate->consumed - $oldTask->consumed;
        if($this->post->comment) $estimate->work = $this->post->comment;
        if($estimate->consumed > 0) $estimateID = $this->addTaskEstimate($estimate);

        if(!empty($oldTask->team) and $currentTeam)
        {
            $team = new stdclass();
            $team->consumed = $this->post->consumed;
            $team->left     = $this->post->left;
            $team->status   = empty($team->left) ? 'done' : 'doing';

            $this->dao->update(TABLE_TASKTEAM)->data($team)->where('id')->eq($currentTeam->id)->exec();
            if($oldTask->mode == 'linear' and !empty($estimateID)) $this->updateEstimateOrder($estimateID, $currentTeam->order);

            $task = $this->computeHours4Multiple($oldTask, $task);
            if($team->status == 'done')
            {
                $task->assignedTo   = $this->getAssignedTo4Multi($oldTask->team, $oldTask, 'next');
                $task->assignedDate = $now;
            }

            $finishedUsers = $this->getFinishedUsers($oldTask->id, array_keys($oldTask->members));
            if(count($finishedUsers) == count($oldTask->team))
            {
                $task->status       = 'done';
                $task->finishedBy   = $this->app->user->account;
                $task->finishedDate = $task->finishedDate;
            }
        }

        $this->dao->update(TABLE_TASK)->data($task)->autoCheck()
            ->check('consumed,left', 'float')
            ->checkFlow()
            ->where('id')->eq((int)$taskID)->exec();

        if($oldTask->parent > 0)
        {
            $this->updateParentStatus($taskID);
            $this->computeBeginAndEnd($oldTask->parent);
        }
        if($oldTask->story) $this->loadModel('story')->setStage($oldTask->story);

        $this->loadModel('kanban');
        if(!isset($output['toColID']) or $task->status == 'done') $this->kanban->updateLane($oldTask->execution, 'task', $taskID);
        if(isset($output['toColID']) and $task->status == 'doing') $this->kanban->moveCard($taskID, $output['fromColID'], $output['toColID'], $output['fromLaneID'], $output['toLaneID']);
        if($this->config->edition != 'open' && $oldTask->feedback) $this->loadModel('feedback')->updateStatus('task', $oldTask->feedback, $task->status, $oldTask->status);
        if(!dao::isError()) return common::createChanges($oldTask, $task);
    }

    public function finish($taskID, $extra = '')
    {
        $message = $this->checkDepend($taskID, 'end');
        if($message) die(js::alert($message));

        $extra = str_replace(array(',', ' '), array('&', ''), $extra);
        parse_str($extra, $output);

        $oldTask = $this->getById($taskID);
        $now     = helper::now();
        $today   = helper::today();

        if($extra != 'DEVOPS' and strpos($this->config->task->finish->requiredFields, 'comment') !== false and !$this->post->comment)
        {
            dao::$errors[] = sprintf($this->lang->error->notempty, $this->lang->comment);
            return false;
        }

        $task = fixer::input('post')
            ->add('id', $taskID)
            ->setIF(is_numeric($this->post->consumed), 'consumed', (float)$this->post->consumed)
            ->setIF(!$this->post->realStarted and helper::isZeroDate($oldTask->realStarted), 'realStarted', $now)
            ->setDefault('left', 0)
            ->setDefault('assignedTo',   $oldTask->openedBy)
            ->setDefault('assignedDate', $now)
            ->setDefault('status', 'done')
            ->setDefault('finishedBy, lastEditedBy', $this->app->user->account)
            ->setDefault('finishedDate, lastEditedDate', $now)
            ->stripTags($this->config->task->editor->finish['id'], $this->config->allowedTags)
            ->removeIF(!empty($oldTask->team), 'finishedBy,status,left')
            ->remove('comment,files,labels,currentConsumed')
            ->get();

        $currentConsumed = trim($this->post->currentConsumed);
        if(!is_numeric($currentConsumed)) return dao::$errors[] = $this->lang->task->error->consumedNumber;
        if(empty($currentConsumed) and $oldTask->consumed == '0') return dao::$errors[] = $this->lang->task->error->consumedEmpty;

        if(!$this->post->realStarted) return dao::$errors[] = $this->lang->task->error->realStartedEmpty;
        if(!$this->post->finishedDate) return dao::$errors[] = $this->lang->task->error->finishedDateEmpty;
        if($this->post->realStarted > $this->post->finishedDate) return dao::$errors[] = $this->lang->task->error->finishedDateSmall;

        /* Record consumed and left. */
        if(empty($oldTask->team))
        {
            $consumed = $task->consumed - $oldTask->consumed;
            if($consumed < 0) return dao::$errors[] = $this->lang->task->error->consumedSmall;
        }
        else
        {
            $currentTeam = $this->getTeamByAccount($oldTask->team);
            $consumed = $currentTeam ? $task->consumed - $currentTeam->consumed : $task->consumed;
            if($consumed < 0) return dao::$errors[] = $this->lang->task->error->consumedSmall;
        }

        if($this->loadModel('effort')->getAccountStatistics() + $consumed > $this->config->limitWorkHour) return dao::$errors[] = $this->lang->effort->hoursConsumedTodayOverflowForTask;

        $estimate = new stdclass();
        $estimate->date     = helper::isZeroDate($task->finishedDate) ? helper::today() : substr($task->finishedDate, 0, 10);
        $estimate->task     = $taskID;
        $estimate->left     = 0;
        $estimate->work     = zget($task, 'work', '');
        $estimate->account  = $this->app->user->account;
        $estimate->consumed = $consumed;
        if($this->post->comment) $estimate->work = $this->post->comment;
        if($estimate->consumed) $estimateID = $this->addTaskEstimate($estimate);

        if(!empty($oldTask->team) and $currentTeam)
        {
            $this->dao->update(TABLE_TASKTEAM)->set('left')->eq(0)->set('consumed')->eq($task->consumed)->set('status')->eq('done')->where('id')->eq($currentTeam->id)->exec();
            if($oldTask->mode == 'linear' and isset($estimateID)) $this->updateEstimateOrder($estimateID, $currentTeam->order);
            $task = $this->computeHours4Multiple($oldTask, $task);
        }

        if($task->finishedDate == substr($now, 0, 10)) $task->finishedDate = $now;

        $task = $this->loadModel('file')->processImgURL($task, $this->config->task->editor->finish['id'], $this->post->uid);
        $this->dao->update(TABLE_TASK)->data($task)->autoCheck()->checkFlow()
            ->where('id')->eq((int)$taskID)
            ->exec();

        if(!dao::isError())
        {
            if($oldTask->parent > 0) $this->updateParentStatus($taskID);
            if($oldTask->story) $this->loadModel('story')->setStage($oldTask->story);
            if($task->status == 'done')
            {
                $this->loadModel('score')->create('task', 'finish', $taskID);

                $this->loadModel('kanban');
                if(!isset($output['toColID'])) $this->kanban->updateLane($oldTask->execution, 'task', $taskID);
                if(isset($output['toColID'])) $this->kanban->moveCard($taskID, $output['fromColID'], $output['toColID'], $output['fromLaneID'], $output['toLaneID']);
            }

            if($this->config->edition != 'open' && $oldTask->feedback) $this->loadModel('feedback')->updateStatus('task', $oldTask->feedback, $task->status, $oldTask->status);

            return common::createChanges($oldTask, $task);
        }

        return false;
    }

    public function checkDepend($taskID, $action = 'begin')
    {
        $actions = $action;
        if($action == 'end') $actions = 'begin,end';

        $relations     = $this->dao->select('*')->from(TABLE_RELATIONOFTASKS)->where('task')->eq($taskID)->andWhere('action')->in($actions)->fetchAll('pretask');
        $relationTasks = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in(array_keys($relations))->fetchAll('id');
        $task          = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in($taskID)->fetch();

        $message = '';
        foreach($relations as $id => $relation)
        {
            $pretask = $relationTasks[$id];
            if($pretask->deleted) continue;
            if($action != $relation->action and $task->status != 'wait') continue;
            if($relation->condition == 'begin' and helper::isZeroDate($pretask->realStarted) and empty($pretask->finishedBy))
            {
                $noticeType = $action == 'begin' ? 'notSS' : 'notSF';
                $message .= sprintf($this->lang->task->gantt->notice->$noticeType, "$id::" . $pretask->name) . '\n';
            }
            elseif($relation->condition == 'end' and empty($pretask->finishedBy))
            {
                $noticeType = $action == 'begin' ? 'notFS' : 'notFF';
                $message .= sprintf($this->lang->task->gantt->notice->$noticeType, "$id::" . $pretask->name) . '\n';
            }
        }

        return $message;
    }

    public function addTaskEstimate($data)
    {
        $this->app->loadLang('effort');
        $oldTask = $this->getById($data->task);

        $relation = $this->loadModel('action')->getRelatedFields('task', $data->task);

        $action = $data->left == 0 ? 'finished' : 'started';
        if(($oldTask->status != 'wait' and $oldTask->status != 'pause') and $action == 'started') $action = 'edited';
        if(($oldTask->status == 'done' or $oldTask->status == 'closed' or $oldTask->status == 'cancel') and $action == 'finished') $action = 'edited';
        if($this->app->rawMethod == 'start') $action = 'started';

        $effort = new stdclass();
        $effort->objectType = 'task';
        $effort->objectID   = $data->task;
        $effort->execution  = $oldTask->execution;
        $effort->product    = $relation['product'];
        $effort->project    = (int)$relation['project'];
        $effort->account    = $data->account;
        $effort->date       = $data->date;
        $effort->consumed   = $data->consumed;
        $effort->left       = $data->left;
        $effort->work       = $this->lang->action->label->$action . $this->lang->effort->objectTypeList['task'] . " : " . $oldTask->name;
        $effort->vision     = $this->config->vision;
        $effort->order      = isset($data->order) ? $data->order : 0;
        $this->dao->insert(TABLE_EFFORT)->data($effort)->autoCheck()->exec();

        $effortID = $this->dao->lastInsertID();
        $this->action->create('effort', $effortID, 'created', '', '', '', false);

        return $effortID;
    }
}
