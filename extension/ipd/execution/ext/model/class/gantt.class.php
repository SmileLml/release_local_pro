<?php
class ganttExecution extends executionModel
{
    /**
     * Create relation of tasks.
     *
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function createRelationOfTasks($executionID)
    {
        $relations = fixer::input('post')->get();
        $data->execution = $executionID;
        foreach($relations->id as $id)
        {
            if($relations->pretask[$id] != '' and $relations->condition[$id] != '' and $relations->task[$id] != '' and $relations->action[$id] != '')
            {
                $data->pretask   = $relations->pretask[$id];
                $data->condition = $relations->condition[$id];
                $data->task      = $relations->task[$id];
                $data->action    = $relations->action[$id];

                $this->dao->insert(TABLE_RELATIONOFTASKS)->data($data)->exec();
            }
        }
    }

    /**
     * Edit relation of tasks.
     *
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function editRelationOfTasks($executionID)
    {
        $relations = fixer::input('post')->get();

        $data = new stdclass();
        $data->execution = $executionID;

        /*Whether there is conflict between the judgment task relations.*/
        foreach($relations->pretask as $id => $pretask)
        {
            if(empty($pretask)) continue;
            if($pretask == $relations->task[$id])
            {
                dao::$errors = sprintf($this->lang->execution->gantt->warning->noEditSame, $id);
                return false;
            }
            foreach($relations->pretask as $newid => $newpretask)
            {
                if($newid != $id and $pretask == $newpretask and $relations->task[$id] == $relations->task[$newid])
                {
                    dao::$errors = sprintf($this->lang->execution->gantt->warning->noEditRepeat, $id, $newid);
                    return false;
                }
                if($newid != $id and $relations->task[$id] == $newpretask and $pretask == $relations->task[$newid])
                {
                    dao::$errors = sprintf($this->lang->execution->gantt->warning->noEditContrary, $id, $newid);
                    return false;
                }
            }
            foreach($relations->newpretask as $newid => $newpretask)
            {
                if(empty($newpretask)) continue;
                if($newpretask == $pretask and $relations->task[$id] == $relations->newtask[$newid])
                {
                    dao::$errors = sprintf($this->lang->execution->gantt->warning->noRepeat, $id, $newid);
                    return false;
                }
                if($relations->task[$id] == $newpretask and $pretask == $relations->newtask[$newid])
                {
                    dao::$errors = sprintf($this->lang->execution->gantt->warning->noContrary, $id, $newid);
                    return false;
                }
            }
        }
        foreach($relations->newpretask as $id => $pretask)
        {
            if(empty($pretask)) continue;
            if($pretask == $relations->newtask[$id])
            {
                dao::$errors = sprintf($this->lang->execution->gantt->warning->noNewSame, $id);
                return false;
            }
            foreach($relations->newpretask as $newid => $newpretask)
            {
                if(empty($pretask)) continue;
                if($newid != $id and $pretask == $newpretask and $relations->newtask[$id] == $relations->newtask[$newid])
                {
                    dao::$errors = sprintf($this->lang->execution->gantt->warning->noNewRepeat, $id, $newid);
                    return false;
                }
                if($newid != $id and $relations->newtask[$id] == $newpretask and $pretask == $relations->newtask[$newid])
                {
                    dao::$errors = sprintf($this->lang->execution->gantt->warning->noNewContrary, $id, $newid);
                    return false;
                }
            }
        }

        /* update relations.*/
        foreach($relations->id as $id)
        {
            if($relations->pretask[$id] != '' and $relations->condition[$id] != '' and $relations->task[$id] != '' and $relations->action[$id] != '')
            {
                $data->pretask   = $relations->pretask[$id];
                $data->condition = $relations->condition[$id];
                $data->task      = $relations->task[$id];
                $data->action    = $relations->action[$id];

                $this->dao->update(TABLE_RELATIONOFTASKS)->data($data)->where('id')->eq($id)->exec();
            }
        }

