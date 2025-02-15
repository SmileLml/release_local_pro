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