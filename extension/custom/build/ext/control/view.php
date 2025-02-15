<?php

helper::importControl('build');

class mybuild extends build
{
    /**
     * View a build.
     *
     * @param  int    $buildID
     * @param  string $type
     * @param  string $link
     * @param  string $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function view($buildID, $type = 'story', $link = 'false', $param = '', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 100, $pageID = 1)
    {
        $buildID = (int)$buildID;
        $build   = $this->build->getByID($buildID, true);
        if(!$build)
        {
            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'code' => 404, 'message' => '404 Not found'));
            return print(js::error($this->lang->notFound) . js::locate($this->createLink('execution', 'all')));
        }
        $this->session->project = $build->project;

        $this->loadModel('story');
        $this->loadModel('bug');

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;

        $sort = common::appendOrder($orderBy);
        if(strpos($sort, 'pri_') !== false) $sort = str_replace('pri_', 'priOrder_', $sort);

        /* Get product and bugs. */
        $product = $this->loadModel('product')->getById($build->product);
        if($product->type != 'normal') $this->lang->product->branch = sprintf($this->lang->product->branch, $this->lang->product->branchName[$product->type]);

        $bugPager = new pager($type == 'bug' ? $recTotal : 0, $recPerPage, $type == 'bug' ? $pageID : 1);
        $bugs = $this->dao->select('*')->from(TABLE_BUG)
            ->where('id')->in($build->allBugs)
            ->andWhere('deleted')->eq(0)
            ->beginIF($type == 'bug')->orderBy($sort)->fi()
            ->page($bugPager)
            ->fetchAll();

        /* Get stories and stages. */
        $storyPager = new pager($type == 'story' ? $recTotal : 0, $recPerPage, $type == 'story' ? $pageID : 1);
        $stories    = $this->dao->select("*, IF(`pri` = 0, {$this->config->maxPriValue}, `pri`) as priOrder")->from(TABLE_STORY)
            ->where('id')->in($build->allStories)
            ->andWhere('deleted')->eq(0)
            ->beginIF($type == 'story')->orderBy($sort)->fi()
            ->page($storyPager)
            ->fetchAll('id');

        $stages = $this->dao->select('*')->from(TABLE_STORYSTAGE)->where('story')->in(array_keys($stories))->andWhere('branch')->eq($build->branch)->fetchPairs('story', 'stage');
        foreach($stages as $storyID => $stage) $stories[$storyID]->stage = $stage;

        /* Set menu. */
        $objectType = 'execution';
        $objectID   = $build->execution;
        if($this->app->tab == 'project')
        {
            $this->loadModel('project')->setMenu($build->project);
            $objectType = 'project';
            $objectID   = $build->project;
        }
        elseif($this->app->tab == 'execution')
        {
            $this->loadModel('execution')->setMenu($build->execution);
            $objectType = 'execution';
            $objectID   = $build->execution;
        }

        $executions    = $this->loadModel('execution')->getPairs($this->session->project, 'all', 'empty');
        $executionType = $build->execution ? $this->execution->getByID($build->execution) : '';

        $this->commonActions($build->project);

        $this->view->title      = "BUILD #$build->id $build->name" . (isset($executions[$build->execution]) ? " - " . $executions[$build->execution] : '');
        $this->view->stories    = $stories;
        $this->view->storyPager = $storyPager;

        $generatedBugPager = new pager($type == 'generatedBug' ? $recTotal : 0, $recPerPage, $type == 'generatedBug' ? $pageID : 1);
        $this->view->generatedBugs     = $this->bug->getExecutionBugs($build->execution, $build->product, 'all', "$build->id,{$build->builds}", $type, $param, $type == 'generatedBug' ? $sort : 'status_desc,id_desc', '', $generatedBugPager);
        $this->view->generatedBugPager = $generatedBugPager;

        $this->executeHooks($buildID);
        $branchName = '';
        $this->loadModel('branch');
        if($build->productType != 'normal')
        {
            foreach(explode(',', $build->branch) as $buildBranch)
            {
                $branchName .= $buildBranch == 0 ? $this->lang->branch->main : $this->branch->getById($buildBranch);
                $branchName .= ',';
            }
            $branchName = trim($branchName, ',');
        }

        /* Assign. */
        $this->view->canBeChanged = common::canBeChanged('build', $build); // Determines whether an object is editable.
        $this->view->users        = $this->loadModel('user')->getPairs('noletter');
        $this->view->build         = $build;
        $this->view->buildPairs    = $this->build->getBuildPairs(0, 'all', 'noempty,notrunk', $objectID, $objectType);
        $this->view->builds        = $this->build->getByList(array_keys($this->view->buildPairs));
        $this->view->executions    = $executions;
        $this->view->actions       = $this->loadModel('action')->getList('build', $buildID);
        $this->view->link          = $link;
        $this->view->param         = $param;
        $this->view->orderBy       = $orderBy;
        $this->view->bugs          = $bugs;
        $this->view->type          = $type;
        $this->view->bugPager      = $bugPager;
        $this->view->branchName    = empty($branchName) ? $this->lang->branch->main : $branchName;
        $this->view->childBuilds   = empty($build->builds) ? array() : $this->dao->select('id,name,bugs,stories')->from(TABLE_BUILD)->where('id')->in($build->builds)->fetchAll();
        $this->view->executionType = (!empty($executionType) and $executionType->type == 'stage') ? 1 : 0;

        if($this->app->getViewType() == 'json')
        {
            unset($this->view->storyPager);
            unset($this->view->generatedBugPager);
            unset($this->view->bugPager);
        }

        $this->display();
    }

    /**
     * Common actions.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function commonActions($projectID = 0)
    {
        $hidden  = '';
        if($projectID)
        {
            $project = $this->loadModel('project')->getByID($projectID);
            if(!$project->hasProduct) $hidden = 'hide';

            $this->view->multipleProject = $project->multiple;
            $this->view->project         = $project;
        }

        $this->view->hidden = $hidden;
    }
}