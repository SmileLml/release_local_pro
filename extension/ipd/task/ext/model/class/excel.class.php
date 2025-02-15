<?php
class excelTask extends taskModel
{
    public function setListValue($executionID)
    {
        $execution  = $this->loadModel('execution')->getByID($executionID);
        $stories  = $this->loadModel('story')->getExecutionStories($executionID);
        $priList  = $this->lang->task->priList;
        $typeList = $this->lang->task->typeList;
        $members  = $this->execution->getTeamMembers($executionID);

        $showAllModule = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
        $modules       = $this->loadModel('tree')->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');

        unset($typeList['']);

        foreach($modules  as $id => $module) $modules[$id] .= "(#$id)";
        foreach($stories  as $id => $story)  $stories[$id]  = "$story->title(#$story->id)";
        foreach($members  as $id => $member) $members[$id]  = "$member->realname(#$member->account)";

        $this->post->set('moduleList',     array_values($modules));
        $this->post->set('storyList',      array_values($stories));
        $this->post->set('assignedToList', array_values($members));
        $this->post->set('priList',      join(',', $priList));
        $this->post->set('typeList',     join(',', $typeList));
        $this->post->set('listStyle',  $this->config->task->export->listFields);
        $this->post->set('extraNum',   0);
        $this->post->set('execution',    $execution->name);
    }

