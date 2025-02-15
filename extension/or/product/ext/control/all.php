<?php
class myProduct extends product
{
    public function all($browseType = '', $orderBy = 'program_asc', $param = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        if(empty($browseType))
        {
            $browseType   = 'mine';
            $productStats = $this->loadModel('product')->getStats($orderBy, null, 'mine', '', 'story', '', 0);
            if(empty($productStats)) $browseType = 'all';
        }

        $this->view->roadmapGroup     = $this->loadModel('roadmap')->getRoadmapCount();
        $this->view->lineRoadmapGroup = $this->roadmap->getLineRoadmapCount();
        $this->view->programProducts  = $this->product->getProductsGroupByProgram(array(), true);
        $this->view->productLinePairs = $this->dao->select('id,line')->from(TABLE_PRODUCT)->where('line')->ne(0)->fetchPairs();

        parent::all($browseType, $orderBy, $param, $recTotal, $recPerPage, $pageID);
    }
}
