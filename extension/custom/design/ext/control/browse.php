<?php

class mydesign extends design
{
    /**
     * Browse designs.
     *
     * @param  int    $projectID
     * @param  int    $productID
     * @param  string $type all|bySearch|HLDS|DDS|DBDS|ADS
     * @param  string $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($projectID = 0, $productID = 0, $type = 'all', $param = '', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $productID = $this->commonAction($projectID, $productID);

        $project  = $this->loadModel('project')->getByID($projectID);
        $typeList = $project->model == 'waterfall' ? $this->lang->design->typeList : $this->lang->design->plusTypeList;

        /* Save session for design list and process product id. */
        $this->session->set('designList', $this->app->getURI(true), 'project');
        $this->session->set('reviewList', $this->app->getURI(true), 'project');

        /* Build the search form. */
        $products      = $this->product->getProductPairsByProject($projectID);
        $productIdList = $productID ? $productID : array_keys($products);
        $stories       = $this->loadModel('story')->getProductStoryPairs($productIdList, 'all', 0, 'active', 'id_desc', 0, 'full', 'story', false);
        $this->config->design->search['params']['story']['values'] = $stories;
        $this->config->design->search['params']['type']['values']  = $typeList;

        $queryID   = ($type == 'bySearch') ? (int)$param : 0;
        $actionURL = $this->createLink('design', 'browse', "projectID=$projectID&productID=$productID&type=bySearch&queryID=myQueryID");
        $this->design->buildSearchForm($queryID, $actionURL);

        /* Print top and right actions. */
        $this->lang->TRActions  = '<div class="btn-toolbar pull-right">';
        if(($this->config->edition == 'max' or $this->config->edition == 'ipd') and common::hasPriv('design', 'submit'))
        {
            $this->lang->TRActions .= '<div class="btn-group">';
            $this->lang->TRActions .= html::a($this->createLink('design', 'submit', "productID=$productID", '', true), "<i class='icon-plus'></i> {$this->lang->design->submit}", '', "class='btn btn-secondary iframe'");
            $this->lang->TRActions .= '</div>';
        }

        $canBeChange = common::canModify('project', $project);

        if($canBeChange)
        {
            if(common::hasPriv('design', 'create') and common::hasPriv('design', 'batchCreate'))
            {
                $this->lang->TRActions .= '<div class="btn-group dropdown">';
                $this->lang->TRActions .= html::a(inlink('create', "projectID=$projectID&productID=$productID&type=$type"), "<i class='icon-plus'></i> {$this->lang->design->create}", '', "class='btn btn-primary'");
                $this->lang->TRActions .= "<button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown'><span class='caret'></span>";
                $this->lang->TRActions .= '</button>';
                $this->lang->TRActions .= "<ul class='dropdown-menu pull-right' id='createActionMenu'>";

                if(common::hasPriv('design', 'create'))      $this->lang->TRActions .= '<li>' . html::a($this->createLink('design', 'create', "projectID=$projectID&productID=$productID&type=$type"), $this->lang->design->create, '', "class='btn btn-link'") . '</li>';
                if(common::hasPriv('design', 'batchCreate')) $this->lang->TRActions .= '<li>' . html::a($this->createLink('design', 'batchCreate', "projectID=$projectID&productID=$productID&type=$type"), $this->lang->design->batchCreate, '', "class='btn btn-link'") . '</li>';

                $this->lang->TRActions .= '</ul>';
                $this->lang->TRActions .= '</div>';
                $this->lang->TRActions .= '</div>';
            }
            else
            {
                if(common::hasPriv('design', 'create')) $this->lang->TRActions .= html::a(inlink('create', "projectID=$projectID&productID=$productID&type=$type"), "<i class='icon-plus'></i> {$this->lang->design->create}", '', "class='btn btn-primary'");
                if(common::hasPriv('design', 'batchCreate')) $this->lang->TRActions .= html::a(inlink('batchCreate', "projectID=$projectID&productID=$productID&type=$type"), "<i class='icon-plus'></i> {$this->lang->design->batchCreate}", '', "class='btn btn-primary'");
            }
        }

        /* Init pager and get designs. */
        $this->app->loadClass('pager', $static = true);
        $pager   = pager::init(0, $recPerPage, $pageID);
        $designs = $this->design->getList($projectID, $productID, $type, $queryID, $orderBy, $pager);

        $this->view->hiddenProduct = $project->hasProduct ? false : true;

        $this->view->title       = $this->lang->design->common . $this->lang->colon . $this->lang->design->browse;
        $this->view->position[]  = $this->lang->design->browse;
        $this->view->canBeChange = $canBeChange;
        $this->view->designs     = $designs;
        $this->view->type        = $type;
        $this->view->param       = $param;
        $this->view->orderBy     = $orderBy;
        $this->view->productID   = $productID;
        $this->view->projectID   = $projectID;
        $this->view->pager       = $pager;
        $this->view->users       = $this->loadModel('user')->getPairs('noletter');
        $this->view->typeList    = $typeList;

        $this->display();
    }
}