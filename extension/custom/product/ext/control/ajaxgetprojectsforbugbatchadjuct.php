<?php

class myproduct extends product
{
    /**
     * AJAX: get projects of a product in html select.
     *
     * @param  int    $productID
     * @param  int    $branch
     * @param  int    $projectID
     * @access public
     */
    public function ajaxGetProjectsForBugBatchAdjuct($productID, $bugID, $branchID = 0, $projectID = 0)
    {
        $projects = $this->product->getProjectPairsByProduct($productID, $branchID, '', 'unclosed');
        if(!isset($projects[$projectID])) $projectID = key($projects);
        return print(html::select("project[$bugID]", $projects, $projectID, "onchange='loadBuilds($productID, $branchID, $bugID, this.value)' class='form-control chosen' id='project-$bugID'"));
    }
}