    public function createFromImport($executionID)
    {
        $this->loadModel('action');
        $this->loadModel('file');
        $this->loadModel('story');
        $now  = helper::now();
        $data = fixer::input('post')->get();

        if(!empty($_POST['id']))
        {
            $oldTasks = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in(($_POST['id']))->andWhere('execution')->eq($executionID)->fetchAll('id');
            $oldTeams = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('`task`')->in(($_POST['id']))->fetchGroup('task', 'id');
        }

        $projects = $this->dao->select('id,project')->from(TABLE_EXECUTION)->where('id')->in(array_unique($data->execution))->fetchPairs('id', 'project');

        $tasks        = array();
        $line         = 1;
        $extendFields = array();

        if($this->config->edition != 'open')
        {
            $extendFields = $this->getFlowExtendFields();
            $notEmptyRule = $this->loadModel('workflowrule')->getByTypeAndRule('system', 'notempty');

            foreach($extendFields as $extendField)
            {
                if(strpos(",$extendField->rules,", ",$notEmptyRule->id,") !== false)
                {
                    $this->config->task->create->requiredFields .= ',' . $extendField->field;
                }
            }
        }

        foreach($data->execution as $key => $execution)
        {
            $taskData = new stdclass();

            $taskData->project      = zget($projects, $execution, 0);
            $taskData->execution    = $execution;
            $taskData->module       = (int)$data->module[$key];
            $taskData->name         = trim($data->name[$key]);
            $taskData->desc         = nl2br(strip_tags($this->post->desc[$key], $this->config->allowedTags));
            $taskData->story        = isset($data->story) ? (int)$data->story[$key] : 0;
            $taskData->pri          = (int)$data->pri[$key];
            $taskData->assignedTo   = $data->assignedTo[$key];
            $taskData->type         = $data->type[$key];
            $taskData->estimate     = (float)$data->estimate[$key];
            $taskData->estStarted   = empty($data->estStarted[$key]) ? '0000-00-00' : $data->estStarted[$key];
            $taskData->deadline     = empty($data->deadline[$key]) ? '0000-00-00' : $data->deadline[$key];
            $taskData->mode         = $data->mode[$key];
            if(!empty($data->assignedTo[$key])) $taskData->assignedDate = $now;

            foreach($extendFields as $extendField)
            {
                $dataArray = $_POST[$extendField->field];
                $taskData->{$extendField->field} = $dataArray[$key];
                if(is_array($taskData->{$extendField->field})) $taskData->{$extendField->field} = join(',', $taskData->{$extendField->field});

                $taskData->{$extendField->field} = htmlSpecialString($taskData->{$extendField->field});
            }

            if(isset($this->config->task->create->requiredFields))
            {
                $requiredFields = explode(',', $this->config->task->create->requiredFields);
                foreach($requiredFields as $requiredField)
                {
                    $requiredField = trim($requiredField);
                    if(empty($taskData->$requiredField)) dao::$errors[] = sprintf($this->lang->task->noRequire, $line, $this->lang->task->$requiredField);
                }
            }

            if(isset($this->config->task->appendFields))
            {
                foreach(explode(',', $this->config->task->appendFields) as $appendField)
                {
                    if(empty($appendField)) continue;
                    $taskData->$appendField = $_POST[$appendField][$key];
                }
            }

            $tasks[$key] = $taskData;
            $line++;
        }
        if(dao::isError()) die(js::error(dao::getError()));

        $tasksID      = array();
        $parentIDList = array();
        foreach($tasks as $key => $taskData)
        {
            $taskID = 0;
            if(!empty($_POST['id'][$key]) and empty($_POST['insert']))
            {
                $taskID = $data->id[$key];
                if(!isset($oldTasks[$taskID])) $taskID = 0;
            }

            if($data->mode[$key])
            {
                if($data->assignedTo[$key] and !in_array($data->assignedTo[$key], $data->team[$key])) die(js::alert($this->lang->task->error->assignedToError));
                if(empty($data->mode[$key])) $taskData->mode = 'linear';
            }

            if($taskID)
            {
                /* Process assignedTo.*/
                if($taskData->story != $oldTasks[$taskID]->story) $taskData->storyVersion = $this->story->getVersion($taskData->story);
                $taskData->desc   = str_replace('src="' . common::getSysURL() . '/', 'src="', $taskData->desc);
                $taskData->status = $oldTasks[$taskID]->status;

                $oldTask = (array)$oldTasks[$taskID];
                $newTask = (array)$taskData;
                $oldTask['desc'] = trim($this->file->excludeHtml($oldTask['desc'], 'noImg'));
                $newTask['desc'] = trim($this->file->excludeHtml($newTask['desc'], 'noImg'));
                $changes = common::createChanges((object)$oldTask, (object)$newTask);
                if(empty($changes)) continue;

                /* Ignore updating tasks for different executions. */
                if($oldTask['execution'] != $newTask['execution']) continue;

                if($oldTask['estimate'] == 0 and $oldTask['left'] == 0) $taskData->left = $taskData->estimate;

                $taskData->lastEditedBy   = $this->app->user->account;
                $taskData->lastEditedDate = $now;

                $this->dao->update(TABLE_TASK)->data($taskData)
                    ->autoCheck()
                    ->checkFlow()
                    ->where('id')->eq((int)$taskID)->exec();

                if(!dao::isError())
                {
                    if($oldTask['parent'] > 0)$this->updateParentStatus($oldTask['id']);
                    $actionID = $this->action->create('task', $taskID, 'Edited', '');
                    $this->action->logHistory($actionID, $changes);
                    $tasksID[$key] = $taskID;
                }
            }
            else
            {
                if(strpos($this->post->name[$key], '>') === 0)
                {
                    if(!isset($parentIDList[$parentID])) $parentIDList[$parentID] = $parentID;

                    $taskData->parent = $parentID;
                    $this->dao->update(TABLE_TASK)->set('parent')->eq(-1)->where('id')->eq($parentID)->exec();
                    $taskData->name = ltrim($this->post->name[$key], '>');
                }

                if($taskData->story != false) $taskData->storyVersion = $this->loadModel('story')->getVersion($taskData->story);
                $taskData->left       = $taskData->estimate;
                $taskData->status     = 'wait';
                $taskData->openedBy   = $this->app->user->account;
                $taskData->openedDate = $now;
                $taskData->vision     = $this->config->vision;

                if($taskData->deadline != '' and strtotime($taskData->deadline) < strtotime($taskData->estStarted)) continue;
                $this->dao->insert(TABLE_TASK)->data($taskData)
                    ->autoCheck()
                    ->checkFlow()
                    ->exec();

                if(!dao::isError())
                {
                    $taskID = $this->dao->lastInsertID();
                    if(strpos($this->post->name[$key], '>') !== 0) $parentID = $taskID;
                    $this->loadModel('action')->create('task', $taskID, 'Opened', '');
                    $tasksID[$key] = $taskID;
                }
            }

            $teams = array();
            if($data->mode[$key])
            {
                $oldTeam = isset($oldTeams[$key]) ? $oldTeams[$key] : array();
                foreach($data->team[$key] as $id => $account)
                {
                    if(!$account or isset($teams[$account])) continue;

                    $member = new stdclass();
                    foreach($oldTeam as $teamID => $oldMember)
                    {
                        if($oldMember->account == $account)
                        {
                            $member = $oldMember;
                            unset($oldTeam[$teamID]);
                            break;
                        }
                    }

                    $member->task     = $taskID;
                    $member->account  = $account;
                    $member->estimate = $data->estimate[$key][$id];
                    $member->status   = 'wait';
                    if(!isset($member->left))  $member->left  = $member->estimate;
                    if(!isset($member->order)) $member->order = $id + 1;

                    $teams[] = $member;
                }

                $this->dao->delete()->from(TABLE_TASKTEAM)->where('task')->eq($taskID)->exec();
                if(!empty($teams))
                {
                    foreach($teams as $team) $this->dao->insert(TABLE_TASKTEAM)->data($team)->autoCheck()->exec();
                    $task = $this->getByID($taskID);
                    $this->computeHours4Multiple($task);
                }
            }
        }

        foreach($parentIDList as $parent) $this->computeWorkingHours($parent);

        if($this->post->isEndPage)
        {
            unlink($this->session->fileImport);
            unlink($this->session->fileImportFileName);
            unset($_SESSION['fileImport']);
            unset($_SESSION['fileImportFileName']);
            unset($_SESSION['fileImportExtension']);
        }

        return $tasksID;
    }

