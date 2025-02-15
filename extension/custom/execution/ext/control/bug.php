<?php

class myexecution extends execution
{
    /**
     * Browse bugs of a execution.
     *
     * @param  int    $executionID
     * @param  int    $productID
     * @param  int    $branchID
     * @param  string $orderBy
     * @param  int    $build
     * @param  string $type
     * @param  int    $param
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function bug($executionID = 0, $productID = 0, $branch = 'all', $orderBy = 'status,id_desc', $build = 0, $type = 'all', $param = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Load these two models. */
        $this->loadModel('bug');
        $this->loadModel('user');
        $this->loadModel('product');
        $this->loadModel('datatable');
        $this->loadModel('tree');

        /* Save session. */
        $this->session->set('bugList', $this->app->getURI(true), 'execution');
        $this->session->set('buildList', $this->app->getURI(true), 'execution');

        $type        = strtolower($type);
        $queryID     = ($type == 'bysearch') ? (int)$param : 0;
        $execution   = $this->commonAction($executionID);
        $project     = $this->loadModel('project')->getByID($execution->project);
        $executionID = $execution->id;
	$products    = $this->product->getProducts($execution->id);
	if(count($products) === 1) $productID = current($products)->id;

        if($execution->hasProduct)
        {
            unset($this->config->bug->search['fields']['product']);
            if($project->model != 'scrum')
            {
                unset($this->config->bug->search['fields']['plan']);
            }
        }

        $productPairs = array('0' => $this->lang->product->all);
        foreach($products as $productData) $productPairs[$productData->id] = $productData->name;
        if($execution->hasProduct) $this->lang->modulePageNav = $this->product->select($productPairs, $productID, 'execution', 'bug', $executionID, $branch);

        /* Header and position. */
        $title      = $execution->name . $this->lang->colon . $this->lang->execution->bug;
        $position[] = html::a($this->createLink('execution', 'browse', "executionID=$executionID"), $execution->name);
        $position[] = $this->lang->execution->bug;

        /* Load pager and get bugs, user. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'xhtml') $recPerPage = 10;
        $pager = new pager($recTotal, $recPerPage, $pageID);
        $sort  = common::appendOrder($orderBy);
        $bugs  = $this->bug->getExecutionBugs($executionID, $productID, $branch, $build, $type, $param, $sort, '', $pager);
        $bugs  = $this->bug->checkDelayedBugs($bugs);
        $users = $this->user->getPairs('noletter');

        /* team member pairs. */
        $memberPairs = array();
        $memberPairs[] = "";
        foreach($this->view->teamMembers as $key => $member)
        {
            $memberPairs[$key] = $member->realname;
        }
        $memberPairs = $this->user->processAccountSort($memberPairs);

        /* Build the search form. */
        $actionURL = $this->createLink('execution', 'bug', "executionID=$executionID&productID=$productID&branch=$branch&orderBy=$orderBy&build=$build&type=bysearch&queryID=myQueryID");
        $this->execution->buildBugSearchForm($products, $queryID, $actionURL);

        $product = $this->product->getById($productID);
        $showBranch      = false;
        $branchOption    = array();
        $branchTagOption = array();
        if($product and $product->type != 'normal')
        {
            /* Display of branch label. */
            $showBranch = $this->loadModel('branch')->showBranch($productID);

            /* Display status of branch. */
            $branches = $this->branch->getList($productID, $executionID, 'all');
            foreach($branches as $branchInfo)
            {
                $branchOption[$branchInfo->id]    = $branchInfo->name;
                $branchTagOption[$branchInfo->id] = $branchInfo->name . ($branchInfo->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : '');
            }
        }

