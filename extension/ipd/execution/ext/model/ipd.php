<?php
/**
 * Check ipd stage is ready to begin.
 *
 * @param  string $currentStage
 * @param  array  $projectStages
 * @access public
 * @return void
 */
public function canStageStart($currentStage = '', $projectStages = array())
{
    $this->loadModel('stage');
    $project      = $this->loadModel('project')->getByID($currentStage->project);
    $canStart     = true;
    $isFirst      = false;
    $preAttribute = $currentStage->attribute;

    if($project->model != 'ipd') return array('canStart' => $canStart, 'preAttribute' => $preAttribute);
    if($currentStage and !$projectStages)
    {
        $projectStages = $this->dao->select('*')->from(TABLE_EXECUTION)
            ->where('project')->eq($currentStage->project)
            ->andWhere('deleted')->eq(0)
            ->fetchGroup('attribute', 'id');
    }

    $categoryStages = $this->config->project->categoryStages[$project->category];
    if($preAttribute == $categoryStages[0]) $isFirst = true;

    /* Gets the previous stage of the current phase*/
    foreach($this->config->stage->ipdStageOrder as $attribute)
    {
        if($attribute == $currentStage->attribute) break;
        if(isset($projectStages[$attribute])) $preAttribute = $attribute;
    }

    /* If the previous stages is not closed and The current stage is not the first stage, the current stage cannot start. */
    foreach($projectStages[$preAttribute] as $stage)
    {
        if($stage->status != 'closed' and $preAttribute != $currentStage->attribute) $canStart = false;
    }

    /* If current stage is not 'wait', cannot start. */
    if($currentStage->status != 'wait') $canStart = false;

    return array('canStart' => $canStart, 'preAttribute' => $preAttribute, 'isFirst' => $isFirst);
}

/**
 * Check whether the phase can be closed.
 *
 * @access public
 * @return void
 */
public function canStageClose($stageID = 0)
{
    $this->loadModel('stage');
    $stage   = $this->loadModel('execution')->getByID($stageID);
    if(!$stage->attribute) return false;

    $ipdPoints = $this->config->stage->ipdReviewPoint->{$stage->attribute};

    if($stage->attribute == 'launch' and $stage->status == 'wait') return false;
    if(!$ipdPoints) return true;

    $reviews = $this->dao->select('t1.*')->from(TABLE_REVIEW)->alias('t1')
        ->leftJoin(TABLE_OBJECT)->alias('t2')->on('t1.object=t2.id')
        ->where('t1.project')->eq($stage->project)
        ->andWhere('t1.deleted')->eq(0)
        ->andWhere('t1.status')->eq('pass')
        ->andWhere('t2.category')->in($ipdPoints)
        ->fetchAll('id');

    if(count($reviews) != count($ipdPoints)) return false;
    return true;
}

public function getTasks($productID, $executionID, $executions, $browseType, $queryID, $moduleID, $sort, $pager)
{
    $tasks = parent::getTasks($productID, $executionID, $executions, $browseType, $queryID, $moduleID, $sort, $pager);

    $this->loadModel('stage');
    $execution = $this->getByID($executionID);

    if(isset($this->config->stage->ipdReviewPoint->{$execution->attribute}))
    {
        $attribute = $execution->attribute;
        $stage     = $this->loadModel('execution')->canStageStart($execution);
        foreach($tasks as $task)
        {
            $canStart = $execution->status == 'wait' ? $stage['canStart'] : 1;
            if($execution->status == 'close') $canStart = false;
            if($execution->parallel) $canStart = true;

            $task->ipdStage = new stdclass();
            $task->ipdStage->canStart      = $canStart;
            $task->ipdStage->taskStartTip  = sprintf($this->lang->execution->disabledTip->taskStartTip, $this->lang->stage->ipdTypeList[$stage['preAttribute']], $this->lang->stage->ipdTypeList[$attribute]);
            $task->ipdStage->taskFinishTip = sprintf($this->lang->execution->disabledTip->taskFinishTip, $this->lang->stage->ipdTypeList[$stage['preAttribute']], $this->lang->stage->ipdTypeList[$attribute]);
            $task->ipdStage->taskRecordTip = sprintf($this->lang->execution->disabledTip->taskRecordTip, $this->lang->stage->ipdTypeList[$stage['preAttribute']], $this->lang->stage->ipdTypeList[$attribute]);
        }
    }

    return $tasks;
}

/**
 * Get statData.
 *
 * @param  int    $projectID
 * @param  string $browseType
 * @param  int    $productID
 * @param  int    $branch
 * @param  int    $withTasks
 * @param  string $param
 * @param  string $orderBy
 * @param  int    $pager
 * @access public
 * @return void
 */
