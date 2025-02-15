<?php
helper::importControl('testcase');
class mytestcase extends testcase
{
    /**
     * showImport
     *
     * @param  int    $productID
     * @param  int    $branch
     * @param  int    $pagerID
     * @param  int    $maxImport
     * @param  string $insert
     * @access public
     * @return void
     */
    public function showImport($productID, $branch = 0, $pagerID = 1, $maxImport = 0, $insert = '')
    {
        $this->loadModel('transfer');
        $product = $this->loadModel('product')->getByID($productID);
        if($product->type != 'normal') $this->config->testcase->templateFields = str_replace('module,', 'branch,module,', $this->config->testcase->templateFields);

        $this->session->set('testcaseTransferParams', array('productID' => $productID, 'branch' => $branch));

        $product = $this->product->getById($productID);

        if($product->type == 'normal') $this->config->testcase->templateFields = str_ireplace('branch,', '', $this->config->testcase->templateFields);
        if($this->app->tab == 'project')
        {
            $this->loadModel('project')->setMenu($this->session->project);
        }
        else
        {
            $this->testcase->setMenu($this->products, $productID, $branch);
        }

        if($_POST)
        {
            $this->testcase->createFromImport($productID, (int)$branch);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $locate = inlink('showImport', "productID=$productID&branch=$branch&pagerID=" . ($this->post->pagerID + 1) . "&maxImport=$maxImport&insert=" . zget($_POST, 'insert', ''));

            if($this->post->isEndPage)
            {
                if($this->app->tab == 'project')
                {
                    $locate = $this->createLink('project', 'testcase', "projectID={$this->session->project}&productID=$productID");
                }
                else
                {
                    $locate = inlink('browse', "productID=$productID");
                }
            }
            return print(js::locate($locate, 'parent'));
        }

        $datas = $this->transfer->readExcel('testcase', $pagerID);

        $stepData = $this->testcase->processDatas($datas);

        $branchModules = array();
        $branches      = $this->loadModel('branch')->getPairs($productID);
        foreach($branches as $branchID => $branchName)
        {
            $modules = $this->loadModel('tree')->getOptionMenu($productID, 'case', 0, $branchID);
            $modules = empty($modules) ? array('' => '') : $modules;
            $branchModules[$branchID] = html::select("module", $modules, 0, 'class=form-control');
        }

        $this->view->title         = $this->lang->testcase->common . $this->lang->colon . $this->lang->testcase->showImport;
        $this->view->productID     = $productID;
        $this->view->branch        = $branch;
        $this->view->branchModules = $branchModules;
        $this->view->stepData      = $stepData;
        $this->view->datas         = $datas;
        $this->view->backLink      = inlink('browse', "productID=$productID");

        $this->display();
    }
}
