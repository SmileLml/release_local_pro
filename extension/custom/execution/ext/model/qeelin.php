<?php

public function getIdListByProjectClosedStatus()
{
    return $this->dao->select('t1.id')->from(TABLE_EXECUTION)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project=t2.id')
        ->where('t1.type')->in('sprint,stage,kanban')
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t2.deleted')->eq('0')
        ->andWhere('t2.status')->eq('closed')
        ->fetchPairs('id', 'id');
}

public function getPairsForEffortExport()
{
    $executions = $this->dao->select("id, name, project, multiple, path, IF(INSTR('done,closed', status) < 2, 0, 1) AS isDone, INSTR('doing,wait,suspended,closed', status) AS sortStatus")->from(TABLE_EXECUTION)
        ->where('deleted')->eq(0)
        ->beginIF($this->config->vision == 'lite')->andWhere('vision')->eq($this->config->vision)->fi()
        ->andWhere('multiple')->eq('1')->fi()
        ->andWhere('type')->in('stage,sprint,kanban')->fi()
        ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->sprints)->fi()
        ->fetchAll('id');
    $allExecutions = $this->dao->select('id,name,parent,grade')->from(TABLE_EXECUTION)
        ->where('type')->notin(array('program', 'project'))
        ->andWhere('deleted')->eq('0')
        ->fetchAll('id');
    $parents = array();
    foreach($allExecutions as $exec) $parents[$exec->parent] = true;
    $pairs       = array();
    $noMultiples = array();
    foreach($executions as $execution)
    {
        if(empty($execution->multiple)) $noMultiples[$execution->id] = $execution->project;

        /* Set execution name. */
        $paths = array_slice(explode(',', trim($execution->path, ',')), 1);
        $executionName = '';
        foreach($paths as $path)
        {
            if(isset($allExecutions[$path])) $executionName .= '/' . $allExecutions[$path]->name;
        }

        $pairs[$execution->id] = $executionName;
    }

    if($noMultiples)
    {
        $this->app->loadLang('project');
        $noMultipleProjects = $this->dao->select('id, name')->from(TABLE_PROJECT)->where('id')->in($noMultiples)->fetchPairs('id', 'name');

        foreach($noMultiples as $executionID => $projectID)
        {
            if(isset($noMultipleProjects[$projectID])) $pairs[$executionID] = $noMultipleProjects[$projectID] . "({$this->lang->project->disableExecution})";
        }
    }

    return $pairs;
}


