<?php

/**
 * Print assigned html
 *
 * @param  object $task
 * @param  array  $users
 * @param  bool   $output
 * @access public
 * @return void
 */
public function printAssignedHtml($task, $users, $output = true)
{
    $btnTextClass   = '';
    $btnClass       = '';
    $assignedToText = $assignedToTitle = zget($users, $task->assignedTo);
    if(!empty($task->team) and $task->mode == 'multi' and strpos('done,closed', $task->status) === false)
    {
        $assignedToText = $this->lang->task->team;

        $teamMembers = array();
        foreach($task->team as $teamMember)
        {
            $realname = zget($users, $teamMember->account);
            if($this->app->user->account == $teamMember->account and $teamMember->status != 'done')
            {
                $task->assignedTo = $this->app->user->account;
                $assignedToText   = $realname;
            }
            $teamMembers[] = $realname;
        }

        $assignedToTitle = implode($this->lang->comma, $teamMembers);
    }
    elseif(empty($task->assignedTo))
    {
        $btnClass       = $btnTextClass = 'assigned-none';
        $assignedToText = $this->lang->task->noAssigned;
        if(isset($task->assignedToChange) && !$task->assignedToChange)
        {
            if(!$output) return '';
            echo '';
        }
    }
    if($task->assignedTo == $this->app->user->account) $btnClass = $btnTextClass = 'assigned-current';
    if(!empty($task->assignedTo) and $task->assignedTo != $this->app->user->account) $btnClass = $btnTextClass = 'assigned-other';

    $btnClass    .= $task->assignedTo == 'closed' ? ' disabled' : '';
    $btnClass    .= ' iframe btn btn-icon-left btn-sm';
    $assignToLink = $task->assignedTo == 'closed' ? '#' : helper::createLink('task', 'assignTo', "executionID=$task->execution&taskID=$task->id", '', true);
    $assignToHtml = html::a($assignToLink, "<i class='icon icon-hand-right'></i> <span title='" . $assignedToTitle . "'>{$assignedToText}</span>", '', "class='$btnClass' data-toggle='modal'");

    $html = !common::hasPriv('task', 'assignTo', $task) || (isset($task->assignedToChange) && !$task->assignedToChange) ? "<span style='padding-left: 21px' class='{$btnTextClass}'>{$assignedToText}</span>" : $assignToHtml;

    if(!$output) return $html;
    echo $html;
}

public function buildOperateBrowseMenu($task, $execution)
{
    $menu   = '';
    $params = "taskID=$task->id";

    $storyChanged = !empty($task->storyStatus) && $task->storyStatus == 'active' && $task->latestStoryVersion > $task->storyVersion && !in_array($task->status, array('cancel', 'closed'));
    if($storyChanged) return $this->buildMenu('task', 'confirmStoryChange', $params, $task, 'browse', '', 'hiddenwin');

    $canStart          = ($task->status != 'pause' and common::hasPriv('task', 'start'));
    $canRestart        = ($task->status == 'pause' and common::hasPriv('task', 'restart'));
    $canFinish         = common::hasPriv('task', 'finish');
    $canClose          = common::hasPriv('task', 'close');
    $canRecordEstimate = common::hasPriv('task', 'recordEstimate');
    $canEdit           = common::hasPriv('task', 'edit');
    $canBatchCreate    = ($this->config->vision != 'lite' and common::hasPriv('task', 'batchCreate'));

    if($task->status != 'pause') $menu .= $this->buildMenu('task', 'start',   $params, $task, 'browse', '', '', 'iframe', true, 'data-width="95%"');
    if($task->status == 'pause') $menu .= $this->buildMenu('task', 'restart', $params, $task, 'browse', '', '', 'iframe', true, 'data-width="95%"');
    $menu .= $this->buildMenu('task', 'finish', $params, $task, 'browse', '', '', 'iframe', true, 'data-width="95%"');
    $menu .= $this->buildMenu('task', 'close',  $params, $task, 'browse', '', '', 'iframe', true);

    if(($canStart or $canRestart or $canFinish or $canClose) and ($canRecordEstimate or $canEdit or $canBatchCreate))
    {
        $menu .= "<div class='dividing-line'></div>";
    }

    $menu .= $this->buildMenu('task', 'recordEstimate', $params, $task, 'browse', 'time', '', 'iframe', true, 'data-width="95%"');
    $menu .= $this->buildMenu('task', 'edit',           $params, $task, 'browse', 'edit', '', '', false, 'data-app="execution"');
    if($this->config->vision != 'lite')
    {
        $menu .= $this->buildMenu('task', 'batchCreate', "execution=$task->execution&storyID=$task->story&moduleID=$task->module&taskID=$task->id&ifame=0", $task, 'browse', 'split', '', '', '', '', $this->lang->task->children);
    }
    return $menu;
}



