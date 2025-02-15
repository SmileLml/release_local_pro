<?php

class myprojectrelease extends projectrelease
{
    /**
     * Browse releases.
     *
     * @param  int    $projectID
     * @param  int    $executionID
     * @param  string $type
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($projectID = 0, $executionID = 0, $type = 'all', $orderBy = 't1.date_desc', $recTotal = 0, $recPerPage = 15, $pageID = 1)
    {
        $uri = $this->app->getURI(true);
        $this->session->set('releaseList', $uri, 'project');
        $this->session->set('buildList', $uri);

        $project   = $this->project->getById($projectID);
        $execution = $this->loadModel('execution')->getById($executionID);

        if($projectID) $this->project->setMenu($projectID);
        if($executionID) $this->loadModel('execution')->setMenu($executionID, $this->app->rawModule, $this->app->rawMethod);

        $objectName = isset($project->name) ? $project->name : $execution->name;

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $releases = $this->projectrelease->getList($projectID, $type, $orderBy, $pager);

        $showBranch = false;
        foreach($releases as $release)
        {
            if($release->productType != 'normal')
            {
                $showBranch = true;
                break;
            }
        }

        $this->view->title       = $objectName . $this->lang->colon . $this->lang->release->browse;
        $this->view->execution   = $execution;
        $this->view->project     = $project;
        $this->view->products    = $this->loadModel('product')->getProducts($projectID);
        $this->view->releases    = $releases;
        $this->view->projectID   = $projectID;
        $this->view->executionID = $executionID;
        $this->view->type        = $type;
        $this->view->from        = $this->app->tab;
        $this->view->pager       = $pager;
        $this->view->showBranch  = $showBranch;
        $this->view->canBeChanged = common::canModify('project', $project ); // Determines whether an object is editable.

        $this->display();
    }
}