public function getPairs($projectID = 0, $type = 'all', $mode = '')
{
    if(defined('TUTORIAL')) return $this->loadModel('tutorial')->getExecutionPairs();

    $mode   .= $this->cookie->executionMode;
    $orderBy = $this->config->execution->orderBy;
    if($projectID)
    {
        $executionModel = $this->dao->select('model')->from(TABLE_EXECUTION)->where('id')->eq($projectID)->andWhere('deleted')->eq(0)->fetch('model');
        $orderBy = in_array($executionModel, array('waterfall', 'waterfallplus')) ? 'sortStatus_asc,begin_asc,id_asc' : 'id_desc';

        /* Waterfall execution, when all phases are closed, in reverse order of date. */
        if(in_array($executionModel, array('waterfall', 'waterfallplus')))
        {
            $summary = $this->dao->select("count(id) as executions, sum(IF(INSTR('closed', status) < 1, 0, 1)) as closedExecutions")->from(TABLE_EXECUTION)->where('project')->eq($projectID)->andWhere('deleted')->eq('0')->fetch();
            if($summary->executions == $summary->closedExecutions) $orderBy = 'sortStatus_asc,begin_desc,id_asc';
        }
    }

    /* Can not use $this->app->tab in API. */
    $filterMulti = ((defined('RUN_MODE') and RUN_MODE == 'api') or $this->app->getViewType() == 'json') ? false : (!$this->session->multiple and $this->app->tab == 'execution');
    /* Order by status's content whether or not done */
    $executions = $this->dao->select("*, IF(INSTR('done,closed', status) < 2, 0, 1) AS isDone, INSTR('doing,wait,suspended,closed', status) AS sortStatus")->from(TABLE_EXECUTION)
        ->where('deleted')->eq(0)
        ->beginIF($this->config->vision == 'lite')->andWhere('vision')->eq($this->config->vision)->fi()
        ->beginIF($filterMulti)->andWhere('multiple')->eq('1')->fi()
        ->beginIF(strpos($mode, 'multiple') !== false)->andWhere('multiple')->eq('1')->fi()
        ->beginIF($type == 'all')->andWhere('type')->in('stage,sprint,kanban')->fi()
        ->beginIF($projectID)->andWhere('project')->eq($projectID)->fi()
        ->beginIF($type != 'all')->andWhere('type')->in($type)->fi()
        ->beginIF(strpos($mode, 'withdelete') === false)->andWhere('deleted')->eq(0)->fi()
        ->beginIF(!$this->app->user->admin and strpos($mode, 'all') === false)->andWhere('id')->in($this->app->user->view->sprints)->fi()
        ->orderBy($orderBy)
        ->fetchAll('id');

    /* If mode == leaf, only show leaf executions. */
    $allExecutions = $this->dao->select('id,name,parent,grade')->from(TABLE_EXECUTION)
        ->where('type')->notin(array('program', 'project'))
        ->andWhere('deleted')->eq('0')
        ->beginIf($projectID)->andWhere('project')->eq($projectID)->fi()
        ->fetchAll('id');

    $closeProjectIdList = array();
    if(strpos($mode, 'projectclosefilter') != false)
    {
        $closeProjectIdList = $this->dao->select('id')->from(TABLE_PROJECT)
        ->where('type')->eq('project')
        ->andWhere('status')->eq('closed')
        ->fetchAll('id');
    }

    $parents = array();
    foreach($allExecutions as $exec) $parents[$exec->parent] = true;

    if(strpos($mode, 'order_asc') !== false) $executions = $this->resetExecutionSorts($executions);
    if(strpos($mode, 'withobject') !== false)
    {
        $projectPairs = $this->dao->select('id,name')->from(TABLE_PROJECT)->fetchPairs('id');
    }

    $pairs       = array();
    $noMultiples = array();
    foreach($executions as $execution)
    {
        if(strpos($mode, 'leaf') !== false and isset($parents[$execution->id])) continue; // Only show leaf.
        if(strpos($mode, 'noclosed') !== false and ($execution->status == 'done' or $execution->status == 'closed')) continue;
        if(strpos($mode, 'stagefilter') !== false and isset($executionModel) and in_array($executionModel, array('waterfall', 'waterfallplus')) and in_array($execution->attribute, array('request', 'design', 'review'))) continue; // Some stages of waterfall and waterfallplus not need.

        if(strpos($mode, 'projectclosefilter') != false and isset($closeProjectIdList[$execution->parent])) continue;
        if(empty($execution->multiple)) $noMultiples[$execution->id] = $execution->project;

        /* Set execution name. */
        $paths = array_slice(explode(',', trim($execution->path, ',')), 1);
        $executionName = '';
        foreach($paths as $path)
        {
            if(isset($allExecutions[$path])) $executionName .= '/' . $allExecutions[$path]->name;
        }

        if(strpos($mode, 'withobject') !== false) $executionName = zget($projectPairs, $execution->project, '') . $executionName;
        if(strpos($mode, 'noprefix') !== false) $executionName = ltrim($executionName, '/');

        $pairs[$execution->id] = $executionName;
    }

    if($noMultiples)
    {
        if(strpos($mode, 'hideMultiple') !== false)
        {
            foreach($noMultiples as $executionID => $projectID) $pairs[$executionID] = '';
        }
        else
        {
            $this->app->loadLang('project');
            $noMultipleProjects = $this->dao->select('id, name')->from(TABLE_PROJECT)->where('id')->in($noMultiples)->fetchPairs('id', 'name');

            foreach($noMultiples as $executionID => $projectID)
            {
                if(isset($noMultipleProjects[$projectID])) $pairs[$executionID] = $noMultipleProjects[$projectID] . "({$this->lang->project->disableExecution})";
            }
        }
    }

    if(strpos($mode, 'empty') !== false) $pairs[0] = '';

    /* If the pairs is empty, to make sure there's an execution in the pairs. */
    if(empty($pairs) and isset($executions[0]))
    {
        $firstExecution = $executions[0];
        $pairs[$firstExecution->id] = $firstExecution->name;
    }

    return $pairs;
}

