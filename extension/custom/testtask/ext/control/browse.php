<?php

class mytesttask extends testtask
{
    /**
     * Browse test tasks.
     *
     * @param  int    $productID
     * @param  string $type
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($productID = 0, $branch = '', $type = 'local,totalStatus', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1, $beginTime = 0, $endTime = 0)
    {
        /* Save session. */
        $uri = $this->app->getURI(true);
        $this->session->set('testtaskList', $uri, 'qa');
        $this->session->set('reportList',   $uri, 'qa');
        $this->session->set('buildList',    $uri, 'execution');

        $scopeAndStatus = explode(',', $type);
        $this->session->set('testTaskVersionScope', $scopeAndStatus[0]);
        $this->session->set('testTaskVersionStatus', $scopeAndStatus[1]);

        $beginTime = $beginTime ? date('Y-m-d', strtotime($beginTime)) : '';
        $endTime   = $endTime   ? date('Y-m-d', strtotime($endTime))   : '';

        /* Set menu. */
        $productID = $this->product->saveState($productID, $this->products);
        if($branch === '') $branch = (int)$this->cookie->preBranch;
        $this->loadModel('qa')->setMenu($this->products, $productID, $branch, $type);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        /* Set browse type. */
        $browseType = strtolower($scopeAndStatus[1]);
        if($browseType == 'bysearch') $this->session->set('testTaskVersionStatus', $browseType);
        $queryID = ($browseType == 'bysearch') ? (int)$param : 0;
        $actionURL = $this->createLink('testtask', 'browse', "&productID=$productID&branch=$branch&type=$scopeAndStatus[0],bySearch&queryID=myQueryID");
        $this->testtask->buildSearchForm($productID, $this->products, $queryID, $actionURL, $branch);

        /* Get tasks. */
        $tasks = $this->testtask->getProductTasks($productID, $branch, $sort, $pager, $scopeAndStatus, $queryID, $beginTime, $endTime);

        if(isset($this->config->CRProject) && empty($this->config->CRProject))
        {
            $projectIdList = [];
            foreach($tasks as $task) $projectIdList[] = $task->project;
            $projectIdList = array_unique($projectIdList);
            $projectList   = $this->loadModel('project')->getByIdList($projectIdList);
            foreach($tasks as $taskId => $taskInfo)
            {
                $taskInfo->canAction = true;
                if(isset($projectList[$taskInfo->project]) && $projectList[$taskInfo->project]->status == 'closed') $taskInfo->canAction = false;
            }
        }

        $this->view->title       = $this->products[$productID] . $this->lang->colon . $this->lang->testtask->common;
        $this->view->position[]  = html::a($this->createLink('testtask', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[]  = $this->lang->testtask->common;
        $this->view->username    = $this->app->user->account;
        $this->view->productID   = $productID;
        $this->view->productName = $this->products[$productID];
        $this->view->orderBy     = $orderBy;
        $this->view->tasks       = $tasks;
        $this->view->users       = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->pager       = $pager;
        $this->view->param       = $param;
        $this->view->branch      = $branch;
        $this->view->beginTime   = $beginTime;
        $this->view->endTime     = $endTime;
        $this->view->product     = $this->product->getByID($productID);

        $this->display();
    }
}