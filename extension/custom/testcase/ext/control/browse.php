<?php
helper::importControl('testcase');
class mytestcase extends testcase
{
    public function browse($productID = 0, $branch = '', $browseType = 'all', $param = 0, $caseType = '', $orderBy = 'sort_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1, $projectID = 0)
    {
        if($this->app->tab == 'project') $this->view->project = $this->loadModel('project')->getById($projectID);
        $this->loadModel('datatable');
        $this->app->loadLang('zanode');

        /* Set browse type. */
        $browseType = strtolower($browseType);

        /* Set browseType, productID, moduleID and queryID. */
        $productID = $this->app->tab != 'project' ? $this->product->saveState($productID, $this->products) : $productID;
        $branch    = ($this->cookie->preBranch !== '' and $branch === '') ? $this->cookie->preBranch : $branch;
        setcookie('preProductID', $productID, $this->config->cookieLife, $this->config->webRoot, '', $this->config->cookieSecure, true);
        setcookie('preBranch', $branch, $this->config->cookieLife, $this->config->webRoot, '', $this->config->cookieSecure, true);

        if($this->cookie->preProductID != $productID or $this->cookie->preBranch != $branch)
        {
            $_COOKIE['caseModule'] = 0;
            setcookie('caseModule', 0, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
        }
        if($browseType == 'bymodule') setcookie('caseModule', (int)$param, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);
        if($browseType == 'bysuite')  setcookie('caseSuite', (int)$param, 0, $this->config->webRoot, '', $this->config->cookieSecure, true);
        if($browseType != 'bymodule') $this->session->set('caseBrowseType', $browseType);

        $moduleID = ($browseType == 'bymodule') ? (int)$param : ($browseType == 'bysearch' ? 0 : ($this->cookie->caseModule ? $this->cookie->caseModule : 0));
        $suiteID  = ($browseType == 'bysuite') ? (int)$param : ($browseType == 'bymodule' ? ($this->cookie->caseSuite ? $this->cookie->caseSuite : 0) : 0);
        $queryID  = ($browseType == 'bysearch') ? (int)$param : 0;

        /* Set menu, save session. */
        if($this->app->tab == 'project')
        {
            $linkedProducts = $this->product->getProducts($projectID, 'all', '', false);
            $this->products = count($linkedProducts) > 1 ? array('0' => $this->lang->product->all) + $linkedProducts : $linkedProducts;
            $productID      = count($linkedProducts) > 1 ? $productID : key($linkedProducts);
            $hasProduct     = $this->dao->findById($projectID)->from(TABLE_PROJECT)->fetch('hasProduct');
            if(!$hasProduct) unset($this->config->testcase->search['fields']['product']);

            $branch = intval($branch) > 0 ? $branch : 'all';
            $this->loadModel('project')->setMenu($projectID);
        }
        else
        {
            $this->qa->setMenu($this->products, $productID, $branch, $browseType);
        }

        $uri = $this->app->getURI(true);
        $this->session->set('caseList', $uri, $this->app->tab);
        $this->session->set('productID', $productID);
        $this->session->set('moduleID', $moduleID);
        $this->session->set('browseType', $browseType);
        $this->session->set('orderBy', $orderBy);
        $this->session->set('testcaseOrderBy', '`sort` asc', $this->app->tab);
        $this->session->set('testcaseOrderBy', '`sort` asc');

        /* Load lang. */
        $this->app->loadLang('testtask');

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);
        $sort  = common::appendOrder($orderBy);

        $cases  = array();
        $pager->pageID = $pageID;   // 场景和用例混排，$pageID 可能大于场景分页后的总页数。在 pager 构造函数中会被设为 1，这里要重新赋值。

        $scenes = $this->testcase->getSceneGroups($productID, $branch, $moduleID, $caseType, $sort, $pager);   // 获取包含子场景和用例的顶级场景树。

        if(!$this->cookie->onlyScene)
        {
            $recPerPage = $pager->recPerPage;
            $sceneTotal = $pager->recTotal;
            $sceneCount = count($scenes);

            /* 场景条数小于每页记录数，继续获取用例。 */
            if($sceneCount < $recPerPage)
            {
                /* 重置 $pager 属性，只获取需要的用例条数。*/
                $pager->recTotal   = 0;
                $pager->pageID     = 1; // 查询用例时的分页起始偏移量单独计算，每次查询的页码都设为 1 即可，后面会重新设置页码。
                $pager->recPerPage = $recPerPage - $sceneCount; // 可能存在场景没排满一页，需要用例补全的情况。这里只查询需要补全的记录数。

                if($sceneCount == 0) $pager->offset = $recPerPage * ($pageID - 1) - $sceneTotal;   // 场景数为 0 表示本页查询只显示用例，需要计算用例分页的起始偏移量。

                $cases = $this->testcase->getTestCases($productID, $branch, $browseType, $browseType == 'bysearch' ? $queryID : $suiteID, $moduleID, $caseType, $sort, $pager);
                $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'testcase', false);
            }

            /* 合并场景和用例的总记录数，并重新计算总页数和当前页码。*/
            $pager->recTotal  += $sceneTotal;
            $pager->recPerPage = $recPerPage;
            $pager->pageTotal  = ceil($pager->recTotal / $recPerPage);
            $pager->pageID     = $pageID;
        }

        $sceneCount = count($scenes);
        $caseCount  = 0;
        /* Process case for check story changed. */
        if($this->config->edition == 'ipd') $cases = $this->loadModel('story')->getAffectObject($cases, 'case');
        $cases = $this->loadModel('story')->checkNeedConfirm($cases);
        $cases = $this->testcase->appendData($cases);
        foreach($cases as $case)
        {
            $case->id      = 'case_' . $case->id;   // Add a prefix to avoid duplication with the scene ID.
            $case->parent  = 0;
            $case->grade   = 1;
            $case->path    = ',' . $case->id . ',';
            $case->isScene = false;
            if(!$case->scene) $caseCount++;
        }

        if($this->cookie->onlyScene)
        {
            $summary = sprintf($this->lang->testcase->summaryScene, $sceneCount);
        }
        else
        {
            $summary = sprintf($this->lang->testcase->summary, $sceneCount, $caseCount);
        }

        /* Build the search form. */
        $currentModule = $this->app->tab == 'project' ? 'project'  : 'testcase';
        $currentMethod = $this->app->tab == 'project' ? 'testcase' : 'browse';
        $projectParam  = $this->app->tab == 'project' ? "projectID={$this->session->project}&" : '';
        $actionURL = $this->createLink($currentModule, $currentMethod, $projectParam . "productID=$productID&branch=$branch&browseType=bySearch&queryID=myQueryID");
        $this->config->testcase->search['onMenuBar'] = 'yes';

        $searchProducts = $this->product->getPairs('', 0, '', 'all');
        $this->testcase->buildSearchForm($productID, $searchProducts, $queryID, $actionURL, $projectID, 0, $branch);

        $showModule = !empty($this->config->datatable->testcaseBrowse->showModule) ? $this->config->datatable->testcaseBrowse->showModule : '';

        /* Get module tree.*/
        if($projectID and empty($productID))
        {
            $moduleTree = $this->tree->getCaseTreeMenu($projectID, $productID, 0, array('treeModel', 'createCaseLink'));
        }
        else
        {
            $moduleTree = $this->tree->getTreeMenu($productID, 'case', 0, array('treeModel', 'createCaseLink'), array('projectID' => $projectID, 'productID' => $productID), $branch);
        }

        $product = $this->product->getById($productID);

        $showBranch      = false;
        $branchOption    = array();
        $branchTagOption = array();
        if($product and $product->type != 'normal')
        {
            /* Display of branch label. */
            $showBranch = $this->loadModel('branch')->showBranch($productID);

            /* Display status of branch. */
            $branches = $this->loadModel('branch')->getList($productID, $projectID, 'all');
            foreach($branches as $branchInfo)
            {
                $branchOption[$branchInfo->id]    = $branchInfo->name;
                $branchTagOption[$branchInfo->id] = $branchInfo->name . ($branchInfo->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : '');
            }
        }

        $sceneCases = array_merge($scenes, $cases);
        if($browseType == 'bysearch')
        {
            $sceneCases = $this->testcase->processSearchedData($scenes, $cases);
        }

        foreach($sceneCases as $case)
        {
            $case->canAction = true;
            if($this->app->tab == 'project')
            {
                $project = $this->loadModel('project')->getByID($this->session->project);
                if($project->status == 'closed' && isset($this->config->CRProject) && empty($this->config->CRProject)) $case->canAction = false;
            }
        }

        /* Assign. */
        $tree = $moduleID ? $this->tree->getByID($moduleID) : '';
        $this->view->title           = $this->products[$productID] . $this->lang->colon . $this->lang->testcase->common;
        $this->view->position[]      = html::a($this->createLink('testcase', 'browse', "productID=$productID&branch=$branch"), $this->products[$productID]);
        $this->view->position[]      = $this->lang->testcase->common;
        $this->view->projectID       = $projectID;
        $this->view->productID       = $productID;
        $this->view->product         = $product;
        $this->view->productName     = $this->products[$productID];
        $this->view->modules         = $this->tree->getOptionMenu($productID, $viewType = 'case', $startModuleID = 0, $branch == 'all' ? '0' : $branch);
        $this->view->scenes          = $this->testcase->getSceneMenu($productID, $moduleID, $viewType = 'case', $startSceneID = 0,  0);
        $this->view->moduleTree      = $moduleTree;
        $this->view->moduleName      = $moduleID ? $tree->name : $this->lang->tree->all;
        $this->view->moduleID        = $moduleID;
        $this->view->projectType     = !empty($projectID) ? $this->dao->select('model')->from(TABLE_PROJECT)->where('id')->eq($projectID)->fetch('model') : '';
        $this->view->summary         = $summary;
        $this->view->pager           = $pager;
        $this->view->users           = $this->user->getPairs('noletter');
        $this->view->orderBy         = $orderBy;
        $this->view->browseType      = $browseType;
        $this->view->param           = $param;
        $this->view->caseType        = $caseType;
        $this->view->cases           = $sceneCases;
        $this->view->branch          = (!empty($product) and $product->type != 'normal') ? $branch : 0;
        $this->view->branchOption    = $branchOption;
        $this->view->branchTagOption = $branchTagOption;
        $this->view->suiteList       = $this->loadModel('testsuite')->getSuites($productID);
        $this->view->suiteID         = $suiteID;
        $this->view->setModule       = true;
        $this->view->modulePairs     = $showModule ? $this->tree->getModulePairs($productID, 'case', $showModule) : array();
        $this->view->showBranch      = $showBranch;
        $this->view->libraries       = $this->loadModel('caselib')->getLibraries();
        $this->view->automation      = $this->loadModel('zanode')->getAutomationByProduct($productID);
        $this->display();
    }
}