public function getStatData($projectID = 0, $browseType = 'undone', $productID = 0, $branch = 0, $withTasks = false, $param = '', $orderBy = 'id_asc', $pager = null)
{
    if(defined('TUTORIAL')) return $this->loadModel('tutorial')->getExecutionStats($browseType);

    $productID = (int)$productID;

    /* Construct the query SQL at search executions. */
    $executionQuery = '';
    if($browseType == 'bySearch')
    {
        $queryID = (int)$param;
        if($queryID)
        {
            $query = $this->loadModel('search')->getQuery($queryID);
            if($query)
            {
                $this->session->set('executionQuery', $query->sql);
                $this->session->set('executionForm', $query->form);
            }
        }
        if($this->session->executionQuery == false) $this->session->set('executionQuery', ' 1 = 1');

        $executionQuery = $this->session->executionQuery;
        $allProject = "`project` = 'all'";

        if(strpos($executionQuery, $allProject) !== false) $executionQuery = str_replace($allProject, '1', $executionQuery);
        $executionQuery = preg_replace('/(`\w*`)/', 't1.$1',$executionQuery);
    }

    $parentExecutions = $this->dao->select('t1.*,t2.name projectName, t2.model as projectModel, t2.status as projectStatus')->from(TABLE_EXECUTION)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
        ->beginIF($productID)->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t1.id=t3.project')->fi()
        ->where('t1.type')->in('sprint,stage,kanban')
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t1.vision')->eq($this->config->vision)
        ->andWhere('t1.multiple')->eq('1')
        ->andWhere('t1.grade')->eq('1')
        ->beginIF(!$this->app->user->admin)->andWhere('t1.id')->in($this->app->user->view->sprints)->fi()
        ->beginIF(!empty($executionQuery))->andWhere($executionQuery)->fi()
        ->beginIF($productID)->andWhere('t3.product')->eq($productID)->fi()
        ->beginIF($projectID)->andWhere('t1.project')->eq($projectID)->fi()
        ->beginIF(!in_array($browseType, array('all', 'undone', 'involved', 'review', 'bySearch')))->andWhere('t1.status')->eq($browseType)->fi()
        ->beginIF($browseType == 'undone')->andWhere('t1.status')->notIN('done,closed')->fi()
        ->beginIF($browseType == 'review')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.reviewers)")
        ->andWhere('t1.reviewStatus')->eq('doing')
        ->fi()
        ->orderBy($orderBy)
        ->page($pager, 't1.id')
        ->fetchAll('id');

    /* Get child executions. */
    $childExecutions = $this->dao->select('t1.*,t2.name projectName, t2.model as projectModel, t2.status as projectStatus')->from(TABLE_EXECUTION)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
        ->beginIF($productID)->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t1.id=t3.project')->fi()
        ->where('t1.type')->in('sprint,stage,kanban')
        ->beginIF($projectID)->andWhere('t1.project')->eq($projectID)->fi()
        ->andWhere('t1.deleted')->eq('0')
        ->andWhere('t1.vision')->eq($this->config->vision)
        ->andWhere('t1.multiple')->eq('1')
        ->andWhere('t1.grade')->gt('1')
        ->beginIF(!$this->app->user->admin)->andWhere('t1.id')->in($this->app->user->view->sprints)->fi()
        ->orderBy($orderBy)
        ->fetchAll('id');

    if(empty($productID) and !empty($parentExecutions)) $projectProductIdList = $this->dao->select('project, GROUP_CONCAT(product) as product')->from(TABLE_PROJECTPRODUCT)->where('project')->in(array_keys($parentExecutions))->groupBy('project')->fetchPairs();

    $productNameList = $this->dao->select('t1.id,GROUP_CONCAT(t3.`name`) as productName')->from(TABLE_EXECUTION)->alias('t1')
        ->leftjoin(TABLE_PROJECTPRODUCT)->alias('t2')->on('t1.id=t2.project')
        ->leftjoin(TABLE_PRODUCT)->alias('t3')->on('t2.product=t3.id')
        ->where('t1.project')->eq($projectID)
        ->andWhere('t1.type')->in('kanban,sprint,stage')
        ->groupBy('t1.id')
        ->fetchPairs();

    $executions = array_replace($parentExecutions, $childExecutions);

    $burns = $this->getBurnData($executions);

    if($withTasks) $executionTasks = $this->getTaskGroupByExecution(array_keys($executions));

    /* Process executions. */
    $this->app->loadConfig('task');

    $emptyHour  = array('totalEstimate' => 0, 'totalConsumed' => 0, 'totalLeft' => 0, 'progress' => 0);
    $today      = helper::today();
    $childList  = array();
    $parentList = array();
    foreach($executions as $key => $execution)
    {
        $execution->productName = isset($productNameList[$execution->id]) ? $productNameList[$execution->id] : '';

        /* Process the end time. */
        $execution->end = date(DT_DATE1, strtotime($execution->end));

        /* Judge whether the execution is delayed. */
        if($execution->status != 'done' and $execution->status != 'closed' and $execution->status != 'suspended')
        {
            $delay = helper::diffDate($today, $execution->end);
            if($delay > 0) $execution->delay = $delay;
        }

        /* Process the burns. */
        $execution->burns = array();
        $burnData = isset($burns[$execution->id]) ? $burns[$execution->id] : array();
        foreach($burnData as $data) $execution->burns[] = $data->value;

        if(isset($executionTasks) and isset($executionTasks[$execution->id]))
        {
            $tasks = array_chunk($executionTasks[$execution->id], $this->config->task->defaultLoadCount, true);
            $execution->tasks = $tasks[0];
        }

        /* In the case of the waterfall model, calculate the sub-stage. */
        if($param === 'skipParent')
        {
            if($execution->parent) $parentList[$execution->parent] = $execution->parent;
            if($execution->projectName) $execution->name = $execution->projectName . ' / ' . $execution->name;
        }
        elseif(strpos($param, 'hasParentName') !== false)
        {
            $parents = $this->dao->select('id,name')->from(TABLE_EXECUTION)->where('id')->in(trim($execution->path, ','))->andWhere('type')->in('stage,kanban,sprint')->orderBy('grade')->fetchPairs();
            $executions[$execution->id]->title = implode('/', $parents);
            if(strpos($param, 'skipParent') !== false)
            {
                $children = $this->getChildExecutions($execution->id);
                if(count($children) > 0) $parentList[$execution->id] = $execution->id;
            }
        }
        elseif(isset($executions[$execution->parent]))
        {
            $executions[$execution->parent]->children[$key] = $execution;
            $childList[$key] = $key;
        }

        /* Bind execution product */
        if(!empty($projectProductIdList) and !empty($projectProductIdList[$execution->id]))
        {
            $execution->product = $projectProductIdList[$execution->id];
        }
    }

    if(strpos($param, 'withchild') === false)
    {
        foreach($childList as $childID) unset($executions[$childID]);
    }

    foreach($parentList as $parentID) unset($executions[$parentID]);

    $parentExecutions = array_intersect_key($executions, $parentExecutions);

    return array_values($parentExecutions);
}

