<?php

class myproject extends project
{
    /**
     * Project list.
     *
     * @param  int    $programID
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($programID = 0, $browseType = 'doing', $param = 0, $orderBy = 'order_asc', $recTotal = 0, $recPerPage = 15, $pageID = 1)
    {
        $this->loadModel('datatable');
        $this->loadModel('execution');
        $this->session->set('projectList', $this->app->getURI(true), 'project');

        $projectType = $this->cookie->projectType ? $this->cookie->projectType : 'bylist';
        $browseType  = strtolower($browseType);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $queryID = ($browseType == 'bysearch') ? (int)$param : 0;
        $actionURL = $this->createLink('project', 'browse', "&programID=$programID&browseType=bySearch&queryID=myQueryID");
        $this->project->buildSearchForm($queryID, $actionURL);

        $this->loadModel('program')->refreshStats(); // Refresh stats fields of projects.

        $programTitle = $this->loadModel('setting')->getItem('owner=' . $this->app->user->account . '&module=project&key=programTitle');

        $this->view->title          = $this->lang->project->browse;
        $this->view->projectStats   = $this->program->getProjectStats($programID, $browseType, $queryID, $orderBy, $pager, $programTitle);
        $this->view->pager          = $pager;
        $this->view->programID      = $programID;
        $this->view->program        = $this->program->getByID($programID);
        $this->view->programTree    = $this->project->getTreeMenu(0, array('projectmodel', 'createManageLink'), 0, 'list');
        $this->view->programs       = array('0' => '') + $this->program->getParentPairs();
        $this->view->users          = $this->loadModel('user')->getPairs('noletter|pofirst|nodeleted');
        $this->view->userIdPairs    = $this->loadModel('user')->getPairs('nodeleted|showid');
        $this->view->usersAvatar    = $this->user->getAvatarPairs();
        $this->view->browseType     = $browseType;
        $this->view->projectType    = $projectType;
        $this->view->param          = $param;
        $this->view->orderBy        = $orderBy;
        $this->view->recTotal       = $recTotal;
        $this->view->recPerPage     = $recPerPage;
        $this->view->pageID         = $pageID;

        empty($this->config->CRProject) && $this->session->currentProjectsIsColse && $this->cookie->set('showProjectBatchEdit', 0);
        $this->view->showBatchEdit  = $this->cookie->showProjectBatchEdit;

        $this->display();
    }
}