        /* create new relations.*/
        foreach($relations->newid as $id)
        {
            if($relations->newpretask[$id] != '' and $relations->newcondition[$id] != '' and $relations->newtask[$id] != '' and $relations->newaction[$id] != '')
            {
                $data->pretask   = $relations->newpretask[$id];
                $data->condition = $relations->newcondition[$id];
                $data->task      = $relations->newtask[$id];
                $data->action    = $relations->newaction[$id];

                $this->dao->insert(TABLE_RELATIONOFTASKS)->data($data)->exec();
            }
        }
    }

    /**
     * Get relations of tasks.
     *
     * @param  int    $executionID
     * @access public
     * @return array
     */
    public function getRelationsOfTasks($executionID)
    {
        $relations = $this->dao->select('*')->from(TABLE_RELATIONOFTASKS)->where('execution')->eq($executionID)->fetchAll('id');
        return $relations;
    }

    /**
     * Get data for gantt.
     *
     * @param  int    $executionID
     * @param  string $type
     * @param  string $orderBy
     * @access public
     * @return string
     */
    public function getDataForGantt($executionID, $type, $orderBy)
    {
        $this->app->loadLang('task');
        $relations  = $this->dao->select('*')->from(TABLE_RELATIONOFTASKS)->where('execution')->eq($executionID)->fetchGroup('task', 'pretask');
        $taskGroups = $this->dao->select('t1.*, t2.realname,t3.branch')->from(TABLE_TASK)->alias('t1')
            ->leftJoin(TABLE_USER)->alias('t2')->on('t1.assignedTo = t2.account')
            ->leftJoin(TABLE_STORY)->alias('t3')->on('t1.story = t3.id')
            ->where('t1.execution')->eq($executionID)
            ->andWhere('t1.deleted')->eq(0)
            ->andWhere('t1.status')->ne('cancel')
            ->orderBy("{$type}_asc,id_asc")
            ->fetchGroup($type, 'id');

        $products     = $this->loadModel('product')->getProducts($executionID, 'all', '', false);
        $branchGroups = $this->loadModel('branch')->getByProducts(array_keys($products));
        $branches     = array();
        foreach($branchGroups as $product => $productBranch)
        {
            foreach($productBranch as $branchID => $branchName) $branches[$branchID] = $branchName;
        }

        $execution = $this->dao->select('*')->from(TABLE_EXECUTION)->where('id')->eq($executionID)->fetch();
        if($type == 'story') $stories = $this->dao->select('*')->from(TABLE_STORYSPEC)->where('story')->in(array_keys($taskGroups))->fetchGroup('story', 'version');
        if($type == 'module')
        {
            $showAllModule = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
            $modules       = $this->loadModel('tree')->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');
            $orderedGroup  = array();
            foreach($modules as $moduleID => $moduleName)
            {
                if(isset($taskGroups[$moduleID])) $orderedGroup[$moduleID] = $taskGroups[$moduleID];
            }
            $taskGroups = $orderedGroup;
        }
        if($type == 'assignedTo') $users = $this->loadModel('user')->getPairs('noletter');

        $groupID    = 0;
        $ganttGroup = array();
        list($orderField, $orderDirect) = $this->parseOrderBy($orderBy);

        /* Fix bug #24555. */
        $taskIdList = array();
        foreach($taskGroups as $group => $tasks) $taskIdList = array_merge($taskIdList, array_keys($tasks));
        $teamGroups = $this->dao->select('t1.*,t2.realname')->from(TABLE_TASKTEAM)->alias('t1')
            ->leftJoin(TABLE_USER)->alias('t2')->on('t1.account = t2.account')
            ->where('t1.task')->in($taskIdList)
            ->orderBy('t1.order')
            ->fetchGroup('task', 'account');
        foreach($taskGroups as $group => $tasks)
        {
            foreach($tasks as $id => $task)
            {
                if($task->mode == 'multi')
                {
                    if($type == 'assignedTo')
                    {
                        $team = zget($teamGroups, $id, array());
                        foreach($team as $account => $member)
                        {
                            if($account == $group) continue;
                            if(!isset($taskGroups[$account])) $taskGroups[$account] = array();

                            $taskGroups[$account][$id] = clone $task;
                            $taskGroups[$account][$id]->id         = $id . '_' . $account;
                            $taskGroups[$account][$id]->realID     = $id;
                            $taskGroups[$account][$id]->assignedTo = $account;
                            $taskGroups[$account][$id]->realname   = $member->realname;
                        }
                    }
                    else
                    {
                        $task->assignedTo = $this->lang->task->team;
                    }
                }
            }
        }

        foreach($taskGroups as $group => $tasks)
        {
            $groupID --;
            $groupName = $group;
            if($type == 'type')   $groupName = zget($this->lang->task->typeList, $group);
            if($type == 'module') $groupName = zget($modules, $group);
            if($type == 'assignedTo') $groupName = zget($users, $group);
            if($type == 'story')
            {
                $task = current($tasks);
                if(isset($stories[$group][$task->storyVersion]))
                {
                    $story = $stories[$group][$task->storyVersion];
                    $groupName = $story->title;
                    unset($taskGroups[$group]);
                    $group = $groupName;
                }
                if((string)$groupName === '0') $groupName = $this->lang->task->noStory;
            }

            $data             = new stdclass();
            $data->id         = $groupID;
            $data->text       = $groupName;
            $data->start_date = '';
            $data->deadline   = '';
            $data->priority   = '';
            $data->owner_id   = '';
            $data->progress   = '';
            $data->parent     = 0;
            $data->open       = true;

            $groupKey = $type == 'story' ? $groupID : $groupID . $group;
            $ganttGroup[$groupKey]['common'] = $data;

            $totalConsumed = 0;
            $totalHours    = 0;
            $totalLeft     = 0;
            $totalEstimate = 0;
            $minStartDate  = '';
            $maxDeadline   = '';
            $minRealBegan  = '';
            $maxRealEnd    = '';
            $ganttItems    = array();
            $orderKeys     = array();
            $today         = helper::today();

            foreach($tasks as $id => $task)
            {
                $ganttItem = $this->buildGanttItem(($task->parent > 0 and isset($tasks[$task->parent])) ? $task->parent : $groupID, $task, $execution, $branches);
                $ganttItems[$id] = $ganttItem;

                $taskLeft = ($task->status == 'wait' and $task->left == 0) ? $task->estimate : $task->left;

                $totalConsumed += $task->consumed;
                $totalHours    += $taskLeft + $task->consumed;
                $totalLeft     += $taskLeft;
                $totalEstimate += $task->estimate;

                if(empty($minStartDate)) $minStartDate = $ganttItem->start_date;
                if(!empty($ganttItem->start_date) and strtotime($ganttItem->start_date) < strtotime($minStartDate)) $minStartDate = $ganttItem->start_date;

                if(empty($maxDeadline)) $maxDeadline = $ganttItem->deadline;
                if(strtotime($ganttItem->deadline) > strtotime($maxDeadline)) $maxDeadline = $ganttItem->deadline;

                if(empty($minRealBegan)) $minRealBegan = $ganttItem->realBegan;
                if(!empty($ganttItem->realBegan) and strtotime($ganttItem->realBegan) < strtotime($minRealBegan)) $minRealBegan = $ganttItem->realBegan;

                if(empty($maxRealEnd)) $maxRealEnd = $ganttItem->realEnd;
                if(strtotime($ganttItem->realEnd) > strtotime($maxRealEnd)) $maxRealEnd = $ganttItem->realEnd;

                $orderKeys[$id] = $orderField ? $ganttItem->{$orderField} : $id;
                if($orderField == 'start_date') $orderKeys[$id] = date('Y-m-d', strtotime($orderKeys[$id]));

                $ganttItem->isDelay = false;

                /* Check delay. */
                if($today > $ganttItem->deadline and !in_array($task->status, array('done', 'closed', 'cancel'))) $ganttItem->isDelay = true;
            }

            if($orderField)
            {
                if($orderDirect == 'asc')  asort($orderKeys);
                if($orderDirect == 'desc') arsort($orderKeys);
            }
            foreach($orderKeys as $id => $fieldValue) $ganttGroup[$groupKey][$id] = $ganttItems[$id];

            $ganttGroup[$groupKey]['common']->progress   = $totalHours == 0 ? 0 : round($totalConsumed / $totalHours, 4);
            $ganttGroup[$groupKey]['common']->start_date = $minStartDate;
            $ganttGroup[$groupKey]['common']->deadline   = $maxDeadline;
            $ganttGroup[$groupKey]['common']->realBegan  = $minRealBegan;
            $ganttGroup[$groupKey]['common']->realEnd    = $maxRealEnd;
            $ganttGroup[$groupKey]['common']->consumed   = $totalConsumed;
            $ganttGroup[$groupKey]['common']->estimate   = $totalEstimate;
            $ganttGroup[$groupKey]['common']->left       = $totalLeft;
            $ganttGroup[$groupKey]['common']->duration   = helper::diffDate($maxDeadline, $minStartDate) + 1;
        }
        if($type == 'story') krsort($ganttGroup);

        $execution = array();
        foreach($ganttGroup as $groupID => $tasks)
        {
            foreach($tasks as $task)
            {
                $task->color         = $this->lang->execution->gantt->stage->color;
                $task->progressColor = $this->lang->execution->gantt->stage->progressColor;
                $task->textColor     = $this->lang->execution->gantt->stage->textColor;
                if(isset($task->pri))
                {
                    $task->color         = zget($this->lang->execution->gantt->color, $task->pri, $this->lang->execution->gantt->defaultColor);
                    $task->progressColor = zget($this->lang->execution->gantt->progressColor, $task->pri, $this->lang->execution->gantt->defaultProgressColor);
                    $task->textColor     = zget($this->lang->execution->gantt->textColor, $task->pri, $this->lang->execution->gantt->defaultTextColor);
                }
                $task->bar_height    = $this->lang->execution->gantt->bar_height;

                $execution['data'][] = $task;
            }
        }
        foreach($relations as $taskID => $preTasks)
        {
            foreach($preTasks as $preTask => $relation)
            {
                $link['id']     = $preTask;
                $link['source'] = $preTask;
                $link['target'] = $taskID;
                $link['type']   = $this->config->execution->gantt->linkType[$relation->condition][$relation->action];
                $execution['links'][] = $link;
            }
        }
        return json_encode($execution);
    }

    /**
     * Build gantt item.
     *
     * @param  int    $groupID
     * @param  object $task
     * @param  object $execution
     * @param  array  $branches
     * @access public
     * @return object
     */
    public function buildGanttItem($groupID, $task, $execution, $branches)
    {
        $today       = helper::today();
        $account     = $this->app->user->account;
        $ganttFields = $this->config->execution->ganttCustom->ganttFields;
        $showID      = strpos($ganttFields, 'id') !== false ? 1 : 0;
        $showBranch  = strpos($ganttFields, 'branch') !== false ? 1 : 0;

        $start = '';
        if(helper::isZeroDate($task->realStarted) and helper::isZeroDate($task->estStarted))
        {
            $start = date('d-m-Y', strtotime($execution->begin));
        }
        else
        {
            $start = helper::isZeroDate($task->realStarted) ? $task->estStarted : $task->realStarted;
            $start = date('d-m-Y', strtotime($start));
        }

        $end = '';
        $end = helper::isZeroDate($task->deadline) ? $execution->end : $task->deadline;
        $end = (in_array($task->status, array('done', 'closed')) and !helper::isZeroDate($task->finishedDate)) ? $task->finishedDate : $end;
        $end = date('Y-m-d', strtotime($end));

        $name  = '';
        if($showID) $name .= '#' . (empty($task->realID) ? $task->id : $task->realID) . ' ';
        if(isset($branches[$task->branch]) and $showBranch) $name .= "<span class='label label-info'>{$branches[$task->branch]}</span> ";
        $name    .= $task->name;
        $taskPri  = zget($this->lang->task->priList, $task->pri);
        $taskPri  = mb_substr($taskPri, 0, 1, 'UTF-8');
        $priIcon  = "<span class='label-pri label-pri-$task->pri' title='$taskPri'>$taskPri</span> ";

        $data             = new stdclass();
        $data->id         = $task->id;
        $data->text       = $priIcon . $name;
        $data->start_date = $start;
        $data->deadline   = $end;
        $data->pri        = $task->pri;
        $data->estimate   = $task->estimate;
        $data->consumed   = $task->consumed;
        $data->left       = $task->left;
        $data->openedBy   = $task->openedBy;
        $data->finishedBy = $task->finishedBy;
        $data->duration   = helper::diffDate($end, $start) + 1;
        $data->owner_id   = $task->assignedTo;
        $data->progress   = ($task->consumed + $task->left) == 0 ? 0 : round($task->consumed / ($task->consumed + $task->left), 4);
        $data->parent     = $groupID;
        $data->status     = isset($this->lang->task->statusList[$task->status]) ? $this->lang->task->statusList[$task->status] : '';
        $data->open       = true;
        $data->realBegan  = helper::isZeroDate($task->realStarted) ? '' : date('Y-m-d', strtotime($task->realStarted));
        $data->realEnd    = helper::isZeroDate($task->finishedDate) ? '' : date('Y-m-d', strtotime($task->finishedDate));
        $data->delay      = $this->lang->programplan->delayList[0];
        $data->isDelay    = false;
        $data->delayDays  = 0;

        if($today > $task->deadline and !in_array($task->status, array('done', 'closed', 'cancel'))) $data->isDelay = true;

        if(($today > $end) and !in_array($task->status, array('done', 'closed', 'cancel')) and $execution->status != 'closed')
        {
            $data->delay     = $this->lang->programplan->delayList[1];
            $data->delayDays = helper::diffDate(($task->status == 'done' || $task->status == 'closed') ? substr($task->finishedDate, 0, 10) : $today, substr($end, 0, 10));
        }



        return $data;
    }

    /**
     * Delete relation.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function deleteRelation($id)
    {
        $this->dao->delete()->from(TABLE_RELATIONOFTASKS)->where('id')->eq($id)->exec();
    }

    /**
     * Parse orderBy.
     *
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function parseOrderBy($orderBy)
    {
        $orderField  = '';
        $orderDirect = '';
        if($orderBy)
        {
            $orderBy     = str_replace('_', ' ', $orderBy);
            $orderBy     = explode(' ', $orderBy);
            $orderDirect = end($orderBy);
            if($orderDirect == 'asc' or $orderDirect == 'desc')
            {
                array_pop($orderBy);
            }
            else
            {
                $orderDirect = 'asc';
            }
            $orderField = join('_', $orderBy);
        }

        return array($orderField, $orderDirect);
    }

    /**
     * Build kanban orderBy.
     *
     * @param  string $field
     * @param  string $currentOrder
     * @param  string $currentDirect
     * @access public
     * @return array
     */
    public function buildKanbanOrderBy($field, $currentOrder, $currentDirect)
    {
        $fieldOrderBy = "{$field}_asc";
        $fieldClass   = "{$field}_head sort";
        if($currentOrder == $field)
        {
            $fieldOrderBy  = "{$field}_";
            $fieldOrderBy .= $currentDirect == 'asc' ? 'desc' : 'asc';
            $fieldClass   .= $currentDirect == 'asc' ? ' sort-up' : ' sort-down';
        }

        return array($fieldOrderBy, $fieldClass);
    }
}
