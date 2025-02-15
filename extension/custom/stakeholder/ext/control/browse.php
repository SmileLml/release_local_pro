<?php

class mystakeholder extends stakeholder
{
    /**
     * Stakeholder list.
     *
     * @param  int    $projectID
     * @param  string $browseType
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($projectID, $browseType = 'all', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {

        $this->app->loadClass('pager', true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $this->loadModel('project')->setMenu($projectID);

        $project      = $this->project->getById($projectID);
        $stakeholders = $this->stakeholder->getStakeholders($projectID, $browseType, $orderBy, $pager);

        $this->view->title       = $this->lang->stakeholder->browse;
        $this->view->position[]  = $this->lang->stakeholder->browse;

        $this->view->pager        = $pager;
        $this->view->recTotal     = $recTotal;
        $this->view->recPerPage   = $recPerPage;
        $this->view->pageID       = $pageID;
        $this->view->projectID    = $projectID;
        $this->view->orderBy      = $orderBy;
        $this->view->browseType   = $browseType;
        $this->view->stakeholders = $stakeholders;
        $this->view->canBeChanged = common::canModify('project', $project);

        $this->display();
    }
}