/**
 * Print execution nested list.
 *
 * @param  int    $execution
 * @param  int    $isChild
 * @param  int    $users
 * @param  int    $productID
 * @param  string $project
 * @access public
 * @return void
 */
public function printNestedList($execution, $isChild, $users, $productID, $project = '')
{
    $this->loadModel('task');
    $this->loadModel('execution');
    $this->loadModel('programplan');

    $today = helper::today();

    if(!$isChild)
    {
        $trClass = 'is-top-level table-nest-child-hide';
        $trAttrs = "data-id='$execution->id' data-order='$execution->order' data-nested='true' data-status={$execution->status}";
    }
    else
    {
        if(strpos($execution->path, ",$execution->project,") !== false)
        {
            $path = explode(',', trim($execution->path, ','));
            $path = array_slice($path, array_search($execution->project, $path) + 1);
            $path = implode(',', $path);
        }

        $trClass  = 'table-nest-hide';
        $trAttrs  = "data-id={$execution->id} data-parent={$execution->parent} data-status={$execution->status}";
        $trAttrs .= " data-nest-parent='$execution->parent' data-order='$execution->order' data-nest-path='$path'";
    }

    $burns = join(',', $execution->burns);
    echo "<tr $trAttrs class='$trClass'>";
    echo "<td class='c-name text-left flex sort-handler'>";
    if(common::hasPriv('execution', 'batchEdit')) echo "<span id=$execution->id class='table-nest-icon icon table-nest-toggle'></span>";
    $spanClass = $execution->type == 'stage' ? 'label-warning' : 'label-info';
    echo "<span class='project-type-label label label-outline $spanClass'>{$this->lang->execution->typeList[$execution->type]}</span> ";
    if(empty($execution->children))
    {
        echo html::a(helper::createLink('execution', 'view', "executionID=$execution->id"), $execution->name, '', "class='text-ellipsis' title='{$execution->name}'");
        if(!helper::isZeroDate($execution->end))
        {
            if($execution->status != 'closed')
            {
                echo strtotime($today) > strtotime($execution->end) ? '<span class="label label-danger label-badge">' . $this->lang->execution->delayed . '</span>' : '';
            }
        }
    }
    else
    {
        echo "<span class='text-ellipsis'>" . $execution->name . '</span>';
        if(!helper::isZeroDate($execution->end))
        {
            if($execution->status != 'closed')
            {
                echo strtotime($today) > strtotime($execution->end) ? '<span class="label label-danger label-badge">' . $this->lang->execution->delayed . '</span>' : '';
            }
        }
    }
    if(!empty($execution->division) and $execution->hasProduct) echo "<td class='text-left' title='{$execution->productName}'>{$execution->productName}</td>";
    echo "<td class='status-{$execution->status} text-center'>" . zget($this->lang->project->statusList, $execution->status) . '</td>';
    echo '<td>' . zget($users, $execution->PM) . '</td>';
    echo helper::isZeroDate($execution->begin) ? '<td class="c-date"></td>' : '<td class="c-date">' . $execution->begin . '</td>';
    echo helper::isZeroDate($execution->end) ? '<td class="endDate c-date"></td>' : '<td class="endDate c-date">' . $execution->end . '</td>';
    echo "<td class='hours text-right' title='{$execution->estimate}{$this->lang->execution->workHour}'>" . $execution->estimate . $this->lang->execution->workHourUnit . '</td>';
    echo "<td class='hours text-right' title='{$execution->consumed}{$this->lang->execution->workHour}'>" . $execution->consumed . $this->lang->execution->workHourUnit . '</td>';
    echo "<td class='hours text-right' title='{$execution->left}{$this->lang->execution->workHour}'>" . $execution->left . $this->lang->execution->workHourUnit . '</td>';
    echo '<td>' . html::ring($execution->progress) . '</td>';
    echo "<td id='spark-{$execution->id}' class='sparkline text-left no-padding' values='$burns'></td>";
    if(common::canModify('project', $project))
    {
        echo '<td class="c-actions text-left">';

        $title    = '';
        $disabled = $execution->status == 'wait' ? '' : 'disabled';
        $this->app->loadLang('stage');
        if($project and $project->model == 'ipd' and !$execution->parallel)
        {
            $title    = ($execution->ipdStage['canStart'] or $execution->ipdStage['isFirst']) ? '' : sprintf($this->lang->execution->disabledTip->startTip, $this->lang->stage->ipdTypeList[$execution->ipdStage['preAttribute']], $this->lang->stage->ipdTypeList[$execution->attribute]);
            $disabled = $execution->ipdStage['canStart'] ? $disabled : 'disabled';
        }
        echo common::buildIconButton('execution', 'start', "executionID={$execution->id}", $execution, 'list', '', '', 'iframe', true, $disabled, $title, '', empty($disabled));

        $class = !empty($execution->children) ? 'disabled' : '';
        echo $this->buildMenu('task', 'create', "executionID={$execution->id}", '', 'browse', '', '', $class, false, "data-app='execution'");

        if(empty($project)) $project = $this->loadModel('project')->getByID($execution->project);
        if($execution->type == 'stage' or ($this->app->tab == 'project' and !empty($project->model) and $project->model == 'waterfallplus'))
        {
            $isCreateTask = $this->loadModel('programplan')->isCreateTask($execution->id);
            $disabled     = ($isCreateTask and $execution->type == 'stage') ? '' : ' disabled';
            $title        = !$isCreateTask ? $this->lang->programplan->error->createdTask : $this->lang->programplan->createSubPlan;
            $title        = (!empty($disabled) and $execution->type != 'stage') ? $this->lang->programplan->error->notStage : $title;
            echo $this->buildMenu('programplan', 'create', "program={$execution->project}&productID=$productID&planID=$execution->id", $execution, 'browse', 'split', '', $disabled, '', '', $title);
        }

        if($execution->type == 'stage')
        {
            echo $this->buildMenu('programplan', 'edit', "stageID=$execution->id&projectID=$execution->project", $execution, 'browse', '', '', 'iframe', true);
        }
        else
        {
            echo $this->buildMenu('execution', 'edit', "executionID=$execution->id", $execution, 'browse', '', '', 'iframe', true);
        }

        $disabled = !empty($execution->children) ? ' disabled' : '';
        if($this->config->systemMode == 'PLM' and in_array($execution->attribute, array_keys($this->lang->stage->ipdTypeList))) $disabled = '';
        if($execution->status != 'closed' and common::hasPriv('execution', 'close', $execution))
        {
            $ipdDisabled = '';
            $title = $this->lang->execution->close;
            if(isset($execution->ipdStage['canClose']) and !$execution->ipdStage['canClose'] and !$isChild)
            {
                $ipdDisabled = ' disabled ';
                $title       = $execution->attribute == 'launch' ? $this->lang->execution->disabledTip->launchTip : $this->lang->execution->disabledTip->closeTip;
            }
            echo common::buildIconButton('execution', 'close', "stageID={$execution->id}", $execution, 'list', 'off', 'hiddenwin', $disabled . $ipdDisabled . ' iframe', true, (!empty($disabled) || !empty($ipdDisabled)) ? ' disabled' : '', $title, 0, empty($disabled) && empty($ipdDisabled));
        }
        elseif($execution->status == 'closed' and common::hasPriv('execution', 'activate', $execution))
        {
            echo $this->buildMenu('execution', 'activate', "stageID=$execution->id", $execution, 'browse', 'magic', 'hiddenwin' , $disabled . ' iframe', true, '', $this->lang->execution->activate);
        }

        if(common::hasPriv('execution', 'delete', $execution)) echo $this->buildMenu('execution', 'delete', "stageID=$execution->id&confirm=no", $execution, 'browse', 'trash', 'hiddenwin' , $disabled, '', '', $this->lang->delete);

        echo '</td>';
    }

    echo '</tr>';

    if(!empty($execution->children))
    {
        foreach($execution->children as $child)
        {
            $child->division = $execution->division;
            $this->printNestedList($child, true, $users, $productID, $project);
        }
    }

    if(!empty($execution->tasks) && common::canModify('project', $project))
    {
        foreach($execution->tasks as $task)
        {
            $showmore = (count($execution->tasks) == 50) && ($task == end($execution->tasks));
            if($project->model == 'ipd')
            {
                $canStart = $execution->status == 'wait' ? $execution->ipdStage['canStart'] : 1;
                if($execution->status == 'close') $canStart = false;
                if($project->parallel) $canStart = true;
                $task->ipdStage = new stdclass();
                $task->ipdStage->canStart      = $canStart;
                $task->ipdStage->taskStartTip  = sprintf($this->lang->execution->disabledTip->taskStartTip, $this->lang->stage->ipdTypeList[$execution->ipdStage['preAttribute']], $this->lang->stage->ipdTypeList[$execution->attribute]);
                $task->ipdStage->taskFinishTip = sprintf($this->lang->execution->disabledTip->taskFinishTip, $this->lang->stage->ipdTypeList[$execution->ipdStage['preAttribute']], $this->lang->stage->ipdTypeList[$execution->attribute]);
                $task->ipdStage->taskRecordTip = sprintf($this->lang->execution->disabledTip->taskRecordTip, $this->lang->stage->ipdTypeList[$execution->ipdStage['preAttribute']], $this->lang->stage->ipdTypeList[$execution->attribute]);
            }
            echo $this->task->buildNestedList($execution, $task, false, $showmore, $users);
        }
    }

    if(!empty($execution->points) and $this->cookie->showStage)
    {
        $pendingReviews = $this->loadModel('approval')->getPendingReviews('review');
        foreach($execution->points as $point) echo $this->buildPointList($execution, $point, $pendingReviews);
    }
}

