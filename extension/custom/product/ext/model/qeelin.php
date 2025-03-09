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

/**
 * Get products.
 *
 * @param  int        $programID
 * @param  string     $mode         all|noclosed|involved|review|feedback
 * @param  int        $limit
 * @param  int        $line
 * @param  string|int $shadow       all | 0 | 1
 * @param  string     $fields       * or fieldList, such as id,name,program
 * @access public
 * @return array
 */
public function getList($programID = 0, $mode = 'all', $limit = 0, $line = 0, $shadow = 0, $fields = '*')
{
    $fields = explode(',', $fields);
    $fields = trim(implode(',t1.', $fields), ',');

    $products = $this->dao->select("DISTINCT t1.$fields,t2.order")->from(TABLE_PRODUCT)->alias('t1')
        ->leftJoin(TABLE_PROGRAM)->alias('t2')->on('t1.program = t2.id')
        ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t3.product = t1.id')
        ->leftJoin(TABLE_TEAM)->alias('t4')->on("t4.root = t3.project and t4.type='project'")
        ->where('t1.deleted')->eq(0)
        ->beginIF($shadow !== 'all')->andWhere('t1.shadow')->eq((int)$shadow)->fi()
        ->beginIF($programID)->andWhere('t1.program')->eq($programID)->fi()
        ->beginIF($line > 0)->andWhere('t1.line')->eq($line)->fi()
        ->beginIF(strpos($mode, 'feedback') === false && !$this->app->user->admin)->andWhere('t1.id')->in($this->app->user->view->products)->fi()
        ->beginIF(strpos($mode, 'feedback') === false)->andWhere("CONCAT(',', t1.vision, ',')")->like("%,{$this->config->vision},%")->fi()
        ->beginIF(strpos($mode, 'noclosed') !== false)->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF(strpos($mode, 'nowait') !== false)->andWhere('t1.status')->ne('wait')->fi()
        ->beginIF(in_array($mode, array_keys($this->lang->story->statusList)))->andWhere('t1.status')->in($mode)->fi()
        ->beginIF($mode == 'involved')
        ->andWhere('t1.PO', true)->eq($this->app->user->account)
        ->orWhere('t1.QD')->eq($this->app->user->account)
        ->orWhere('t1.RD')->eq($this->app->user->account)
        ->orWhere('t1.createdBy')->eq($this->app->user->account)
        ->orWhere('t4.account')->eq($this->app->user->account)
        ->markRight(1)
        ->fi()
        ->beginIF($mode == 'review')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.reviewers)")
        ->andWhere('t1.reviewStatus')->eq('doing')
        ->fi()
        ->beginIF($mode == 'collect')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.favorites)")
        ->fi()
        ->orderBy('t2.order_asc, t1.line_desc, t1.order_asc')
        ->beginIF($limit > 0)->limit($limit)->fi()
        ->fetchAll('id');

    return $products;
}

/**
 * Get product stats.
 *
 * @param string $orderBy
 * @param mixed $pager
 * @param string $status
 * @param int $line
 * @param string $storyType
 * @param int $programID
 * @param int $param
 * @param int $shadow
 * @access public
 * @return void
 */
