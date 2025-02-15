<?php

class myproject extends project
{
    /**
     * Execution list.
     *
     * @param  string $status
     * @param  int    $projectID
     * @param  string $orderBy
     * @param  int    $productID
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function execution($status = 'all', $projectID = 0, $orderBy = 'order_asc', $productID = 0, $recTotal = 0, $recPerPage = 100, $pageID = 1)
    {
        $this->loadModel('execution');
        $this->loadModel('task');
        $this->loadModel('programplan');
        $this->session->set('executionList', $this->app->getURI(true), 'project');

        if($this->cookie->showTask) $this->session->set('taskList', $this->app->getURI(true), 'project');

        $projects  = $this->project->getPairsByProgram();
        $projectID = $this->project->saveState($projectID, $projects);
        $project   = $this->project->getByID($projectID);
        $this->project->setMenu($projectID);

        $canBeChange = common::canModify('project', $project);

        !$canBeChange && $this->cookie->set('editExecution', 0);
        !$canBeChange && $this->cookie->set('showTask', 0);

        if(!$projectID) return print(js::locate($this->createLink('project', 'browse')));
        if(!$project->multiple)
        {
            $executionID = $this->execution->getNoMultipleID($projectID);
            if(defined('RUN_MODE') && RUN_MODE == 'api')
            {
                $this->view->executionStats = array($this->execution->getByID($executionID));
                return $this->display();
            }
            return print(js::locate($this->createLink('execution', 'task', "executionID=$executionID")));
        }
        if(!empty($project->model) and $project->model == 'kanban' and !(defined('RUN_MODE') and RUN_MODE == 'api')) return print(js::locate($this->createLink('project', 'index', "projectID=$projectID")));

        /* Load pager and get tasks. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->loadModel('program')->refreshStats(); // Refresh stats fields of projects.

        $allExecution = $this->execution->getStatData($projectID, 'all');
        $this->view->allExecutionNum = empty($allExecution);

        $this->view->title      = $this->lang->execution->allExecutions;
        $this->view->position[] = $this->lang->execution->allExecutions;

        $executionStats  = $this->execution->getStatData($projectID, $status, $productID, 0, $this->cookie->showTask, '', $orderBy, $pager);
        $showToggleIcon  = false;

        foreach($executionStats as $execution)
        {
            if(!empty($execution->tasks) or !empty($execution->children)) $showToggleIcon = true;
        }

        $changeStatusHtml  = "<div class='btn-group dropup'>";
        $changeStatusHtml .= "<button data-toggle='dropdown' type='button' class='btn'>{$this->lang->statusAB} <span class='caret'></span></button>";
        $changeStatusHtml .= "<div class='dropdown-menu search-list'><div class='list-group'>";
        foreach($this->lang->execution->statusList as $statusAB => $statusText)
        {
            $actionLink        = $this->createLink('execution', 'batchChangeStatus', "status=$statusAB&projectID=$projectID");
            $changeStatusHtml .= html::a('#', $statusText, '', "onclick=\"setFormAction('$actionLink', 'hiddenwin', '#executionForm')\" onmouseover=\"setBadgeStyle(this, true);\" onmouseout=\"setBadgeStyle(this, false)\"");
        }
        $changeStatusHtml .= "</div></div></div>";

        if($project->model == 'ipd' and $this->config->edition == 'ipd')
        {
            $this->view->reviewPoints = $this->loadModel('review')->getReviewPointByProject($projectID);
        }

        $this->view->canBeChange      = $canBeChange;
        $this->view->executionStats   = $executionStats;
        $this->view->showToggleIcon   = $showToggleIcon;
        $this->view->productList      = $this->loadModel('product')->getProductPairsByProject($projectID, 'all', '', false);
        $this->view->productID        = $productID;
        $this->view->product          = $this->product->getByID($productID);
        $this->view->projectID        = $projectID;
        $this->view->project          = $project;
        $this->view->projects         = $projects;
        $this->view->pager            = $pager;
        $this->view->orderBy          = $orderBy;
        $this->view->users            = $this->loadModel('user')->getPairs('noletter');
        $this->view->status           = $status;
        $this->view->isStage          = (isset($project->model) and in_array($project->model, array('waterfall', 'waterfallplus', 'ipd'))) ? true : false;
        $this->view->changeStatusHtml = common::hasPriv('execution', 'batchChangeStatus') && $canBeChange ? $changeStatusHtml : '';

        $this->display();
    }
}