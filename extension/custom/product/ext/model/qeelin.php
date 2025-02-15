<?php

public function getIdListByProjectClosedStatus()
{
    return $this->dao->select('id')->from(TABLE_PRODUCT)
        ->where('shadow')->eq('1')
        ->andWhere('status')->eq('closed')
        ->fetchPairs('id', 'id');
}

public function getAllExecutionPairsByProduct($productID, $branch = 0, $projectID = 0, $mode = '')
{
    if(empty($productID)) return array();
    $executions = $this->dao->select('t2.id,t2.project,t2.name,t2.grade,t2.parent,t2.attribute')->from(TABLE_PROJECTPRODUCT)->alias('t1')
        ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
        ->where('t1.product')->eq($productID)
        ->andWhere('t2.type')->in('stage,sprint,kanban')
        ->beginIF($branch and $branch != 'all')->andWhere('t1.branch')->in($branch)->fi()
        ->beginIF($projectID)->andWhere('t2.project')->eq($projectID)->fi()
        ->beginIF(strpos($mode, 'projectclosefilter') !== false)->andWhere('t2.status')->ne('closed')->fi()
        ->beginIF(!$this->app->user->admin)->andWhere('t2.id')->in($this->app->user->view->sprints)->fi()
        ->andWhere('t2.deleted')->eq('0')
        ->orderBy('id_desc')
        ->fetchAll('id');

    $projectIdList = array();
    foreach($executions as $id => $execution) $projectIdList[$execution->project] = $execution->project;

    $executionPairs = array(0 => '');
    $projectPairs   = $this->loadModel('project')->getPairsByIdList($projectIdList, 'all');
    foreach($executions as $id => $execution)
    {
        if($execution->grade == 2 && isset($executions[$execution->parent]))
        {
            $execution->name = $projectPairs[$execution->project] . '/' . $executions[$execution->parent]->name . '/' . $execution->name;
            $executions[$execution->parent]->children[$id] = $execution->name;
            unset($executions[$id]);
        }
    }

    if($projectID) $executions = $this->loadModel('execution')->resetExecutionSorts($executions);
    foreach($executions as $execution)
    {
        if(strpos($mode, 'stagefilter') !== false and in_array($execution->attribute, array('request', 'design', 'review'))) continue;

        if(isset($execution->children))
        {
            $executionPairs = $executionPairs + $execution->children;
            continue;
        }

        /* Some stage of waterfall not need.*/
        if(isset($projectPairs[$execution->project])) $executionPairs[$execution->id] = $projectPairs[$execution->project] . '/' . $execution->name;
    }

    return $executionPairs;
}

public function getPairs($mode = '', $programID = 0, $append = '', $shadow = 0)
{
    if(defined('TUTORIAL')) return $this->loadModel('tutorial')->getProductPairs();

    if(!empty($append) and is_array($append)) $append = implode(',', $append);

    $views   = empty($append) ? $this->app->user->view->products : $this->app->user->view->products . ",$append";
    $orderBy = !empty($this->config->product->orderBy) ? $this->config->product->orderBy : 'isClosed';
    /* Order by program. */
    return $this->dao->select("t1.*,  IF(INSTR(' closed', t1.status) < 2, 0, 1) AS isClosed")->from(TABLE_PRODUCT)->alias('t1')
        ->leftJoin(TABLE_PROGRAM)->alias('t2')->on('t1.program = t2.id')
        ->where('t1.deleted')->eq(0)
        ->beginIF($programID)->andWhere('t1.program')->eq($programID)->fi()
        ->beginIF(strpos($mode, 'noclosed') !== false)->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF(!$this->app->user->admin && $this->config->vision != 'lite' && strpos($mode, 'all') === false)->andWhere('t1.id')->in($views)->fi()
        ->beginIF($shadow !== 'all')->andWhere('t1.shadow')->eq((int)$shadow)->fi()
        ->beginIF($shadow !== 'all' && (int)$shadow == 1 && isset($this->config->CRProject) && empty($this->config->CRProject))->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF($this->config->vision != 'or' && $this->config->vision != 'lite')->andWhere("FIND_IN_SET('{$this->config->vision}', t1.vision)")->fi()
        ->beginIF(($this->config->vision == 'or' || $this->config->vision == 'lite') && $this->app->tab == 'feedback')->andWhere('t1.status')->eq('normal')->fi()
        ->orderBy("$orderBy, t2.order_asc, t1.line_desc, t1.order_asc")
        ->fetchPairs('id', 'name');
}