        /* Get story and task id list. */
        $storyIdList = $taskIdList = array();
        foreach($bugs as $bug)
        {
            if($bug->story)  $storyIdList[$bug->story] = $bug->story;
            if($bug->task)   $taskIdList[$bug->task]   = $bug->task;
            if($bug->toTask) $taskIdList[$bug->toTask] = $bug->toTask;
        }
        $storyList = $storyIdList ? $this->loadModel('story')->getByList($storyIdList) : array();
        $taskList  = $taskIdList  ? $this->loadModel('task')->getByList($taskIdList)   : array();

        $showModule  = !empty($this->config->datatable->bugBrowse->showModule) ? $this->config->datatable->bugBrowse->showModule : '';

        /* Process the openedBuild and resolvedBuild fields. */
        $bugs = $this->bug->processBuildForBugs($bugs);

        $moduleID = $type != 'bysearch' ? $param : 0;
        $modules  = $this->tree->getAllModulePairs('bug');

        /* Get module tree.*/
        $extra = array('projectID' => $executionID, 'orderBy' => $orderBy, 'type' => $type, 'build' => $build, 'branchID' => $branch);
        if($executionID and empty($productID) and count($products) > 1)
        {
            $moduleTree = $this->tree->getBugTreeMenu($executionID, $productID, 0, array('treeModel', 'createBugLink'), $extra);
        }
        elseif(!empty($products))
        {
            $productID  = empty($productID) ? reset($products)->id : $productID;
            $moduleTree = $this->tree->getTreeMenu($productID, 'bug', 0, array('treeModel', 'createBugLink'), $extra + array('branchID' => $branch, 'productID' => $productID), $branch);
        }
        else
        {
            $moduleTree = '';
        }
        $tree = $moduleID ? $this->tree->getByID($moduleID) : '';

        $showModule = !empty($this->config->datatable->executionBug->showModule) ? $this->config->datatable->executionBug->showModule : '';
        if($this->config->edition == 'ipd') $bugs = $this->loadModel('story')->getAffectObject($bugs, 'bug');
        
        $products = array('' => '');
        $products += $this->product->getPairs((empty($this->config->CRProduct) || empty($this->config->CRProject)) ? 'noclosed' : '', 0, 'all');

        /* Assign. */
        $this->view->title           = $title;
        $this->view->position        = $position;
        $this->view->bugs            = $bugs;
        $this->view->tabID           = 'bug';
        $this->view->build           = $this->loadModel('build')->getById($build);
        $this->view->buildID         = $this->view->build ? $this->view->build->id : 0;
        $this->view->pager           = $pager;
        $this->view->orderBy         = $orderBy;
        $this->view->users           = $users;
        $this->view->productID       = $productID;
        $this->view->project         = $project;
        $this->view->branchID        = empty($this->view->build->branch) ? $branch : $this->view->build->branch;
        $this->view->memberPairs     = $memberPairs;
        $this->view->type            = $type;
        $this->view->summary         = $this->bug->summary($bugs);
        $this->view->param           = $param;
        $this->view->defaultProduct  = (empty($productID) and !empty($products)) ? current(array_keys($products)) : $productID;
        $this->view->builds          = $this->build->getBuildPairs($productID);
        $this->view->branchOption    = $branchOption;
        $this->view->branchTagOption = $branchTagOption;
        $this->view->plans           = $this->loadModel('productplan')->getPairs($productID ? $productID : array_keys($products));
        $this->view->stories         = $storyList;
        $this->view->tasks           = $taskList;
        $this->view->projectPairs    = $this->loadModel('project')->getPairsByProgram();
        $this->view->moduleTree      = $moduleTree;
        $this->view->modules         = $modules;
        $this->view->moduleID        = $moduleID;
        $this->view->moduleName      = $moduleID ? $tree->name : $this->lang->tree->all;
        $this->view->modulePairs     = $showModule ? $this->tree->getModulePairs($productID, 'bug', $showModule) : array();
        $this->view->setModule       = true;
        $this->view->showBranch      = false;
        $this->view->products        = $products;
        $this->view->projectExecutionPairs = $this->loadModel('project')->getProjectExecutionPairs();
        $this->display();
    }
}