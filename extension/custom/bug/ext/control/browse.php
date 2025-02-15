<?php

class mybug extends bug
{
    /**
     * Browse bugs.
     *
     * @param  int    $productID
     * @param  string $branch
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($productID = 0, $branch = '', $browseType = '', $param = 0, $orderBy = '', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->loadModel('datatable');

        $productID = $this->product->saveState($productID, $this->products);
        $product   = $this->product->getById($productID);
        if($product->type != 'normal')
        {
            /* Set productID, moduleID, queryID and branch. */
            $branch = ($this->cookie->preBranch !== '' and $branch === '') ? $this->cookie->preBranch : $branch;
            setcookie('preProductID', $productID, $this->config->cookieLife, $this->config->webRoot, '', $this->config->cookieSecure, true);
            setcookie('preBranch', $branch, $this->config->cookieLife, $this->config->webRoot, '', $this->config->cookieSecure, true);
        }
        else
        {
            $branch = 'all';
        }

        $canModifyProject = true;

        if($product->shadow && $product->status == 'closed') $canModifyProject = false;

        $this->qa->setMenu($this->products, $productID, $branch);

        /* Set browse type. */
        $browseType = strtolower($browseType);
        if($this->cookie->preProductID != $productID or ($this->cookie->preBranch != $branch and $product->type != 'normal' and $branch != 'all') or $browseType == 'bybranch')
        {
            $_COOKIE['bugModule'] = 0;
            setcookie('bugModule', 0, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
        }
        if($browseType == 'bymodule' or $browseType == '')
        {
            setcookie('bugModule', (int)$param, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
            $_COOKIE['bugBranch'] = 0;
            setcookie('bugBranch', 0, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
            if($browseType == '') setcookie('treeBranch', $branch, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
        }
        if($browseType == 'bybranch') setcookie('bugBranch', $branch, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
        if($browseType != 'bymodule' and $browseType != 'bybranch') $this->session->set('bugBrowseType', $browseType);

        $moduleID = ($browseType == 'bymodule') ? (int)$param : (($browseType == 'bysearch' or $browseType == 'bybranch') ? 0 : ($this->cookie->bugModule ? $this->cookie->bugModule : 0));
        $queryID  = ($browseType == 'bysearch') ? (int)$param : 0;

        /* Set session. */
        $this->session->set('bugList', $this->app->getURI(true) . "#app={$this->app->tab}", 'qa');

        /* Set moduleTree. */
        if($browseType == '')
        {
            setcookie('treeBranch', $branch, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
            $browseType = 'unclosed';
        }
        else
        {
            $branch = $this->cookie->treeBranch;
        }

        if($this->projectID and !$productID)
        {
            $moduleTree = $this->tree->getBugTreeMenu($this->projectID, $productID, 0, array('treeModel', 'createBugLink'));
        }
        else
        {
            $moduleTree = $this->tree->getTreeMenu($productID, 'bug', 0, array('treeModel', 'createBugLink'), '', $branch);
        }

        if(($browseType != 'bymodule' && $browseType != 'bybranch')) $this->session->set('bugBrowseType', $browseType);
        if(($browseType == 'bymodule' || $browseType == 'bybranch') and $this->session->bugBrowseType == 'bysearch') $this->session->set('bugBrowseType', 'unclosed');

        /* Process the order by field. */
        if(!$orderBy) $orderBy = $this->cookie->qaBugOrder ? $this->cookie->qaBugOrder : 'id_desc';
        setcookie('qaBugOrder', $orderBy, 0, $this->config->webRoot, '', $this->config->cookieSecure, true);

        /* Append id for secend sort. */
        $sort = common::appendOrder($orderBy);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml' || $this->app->getViewType() == 'xhtml') $recPerPage = 10;
        $pager = new pager($recTotal, $recPerPage, $pageID);

        /* Get executions. */
        $cacheKey = sprintf($this->config->cacheKeys->bug->browse, $this->projectID);
        if(helper::isCacheEnabled() && $this->cache->has($cacheKey))
        {
            $executions = $this->cache->get($cacheKey);
        }
        else
        {
            $executions = $this->loadModel('execution')->fetchPairs($this->projectID, 'all', 'empty|withdelete|hideMultiple');
            if($this->config->cache->enable) $this->cache->set($cacheKey, $executions);
        }

        /* Get product id list. */
        $productIDList = $productID ? $productID : array_keys($this->products);

        /* Get bugs. */
        $bugs = $this->bug->getBugs($productIDList, $executions, $branch, $browseType, $moduleID, $queryID, $sort, $pager, $this->projectID);

        /* Process the sql, get the conditon partion, save it to session. */
        $this->loadModel('common')->saveQueryCondition($this->bug->dao->get(), 'bug', $browseType == 'needconfirm' ? false : true);

        /* Process bug for check story changed. */
        $bugs = $this->loadModel('story')->checkNeedConfirm($bugs);

        /* Process the openedBuild and resolvedBuild fields. */
        $bugs = $this->bug->processBuildForBugs($bugs);

        /* Get story and task id list. */
        $storyIdList = $taskIdList = $projectIdList =  array();
        foreach($bugs as $bug)
        {
            if($bug->story)  $storyIdList[$bug->story] = $bug->story;
            if($bug->task)   $taskIdList[$bug->task]   = $bug->task;
            if($bug->toTask) $taskIdList[$bug->toTask] = $bug->toTask;
            $bug->canBeClosedByProject = true;
            if($product->shadow)
            {
                if($product->status == 'closed' && empty($this->config->CRProject)) $bug->canBeClosedByProject = false;
            }
            else
            {
                $projectIdList[] = isset($bug->project) && !empty($bug->project) ? $bug->project : 0;
            }
        }
        $storyList   = $storyIdList ? $this->loadModel('story')->getByList($storyIdList) : array();
        $taskList    = $taskIdList  ? $this->loadModel('task')->getByList($taskIdList)   : array();
        $projectList = $projectIdList ? $this->loadModel('project')->getByIdList($projectIdList) : array();

        foreach($bugs as $bug)
        {
            if(isset($projectList[$bug->project]) && empty($this->config->CRProject) && $projectList[$bug->project]->status == 'closed') $bug->canBeClosedByProject = false;
        }

        /* Build the search form. */
        $actionURL = $this->createLink('bug', 'browse', "productID=$productID&branch=$branch&browseType=bySearch&queryID=myQueryID");
        $this->config->bug->search['onMenuBar'] = 'yes';
        $searchProducts = $this->product->getPairs('', 0, '', 'all');
        $this->bug->buildSearchForm($productID, $searchProducts, $queryID, $actionURL, $branch);

        $showModule  = !empty($this->config->datatable->bugBrowse->showModule) ? $this->config->datatable->bugBrowse->showModule : '';
        $productName = ($productID and isset($this->products[$productID])) ? $this->products[$productID] : $this->lang->product->allProduct;

        $showBranch      = false;
        $branchOption    = array();
        $branchTagOption = array();
        if($product and $product->type != 'normal')
        {
            /* Display of branch label. */
            $showBranch = $this->loadModel('branch')->showBranch($productID);

            /* Display status of branch. */
            $branches = $this->loadModel('branch')->getList($productID, 0, 'all');
            foreach($branches as $branchInfo)
            {
                $branchOption[$branchInfo->id]    = $branchInfo->name;
                $branchTagOption[$branchInfo->id] = $branchInfo->name . ($branchInfo->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : '');
            }
        }
        if($this->config->edition == 'ipd') $bugs = $this->loadModel('story')->getAffectObject($bugs, 'bug');

        $projects      = $this->product->getProjectPairsByProduct($productID, $branch, '', (isset($this->config->CRProject) && empty($this->config->CRProject)) ? 'unclosed' : '');
        $projectID     = !empty($projects) ? key($projects) : '';
        $showExecution = true;
        $projectModel  = '';
        $executions    = [];
        $executionID   = '';
        if(!empty($projectID))
        {
            $project = $this->loadModel('project')->getByID($projectID);
            if(!$project->multiple) $showExecution = false;
            $projectModel = $project->model;
            $executions   = $this->product->getExecutionPairsByProduct($productID, $branch ? "0,$branch" : 0, 'id_asc', $projectID);
            $executionID  = !empty($executions) ? key($executions) : '';
        }
        if($executionID)
        {
            $builds  = $this->loadModel('build')->getBuildPairs($productID, $branch ? $branch : 0, 'noempty,noterminate,nodone,noreleased', $executionID, 'execution');
        }
        else
        {
            $builds  = $this->loadModel('build')->getBuildPairs($productID, $branch ? $branch : 0, 'noempty,noterminate,nodone,withbranch,noreleased');
        }
        $projectsAll = $this->loadModel('project')->getByIdList(array_keys($projects));
        $projectModels = [];
        foreach($projectsAll as $project)
        {
            $projectModels[$project->id] = $project->model;
        }
        /* Set view. */
        $this->view->title           = $productName . $this->lang->colon . $this->lang->bug->common;
        $this->view->position[]      = html::a($this->createLink('bug', 'browse', "productID=$productID"), $productName,'','title=' . $productName);
        $this->view->position[]      = $this->lang->bug->common;
        $this->view->productID       = $productID;
        $this->view->product         = $product;
        $this->view->productName     = $productName;
        $this->view->builds          = $this->loadModel('build')->getBuildPairs($productID, $branch);
        $this->view->releasedBuilds  = $this->loadModel('release')->getReleasedBuilds($productID, $branch);
        $this->view->modules         = $this->tree->getOptionMenu($productID, $viewType = 'bug', $startModuleID = 0, $branch);
        $this->view->moduleTree      = $moduleTree;
        $this->view->moduleName      = $moduleID ? $this->tree->getById($moduleID)->name : $this->lang->tree->all;
        $this->view->summary         = $this->bug->summary($bugs);
        $this->view->browseType      = $browseType;
        $this->view->bugs            = $bugs;
        $this->view->users           = $this->user->getPairs('noletter');
        $this->view->pager           = $pager;
        $this->view->param           = $param;
        $this->view->orderBy         = $orderBy;
        $this->view->moduleID        = $moduleID;
        $this->view->memberPairs     = $this->user->getPairs('noletter|noclosed');
        $this->view->branch          = $branch;
        $this->view->branchOption    = $branchOption;
        $this->view->branchTagOption = $branchTagOption;
        $this->view->executions      = $executions;
        $this->view->plans           = $this->loadModel('productplan')->getPairs($productID);
        $this->view->stories         = $storyList;
        $this->view->tasks           = $taskList;
        $this->view->setModule       = true;
        $this->view->isProjectBug    = ($productID and !$this->projectID) ? false : true;
        $this->view->modulePairs     = $showModule ? $this->tree->getModulePairs($productID, 'bug', $showModule) : array();
        $this->view->showBranch      = $showBranch;
        $this->view->projectPairs    = $this->loadModel('project')->getPairsByProgram();
        $this->view->projects        = $projects;
        $this->view->projectID       = $projectID;
        $this->view->projectModels   = $projectModels;
        $this->view->showExecution   = $showExecution;
        $this->view->projectModel    = $projectModel;
        $this->view->executions      = $executions;
        $this->view->executionID     = $executionID;
        $this->view->builds          = $builds;
        $this->view->projectExecutionPairs = $this->loadModel('project')->getProjectExecutionPairs();
        $this->display();
    }
}