public function buildOperateViewMenu($task)
{
    if($task->deleted) return '';

    $plmCanStart = true;
    $isPLMMode   = $this->config->systemMode == 'PLM';

    if($isPLMMode)
    {
        $execution           = $this->loadModel('execution')->getByID($task->execution);
        $execution->ipdStage = $this->loadModel('execution')->canStageStart($execution);
        $plmCanStart = $execution->status == 'wait' ? $execution->ipdStage['canStart'] : 1;
        if($execution->status == 'close') $plmCanStart = false;
        if($execution->parallel) $plmCanStart = true;
    }

    $menu   = '';
    $params = "taskID=$task->id";
    if((empty($task->team) || empty($task->children)) && $task->executionList->type != 'kanban')
    {
        $menu .= $this->buildMenu('task', 'batchCreate', "execution=$task->execution&storyID=$task->story&moduleID=$task->module&taskID=$task->id", $task, 'view', 'split', '', '', '', "title='{$this->lang->task->children}'", $this->lang->task->children);
    }

    $menu .= $this->buildMenu('task', 'assignTo', "executionID=$task->execution&taskID=$task->id", $task, 'button', '', '', 'iframe', true, '', $this->lang->task->assignTo);

    if($plmCanStart) $menu .= $this->buildMenu('task', 'start',          $params, $task, 'view', '', '', 'iframe showinonlybody', true);
    if($plmCanStart) $menu .= $this->buildMenu('task', 'restart',        $params, $task, 'view', '', '', 'iframe showinonlybody', true);
    //if(empty($task->linkedBranch))
    //{
    //    $hasRepo = $this->loadModel('repo')->getRepoPairs('execution', $task->execution, false);
    //    if($hasRepo) $menu .= $this->buildMenu('repo', 'createBranch', $params . "&execution={$task->execution}", $task, '', 'treemap', '', 'iframe showinonlybody', true, '', $this->lang->repo->createBranchAction);
    //}
    if($plmCanStart) $menu .= $this->buildMenu('task', 'recordEstimate', $params, $task, 'view', '', '', 'iframe showinonlybody', true, 'data-width="90%"');

    $menu .= $this->buildMenu('task', 'pause',          $params, $task, 'view', '', '', 'iframe showinonlybody', true);
    if($plmCanStart) $menu .= $this->buildMenu('task', 'finish',         $params, $task, 'view', '', '', 'iframe showinonlybody text-success', true);
    $menu .= $this->buildMenu('task', 'activate',       $params, $task, 'view', '', '', 'iframe showinonlybody text-success', true);
    $menu .= $this->buildMenu('task', 'close',          $params, $task, 'view', '', '', 'iframe showinonlybody', true);
    $menu .= $this->buildMenu('task', 'cancel',         $params, $task, 'view', '', '', 'iframe showinonlybody', true);

    $menu .= "<div class='divider'></div>";
    $menu .= $this->buildFlowMenu('task', $task, 'view', 'direct');
    $menu .= "<div class='divider'></div>";

    $menu .= $this->buildMenu('task', 'edit', $params, $task, 'view', '', '', 'showinonlybody');
    $menu .= $this->buildMenu('task', 'create', "projctID={$task->execution}&storyID=0&moduleID=0&taskID=$task->id", $task, 'view', 'copy');
    $menu .= $this->buildMenu('task', 'delete', "executionID=$task->execution&taskID=$task->id", $task, 'view', 'trash', 'hiddenwin', 'showinonlybody');
    if($task->parent > 0) $menu .= $this->buildMenu('task', 'view', "taskID=$task->parent", $task, 'view', 'chevron-double-up', '', '', '', '', $this->lang->task->parent);

    return $menu;
}

/**
 * Get tasks of a execution.
 *
 * @param int    $executionID
 * @param int    $productID
 * @param string $type
 * @param string $modules
 * @param string $orderBy
 * @param null   $pager
 *
 * @access public
 * @return array|void
 */
