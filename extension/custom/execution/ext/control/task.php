<?php

class myexecution extends execution
{
    /**
     * Tasks of a execution.
     *
     * @param  int    $executionID
     * @param  string $status
     * @param  string $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function task($executionID = 0, $status = 'unclosed', $param = 0, $orderBy = '', $recTotal = 0, $recPerPage = 100, $pageID = 1)
    {
        $this->loadModel('tree');
        $this->loadModel('search');
        $this->loadModel('task');
        $this->loadModel('datatable');
        $this->loadModel('setting');
        $this->loadModel('product');
        $this->loadModel('user');

        if(common::hasPriv('execution', 'create')) $this->lang->TRActions = html::a($this->createLink('execution', 'create'), "<i class='icon icon-sm icon-plus'></i> " . $this->lang->execution->create, '', "class='btn btn-primary'");

        if(!isset($_SESSION['limitedExecutions'])) $this->execution->getLimitedExecution();

        if($executionID) $this->session->set("storyList", $this->createLink("execution", "story", "&executionID=" . $executionID));

        /* Set browse type. */
        $browseType = strtolower($status);

        $execution = $this->commonAction($executionID, $status);
        if($this->config->systemMode == 'PLM') $execution->ipdStage = $this->execution->canStageStart($execution);
        $executionID = $execution->id;

        if($execution->type == 'kanban' and $this->config->vision != 'lite' and $this->app->getViewType() != 'json') $this->locate($this->createLink('execution', 'kanban', "executionID=$executionID"));

        /* Get products by execution. */
        $products = $this->product->getProductPairsByProject($executionID);
        setcookie('preExecutionID', $executionID, $this->config->cookieLife, $this->config->webRoot, '', false, true);

        /* Save the recently five executions visited in the cookie. */
        $recentExecutions = isset($this->config->execution->recentExecutions) ? explode(',', $this->config->execution->recentExecutions) : array();
        array_unshift($recentExecutions, $executionID);
        $recentExecutions = array_unique($recentExecutions);
        $recentExecutions = array_slice($recentExecutions, 0, 5);
        $recentExecutions = join(',', $recentExecutions);
        if($this->session->multiple)
        {
            if(!isset($this->config->execution->recentExecutions) or $this->config->execution->recentExecutions != $recentExecutions) $this->setting->updateItem($this->app->user->account . 'common.execution.recentExecutions', $recentExecutions);
            if(!isset($this->config->execution->lastExecution)    or $this->config->execution->lastExecution != $executionID)         $this->setting->updateItem($this->app->user->account . 'common.execution.lastExecution', $executionID);
        }

        if($this->cookie->preExecutionID != $executionID)
        {
            $_COOKIE['moduleBrowseParam'] = $_COOKIE['productBrowseParam'] = 0;
            setcookie('moduleBrowseParam',  0, 0, $this->config->webRoot, '', false, false);
            setcookie('productBrowseParam', 0, 0, $this->config->webRoot, '', false, true);
        }
        if($browseType == 'bymodule')
        {
            setcookie('moduleBrowseParam',  (int)$param, 0, $this->config->webRoot, '', false, false);
            setcookie('productBrowseParam', 0, 0, $this->config->webRoot, '', false, true);
        }
        elseif($browseType == 'byproduct')
        {
            setcookie('moduleBrowseParam',  0, 0, $this->config->webRoot, '', false, false);
            setcookie('productBrowseParam', (int)$param, 0, $this->config->webRoot, '', false, true);
        }
        else
        {
            $this->session->set('taskBrowseType', $browseType);
        }

        if($browseType == 'bymodule' and $this->session->taskBrowseType == 'bysearch') $this->session->set('taskBrowseType', 'unclosed');

        /* Set queryID, moduleID and productID. */
        $queryID   = ($browseType == 'bysearch')  ? (int)$param : 0;
        $moduleID  = ($browseType == 'bymodule')  ? (int)$param : (($browseType == 'bysearch' or $browseType == 'byproduct') ? 0 : $this->cookie->moduleBrowseParam);
        $productID = ($browseType == 'byproduct') ? (int)$param : (($browseType == 'bysearch' or $browseType == 'bymodule')  ? 0 : $this->cookie->productBrowseParam);

        /* Save to session. */
        $uri = $this->app->getURI(true);
        $this->app->session->set('taskList', $uri . "#app={$this->app->tab}", 'execution');

