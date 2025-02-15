<?php
public function getStories($productID, $branch, $browseType, $queryID, $moduleID, $type = 'story', $sort = 'id_desc', $pager = null)
{
    return $this->loadExtension('feedback')->getStories($productID, $branch, $browseType, $queryID, $moduleID, $type, $sort, $pager);
}
