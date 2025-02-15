<?php
if(helper::hasFeature('devops') or (defined('IN_UPGRADE') and IN_UPGRADE))
{
    $lang->resource->repo->review        = 'reviewAction';
    $lang->resource->repo->addBug        = 'addBug';
    $lang->resource->repo->editBug       = 'editBug';
    $lang->resource->repo->deleteBug     = 'deleteBug';
    $lang->resource->repo->addComment    = 'addComment';
    $lang->resource->repo->editComment   = 'editComment';
    $lang->resource->repo->deleteComment = 'deleteComment';
}