/**
 * Get tasks.
 *
 * @param  int    $productID
 * @param  int    $executionID
 * @param  array  $executions
 * @param  string $browseType
 * @param  int    $queryID
 * @param  int    $moduleID
 * @param  string $sort
 * @param  object $pager
 * @access public
 * @return array
 */
public function getTasks($productID, $executionID, $executions, $browseType, $queryID, $moduleID, $sort, $pager, $showParentTask = false)
{
    $this->loadModel('task');

    /* Set modules and $browseType. */
    $modules = array();
    if($moduleID) $modules = $this->loadModel('tree')->getAllChildID($moduleID);
    if($browseType == 'bymodule' or $browseType == 'byproduct')
    {
        if(($this->session->taskBrowseType) and ($this->session->taskBrowseType != 'bysearch')) $browseType = $this->session->taskBrowseType;
    }

    /* Get tasks. */
    $tasks = array();
    if($browseType != "bysearch")
    {
        $queryStatus = $browseType == 'byexecution' ? 'all' : $browseType;
        if($queryStatus == 'unclosed')
        {
            $queryStatus = $this->lang->task->statusList;
            unset($queryStatus['closed']);
            $queryStatus = array_keys($queryStatus);
        }
        $tasks = $this->task->getExecutionTasks($executionID, $productID, $queryStatus, $modules, $sort, $pager, $showParentTask);
    }
    else
    {
        if($queryID)
        {
            $query = $this->loadModel('search')->getQuery($queryID);
            if($query)
            {
                $this->session->set('taskQuery', $query->sql);
                $this->session->set('taskForm', $query->form);
            }
            else
            {
                $this->session->set('taskQuery', ' 1 = 1');
            }
        }
        else
        {
            if($this->session->taskQuery == false) $this->session->set('taskQuery', ' 1 = 1');
        }

        if(strpos($this->session->taskQuery, "deleted =") === false) $this->session->set('taskQuery', $this->session->taskQuery . " AND deleted = '0'");

        $taskQuery = $this->session->taskQuery;
        /* Limit current execution when no execution. */
        if(strpos($taskQuery, "`execution` =") === false) $taskQuery = $taskQuery . " AND `execution` = $executionID";
        $executionQuery = "`execution` " . helper::dbIN(array_keys($executions));
        $taskQuery      = str_replace("`execution` = 'all'", $executionQuery, $taskQuery); // Search all execution.
        if($showParentTask) $taskQuery .= " AND `parent` != 0";
        $this->session->set('taskQueryCondition', $taskQuery, $this->app->tab);
        $this->session->set('taskOnlyCondition', true, $this->app->tab);

        $tasks = $this->getSearchTasks($taskQuery, $pager, $sort);
    }

    return $tasks;
}