    /**
     * Process datas for task (split multiplayer tasks) and child tasks.
     *
     * @param  int    $taskData
     * @access public
     * @return void
     */
    public function processDatas4Task($taskData)
    {
        $estimateList = array();
        $parentList   = array();
        $datas = $taskData->datas;
        foreach($datas as $key => $data)
        {
            if(!$data) continue;
            foreach($data as $field => $value)
            {
                if(!$value) continue;
                if(($field == 'estimate' or $field == 'left' or $field == 'consumed') and strrpos($value, ':') !== false)
                {
                    $valueArray = explode("\n", $value);
                    $tmpArray = array();
                    foreach($valueArray as $tmpValue)
                    {
                        if(!$tmpValue) continue;
                        if(strpos($tmpValue, ':') === false) continue;
                        $tmpValue = explode(':', $tmpValue);
                        $account   = trim($tmpValue[0]);
                        if(strpos($account, '(#') !== false)
                        {
                            $account = trim(substr($account, strrpos($account, '(#') + 2), ')');
                        }
                        elseif(strpos($account, '#') === 0)
                        {
                            $account = trim(substr($tmpValue[0], 1));
                        }

                        $estimate  = $tmpValue[1];
                        $tmpArray[$account] = $estimate;
                    }
                    $data->$field = $tmpArray;
                }
                elseif($field == 'name')
                {
                    if(strpos($value, '[' . $this->lang->task->multipleAB . '] ') === 0) $value = str_replace('[' . $this->lang->task->multipleAB . '] ', '', $value);
                    $data->$field = $value;
                }
            }

            if(strpos($data->name, '>') === 0 and is_array($data->estimate)) return print(js::alert($this->lang->excel->help->taskTip) . js::locate('back'));
            if(strpos($data->name, '>') !== 0 and isset($data->estimate))
            {
                $parentRow                = $key;
                $estimateList[$parentRow] = 0;
            }
            if(strpos($data->name, '>') === 0 and isset($data->estimate))
            {
                if(!isset($parentList[$parentRow])) $parentList[$parentRow] = $parentRow;
                $estimateList[$parentRow] += (float)$data->estimate;
            }

            $data->execution = $this->session->taskTransferParams['executionID'];
            $datas[$key] = $data;
        }

        if($parentList) foreach($parentList as $line) $datas[$line]->estimate = $estimateList[$line];
        $taskData->datas = $datas;
        return $taskData;
    }
}
