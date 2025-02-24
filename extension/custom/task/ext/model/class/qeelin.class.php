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

    public function create($executionID = 0, $bugID = 0)
    {
        $feedbackID = $this->post->feedback;
        if(!empty($feedbackID))
        {
            $fileIDPairs = $this->loadModel('file')->copyObjectFiles('task');
            if(isset($_POST['deleteFiles'])) unset($_POST['deleteFiles']);
        }

        if((float)$this->post->estimate < 0)
        {
            dao::$errors[] = $this->lang->task->error->recordMinus;
            return false;
        }

        if(!empty($this->config->limitTaskDate))
        {
            $this->checkEstStartedAndDeadline($executionID, $this->post->estStarted, $this->post->deadline);
            if(dao::isError()) return false;
        }

        $executionID    = (int)$executionID;
        $estStarted     = null;
        $deadline       = null;
        $assignedTo     = '';
        $taskIdList     = array();
        $taskDatas      = array();
        $taskFiles      = array();
        $requiredFields = "," . $this->config->task->create->requiredFields . ",";

        if($this->post->selectTestStory)
        {
            foreach($this->post->testStory as $i => $storyID)
            {
                if(empty($storyID)) continue;
            }

            /* Check required fields when create test task. */
            foreach($this->post->testStory as $i => $storyID)
            {
                if(empty($storyID)) continue;
                $estStarted = (!isset($this->post->testEstStarted[$i]) or (isset($this->post->estStartedDitto[$i]) and $this->post->estStartedDitto[$i] == 'on')) ? $estStarted : $this->post->testEstStarted[$i];
                $deadline   = (!isset($this->post->testDeadline[$i]) or (isset($this->post->deadlineDitto[$i]) and $this->post->deadlineDitto[$i] == 'on'))     ? $deadline : $this->post->testDeadline[$i];
                $assignedTo = (!isset($this->post->testAssignedTo[$i]) or $this->post->testAssignedTo[$i] == 'ditto') ? $assignedTo : $this->post->testAssignedTo[$i];

                if(!empty($this->config->limitTaskDate))
                {
                    $this->checkEstStartedAndDeadline($executionID, $estStarted, $deadline);
                    if(dao::isError())
                    {
                        foreach(dao::getError() as $field => $error)
                        {
                            dao::$errors[] = $error;
                            return false;
                        }
                    }
                }

                if($estStarted > $deadline)
                {
                    dao::$errors[] = "ID: $storyID {$this->lang->task->error->deadlineSmall}";
                    return false;
                }

                $task = new stdclass();
                $task->pri        = $this->post->testPri[$i];
                $task->estStarted = $estStarted;
                $task->deadline   = $deadline;
                $task->assignedTo = $assignedTo;
                $task->estimate   = $this->post->testEstimate[$i];
                $task->left       = $this->post->testEstimate[$i];

                /* Check requiredFields */
                $this->dao->insert(TABLE_TASK)->data($task)->batchCheck($requiredFields, 'notempty');
                if(dao::isError())
                {
                    foreach(dao::getError() as $field => $error)
                    {
                        dao::$errors[] = $error;
                        return false;
                    }
                }
                $taskDatas[$i] = $task;
            }

            $requiredFields = str_replace(",estimate,", ',', "$requiredFields");
            $requiredFields = str_replace(",story,", ',', "$requiredFields");
            $requiredFields = str_replace(",estStarted,", ',', "$requiredFields");
            $requiredFields = str_replace(",deadline,", ',', "$requiredFields");
            $requiredFields = str_replace(",module,", ',', "$requiredFields");
        }

        $this->loadModel('file');
        $task = fixer::input('post')
            ->setDefault('execution', $executionID)
            ->setDefault('estimate,left,story', 0)
            ->setDefault('status', 'wait')
            ->setDefault('project', $this->getProjectID($executionID))
            ->setIF($this->post->estimate != false, 'left', $this->post->estimate)
            ->setIF($this->post->story != false, 'storyVersion', $this->loadModel('story')->getVersion($this->post->story))
            ->setIF(strpos($requiredFields, 'estStarted') !== false, 'estStarted', helper::isZeroDate($this->post->estStarted) ? '' : $this->post->estStarted)
            ->setIF(strpos($requiredFields, 'deadline') !== false, 'deadline', helper::isZeroDate($this->post->deadline) ? '' : $this->post->deadline)
            ->setIF(strpos($requiredFields, 'estimate') !== false, 'estimate', $this->post->estimate)
            ->setIF(strpos($requiredFields, 'left') !== false, 'left', $this->post->left)
            ->setIF(strpos($requiredFields, 'story') !== false, 'story', $this->post->story)
            ->setIF(is_numeric($this->post->estimate), 'estimate', (float)$this->post->estimate)
            ->setIF(is_numeric($this->post->consumed), 'consumed', (float)$this->post->consumed)
            ->setIF(is_numeric($this->post->left),     'left',     (float)$this->post->left)
            ->setIF(!$this->post->estStarted, 'estStarted', null)
            ->setIF(!$this->post->deadline, 'deadline', null)
            ->setDefault('openedBy',   $this->app->user->account)
            ->setDefault('openedDate', helper::now())
            ->setDefault('vision', $this->config->vision)
            ->cleanINT('execution,story,module')
            ->stripTags($this->config->task->editor->create['id'], $this->config->allowedTags)
            ->join('mailto', ',')
            ->remove('after,files,labels,assignedTo,uid,storyEstimate,storyDesc,storyPri,team,teamSource,teamEstimate,teamConsumed,teamLeft,teamMember,multiple,teams,contactListMenu,selectTestStory,testStory,testPri,testEstStarted,testDeadline,testAssignedTo,testEstimate,sync,otherLane,region,lane,estStartedDitto,deadlineDitto,newTaskFileID,deleteFiles')
            ->add('version', 1)
            ->get();

        if($task->type != 'test') $this->post->set('selectTestStory', 0);

        foreach($this->post->assignedTo as $assignedTo)
        {
            /* When type is affair and has assigned then ignore none. */
            if($task->type == 'affair' and count($this->post->assignedTo) > 1 and empty($assignedTo)) continue;

            $task->assignedTo = $assignedTo;
            if($assignedTo) $task->assignedDate = helper::now();

            /* Check duplicate task. */
            if($task->type != 'affair' and $task->name)
            {
                $result = $this->loadModel('common')->removeDuplicate('task', $task, "execution={$executionID} and story=" . (int)$task->story . (isset($task->feedback) ? " and feedback=" . (int)$task->feedback : ''));
                if($result['stop'])
                {
                    $taskIdList[$assignedTo] = array('status' => 'exists', 'id' => $result['duplicate']);
                    continue;
                }
            }

            $task = $this->loadModel('file')->processImgURL($task, $this->config->task->editor->create['id'], $this->post->uid);

            /* Fix Bug #1525 */
            $execution = $this->dao->select('*')->from(TABLE_PROJECT)->where('id')->eq($task->execution)->fetch();
            if($execution->lifetime == 'ops' or $execution->attribute == 'request' or $execution->attribute == 'review')
            {
                $requiredFields = str_replace(",story,", ',', "$requiredFields");
                $task->story = 0;
            }

            if(strpos($requiredFields, ',estimate,') !== false)
            {
                if(strlen(trim($task->estimate)) == 0) dao::$errors['estimate'] = sprintf($this->lang->error->notempty, $this->lang->task->estimate);
                $requiredFields = str_replace(',estimate,', ',', $requiredFields);
            }

            if(strpos($requiredFields, ',estStarted,') !== false and !isset($task->estStarted)) dao::$errors['estStarted'] = sprintf($this->lang->error->notempty, $this->lang->task->estStarted);
            if(strpos($requiredFields, ',deadline,') !== false and !isset($task->deadline)) dao::$errors['deadline'] = sprintf($this->lang->error->notempty, $this->lang->task->deadline);
            if(isset($task->estStarted) and isset($task->deadline) and !helper::isZeroDate($task->deadline) and $task->deadline < $task->estStarted) dao::$errors['deadline'] = sprintf($this->lang->error->ge, $this->lang->task->deadline, $task->estStarted);

            if(dao::isError()) return false;

            $requiredFields = trim($requiredFields, ',');

            /* Fix Bug #2466 */
            if($this->post->multiple)  $task->assignedTo = '';
            if(!$this->post->multiple or count(array_filter($this->post->team)) < 1) $task->mode = '';
            $this->dao->insert(TABLE_TASK)->data($task, $skip = 'gitlab,gitlabProject')
                ->autoCheck()
                ->batchCheck($requiredFields, 'notempty')
                ->checkIF($task->estimate != '', 'estimate', 'float')
                ->checkIF(!helper::isZeroDate($task->deadline), 'deadline', 'ge', $task->estStarted)
                ->checkFlow()
                ->exec();

            if(dao::isError()) return false;

            $taskID = $this->dao->lastInsertID();

            if($bugID > 0)
            {
                $this->dao->update(TABLE_TASK)->set('fromBug')->eq($bugID)->where('id')->eq($taskID)->exec();
                $this->dao->update(TABLE_BUG)->set('toTask')->eq($taskID)->where('id')->eq($bugID)->exec();
                $this->loadModel('action')->create('bug', $bugID, 'converttotask', '', $taskID);
            }

            /* Mark design version.*/
            if(isset($task->design) && !empty($task->design))
            {
                $design = $this->loadModel('design')->getByID($task->design);
                $this->dao->update(TABLE_TASK)->set('designVersion')->eq($design->version)->where('id')->eq($taskID)->exec();
            }

            $taskSpec = new stdClass();
            $taskSpec->task       = $taskID;
            $taskSpec->version    = $task->version;
            $taskSpec->name       = $task->name;

            if($task->estStarted) $taskSpec->estStarted = $task->estStarted;
            if($task->deadline)   $taskSpec->deadline   = $task->deadline;

            $this->dao->insert(TABLE_TASKSPEC)->data($taskSpec)->autoCheck()->exec();
            if(dao::isError()) return false;

            if($this->post->story) $this->loadModel('story')->setStage($this->post->story);
            if($this->post->selectTestStory)
            {
                $testStoryIdList = array();
                $this->loadModel('action');
                if($this->post->testStory)
                {
                    foreach($this->post->testStory as $storyID)
                    {
                        if($storyID) $testStoryIdList[$storyID] = $storyID;
                    }
                    $testStories = $this->dao->select('id,title,version,module')->from(TABLE_STORY)->where('id')->in($testStoryIdList)->fetchAll('id');
                    foreach($this->post->testStory as $i => $storyID)
                    {
                        if(!isset($testStories[$storyID])) continue;

                        $assignedTo     = $taskDatas[$i]->assignedTo;
                        $testEstStarted = $taskDatas[$i]->estStarted;
                        $testDeadline   = $taskDatas[$i]->deadline;

                        $task->parent       = $taskID;
                        $task->story        = $storyID;
                        $task->storyVersion = $testStories[$storyID]->version;
                        $task->name         = $this->lang->task->lblTestStory . " #{$storyID} " . $testStories[$storyID]->title;
                        $task->pri          = $this->post->testPri[$i];
                        $task->estStarted   = $testEstStarted;
                        $task->deadline     = $testDeadline;
                        $task->assignedTo   = $assignedTo;
                        $task->estimate     = $this->post->testEstimate[$i];
                        $task->left         = $this->post->testEstimate[$i];
                        $task->module       = $testStories[$storyID]->module;
                        $this->dao->insert(TABLE_TASK)->data($task)->exec();

                        $childTaskID = $this->dao->lastInsertID();
                        $this->action->create('task', $childTaskID, 'Opened');
                    }

                    $this->computeWorkingHours($taskID);
                    $this->computeBeginAndEnd($taskID);
                    $this->dao->update(TABLE_TASK)->set('parent')->eq(-1)->where('id')->eq($taskID)->exec();
                }
            }
            $this->file->updateObjectID($this->post->uid, $taskID, 'task');
            if(!empty($taskFiles))
            {
                foreach($taskFiles as $taskFile)
                {
                    $taskFile->objectID = $taskID;
                    $this->dao->insert(TABLE_FILE)->data($taskFile)->exec();
                }
            }
            else
            {
                $taskFileTitle = $this->file->saveUpload('task', $taskID);
                $taskFiles     = $this->dao->select('*')->from(TABLE_FILE)->where('id')->in(array_keys($taskFileTitle))->fetchAll('id');

                foreach($taskFiles as $fileID => $taskFile) unset($taskFiles[$fileID]->id);
            }

            if($this->post->multiple and count(array_filter($this->post->team)) > 1)
            {
                $teams = $this->manageTaskTeam($task->mode, $taskID, 'wait');
                if($teams)
                {
                    $task->id = $taskID;
                    $this->computeHours4Multiple($task);
                }
            }

            if(!dao::isError()) $this->loadModel('score')->create('task', 'create', $taskID);
            $taskIdList[$assignedTo] = array('status' => 'created', 'id' => $taskID);
        }

        if($taskIdList)
        {
            /* If task is from feedback, record action for feedback. */
            if($feedbackID > 0)
            {
                foreach($taskIdList as $taskID)
                {
                    $taskID = $taskID['id'];
                    $feedback = new stdclass();
                    $feedback->status        = 'commenting';
                    $feedback->result        = $taskID;
                    $feedback->processedBy   = $this->app->user->account;
                    $feedback->processedDate = helper::now();
                    $feedback->solution      = 'totask';

                    $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($feedbackID)->exec();

                    $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'ToTask', '', $taskID);

                    if(!empty($feedbackID) && !empty($fileIDPairs))
                    {
                        if(!empty($fileIDPairs)) $this->dao->update(TABLE_FILE)->set('objectID')->eq($taskID)->where('id')->in($fileIDPairs)->exec();
                    }
                }
            }

            $deleteFiles   = $this->post->deleteFiles   ?: array();
            $newTaskFileID = $this->post->newTaskFileID ?: array();
            if(!empty($deleteFiles) || !empty($newTaskFileID))
            {
                foreach($deleteFiles as $fileID)
                {
                    if(isset($newTaskFileID[$fileID])) unset($newTaskFileID[$fileID]);
                }

                $i = 0;
                foreach($taskIdList as $taskID)
                {
                    $taskID = $taskID['id'];
                    if(!empty($newTaskFileID))
                    {
                        if($i++ == 0)
                        {
                            $this->dao->update(TABLE_FILE)->set('objectID')->eq($taskID)->where('id')->in($newTaskFileID)->exec();
                        }
                        else
                        {
                            $files = $this->file->getByIdList($newTaskFileID);
                            foreach($files as $fileID => $file)
                            {
                                $file->objectID = $taskID;
                                $this->dao->insert(TABLE_FILE)->data($file, 'id,webPath,realPath')->exec();
                            }
                        }
                    }
                }
                $deleteFiles = $this->loadModel('file')->getByIdList($deleteFiles);
                $this->dao->delete()->from(TABLE_FILE)->where('id')->in($this->post->deleteFiles)->exec();
                foreach($deleteFiles as $fileID => $file) $this->file->unlinkFile($file);
            }

            return $taskIdList;
        }
        return false;
    }
}