/**
 * Import task from Bug.
 *
 * @param  int    $executionID
 * @access public
 * @return void
 */
public function importBugExt($executionID)
{
    $this->loadModel('bug');
    $this->loadModel('task');
    $this->loadModel('story');

    $now = helper::now();

    $showAllModule = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
    $modules       = $this->loadModel('tree')->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');

    $execution      = $this->getByID($executionID);
    $requiredFields = str_replace(',story,', ',', ',' . $this->config->task->create->requiredFields . ',');
    $requiredFields = trim($requiredFields, ',');

    $bugToTasks = fixer::input('post')->get();
    $bugs       = $this->bug->getByList(array_keys($bugToTasks->import));
    foreach($bugToTasks->import as $key => $value)
    {
        $bug = zget($bugs, $key, '');
        if(empty($bug)) continue;

        if($bugToTasks->type[$key] == 'affair')
        {
            foreach($bugToTasks->assignedTo[$key] as $assignedTo)
            {
                $tasks[$key][] = $this->generateTaskData($execution->project, $executionID, $bug, $key, $modules, $requiredFields, $now, $assignedTo, $bugToTasks);
            }
        }
        else
        {
            $tasks[$key] = $this->generateTaskData($execution->project, $executionID, $bug, $key, $modules, $requiredFields, $now, $bugToTasks->assignedTo[$key][0], $bugToTasks);
        }
    }
    if(dao::isError()) return false;

    foreach($tasks as $key => $task)
    {
        if(is_array($task))
        {
            $first = true;
            foreach($task as $assignedTo => $taskData)
            {
                if($first)
                {
                    $bug = $taskData->bug;
                    $this->confirmBug($bug);
                }
                unset($task->bug);
                $this->createTask($taskData);
                $taskID[] = $this->dao->lastInsertID();
                if($first) $this->setStage($taskData);
                $first = false;
            }
        }
        else
        {
            $bug = $task->bug;
            unset($task->bug);
            $this->confirmBug($bug);
            $this->createTask($taskData);
            $taskID = $this->dao->lastInsertID();
            $this->setStage($taskData);
        }

        $actionID = $this->loadModel('action')->create('task', $taskID, 'Opened', '');
        $mails[$key] = new stdClass();
        $mails[$key]->taskID  = $taskID;
        $mails[$key]->actionID = $actionID;

        $this->action->create('bug', $key, 'Totask', '', $taskID);
        $this->dao->update(TABLE_BUG)->set('toTask')->eq($taskID)->where('id')->eq($key)->exec();

        /* activate bug if bug postponed. */
        if($bug->status == 'resolved' && $bug->resolution == 'postponed')
        {
            $newBug = new stdclass();
            $newBug->lastEditedBy   = $this->app->user->account;
            $newBug->lastEditedDate = $now;
            $newBug->assignedDate   = $now;
            $newBug->status         = 'active';
            $newBug->resolvedDate   = '0000-00-00';
            $newBug->resolution     = '';
            $newBug->resolvedBy     = '';
            $newBug->resolvedBuild  = '';
            $newBug->closedBy       = '';
            $newBug->closedDate     = '0000-00-00';
            $newBug->duplicateBug   = '0';

            $this->dao->update(TABLE_BUG)->data($newBug)->autoCheck()->where('id')->eq($key)->exec();
            $this->dao->update(TABLE_BUG)->set('activatedCount = activatedCount + 1')->where('id')->eq($key)->exec();

            $actionID = $this->action->create('bug', $key, 'Activated');
            $changes  = common::createChanges($bug, $newBug);
            $this->action->logHistory($actionID, $changes);
        }

        if(isset($task->assignedTo) and $task->assignedTo and $task->assignedTo != $bug->assignedTo)
        {
            $newBug = new stdClass();
            $newBug->lastEditedBy   = $this->app->user->account;
            $newBug->lastEditedDate = $now;
            $newBug->assignedTo     = $task->assignedTo;
            $newBug->assignedDate   = $now;
            $this->dao->update(TABLE_BUG)->data($newBug)->where('id')->eq($key)->exec();
            if(dao::isError()) return print(js::error(dao::getError()));
            $changes = common::createChanges($bug, $newBug);

            $actionID = $this->action->create('bug', $key, 'Assigned', '', $newBug->assignedTo);
            $this->action->logHistory($actionID, $changes);
        }
    }

    return $mails;
}

