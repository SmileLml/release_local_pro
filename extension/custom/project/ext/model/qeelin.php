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

        if($id == 'realBegan')
        {
            $class .= ' c-name';
            $title  = "title='{$project->realBegan}'";
        }

        if($id == 'realEnd')
        {
            $class .= ' c-name';
            $title  = "title='{$project->realEnd}'";
        }

        if($id == 'closedDate')
        {
            $class .= ' c-name';
            $title  = "title='{$project->closedDate}'";
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
            case 'realBegan':
                echo $project->realBegan;
                break;
            case 'realEnd':
                echo $project->realEnd;
                break;
            case 'closedDate':
                echo $project->closedDate;
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

/**
 * Manage team members.
 *
 * @param  int    $projectID
 * @access public
 * @return void
 */
public function manageMembers($projectID)
{
    $project = $this->getByID($projectID);
    $data    = (array)fixer::input('post')->get();

    extract($data);
    $projectID   = (int)$projectID;
    $projectType = 'project';
    $accounts    = array_unique($accounts);
    $oldJoin     = $this->dao->select('`account`, `join`, `role`')->from(TABLE_TEAM)->where('root')->eq($projectID)->andWhere('type')->eq($projectType)->fetchAll('account');

    foreach($accounts as $key => $account)
    {
        if(empty($account)) continue;

        if(!empty($project->days) and (int)$days[$key] > $project->days)
        {
            dao::$errors['message'][]  = sprintf($this->lang->project->daysGreaterProject, $project->days);
            return false;
        }
        if((float)$hours[$key] > 24)
        {
            dao::$errors['message'][]  = $this->lang->project->errorHours;
            return false;
        }
    }

    $this->dao->delete()->from(TABLE_TEAM)->where('root')->eq($projectID)->andWhere('type')->eq($projectType)->exec();
    $projectMember = array();
    $addMembers    = array();
    $updateMembers = array();
    foreach($accounts as $key => $account)
    {
        if(empty($account)) continue;

        $member          = new stdclass();
        $member->role    = $roles[$key];
        $member->days    = $days[$key];
        $member->hours   = $hours[$key];
        $member->limited = isset($limited[$key]) ? $limited[$key] : 'no';

        $member->root    = $projectID;
        $member->account = $account;
        $member->join    = isset($oldJoin[$account]) ? $oldJoin[$account]->join : helper::today();
        $member->type    = $projectType;

        $projectMember[$account] = $member;
        if(!isset($oldJoin[$account])) $addMembers[$account] = $roles[$key];
        if(isset($oldJoin[$account]) && $roles[$key] != $oldJoin[$account]->role) $updateMembers[$account] = $roles[$key];
        $this->dao->insert(TABLE_TEAM)->data($member)->exec();
    }

    /* Only changed account update userview. */
    $oldAccounts     = array_keys($oldJoin);
    $removedAccounts = array_diff($oldAccounts, $accounts);
    $changedAccounts = array_merge($removedAccounts, array_diff($accounts, $oldAccounts));
    $changedAccounts = array_unique($changedAccounts);

    $childSprints   = $this->dao->select('id')->from(TABLE_PROJECT)->where('project')->eq($projectID)->andWhere('type')->in('stage,sprint')->andWhere('deleted')->eq('0')->fetchPairs();
    $linkedProducts = $this->dao->select("t2.id")->from(TABLE_PROJECTPRODUCT)->alias('t1')
        ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product = t2.id')
        ->where('t2.deleted')->eq(0)
        ->andWhere('t1.project')->eq($projectID)
        ->andWhere("FIND_IN_SET('{$this->config->vision}', t2.vision)")
        ->fetchPairs();

    $this->loadModel('user')->updateUserView(array($projectID), 'project', $changedAccounts);
    if(!empty($childSprints))
    {
        $this->user->updateUserView($childSprints, 'sprint', $changedAccounts);
        $this->updateExecutionMembers($childSprints, $addMembers, $updateMembers);
    }
    if(!empty($linkedProducts)) $this->user->updateUserView(array_keys($linkedProducts), 'product', $changedAccounts);

    /* Remove execution members. */
    if($this->post->removeExecution == 'yes' and !empty($childSprints) and !empty($removedAccounts))
    {
        $this->dao->delete()->from(TABLE_TEAM)
            ->where('root')->in($childSprints)
            ->andWhere('type')->eq('execution')
            ->andWhere('account')->in($removedAccounts)
            ->exec();
    }

    if(empty($project->multiple) and $project->model != 'waterfall') $this->loadModel('execution')->syncNoMultipleSprint($projectID);
}

/**
 * Add the execution team members to the project.
 *
 * @param  int    $projectID
 * @param  array  $members
 * @access public
 * @return void
 */
public function updateExecutionMembers($childSprints, $addMembers, $updateMembers)
{
    $this->loadModel('execution');
    foreach($childSprints as $executionID)
    {
        $action       = false;
        $executionID  = (int)$executionID;
        $execution    = $this->dao->findById($executionID)->from(TABLE_EXECUTION)->fetch();
        $oldJoin      = $this->dao->select('`id`, `account`')->from(TABLE_TEAM)->where('root')->eq($executionID)->andWhere('type')->eq('execution')->fetchAll('account');
        foreach($addMembers as $account => $role)
        {
            if(empty($account)) continue;
            $action = true;
            if(!isset($oldJoin[$account]))
            {
                $member          = new stdclass();
                $member->root    = (int)$executionID;
                $member->role    = $role;
                $member->join    = helper::today();
                $member->days    = zget($execution, 'days', 0);
                $member->type    = 'execution';
                $member->hours   = $this->config->execution->defaultWorkhours;
                $member->account = $account;
                $this->dao->insert(TABLE_TEAM)->data($member)->exec();
            }
            else
            {
                $this->dao->update(TABLE_TEAM)->set('`role`')->eq($role)->where('id')->eq($oldJoin[$account]->id)->exec();
            }
        }

        foreach($updateMembers as $account => $role)
        {
            if(!isset($oldJoin[$account])) continue;
            $action = true;
            $this->dao->update(TABLE_TEAM)->set('`role`')->eq($role)->where('id')->eq($oldJoin[$account]->id)->exec();
        }
        if($action) $this->loadModel('action')->create('team', $executionID, 'managedTeam');
        if($addMembers && $execution->acl != 'open') $this->execution->updateUserView($executionID, 'sprint', array_keys($addMembers));
    }
}

/**
 * Build project browse action menu.
 *
 * @param  object $project
 * @access public
 * @return string
 */
public function buildOperateBrowseMenu($project)
{
    $menu   = '';
    $params = "projectID=$project->id";

    $moduleName = "project";
    if($project->status == 'wait' || $project->status == 'suspended')
    {
        $menu .= $this->buildMenu($moduleName, 'start', $params, $project, 'browse', 'play', '', 'iframe', true);
    }
    if($project->status == 'doing')  $menu .= $this->buildMenu($moduleName, 'close',    $params, $project, 'browse', 'off',   '', 'iframe', true);
    if($project->status == 'closed') $menu .= $this->buildMenu($moduleName, 'activate', $params, $project, 'browse', 'magic', '', 'iframe', true);

    if(common::hasPriv($moduleName, 'suspend') || (common::hasPriv($moduleName, 'close') && $project->status != 'doing') || (common::hasPriv($moduleName, 'activate') && $project->status != 'closed'))
    {
        $menu .= "<div class='btn-group'>";
        $menu .= "<button type='button' class='btn icon-caret-down dropdown-toggle' data-toggle='context-dropdown' title='{$this->lang->more}' style='width: 16px; padding-left: 0px; border-radius: 4px;'></button>";
        $menu .= "<ul class='dropdown-menu pull-right text-center' role='menu' style='position: unset; min-width: auto; padding: 5px 6px;'>";
        $menu .= $this->buildMenu($moduleName, 'suspend', $params, $project, 'browse', 'pause', '', 'iframe btn-action', true);
        if($project->status != 'doing')  $menu .= $this->buildMenu($moduleName, 'close',    $params, $project, 'browse', 'off',   '', 'iframe btn-action', true);
        if($project->status != 'closed') $menu .= $this->buildMenu($moduleName, 'activate', $params, $project, 'browse', 'magic', '', 'iframe btn-action', true);
        $menu .= "</ul>";
        $menu .= "</div>";
    }

    $from     = $project->from == 'project' ? 'project' : 'pgmproject';
    $iframe   = $this->app->tab == 'program' ? 'iframe' : '';
    $onlyBody = $this->app->tab == 'program' ? true : '';
    $dataApp  = "data-app=project";

    $menu .= $this->buildMenu($moduleName, 'edit', $params, $project, 'browse', 'edit', '', $iframe, $onlyBody, $dataApp);

    if($this->config->vision != 'lite')
    {
        $menu .= $this->buildMenu($moduleName, 'team', $params, $project, 'browse', 'group', '', '', '', $dataApp, $this->lang->execution->team);
        $menu .= $this->buildMenu('project', 'group', "$params&programID={$project->programID}", $project, 'browse', 'lock', '', '', '', $dataApp);
        $menu .= $this->buildMenu('project', 'collect', "$params", $project, 'browse', $project->collect ? 'star' : 'star-empty', '', '', '', $dataApp);

        if(common::hasPriv($moduleName, 'manageProducts') || common::hasPriv($moduleName, 'whitelist') || common::hasPriv($moduleName, 'delete'))
        {
            $menu .= "<div class='btn-group'>";
            $menu .= "<button type='button' class='btn dropdown-toggle' data-toggle='context-dropdown' title='{$this->lang->more}'><i class='icon-ellipsis-v'></i></button>";
            $menu .= "<ul class='dropdown-menu pull-right text-center' role='menu'>";
            $menu .= $this->buildMenu($moduleName, 'manageProducts', $params . "&from={$this->app->tab}", $project, 'browse', 'link', '', 'btn-action', '', $project->hasProduct ? '' : "disabled='disabled'", $this->lang->project->manageProducts);
            $menu .= $this->buildMenu('project', 'whitelist', "$params&module=project&from=$from", $project, 'browse', 'shield-check', '', 'btn-action', '', $dataApp);
            $menu .= $this->buildMenu($moduleName, "delete", $params, $project, 'browse', 'trash', 'hiddenwin', 'btn-action');
            $menu .= "</ul>";
            $menu .= "</div>";
        }
    }
    else
    {
        $menu .= $this->buildMenu($moduleName, 'team', $params, $project, 'browse', 'group', '', '', '', $dataApp, $this->lang->execution->team);
        $menu .= $this->buildMenu('project', 'whitelist', "$params&module=project&from=$from", $project, 'browse', 'shield-check', '', 'btn-action', '', $dataApp);
        $menu .= $this->buildMenu($moduleName, "delete", $params, $project, 'browse', 'trash', 'hiddenwin', 'btn-action');
    }

    return $menu;
}


/**
 * Judge an action is clickable or not.
 *
 * @param  object    $project
 * @param  string    $action
 * @access public
 * @return bool
 */
public static function isClickable($project, $action, $module = 'project')
{
    global $config;
    $action = strtolower($action);

    if(empty($project)) return true;
    if(!isset($project->type)) return true;

    if($action == 'start')    return $project->status == 'wait' or $project->status == 'suspended';
    if($action == 'finish')   return $project->status == 'wait' or $project->status == 'doing';
    if($action == 'close')    return $project->status != 'closed';
    if($action == 'suspend')  return $project->status == 'wait' or $project->status == 'doing';
    if($action == 'activate') return $project->status == 'done' or $project->status == 'closed';
    if($action == 'edit' and $module == 'project') return !empty($config->CRProject) or $project->status != 'closed';
    if($action == 'team' and $module == 'project') return !empty($config->CRProject) or $project->status != 'closed';
    if($action == 'collect' and $module == 'project') return !empty($config->CRProject) or $project->status != 'closed';

    if($action == 'whitelist') return $project->acl != 'open' and (!empty($config->CRProject) or $project->status != 'closed');
    if($action == 'group') return $project->model != 'kanban' and (!empty($config->CRProject) or $project->status != 'closed');
    if($action == 'manageproducts') return $project->model != 'kanban' and (!empty($config->CRProject) or $project->status != 'closed');

    return true;
}

public function buildTesttaskSearchForm($products, $queryID, $actionURL)
{
    $productPairs = array('' => '');
    $builds  = array('' => '', 'trunk' => $this->lang->trunk);

    foreach($products as $product)
    {
        $productPairs[$product->id] = $product->name;
        $productBuilds  = $this->loadModel('build')->getBuildPairs($product->id, 'all', $params = 'noempty|notrunk|withbranch');
        foreach($productBuilds as $buildID => $buildName)
        {
            $builds[$buildID] = ((count($products) >= 2 and $buildID) ? $product->name . '/' : '') . $buildName;
        }
    }

    $this->config->testtask->search['queryID']   = $queryID;
    $this->config->testtask->search['actionURL'] = $actionURL;
    unset($this->config->testtask->search['fields']['execution']);

    $this->config->testtask->search['params']['build']['values']   = $builds;
    $this->config->testtask->search['params']['product']['values'] = $productPairs + array('all' => $this->lang->product->allProductsOfProject);

    $this->loadModel('search')->setSearchParams($this->config->testtask->search);
}