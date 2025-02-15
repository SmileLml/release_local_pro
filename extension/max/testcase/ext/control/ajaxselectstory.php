<?php
class testcase extends control
{
    /**
     * Get select stories.
     *
     * @param int        $productID
     * @param int|string $type
     * @param int        $queryID
     * @access public
     * @return void
     */
    public function ajaxSelectStory($productID, $type = 'all', $queryID = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        if($type == 'bysearch')
        {
            $stories = $this->loadModel('story')->getBySearch($productID, 0, $queryID, 'id_desc', '', 'story', '', '', $pager);
        }
        elseif($this->app->tab == 'project')
        {
            $stories = $this->loadModel('story')->getExecutionStories($this->session->project, $productID, 0, 'id_desc', $type, $queryID, 'story', '', '', $pager);
        }
        else
        {
            $stories = $this->loadModel('story')->getProductStories($productID, $type, 0, 'all', 'story', 'id_desc', true, '', $pager);
        }

        $product   = $this->loadModel('product')->getById($productID);
        $actionURL = $this->createLink('testcase', 'ajaxSelectStory', "productID=$productID&type=bysearch&queryID=myQueryID");

        $this->config->product->search['actionURL'] = $actionURL;
        $this->config->product->search['queryID']   = $queryID;

        $newFields = array();
        foreach($this->config->product->search['fields'] as $field => $fieldName)
        {
            if($field == 'product')
            {
                $newFields['execution'] = $this->lang->product->project;
                $this->config->product->search['params']['execution'] = array('operator' => '=', 'control' => 'select');
                $this->config->product->search['params']['execution']['values'] = array('') + $this->product->getExecutionPairsByProduct($productID);
            }
            if($field == 'product' or $field == 'branch' or $field == 'lastEditedDate' or $field == 'lastEditedBy' or $field == 'version') continue;
            $newFields[$field] = $fieldName;
        }

        $this->config->product->search['fields'] = $newFields;
        $this->config->product->search['params']['plan']['values']    = $this->loadModel('productplan')->getPairs($productID);
        $this->config->product->search['params']['module']['values']  = $this->loadModel('tree')->getOptionMenu($productID, $viewType = 'story', $startModuleID = 0);

        $this->loadModel('search')->setSearchParams($this->config->product->search);

        $selectedStories = isset($_COOKIE['selectedStories'][$productID]) ? trim($_COOKIE['selectedStories'][$productID], ',') : '';
        if($selectedStories)
        {
            $noExists = ",{$selectedStories},";
            foreach($stories as $story)
            {
                if(strpos(",$selectedStories,", ",{$story->id},") !== false) $noExists = str_replace(",{$story->id},", ',', $noExists);
            }
            $noExists = trim($noExists, ',');
            if($noExists)
            {
                $noExistsStories = $this->story->getByList(trim($noExists, ','));
                foreach($noExistsStories as $story) $stories[] = $story;
            }
        }

        if($this->app->tab == 'project')
        {
            $this->loadModel('project');
            $products = array('0' => $this->lang->product->all) + $this->loadModel('product')->getProducts($this->session->project, 'all', '', false);
            $this->lang->modulePageNav = $this->product->select($products, $productID, 'testcase', 'ajaxselectstory', '', 0, 0 , '', false);
            $this->project->setMenu($this->session->project);
        }
        else
        {
            $products = $this->product->getPairs();
            $this->loadModel('qa')->setMenu($products, $productID, $type);
        }

        $this->view->title           = $this->lang->testcase->selectStory;
        $this->view->position[]      = $this->lang->testcase->exportTemplate;
        $this->view->position[]      = $this->lang->testcase->selectStory;
        $this->view->selectedStories = $selectedStories;

        $this->view->stories = $stories;
        $this->view->product = $product;
        $this->view->modules = $this->loadModel('tree')->getOptionMenu($product->id, 'story', 0, $type);
        $this->view->users   = $this->loadModel('user')->getPairs('noletter');
        $this->view->pager   = $pager;

        $this->display();
    }
}
