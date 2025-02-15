<?php

/**
 * Get gantt data.
 *
 * @param  int     $projectID
 * @param  int     $productID
 * @param  int     $baselineID
 * @param  string  $selectCustom
 * @param  bool    $returnJson
 * @access public
 * @return string
 */
public function getDataForGantt($projectID, $productID, $baselineID = 0, $selectCustom = '', $returnJson = true)
{
    $this->loadModel('stage');
    $this->loadModel('execution');

    $plans = $this->getStage($projectID, $productID, 'all', 'order');
    if($baselineID)
    {
        $baseline = $this->loadModel('cm')->getByID($baselineID);
        $oldData  = json_decode($baseline->data);
        $oldPlans = $oldData->stage;
        foreach($oldPlans as $id => $oldPlan)
        {
            if(!isset($plans[$id])) continue;
            $plans[$id]->version   = $oldPlan->version;
            $plans[$id]->name      = $oldPlan->name;
            $plans[$id]->milestone = $oldPlan->milestone;
            $plans[$id]->begin     = $oldPlan->begin;
            $plans[$id]->end       = $oldPlan->end;
        }
    }

    $project        = $this->loadModel('project')->getByID($projectID);
    $today          = helper::today();
    $datas          = array();
    $planIdList     = array();
    $isMilestone    = "<icon class='icon icon-flag icon-sm red'></icon> ";
    $stageIndex     = array();
    $reviewDeadline = array();

    foreach($plans as $plan)
    {
        $plan->isParent = false;
        if(isset($plans[$plan->parent])) $plans[$plan->parent]->isParent = true;
    }

    foreach($plans as $plan)
    {
        $planIdList[$plan->id] = $plan->id;
        $reviewDeadline[$plan->id]['stageEnd'] = $plan->end;

        $start     = helper::isZeroDate($plan->begin) ? '' : $plan->begin;
        $end       = helper::isZeroDate($plan->end)   ? '' : $plan->end;
        $realBegan = helper::isZeroDate($plan->realBegan) ? '' : $plan->realBegan;
        $realEnd   = helper::isZeroDate($plan->realEnd)   ? '' : $plan->realEnd;

        $data = new stdclass();
        $data->id            = $plan->id;
        $data->type          = 'plan';
        $data->text          = empty($plan->milestone) ? $plan->name : $plan->name . $isMilestone ;
        $data->name          = $plan->name;
        if(isset($this->config->setPercent) and $this->config->setPercent == 1) $data->percent = $plan->percent;
        $data->attribute     = zget($this->lang->stage->typeList, $plan->attribute);
        $data->milestone     = zget($this->lang->programplan->milestoneList, $plan->milestone);
        $data->owner_id      = $plan->PM;
        $data->status        = $this->processStatus('execution', $plan);
        $data->begin         = $start;
        $data->deadline      = $end;
        $data->realBegan     = $realBegan ? substr($realBegan, 0, 10) : '';
        $data->realEnd       = $realEnd ? substr($realEnd, 0, 10) : '';;
        $data->parent        = $plan->grade == 1 ? 0 : $plan->parent;
        $data->isParent      = $plan->isParent;
        $data->open          = true;
        $data->start_date    = $start;
        $data->endDate       = $end;
        $data->duration      = 1;
        $data->color         = $this->lang->execution->gantt->stage->color;
        $data->progressColor = $this->lang->execution->gantt->stage->progressColor;
        $data->textColor     = $this->lang->execution->gantt->stage->textColor;
        $data->bar_height    = $this->lang->execution->gantt->bar_height;

        /* Determines if the object is delay. */
        $data->delay     = $this->lang->programplan->delayList[0];
        $data->delayDays = 0;

        if(($today > $end) and $plan->status != 'closed')
        {
            $data->delay     = $this->lang->programplan->delayList[1];
            $data->delayDays = helper::diffDate($today, substr($end, 0, 10));
        }

        if($data->endDate > $data->start_date) $data->duration = helper::diffDate(substr($data->endDate, 0, 10), substr($data->start_date, 0, 10)) + 1;
        if($data->start_date) $data->start_date = date('d-m-Y', strtotime($data->start_date));
        if($data->start_date == '' or $data->endDate == '') $data->duration = 1;

        $datas['data'][$plan->id] = $data;
        $stageIndex[$plan->id] = array('planID' => $plan->id, 'parent' => $plan->parent, 'progress' => array('totalEstimate' => 0, 'totalConsumed' => 0, 'totalReal' => 0));
    }

    $taskPri  = "<span class='label-pri label-pri-%s' title='%s'>%s</span> ";

    /* Judge whether to display tasks under the stage. */
    $owner   = $this->app->user->account;
    $module  = 'programplan';
    $section = 'browse';
    $object  = 'stageCustom';

    if(empty($selectCustom)) $selectCustom = $this->loadModel('setting')->getItem("owner={$owner}&module={$module}&section={$section}&key={$object}");

    $tasks     = $this->dao->select('*')->from(TABLE_TASK)->where('deleted')->eq(0)->andWhere('execution')->in($planIdList)->orderBy('execution_asc, order_asc, id_asc')->fetchAll('id');
    $taskTeams = $this->dao->select('task,account')->from(TABLE_TASKTEAM)->where('task')->in(array_keys($tasks))->fetchGroup('task', 'account');
    $users     = $this->loadModel('user')->getPairs('noletter');

    if($baselineID)
    {
        $oldTasks = $oldData->task;
        foreach($oldTasks as $id => $oldTask)
        {
            if(!isset($tasks[$id])) continue;
            $tasks[$id]->version    = $oldTask->version;
            $tasks[$id]->name       = $oldTask->name;
            $tasks[$id]->estStarted = $oldTask->estStarted;
            $tasks[$id]->deadline   = $oldTask->deadline;
        }
    }

    foreach($tasks as $task)
    {
        $execution = zget($plans, $task->execution, array());
        $pri       = zget($this->lang->task->priList, $task->pri);
        $pri       = mb_substr($pri, 0, 1, 'UTF-8');
        $priIcon   = sprintf($taskPri, $task->pri, $pri, $pri);

        $estStart  = helper::isZeroDate($task->estStarted)  ? '' : $task->estStarted;
        $estEnd    = helper::isZeroDate($task->deadline)    ? '' : $task->deadline;
        $realBegan = helper::isZeroDate($task->realStarted) ? '' : $task->realStarted;
        $realEnd   = (in_array($task->status, array('done', 'closed')) and !helper::isZeroDate($task->finishedDate)) ? $task->finishedDate : '';

        /* Get lastest task deadline. */
        $taskExecutionID = $execution->parent ? $execution->parent : $execution->id;
        if(isset($reviewDeadline[$taskExecutionID]['taskEnd']))
        {
            $reviewDeadline[$taskExecutionID]['taskEnd'] = $task->deadline > $reviewDeadline[$taskExecutionID]['taskEnd'] ? $task->deadline : $reviewDeadline[$taskExecutionID]['taskEnd'];
        }
        else
        {
            $reviewDeadline[$taskExecutionID]['taskEnd'] = $task->deadline;
        }

        $start = $estStart;
        $end   = $estEnd;
        if(empty($start) and $execution) $start = $execution->begin;
        if(empty($end)   and $execution) $end   = $execution->end;
        if($start > $end) $end = $start;

        $data = new stdclass();
        $data->id            = $task->execution . '-' . $task->id;
        $data->type          = 'task';
        $data->text          = $priIcon . $task->name;
        $data->percent       = '';
        $data->status        = $this->processStatus('task', $task);
        $data->owner_id      = $task->assignedTo;
        $data->attribute     = '';
        $data->milestone     = '';
        $data->begin         = $start;
        $data->deadline      = $end;
        $data->realBegan     = $realBegan ? substr($realBegan, 0, 10) : '';
        $data->realEnd       = $realEnd ? substr($realEnd, 0, 10) : '';
        $data->pri           = $task->pri;
        $data->parent        = $task->parent > 0 ? $task->execution . '-' . $task->parent : $task->execution;
        $data->open          = true;
        $progress            = $task->consumed ? round($task->consumed / ($task->left + $task->consumed), 3) : 0;
        $data->progress      = $progress;
        $data->taskProgress  = ($progress * 100) . '%';
        $data->start_date    = $start;
        $data->endDate       = $end;
        $data->duration      = 1;
        $data->estimate      = $task->estimate;
        $data->consumed      = $task->consumed;
        $data->color         = zget($this->lang->execution->gantt->color, $task->pri, $this->lang->execution->gantt->defaultColor);
        $data->progressColor = zget($this->lang->execution->gantt->progressColor, $task->pri, $this->lang->execution->gantt->defaultProgressColor);
        $data->textColor     = zget($this->lang->execution->gantt->textColor, $task->pri, $this->lang->execution->gantt->defaultTextColor);
        $data->bar_height    = $this->lang->execution->gantt->bar_height;

        /* Determines if the object is delay. */
        $data->delay     = $this->lang->programplan->delayList[0];
        $data->delayDays = 0;
        if(($today > $end) and $plan->status != 'closed')
        {
            $data->delay     = $this->lang->programplan->delayList[1];
            $data->delayDays = helper::diffDate(($task->status == 'done' || $task->status == 'closed') ? substr($task->finishedDate, 0, 10) : $today, substr($end, 0, 10));
        }

        /* If multi task then show the teams. */
        if($task->mode == 'multi' and !empty($taskTeams[$task->id]))
        {
            $teams     = array_keys($taskTeams[$task->id]);
            $assigneds = array();
            foreach($teams as $assignedTo) $assigneds[] = zget($users, $assignedTo);
            $data->owner_id = join(',', $assigneds);
        }

        if($data->endDate > $data->start_date) $data->duration = helper::diffDate(substr($data->endDate, 0, 10), substr($data->start_date, 0, 10)) + 1;
        if($data->start_date) $data->start_date = date('d-m-Y', strtotime($data->start_date));
        if($data->start_date == '' or $data->endDate == '') $data->duration = 1;

        if(strpos($selectCustom, 'task') !== false) $datas['data'][$data->id] = $data;
        if($task->parent == -1) continue;
        foreach($stageIndex as $index => $stage)
        {
            if($stage['planID'] == $task->execution)
            {
                $stageIndex[$index]['progress']['totalEstimate'] += $task->estimate;
                $stageIndex[$index]['progress']['totalConsumed'] += $task->consumed;
                $stageIndex[$index]['progress']['totalReal']     += ((($task->status == 'closed' || $task->status == 'cancel' || (isset($isParent) && $isParent)) ? 0 : $task->left) + $task->consumed);

                $parent = $stage['parent'];
                if(isset($stageIndex[$parent]))
                {
                    $stageIndex[$parent]['progress']['totalEstimate'] += $task->estimate;
                    $stageIndex[$parent]['progress']['totalConsumed'] += $task->consumed;
                    $stageIndex[$parent]['progress']['totalReal']     += ((($task->status == 'closed' || $task->status == 'cancel') ? 0 : $task->left) + $task->consumed);
                }
            }
        }
    }

    /* Build review points tree for ipd project. */
    if($project->model == 'ipd' and $datas)
    {
        $this->loadModel('review');
        $reviewPoints = $this->dao->select('t1.*, t2.status, t2.lastReviewedDate,t2.id as reviewID')->from(TABLE_OBJECT)->alias('t1')
            ->leftJoin(TABLE_REVIEW)->alias('t2')->on('t1.id = t2.object')
            ->where('t1.deleted')->eq('0')
            ->andWhere('t1.project')->eq($projectID)
            ->andWhere('t1.product')->eq($productID)
            ->fetchAll('id');

        foreach($datas['data'] as $plan)
        {
            if($plan->type != 'plan') continue;

            foreach($reviewPoints as $id => $point)
            {
                if(!isset($this->config->stage->ipdReviewPoint->{$plan->attribute})) continue;
                if(!isset($point->status)) $point->status = '';

                $categories = $this->config->stage->ipdReviewPoint->{$plan->attribute};
                if(in_array($point->category, $categories))
                {
                    if($point->end and !helper::isZeroDate($point->end))
                    {
                        $end = $point->end;
                    }
                    else
                    {
                        $end = $reviewDeadline[$plan->id]['stageEnd'];
                        if(strpos($point->category, "TR") !== false)
                        {
                            if(isset($reviewDeadline[$plan->id]['taskEnd']) and !helper::isZeroDate($reviewDeadline[$plan->id]['taskEnd']))
                            {
                                $end = $reviewDeadline[$plan->id]['taskEnd'];
                            }
                            else
                            {
                                $end = $this->getReviewDeadline($end);
                            }
                        }
                        elseif(strpos($point->category, "DCP") !== false)
                        {
                            $end = $this->getReviewDeadline($end, 2);
                        }
                    }

                    $data = new stdclass();
                    $data->id            = $plan->id . '-' . $point->category . '-' . $point->id;
                    $data->reviewID      = $point->reviewID;
                    $data->type          = 'point';
                    $data->text          = "<i class='icon-seal'></i> " . $point->title;
                    $data->name          = $point->title;
                    $data->attribute     = '';
                    $data->milestone     = '';
                    $data->owner_id      = '';
                    $data->rawStatus     = $point->status;
                    $data->status        = $point->status ? zget($this->lang->review->statusList, $point->status) : $this->lang->programplan->wait;
                    $data->status        = "<span class='status-{$point->status}'>" . $data->status . '</span>';
                    $data->begin         = $end;
                    $data->deadline      = $end;
                    $data->realBegan     = $point->createdDate;
                    $data->realEnd       = $point->lastReviewedDate;;
                    $data->parent        = $plan->id;
                    $data->open          = true;
                    $data->start_date    = $end;
                    $data->endDate       = $end;
                    $data->duration      = 1;
                    $data->color         = isset($this->lang->programplan->reviewColorList[$point->status]) ? $this->lang->programplan->reviewColorList[$point->status] : '#FC913F';
                    $data->progressColor = $this->lang->execution->gantt->stage->progressColor;
                    $data->textColor     = $this->lang->execution->gantt->stage->textColor;
                    $data->bar_height    = $this->lang->execution->gantt->bar_height;

                    if($data->start_date) $data->start_date = date('d-m-Y', strtotime($data->start_date));

                    if($selectCustom && strpos($selectCustom, "point") !== false && !$plan->parent) $datas['data'][$data->id] = $data;
                }
            }
        }
    }

    /* Calculate the progress of the phase. */
    foreach($stageIndex as $index => $stage)
    {
        $progress  = empty($stage['progress']['totalConsumed']) ? 0 : round($stage['progress']['totalConsumed'] / $stage['progress']['totalReal'], 3);
        $datas['data'][$index]->progress = $progress;

        $progress = ($progress * 100) . '%';
        $datas['data'][$index]->taskProgress = $progress;
        $datas['data'][$index]->estimate = $stage['progress']['totalEstimate'];
        $datas['data'][$index]->consumed = $stage['progress']['totalConsumed'];
    }

    $datas['links'] = array();
    if($this->config->edition != 'open')
    {
        $relations = $this->dao->select('*')->from(TABLE_RELATIONOFTASKS)->where('execution')->in($planIdList)->orderBy('task,pretask')->fetchAll();
        foreach($relations as $relation)
        {
            $link['source']   = $relation->execution . '-' . $relation->pretask;
            $link['target']   = $relation->execution . '-' . $relation->task;
            $link['type']     = $this->config->execution->gantt->linkType[$relation->condition][$relation->action];
            $datas['links'][] = $link;
        }
    }

    $datas['data'] = isset($datas['data']) ? array_values($datas['data']) : array();
    return $returnJson ? json_encode($datas) : $datas;
}