public function getStats($orderBy = 'order_asc', $pager = null, $status = 'noclosed', $line = 0, $storyType = 'story', $programID = 0, $param = 0, $shadow = 0)
{
    $this->loadModel('story');
    $this->loadModel('bug');

    $products = $status == 'bySearch' ? $this->getListBySearch($param) : $this->getList($programID, $status, $limit = 0, $line, $shadow, 'id');
    if(empty($products)) return array();

    $productKeys = array_keys($products);
    if($orderBy == 'program_asc')
    {
        $products = $this->dao->select('t1.id as id, t1.*')->from(TABLE_PRODUCT)->alias('t1')
            ->leftJoin(TABLE_PROGRAM)->alias('t2')->on('t1.program = t2.id')
            ->where('t1.id')->in($productKeys)
            ->orderBy('t2.order_asc, t1.line_desc, t1.order_asc')
            ->page($pager)
            ->fetchAll('id');
    }
    else
    {
        $products = $this->dao->select('*')->from(TABLE_PRODUCT)
            ->where('id')->in($productKeys)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /* Recalculate productKeys after paging. */
    $productKeys = array_keys($products);

    $linePairs = $this->getLinePairs();
    foreach($products as $product)
    {
        $product->lineName = zget($linePairs, $product->line, '');
        $product->collect  = false;
        if(strpos(",{$product->favorites},", ",{$this->app->user->account},") !== false) $product->collect = true;
    }

    if(empty($programID))
    {
        $programKeys = array(0 => 0);
        foreach($products as $product) $programKeys[] = $product->program;
        $programs = $this->dao->select('id,name,PM')->from(TABLE_PROGRAM)
            ->where('id')->in(array_unique($programKeys))
            ->fetchAll('id');

        $this->app->loadLang('project');
        foreach($products as $product)
        {
            $product->programName = (!empty($product->line) && empty($product->program)) ? $this->lang->project->future : (isset($programs[$product->program]) ? $programs[$product->program]->name : '');
            $product->programPM   = isset($programs[$product->program]) ? $programs[$product->program]->PM : '';
        }
    }

    $productStories = $this->dao->select("product,
        COUNT(CASE WHEN status = 'closed' AND closedReason = 'done' THEN 1 END) AS finishClosed,
        COUNT(CASE WHEN status = 'launched' THEN 1 END) AS launched,
        COUNT(CASE WHEN status = 'developing' THEN 1 END) AS developing")
        ->from(TABLE_STORY)
        ->where('deleted')->eq(0)
        ->groupBy('product')
        ->fetchAll('product');

    foreach($products as $productID => $product)
    {
        $productStory = isset($productStories[$productID]) ? $productStories[$productID] : null;

        $products[$productID]->finishClosedStories = $productStory ? $productStory->finishClosed : 0;
        $products[$productID]->launchedStories     = $productStory ? $productStory->launched : 0;
        $products[$productID]->developingStories   = $productStory ? $productStory->developing : 0;
    }

    return $products;
}

/**
 * Build operate menu.
 *
 * @param  object $product
 * @param  string $type
 * @access public
 * @return string
 */
public function buildOperateMenu($product, $type = 'view')
{
    $menu   = '';
    $params = "product=$product->id";

    if($type == 'view')
    {
        $menu .= "<div class='divider'></div>";
        $menu .= $this->buildFlowMenu('product', $product, $type, 'direct');
        $menu .= "<div class='divider'></div>";

        if($product->status != 'closed') $menu .= $this->buildMenu('product', 'close', $params, $product, $type, '', '', 'iframe', true, "data-app='product'");
        if($product->status == 'closed') $menu .= $this->buildMenu('product', 'activate', $params, $product, $type, '', '', 'iframe', true, "data-app='product'");
        $menu .= "<div class='divider'></div>";

        $menu .= $this->buildMenu('product', 'edit', $params, $product, $type);
    }
    elseif($type == 'browse')
    {
        $menu .= $this->buildMenu('product', 'edit', $params, $product, $type);
        if($product->status != 'closed') $menu .= $this->buildMenu('product', 'close', $params, $product, $type, '', '', 'iframe', true, "data-app='product'");
        if($product->status == 'closed') $menu .= $this->buildMenu('product', 'activate', $params, $product, $type, '', '', 'iframe', true, "data-app='product'");
        $menu .= $this->buildMenu('product', 'collect', $params, $product, $type, $product->collect ? 'star' : 'star-empty', '', '', '', "data-app='product'");
    }

    $menu .= $this->buildMenu('product', 'delete', $params, $product, $type, 'trash', 'hiddenwin');

    return $menu;
}