public function generateTaskData($projectID, $executionID, $bug, $key, $modules, $requiredFields, $now, $assignedTo, $bugToTasks)
{
    $task = new stdClass();
    $task->bug          = $bug;
    $task->project      = $projectID;
    $task->execution    = $executionID;
    $task->story        = $bug->story;
    $task->storyVersion = $bug->storyVersion;
    $task->module       = !empty($bugToTasks->module[$key]) ?: (isset($modules[$bug->module]) ? $bug->module : 0);
    $task->fromBug      = $key;
    $task->name         = $bug->title;
    $task->type         = $bugToTasks->type[$key];
    $task->pri          = $bugToTasks->pri[$key];
    $task->estStarted   = $bugToTasks->estStarted[$key];
    $task->deadline     = $bugToTasks->deadline[$key];
    $task->estimate     = $bugToTasks->estimate[$key];
    $task->consumed     = 0;
    $task->assignedTo   = $assignedTo;
    $task->mailto       = trim(implode(',', $bugToTasks->mailto[$key]), ',');
    $task->status       = 'wait';
    $task->openedDate   = $now;
    $task->openedBy     = $this->app->user->account;
    if($task->estimate !== '') $task->left = $task->estimate;
    if(strpos($requiredFields, 'estStarted') !== false and helper::isZeroDate($task->estStarted)) $task->estStarted = '';
    if(strpos($requiredFields, 'deadline') !== false and helper::isZeroDate($task->deadline)) $task->deadline = '';
    if(!empty($assignedTo)) $task->assignedDate = $now;

    foreach(explode(',', $requiredFields) as $field)
    {
        if(empty($field))         continue;
        if(!isset($task->$field)) continue;
        if(!empty($task->$field)) continue;

        if($field == 'estimate' and strlen(trim($task->estimate)) != 0) continue;

        dao::$errors['message'][] = sprintf($this->lang->error->notempty, $this->lang->task->$field);
        return false;
    }

    if(!preg_match("/^[0-9]+(.[0-9]{1,3})?$/", $task->estimate) and !empty($task->estimate))
    {
        dao::$errors['message'][] = $this->lang->task->error->estimateNumber;
        return false;
    }
    if(!empty($this->config->limitTaskDate))
    {
        $this->task->checkEstStartedAndDeadline($executionID, $task->estStarted, $task->deadline);
        if(dao::isError()) return false;
    }

    return $task;
}

public function confirmBug($bug)
{
    if(!$bug->confirmed) $this->dao->update(TABLE_BUG)->set('confirmed')->eq(1)->where('id')->eq($bug->id)->exec();
}

public function createTask($task)
{
    $this->dao->insert(TABLE_TASK)->data($task)->checkIF($task->estimate != '', 'estimate', 'float')->exec();
    if(dao::isError())
    {
        echo js::error(dao::getError());
        return print(js::reload('parent'));
    }
}

public function setStage($task)
{
    if($task->story != false) $this->story->setStage($task->story);
}

/**
 * Import task from Bug.
 *
 * @param  int    $executionID
 * @access public
 * @return void
 */
