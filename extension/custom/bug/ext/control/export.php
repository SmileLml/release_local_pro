<?php

class mybug extends bug
{
   /**
     * Get data to export
     *
     * @param  string $productID
     * @param  string $orderBy
     * @param  string $browseType
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function export($productID, $orderBy, $browseType = '', $executionID = 0)
    {
        if($_POST)
        {
            if(!$productID && $executionID)
            {
                $object    = $this->dao->findById($executionID)->from(TABLE_EXECUTION)->fetch();
                $projectID = $object->type == 'project' ? $object->id : $object->parent;
                $products = $this->loadModel('product')->getProductIDByProject($projectID, false);
                $this->config->bug->datatable->fieldList['plan']['dataSource']['params'] = implode(',', $products);
            }
        }
        parent::export($productID, $orderBy, $browseType, $executionID);
    }
}