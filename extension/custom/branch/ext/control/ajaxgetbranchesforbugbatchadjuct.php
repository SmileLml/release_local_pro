<?php

helper::importControl('branch');

class mybranch extends branch
{
    public function ajaxGetBranchesForBugBatchAdjuct($productID, $bugID, $branchID = 0)
    {
        $product = $this->loadModel('product')->getById($productID);
        if(empty($product) or $product->type == 'normal') return;
        $branches = $this->loadModel('branch')->getList($productID, 0, 'all', 'order', null);
        $branchTagOption = array();
        foreach($branches as $branchInfo)
        {
            $branchTagOption[$branchInfo->id] = $branchInfo->name . ($branchInfo->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : '');
        }
        if(is_numeric($branchID) and !isset($branchTagOption[$branchID]))
        {
            $branchID = 0;
        }

        return print(html::select("branch[bugID]", $branchTagOption, $branchID, "onchange='loadProductProjects($productID, this.value, $bugID);' class='form-control chosen control-branch' id='branch-{$bugID}'"));
    }
}