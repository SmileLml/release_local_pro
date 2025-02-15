<?php

public function getIdListByProjectClosedStatus()
{
    return $this->dao->select('id')->from(TABLE_PROJECT)
        ->where('type')->in('project')
        ->andWhere('status')->eq('closed')
        ->fetchPairs('id', 'id');
}

public function getAllProjects()
{
    return $this->dao->select('id, name, status')->from(TABLE_PROJECT)
        ->where('type')->eq('project')
        ->andWhere('deleted')->eq(0)
        ->andWhere('vision')->eq($this->config->vision)
        ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->projects)->fi()
        ->fetchAll('id');
}

public function printCell($col, $project, $users, $programID = 0)
{
    $canOrder     = common::hasPriv('project', 'updateOrder');
    $canBatchEdit = common::hasPriv('project', 'batchEdit');
    $account      = $this->app->user->account;
    $id           = $col->id;
    $projectLink  = helper::createLink('project', 'index', "projectID=$project->id", '', '', $project->id);

    if($col->show)
    {
        $title = '';
        $class = "c-$id" . (in_array($id, array('budget', 'teamCount', 'estimate', 'consume')) ? ' c-number' : '');

        if($id == 'id') $class .= ' cell-id';

        if($id == 'code')
        {
            $class .= ' c-name';
            $title  = "title={$project->code}";
        }
        elseif($id == 'name')
        {
            $class .= ' text-left';
            $title  = "title='{$project->name}'";
        }
        elseif($id == 'PM')
        {
            $class .= ' c-manager';
        }

        if($id == 'end')
        {
            $project->end = $project->end == LONG_TIME ? $this->lang->project->longTime : $project->end;
            $class .= ' c-name';
            $title  = "title='{$project->end}'";
        }

        if($id == 'budget')
        {
            $projectBudget = $this->getBudgetWithUnit($project->budget);
            $budgetTitle   = $project->budget != 0 ? zget($this->lang->project->currencySymbol, $project->budgetUnit) . ' ' . $projectBudget : $this->lang->project->future;

            $title = "title='$budgetTitle'";
        }

        if($id == 'estimate') $title = "title='{$project->estimate} {$this->lang->execution->workHour}'";
        if($id == 'consume')  $title = "title='{$project->consumed} {$this->lang->execution->workHour}'";
        if($id == 'surplus')  $title = "title='{$project->left} {$this->lang->execution->workHour}'";

        echo "<td class='$class' $title>";
        if($this->config->edition != 'open') $this->loadModel('flow')->printFlowCell('project', $project, $id);
        switch($id)
        {
            case 'id':
                if($canBatchEdit) echo empty($this->config->CRProject) && $project->status == 'closed' ? '<div class="checkbox-primary custom-checkbox" disabled><label></label></div>' . html::a($projectLink, sprintf('%03d', $project->id)) : html::checkbox('projectIdList', array($project->id => '')) . html::a($projectLink, sprintf('%03d', $project->id));
                else printf('%03d', $project->id);
                break;
            case 'name':
                $prefix      = '';
                $suffix      = '';
                $projectIcon = '';
                if(isset($project->delay)) $suffix = "<span class='label label-danger label-badge'>{$this->lang->project->statusList['delay']}</span>";
                $projectType = $project->model == 'scrum' ? 'sprint' : $project->model;
                if(!empty($suffix) or !empty($prefix)) echo '<div class="project-name' . (empty($prefix) ? '' : ' has-prefix') . (empty($suffix) ? '' : ' has-suffix') . '">';
                if(!empty($prefix)) echo $prefix;
                if($this->config->vision == 'rnd') $projectIcon = "<i class='text-muted icon icon-{$projectType}'></i> ";
                echo html::a($projectLink, $projectIcon . $project->name, '', "class='text-ellipsis'");
                if(!empty($suffix)) echo $suffix;
                if(!empty($suffix) or !empty($prefix)) echo '</div>';
                break;
            case 'code':
                echo $project->code;
                break;
            case 'PM':
                $user       = $this->loadModel('user')->getByID($project->PM, 'account');
                $userID     = !empty($user) ? $user->id : '';
                $userAvatar = !empty($user) ? $user->avatar : '';
                $PMLink     = helper::createLink('user', 'profile', "userID=$userID", '', true);
                $userName   = zget($users, $project->PM);
                if($project->PM) echo html::smallAvatar(array('avatar' => $userAvatar, 'account' => $project->PM, 'name' => $userName), "avatar-circle avatar-{$project->PM}");
                echo empty($project->PM) ? '' : html::a($PMLink, $userName, '', "title='{$userName}' data-toggle='modal' data-type='iframe' data-width='600'");
                break;
            case 'begin':
                echo $project->begin;
                break;
            case 'end':
                echo $project->end;
                break;
            case 'status':
                echo "<span class='status-task text-center  status-{$project->status}'> " . zget($this->lang->project->statusList, $project->status) . "</span>";
                break;
            case 'hasProduct':
                echo zget($this->lang->project->projectTypeList, $project->hasProduct);
                break;
            case 'budget':
                echo $budgetTitle;
                break;
            case 'teamCount':
                echo $project->teamCount;
                break;
            case 'estimate':
                echo $project->estimate . $this->lang->execution->workHourUnit;
                break;
            case 'consume':
                echo $project->consumed . $this->lang->execution->workHourUnit;
                break;
            case 'surplus':
                echo $project->left . $this->lang->execution->workHourUnit;
                break;
            case 'progress':
                echo html::ring($project->progress);
                break;
            case 'actions':
                $project->programID = $programID;
                echo $this->buildOperateMenu($project, 'browse');
                break;
        }
        echo '</td>';
    }
}