public function getExecutionTasks($executionID, $productID = 0, $type = 'all', $modules = 0, $orderBy = 'status_asc, id_desc', $pager = null, $showParentTask = false)
{
    if(is_string($type)) $type = strtolower($type);
    $orderBy = str_replace('pri_', 'priOrder_', $orderBy);
    $fields  = "DISTINCT t1.*, t2.id AS storyID, t2.title AS storyTitle, t2.product, t2.branch, t2.version AS latestStoryVersion, t2.status AS storyStatus, IF(t1.`pri` = 0, {$this->config->maxPriValue}, t1.`pri`) as priOrder";
    ($this->config->edition == 'max' or $this->config->edition == 'ipd') && $fields .= ', t5.name as designName, t5.version as latestDesignVersion';

    $actionIDList = array();
    if($type == 'assignedbyme') $actionIDList = $this->dao->select('objectID')->from(TABLE_ACTION)->where('objectType')->eq('task')->andWhere('action')->eq('assigned')->andWhere('actor')->eq($this->app->user->account)->fetchPairs('objectID', 'objectID');

    $tasks  = $this->dao->select($fields)
        ->from(TABLE_TASK)->alias('t1')
        ->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story = t2.id')
        ->leftJoin(TABLE_TASKTEAM)->alias('t3')->on('t3.task = t1.id')
        ->beginIF($productID)->leftJoin(TABLE_MODULE)->alias('t4')->on('t1.module = t4.id')->fi()
        ->beginIF($this->config->edition == 'max' or $this->config->edition == 'ipd')->leftJoin(TABLE_DESIGN)->alias('t5')->on('t1.design= t5.id')->fi()
        ->where('t1.execution')->eq((int)$executionID)
        ->beginIF($type == 'myinvolved')
        ->andWhere("((t3.`account` = '{$this->app->user->account}') OR t1.`assignedTo` = '{$this->app->user->account}' OR t1.`finishedby` = '{$this->app->user->account}')")
        ->fi()
        ->beginIF($productID)->andWhere("((t4.root=" . (int)$productID . " and t4.type='story') OR t2.product=" . (int)$productID . ")")->fi()
        ->beginIF($type == 'undone')->andWhere('t1.status')->notIN('done,closed')->fi()
        ->beginIF($type == 'needconfirm')->andWhere('t2.version > t1.storyVersion')->andWhere("t2.status = 'active'")->fi()
        ->beginIF($type == 'assignedtome')->andWhere("(t1.assignedTo = '{$this->app->user->account}' or (t1.mode = 'multi' and t3.`account` = '{$this->app->user->account}' and t1.status != 'closed' and t3.status != 'done') )")->fi()
        ->beginIF($type == 'finishedbyme')
        ->andWhere('t1.finishedby', 1)->eq($this->app->user->account)
        ->orWhere('t3.status')->eq("done")
        ->markRight(1)
        ->fi()
        ->beginIF($type == 'delayed')->andWhere('t1.deadline')->gt('1970-1-1')->andWhere('t1.deadline')->lt(date(DT_DATE1))->andWhere('t1.status')->in('wait,doing')->fi()
        ->beginIF(is_array($type) or strpos(',all,undone,needconfirm,assignedtome,delayed,finishedbyme,myinvolved,assignedbyme,review,', ",$type,") === false)->andWhere('t1.status')->in($type)->fi()
        ->beginIF($modules)->andWhere('t1.module')->in($modules)->fi()
        ->beginIF($type == 'assignedbyme')->andWhere('t1.id')->in($actionIDList)->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF($type == 'review')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.reviewers)")
        ->andWhere('t1.reviewStatus')->eq('doing')
        ->fi()
        ->beginIF($showParentTask)->andWhere('t1.parent')->ne(0)->fi()
        ->andWhere('t1.deleted')->eq(0)
        ->orderBy($orderBy)
        ->page($pager, 't1.id')
        ->fetchAll('id');

    $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'task', ($productID or in_array($type, array('myinvolved', 'needconfirm', 'assignedtome', 'finishedbyme'))) ? false : true);

    if(empty($tasks)) return array();

    $taskList = array_keys($tasks);
    $taskTeam = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('task')->in($taskList)->fetchGroup('task');
    if(!empty($taskTeam))
    {
        foreach($taskTeam as $taskID => $team) $tasks[$taskID]->team = $team;
    }

    $parents = array();
    foreach($tasks as $task)
    {
        if($task->parent > 0) $parents[$task->parent] = $task->parent;
    }
    $parents  = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in($parents)->fetchAll('id');
    $userList = $this->dao->select('account,realname')->from(TABLE_USER)->fetchPairs('account');

    if($this->config->vision == 'lite') $tasks = $this->appendLane($tasks);
    foreach($tasks as $task)
    {
        $task->assignedToRealName = zget($userList, $task->assignedTo);
        if($task->parent > 0)
        {
            if(isset($tasks[$task->parent]))
            {
                $tasks[$task->parent]->children[$task->id] = $task;
                unset($tasks[$task->id]);
            }
            else
            {
                $parent = $parents[$task->parent];
                $task->parentName = $parent->name;
            }
        }
    }

    return $this->processTasks($tasks);
}