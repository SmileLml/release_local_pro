<?php
public function create($from = '', $extras = '')
{
    return $this->loadExtension('feedback')->create($from, $extras);
}

public function getById($bugID, $setImgSize = false)
{
    return $this->loadExtension('feedback')->getById($bugID, $setImgSize);
}

public function getBugs($productID, $executions, $branch, $browseType, $moduleID, $queryID, $sort, $pager, $projectID)
{
    return $this->loadExtension('feedback')->getBugs($productID, $executions, $branch, $browseType, $moduleID, $queryID, $sort, $pager, $projectID);
}
