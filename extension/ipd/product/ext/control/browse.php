<?php
helper::importControl('product');
class myProduct extends product
{
    /**
     * Browse a product.
     *
     * @param  int         $productID
     * @param  int|string  $branch
     * @param  string      $browseType
     * @param  int         $param
     * @param  string      $storyType
     * @param  string      $orderBy
     * @param  int         $recTotal
     * @param  int         $recPerPage
     * @param  int         $pageID
     * @param  int         $projectID
     * @access public
     * @return void
     */
    public function browse($productID = 0, $branch = '', $browseType = '', $param = 0, $storyType = 'story', $orderBy = '', $recTotal = 0, $recPerPage = 20, $pageID = 1, $projectID = 0)
    {
        $this->app->loadLang('projectstory');
        $this->view->approvers = $this->loadModel('assetlib')->getApproveUsers();
        $this->view->libs      = $this->assetlib->getPairs('story');
        parent::browse($productID, $branch, $browseType, $param, $storyType, $orderBy, $recTotal, $recPerPage, $pageID, $projectID);
    }
}
