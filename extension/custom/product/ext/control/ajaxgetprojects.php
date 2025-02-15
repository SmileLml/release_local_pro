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
     * @return void
     */
    public function ajaxGetProjects($productID, $branch = 0, $projectID = 0)
    {
        $projects  = array('' => '');
        $projects += $this->product->getProjectPairsByProduct($productID, $branch, '', 'unclosed');
        if($this->app->getViewType() == 'json') return print(json_encode($projects));

        return print(html::select('project', $projects, $projectID, "class='form-control' onchange='loadProductExecutions({$productID}, this.value)'"));
    }
}