public function getStatData($projectID = 0, $browseType = 'undone', $productID = 0, $branch = 0, $withTasks = false, $param = '', $orderBy = 'order_asc', $pager = null)
{
    $executions = parent::getStatData($projectID, $browseType, $productID, $branch, $withTasks, $param, $orderBy, $pager);
    $project    = $this->loadModel('project')->getByID($projectID);

    if($this->app->tab == 'project' and isset($project->model) and $project->model == 'ipd' and $executions)
    {
        $reviews =  array();
        $this->loadModel('review');
        $this->loadModel('stage');

        $projectStages = $this->dao->select('*')->from(TABLE_EXECUTION)
            ->where('project')->eq($projectID)
            ->andWhere('deleted')->eq(0)
            ->fetchGroup('attribute', 'id');

        $reviewPoints = $this->dao->select('t1.*, t2.status, t2.lastReviewedDate, t2.id as review, t2.result, t2.status as reviewStatus')->from(TABLE_OBJECT)->alias('t1')
            ->leftJoin(TABLE_REVIEW)->alias('t2')->on('t1.id = t2.object')
            ->where('t1.deleted')->eq('0')
            ->andWhere('t1.project')->eq($projectID)
            ->fetchAll('id');

        foreach($reviewPoints as $reviewPoint) $reviews[] = $reviewPoint->review;

        $approvals = $this->dao->select('max(approval) as approval,objectID')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq('review')
            ->andWhere('objectID')->in($reviews)
            ->groupBy('objectID')
            ->fetchAll('objectID');

        foreach($reviewPoints as $id => $reviewPoint)
        {
            $reviewPoints[$id]->approval = isset($approvals[$reviewPoint->review]) ? $approvals[$reviewPoint->review]->approval : 0;
        }

        $reviewDeadline = array();
        foreach($executions as $execution)
        {
            $reviewDeadline[$execution->id]['stageEnd'] = $execution->end;

            foreach($reviewPoints as $id => $point)
            {
                if(!isset($this->config->stage->ipdReviewPoint->{$execution->attribute})) continue;
                if(!isset($point->status)) $point->status = '';

                $categories = $this->config->stage->ipdReviewPoint->{$execution->attribute};

                if(in_array($point->category, $categories))
                {
                    if($point->end)
                    {
                        $end = $point->end;
                    }
                    else
                    {
                        $end = $reviewDeadline[$execution->id]['stageEnd'];
                        if(strpos($point->category, "TR") !== false)
                        {
                            if(isset($reviewDeadline[$execution->id]['End']) and !helper::isZeroDate($reviewDeadline[$execution->id]['taskEnd']))
                            {
                                $end = $reviewDeadline[$execution->id]['End'];
                            }
                            else
                            {
                                $end = $this->loadModel('programplan')->getReviewDeadline($end);
                            }
                        }
                        elseif(strpos($point->category, "DCP") !== false)
                        {
                            $end = $this->loadModel('programplan')->getReviewDeadline($end, 2);
                        }
                    }

                    $data = new stdclass();
                    $data->id            = $execution->id . '-' . $point->category . '-' . $point->id;
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
                    $data->parent        = $execution->id;
                    $data->open          = true;
                    $data->start_date    = $end;
                    $data->endDate       = $end;
                    $data->duration      = 1;
                    $data->review        = $point->review;
                    $data->result        = $point->result;
                    $data->approval      = $point->approval;
                    $data->reviewStatus  = $point->reviewStatus;

                    if($data->start_date) $data->start_date = date('d-m-Y', strtotime($data->start_date));
                    $execution->points[] = $data;
                }
            }
        }

        $executions = $this->getIpdStageStatus($executions, $projectStages);
    }

    return $executions;
}

public function getIpdStageStatus($executions = array(), $projectStages = array())
{
    if(!$executions) return $executions;

    foreach($executions as $execution)
    {
        $execution->ipdStage = $this->canStageStart($execution, $projectStages);
        $execution->ipdStage['canClose'] = $this->canStageClose($execution->id);
        if(!empty($execution->children))
        {
            $this->getIpdStageStatus($execution->children);
        }
    }

    return $executions;
}

/**
 * Start execution.
 *
 * @param  int    $executionID
 * @access public
 * @return array
 */
public function start($executionID)
{
    $oldExecution = $this->getById($executionID);
    $now          = helper::now();
    $project      = $this->loadModel('project')->getByID($oldExecution->project);

    if($project->model != 'ipd' or $project->parallel) return parent::start($executionID);
    $canStart  = $this->canStageStart($oldExecution);
    if($project->model == 'ipd' and !$canStart['canStart'])
    {
        dao::$errors['message'][] = sprintf($this->lang->execution->disabledTip->startTip, $this->lang->stage->ipdTypeList[$canStart['preAttribute']], $this->lang->stage->ipdTypeList[$oldExecution->attribute]);
        return false;
    }

    $execution = fixer::input('post')
        ->add('id', $executionID)
        ->setDefault('status', 'doing')
        ->setDefault('lastEditedBy', $this->app->user->account)
        ->setDefault('lastEditedDate', $now)
        ->stripTags($this->config->execution->editor->start['id'], $this->config->allowedTags)
        ->remove('comment')
        ->get();

    $execution = $this->loadModel('file')->processImgURL($execution, $this->config->execution->editor->start['id'], $this->post->uid);
    $this->dao->update(TABLE_EXECUTION)->data($execution)
        ->autoCheck()
        ->check($this->config->execution->start->requiredFields, 'notempty')
        ->checkIF($execution->realBegan != '', 'realBegan', 'le', helper::today())
        ->checkFlow()
        ->where('id')->eq((int)$executionID)
        ->exec();

    /* When it has multiple errors, only the first one is prompted */
    if(dao::isError() and count(dao::$errors['realBegan']) > 1) dao::$errors['realBegan'] = dao::$errors['realBegan'][0];

    if(!dao::isError()) return common::createChanges($oldExecution, $execution);
}