public function importBug($executionID)
{
    $this->loadModel('bug');
    $this->loadModel('task');
    $this->loadModel('story');

    $now = helper::now();

    $showAllModule = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
    $modules       = $this->loadModel('tree')->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');

    $execution      = $this->getByID($executionID);
    $requiredFields = str_replace(',story,', ',', ',' . $this->config->task->create->requiredFields . ',');
    $requiredFields = trim($requiredFields, ',');

    $bugToTasks = fixer::input('post')->get();
    $bugs       = $this->bug->getByList(array_keys($bugToTasks->import));
    foreach($bugToTasks->import as $key => $value)
    {
        $bug = zget($bugs, $key, '');
        if(empty($bug)) continue;

        $task = new stdClass();
        $task->bug          = $bug;
        $task->project      = $execution->project;
        $task->execution    = $executionID;
        $task->story        = $bug->story;
        $task->storyVersion = $bug->storyVersion;
        $task->module       = !empty($bugToTasks->module[$key]) ? $bugToTasks->module[$key] : (isset($modules[$bug->module]) ? $bug->module : 0);
        $task->fromBug      = $key;
        $task->name         = $bug->title;
        $task->type         = $bugToTasks->type[$key];
        $task->pri          = $bugToTasks->pri[$key];
        $task->estStarted   = $bugToTasks->estStarted[$key];
        $task->deadline     = $bugToTasks->deadline[$key];
        $task->estimate     = $bugToTasks->estimate[$key];
        $task->consumed     = 0;
        $task->assignedTo   = '';
        $task->mailto       = trim(implode(',', $bugToTasks->mailto[$key]), ',');
        $task->status       = 'wait';
        $task->openedDate   = $now;
        $task->openedBy     = $this->app->user->account;

        if($task->estimate !== '') $task->left = $task->estimate;
        if(strpos($requiredFields, 'estStarted') !== false and helper::isZeroDate($task->estStarted)) $task->estStarted = '';
        if(strpos($requiredFields, 'deadline') !== false and helper::isZeroDate($task->deadline)) $task->deadline = '';
        if(!empty($bugToTasks->assignedTo[$key]))
        {
            $task->assignedTo   = $bugToTasks->assignedTo[$key];
            $task->assignedDate = $now;
        }

        /* Check task required fields. */
        foreach(explode(',', $requiredFields) as $field)
        {
            if(empty($field))         continue;
            if(!isset($task->$field)) continue;
            if(!empty($task->$field)) continue;

            if($field == 'estimate' and strlen(trim($task->estimate)) != 0) continue;

            dao::$errors['message'][] = sprintf($this->lang->error->notempty, $this->lang->task->$field);
            return false;
        }

        if(!preg_match("/^[0-9]+(.[0-9]{1,3})?$/", $task->estimate) and !empty($task->estimate))
        {
            dao::$errors['message'][] = $this->lang->task->error->estimateNumber;
            return false;
        }

        if(!empty($this->config->limitTaskDate))
        {
            $this->task->checkEstStartedAndDeadline($executionID, $task->estStarted, $task->deadline);
            if(dao::isError()) return false;
        }

        $tasks[$key] = $task;
    }

    foreach($tasks as $key => $task)
    {
        $bug = $task->bug;
        unset($task->bug);

        if(!$bug->confirmed) $this->dao->update(TABLE_BUG)->set('confirmed')->eq(1)->where('id')->eq($bug->id)->exec();
        $this->dao->insert(TABLE_TASK)->data($task)->checkIF($task->estimate != '', 'estimate', 'float')->exec();

        if(dao::isError())
        {
            echo js::error(dao::getError());
            return print(js::reload('parent'));
        }

        $taskID = $this->dao->lastInsertID();
        if($task->story != false) $this->story->setStage($task->story);
        $actionID = $this->loadModel('action')->create('task', $taskID, 'Opened', '');
        $mails[$key] = new stdClass();
        $mails[$key]->taskID  = $taskID;
        $mails[$key]->actionID = $actionID;

        $this->action->create('bug', $key, 'Totask', '', $taskID);
        $this->dao->update(TABLE_BUG)->set('toTask')->eq($taskID)->where('id')->eq($key)->exec();

        /* activate bug if bug postponed. */
        if($bug->status == 'resolved' && $bug->resolution == 'postponed')
        {
            $newBug = new stdclass();
            $newBug->lastEditedBy   = $this->app->user->account;
            $newBug->lastEditedDate = $now;
            $newBug->assignedDate   = $now;
            $newBug->status         = 'active';
            $newBug->resolvedDate   = '0000-00-00';
            $newBug->resolution     = '';
            $newBug->resolvedBy     = '';
            $newBug->resolvedBuild  = '';
            $newBug->closedBy       = '';
            $newBug->closedDate     = '0000-00-00';
            $newBug->duplicateBug   = '0';

            $this->dao->update(TABLE_BUG)->data($newBug)->autoCheck()->where('id')->eq($key)->exec();
            $this->dao->update(TABLE_BUG)->set('activatedCount = activatedCount + 1')->where('id')->eq($key)->exec();

            $actionID = $this->action->create('bug', $key, 'Activated');
            $changes  = common::createChanges($bug, $newBug);
            $this->action->logHistory($actionID, $changes);
        }

        if(isset($task->assignedTo) and $task->assignedTo and $task->assignedTo != $bug->assignedTo)
        {
            $newBug = new stdClass();
            $newBug->lastEditedBy   = $this->app->user->account;
            $newBug->lastEditedDate = $now;
            $newBug->assignedTo     = $task->assignedTo;
            $newBug->assignedDate   = $now;
            $this->dao->update(TABLE_BUG)->data($newBug)->where('id')->eq($key)->exec();
            if(dao::isError()) return print(js::error(dao::getError()));
            $changes = common::createChanges($bug, $newBug);

            $actionID = $this->action->create('bug', $key, 'Assigned', '', $newBug->assignedTo);
            $this->action->logHistory($actionID, $changes);
        }
    }

    return $mails;
}