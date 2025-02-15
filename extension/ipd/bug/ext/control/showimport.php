<?php
/**
 * The model file of excel module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     excel
 * @link        https://www.zentao.net
 */
helper::importControl('bug');
class mybug extends bug
{
    /**
     * Show import.
     *
     * @param  int    $productID
     * @param  int    $branch
     * @param  int    $pagerID
     * @param  string $insert
     * @access public
     * @return void
     */
    public function showImport($productID, $branch = 0, $pagerID = 1, $insert = '')
    {
        $this->loadModel('transfer');

        $this->session->set('bugTransferParams', array('productID' => $productID, 'branch' => $branch));
        $this->qa->setMenu($this->products, $productID, $branch);

        $product = $this->product->getById($productID);

        if($product->type == 'normal') $this->config->bug->templateFields = str_ireplace('branch,', '', $this->config->bug->templateFields);
        $locate = inlink('showImport', "productID=$productID&branch=$branch&pagerID=" . ($this->post->pagerID + 1) . "&insert=" . zget($_POST, 'insert', ''));

        if($_POST)
        {
            $this->bug->createFromImport($productID, $branch);

            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->isEndPage) $locate = inlink('browse', "productID=$productID&branch=$branch");

            return print(js::locate($locate, 'parent'));
        }

        $bugData = $this->transfer->readExcel('bug', $pagerID, $insert);

        $title = $this->lang->bug->common . $this->lang->colon . $this->lang->bug->showImport;

        $this->view->title     = $title;
        $this->view->datas     = $bugData;
        $this->view->productID = $productID;
        $this->view->branch    = $branch;
        $this->view->backLink  = inlink('browse', "productID=$productID&branch=$branch");

        $this->display();
    }
}