/**
 * Build point list under the stage.
 *
 * @param mixed $execution
 * @param mixed $point
 * @param mixed $pendingReviews
 * @access public
 * @return string
 */
public function buildPointList($execution, $point, $pendingReviews)
{
    $today    = helper::today();
    $trAttrs  = "data-id='t$point->id'";
    $this->app->loadLang('programplan');
    $pointStatus = $this->loadModel('review')->getReviewPointByProject($execution->project);
    $pointType   = explode('-', $point->id);
    $pointType   = isset($pointType[1]) ? $pointType[1] : '';

    $pointStatus = $this->loadModel('review')->getReviewPointByProject($execution->project);
    $pointType   = explode('-', $point->id);
    $pointType   = isset($pointType[1]) ? $pointType[1] : '';

    /* Remove projectID in execution path. */
    $executionPath = $execution->path;
    $executionPath = trim($executionPath, ',');
    $executionPath = substr($executionPath, strpos($executionPath, ',') + 1);

    $path     = ",{$executionPath},t{$point->id},";
    $trAttrs .= " data-parent='$execution->id' data-nest-parent='$execution->id' data-nest-path='$path' data-nested='false'";
    $trClass  = '';
    $disabled = '';

    $list  = "<tr $trAttrs class='$trClass'>";
    $list .= '<td>';
    $list .= (common::hasPriv('review', 'view') and $point->rawStatus) ? html::a(helper::createLink('review', 'view', "id=$point->review"), $point->text, '', "", "data-app='project'") : "<span>$point->text</span>";
    if(empty($point->rawStatus)) $list .= html::a(helper::createLink('review', 'create', "projectID=$execution->project"), '<i class="icon-confirm"></i></button>', '', 'class="btn btn-link submitBtn" title="' . $this->lang->programplan->submit . '"');
    $list .= '</td>';
    if(!empty($execution->division) and $execution->hasProduct) $list .= '<td></td>';
    $list .= "<td class='text-center'>$point->status</td>";
    $list .= '<td></td>';
    $list .= '<td></td>';
    $list .= "<td class='text-center deadline'><span class='endDate'>" . (helper::isZeroDate($point->deadline) ? '' : $point->deadline) . '</span> ';

    if((empty($point->rawStatus) or ($point->rawStatus == 'fail' or $point->rawStatus == 'draft')) and common::hasPriv('review', 'edit')) $list .= html::a('#', "<i class='icon-edit icon'></i> {$this->lang->programplan->edit}", '', 'class="btn btn-primary editDeadline" title="' . $this->lang->programplan->edit. '"');
    $list .= "</td>";
    $list .= '<td></td>';
    $list .= '<td></td>';
    $list .= '<td></td>';
    $list .= '<td></td>';
    $list .= '<td></td>';
    $list .= '<td class="c-actions text-left">';

    $params   = "reviewID=$point->review";

    if(empty($point->rawStatus))
    {
        $disabled = $pointStatus[$pointType]['disabled'] ? 'disabled' : '';
        $list .= common::buildIconButton('review', 'create', "projectID=$execution->project", $point, 'list', 'play', '', "$disabled", false, "", $this->lang->review->submit);
        $disabled = '';
    }
    else
    {
        $list .= common::buildIconButton('review', 'submit', $params, $point, 'list', 'play', '', "iframe", true, '', $this->lang->review->submit);
    }

    $list .= common::buildIconButton('review', 'recall', $params, $point, 'list', 'back', 'hiddenwin', '', '', '', $this->lang->review->recall);
    if(isset($pendingReviews[$point->review]))
    {
        $list .= common::buildIconButton('review', 'assess', $params, $point, 'list', 'glasses', '', '', false);
    }
    else
    {
        $list .= common::buildIconButton('review', 'assess', $params, $point, 'list', 'glasses', '', '', false, '', '', 0, false);
    }

    $point->approval = isset($point->approval) ? $point->approval : 0;

    if(!$point->review) $disabled = 'disabled';
    $list .= common::buildIconButton('approval', 'progress', "approvalID=$point->approval", $point, 'list', 'list-alt', '', "iframe $disabled", true, $disabled);
    $list .= common::buildIconButton('review', 'report',  $params, $point, 'list', 'bar-chart', '', '', false);
    $list .= common::buildIconButton('review', 'edit', $params, $point, 'list', '', '', $disabled, false, $disabled);
    $list .= '</td></tr>';

    return $list;
}