public function getPairsForEffortExport()
{
    return $this->dao->select("id, name")->from(TABLE_PRODUCT)
        ->where('deleted')->eq(0)
        ->beginIF(!$this->app->user->admin && $this->config->vision != 'lite')->andWhere('id')->in($this->app->user->view->products)->fi()
        ->beginIF($this->config->vision != 'or' && $this->config->vision != 'lite')->andWhere("FIND_IN_SET('{$this->config->vision}', vision)")->fi()
        ->fetchPairs('id', 'name');
}

/**
 * Get executions by product and project.
 *
 * @param  int    $productID
 * @param  int    $branch
 * @param  string $orderBy
 * @param  int    $projectID
 * @param  string $mode stagefilter or empty
 * @access public
 * @return array
 */
public function getExecutionPairsByProduct($productID, $branch = 0, $orderBy = 'id_asc', $projectID = 0, $mode = '')
{
    if(empty($productID)) return array();

    $projects     = $this->loadModel('project')->getByIdList($projectID);
    $hasWaterfall = false;
    foreach($projects as $project)
    {
        if(in_array($project->model, array('waterfall', 'waterfallplus'))) $hasWaterfall = true;
    }
    $orderBy = $hasWaterfall ? 't2.begin_asc,t2.id_asc' : 't2.begin_desc,t2.id_desc';

    $executions = $this->dao->select('t2.id,t2.name,t2.project,t2.grade,t2.path,t2.parent,t2.attribute,t2.multiple,t3.name as projectName')->from(TABLE_PROJECTPRODUCT)->alias('t1')
        ->leftJoin(TABLE_EXECUTION)->alias('t2')->on('t1.project = t2.id')
        ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t2.project = t3.id')
        ->where('t1.product')->eq($productID)
        ->andWhere('t2.type')->in('sprint,kanban,stage')
        ->beginIF($projectID)->andWhere('t2.project')->in($projectID)->fi()
        ->beginIF($branch !== '' and strpos($branch, 'all') === false)->andWhere('t1.branch')->in($branch)->fi()
        ->beginIF(!$this->app->user->admin)->andWhere('t2.id')->in($this->app->user->view->sprints)->fi()
        ->beginIF(strpos($mode, 'noclosed') !== false)->andWhere('t2.status')->ne('closed')->fi()
        ->beginIF(strpos($mode, 'multiple') !== false)->andWhere('t2.multiple')->eq('1')->fi()
        ->beginIF(strpos($mode, 'projectclosefilter') !== false)->andWhere('t3.status')->ne('closed')->fi()
        ->andWhere('t2.deleted')->eq('0')
        ->orderBy($orderBy)
        ->fetchAll('id');

    /* Only show leaf executions. */
    $allExecutions = $this->dao->select('id,name,attribute,parent')->from(TABLE_EXECUTION)
        ->where('type')->notin(array('program', 'project'))
        ->andWhere('deleted')->eq('0')
        ->fetchAll('id');
    $parents = array();
    foreach($allExecutions as $exec) $parents[$exec->parent] = true;

    if($projectID) $executions = $this->loadModel('execution')->resetExecutionSorts($executions);

    $executionPairs = array('0' => '');
    foreach($executions as $execID=> $execution)
    {
        if(isset($parents[$execID])) continue; // Only show leaf.
        if(strpos($mode, 'stagefilter') !== false and in_array($execution->attribute, array('request', 'design', 'review'))) continue; // Some stages of waterfall not need.

        if(empty($execution->multiple))
        {
            $this->app->loadLang('project');
            $executionPairs[$execution->id] = $execution->projectName . "({$this->lang->project->disableExecution})";
        }
        else
        {
            $paths = array_slice(explode(',', trim($execution->path, ',')), 1);
            $executionName = $projectID != 0 ? '' : $execution->projectName;
            foreach($paths as $path)
            {
                if(!isset($allExecutions[$path])) continue;
                $executionName .= '/' . $allExecutions[$path]->name;
            }

            $executionPairs[$execID] = $executionName;
        }
    }

    return $executionPairs;
}