        /* Process the order by field. */
        if(!$orderBy) $orderBy = $this->cookie->executionTaskOrder ? $this->cookie->executionTaskOrder : 'status,id_asc';
        setcookie('executionTaskOrder', $orderBy, 0, $this->config->webRoot, '', false, true);

        /* Append id for secend sort. */
        $sort = common::appendOrder($orderBy);

        /* Header and position. */
        $this->view->title = $execution->name . $this->lang->colon . $this->lang->execution->task;

        /* Load pager and get tasks. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml' || $this->app->getViewType() == 'xhtml') $recPerPage = 10;
        $pager = new pager($recTotal, $recPerPage, $pageID);
        $showParentTask = false;
        if($this->cookie->showParentTask) $showParentTask = true;
        /* Get tasks. */
        $tasks    = $this->execution->getTasks($productID, $executionID, $this->executions, $browseType, $queryID, $moduleID, $sort, $pager, $showParentTask);
        if(empty($tasks) and $pageID > 1)
        {
            $pager = pager::init(0, $recPerPage, 1);
            $tasks = $this->execution->getTasks($productID, $executionID, $this->executions, $browseType, $queryID, $moduleID, $sort, $pager, $showParentTask);
        }

        /* Get product. */
        $product = $this->product->getById($productID);

        /* Display of branch label. */
        $showBranch = $this->loadModel('branch')->showBranch($productID, $moduleID, $executionID);

        /* team member pairs. */
        $memberPairs = array();
        foreach($this->view->teamMembers as $key => $member) $memberPairs[$key] = $member->realname;
        $memberPairs = $this->user->processAccountSort($memberPairs);

        $showAllModule = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
        $extra         = (isset($this->config->execution->task->allModule) && $this->config->execution->task->allModule == 1) ? 'allModule' : '';
        $showModule    = !empty($this->config->datatable->executionTask->showModule) ? $this->config->datatable->executionTask->showModule : '';
        $this->view->modulePairs = $showModule ? $this->tree->getModulePairs($executionID, 'task', $showModule) : array();

        /* Build the search form. */
        $modules   = $this->tree->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');
        $actionURL = $this->createLink('execution', 'task', "executionID=$executionID&status=bySearch&param=myQueryID");
        $this->config->execution->search['onMenuBar'] = 'yes';
        if(!$execution->multiple) unset($this->config->execution->search['fields']['execution']);
        $this->execution->buildTaskSearchForm($executionID, $this->executions, $queryID, $actionURL, $modules);
        if($this->config->edition == 'ipd') $tasks = $this->loadModel('story')->getAffectObject($tasks, 'task');

        /* Assign. */
        $this->view->tasks         = $tasks;
        $this->view->hasTasks      = !empty($tasks) || !empty($this->task->getExecutionTasks($executionID));
        $this->view->summary       = $this->execution->summary($tasks);
        $this->view->tabID         = 'task';
        $this->view->pager         = $pager;
        $this->view->recTotal      = $pager->recTotal;
        $this->view->recPerPage    = $pager->recPerPage;
        $this->view->orderBy       = $orderBy;
        $this->view->browseType    = $browseType;
        $this->view->status        = $status;
        $this->view->users         = $this->user->getPairs('noletter|all');
        $this->view->param         = $param;
        $this->view->executionID   = $executionID;
        $this->view->execution     = $execution;
        $this->view->productID     = $productID;
        $this->view->product       = $product;
        $this->view->modules       = $modules;
        $this->view->moduleID      = $moduleID;
        $this->view->moduleTree    = $this->tree->getTaskTreeMenu($executionID, $productID, $startModuleID = 0, array('treeModel', 'createTaskLink'), $extra);
        $this->view->memberPairs   = $memberPairs;
        $this->view->branchGroups  = $this->loadModel('branch')->getByProducts(array_keys($products));
        $this->view->setModule     = true;
        $this->view->showAllModule = !$execution->multiple && !$execution->hasProduct ? false : true;
        $this->view->canBeChanged  = common::canModify('execution', $execution); // Determines whether an object is editable.
        $this->view->showBranch    = $showBranch;
        $this->view->projectName   = $this->loadModel('project')->getById($execution->project)->name . ' / ' . $execution->name;

        $this->display();
    }
}