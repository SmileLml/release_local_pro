<?php

class myproject extends project
{
    /**
     * Project test task list.
     *
     * @param  int    $projectID
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function testtask($projectID = 0, $browseType = '', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->loadModel('testtask');
        $this->app->loadLang('testreport');

        /* Save session. */
        $this->session->set('testtaskList', $this->app->getURI(true), 'qa');
        $this->session->set('buildList', $this->app->getURI(true), 'execution');

        $this->project->setMenu($projectID);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager    = pager::init($recTotal, $recPerPage, $pageID);
        $products = $this->loadModel('product')->getProducts($projectID);

        /* Set browse type. */
        $browseType = strtolower($browseType);
        $queryID    = ($browseType == 'bysearch') ? (int)$param : 0;
        
        /* Build the search form. */
        $actionURL = $this->createLink('project', 'testtask', "projectID=$projectID&browseType=bysearch&queryID=myQueryID");

        $this->project->buildTesttaskSearchForm($products, $queryID, $actionURL);

        $productTasks = array();

        $project = $this->project->getByID($projectID);
        $tasks   = $this->testtask->getProjectTasks($projectID, $browseType, $queryID ,$orderBy, $pager);

        foreach($tasks as $key => $task) $productTasks[$task->product][] = $task;

        $this->view->title        = $project->name . $this->lang->colon . $this->lang->project->common;
        $this->view->position[]   = html::a($this->createLink('project', 'testtask', "projectID=$projectID"), $project->name);
        $this->view->position[]   = $this->lang->testtask->common;
        $this->view->project      = $project;
        $this->view->projectID    = $projectID;
        $this->view->projectName  = $project->name;
        $this->view->pager        = $pager;
        $this->view->browseType   = $browseType;
        $this->view->param        = $param;
        $this->view->orderBy      = $orderBy;
        $this->view->tasks        = $productTasks;
        $this->view->users        = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->products     = $this->loadModel('product')->getPairs('', 0);
        $this->view->canBeChanged = common::canModify('project', $project); // Determines whether an object is editable.

        $this->display();
    }
}