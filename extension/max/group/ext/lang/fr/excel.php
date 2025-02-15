<?php
$lang->resource->testcase->import         = 'importCase';
$lang->resource->testcase->exportTemplate = 'exportTemplate';

$lang->resource->bug->import         = 'importCase';
$lang->resource->bug->exportTemplate = 'exportTemplate';

if(!isset($lang->resource->task)) $lang->resource->task = new stdclass();
$lang->resource->task->import         = 'import';
$lang->resource->task->exportTemplate = 'exportTemplate';

if(!isset($lang->resource->story)) $lang->resource->story = new stdclass();
$lang->resource->story->import         = 'importCase';
$lang->resource->story->exportTemplate = 'exportTemplate';

if($config->URAndSR)
{
    if(!isset($lang->resource->requirement)) $lang->resource->requirement = new stdclass();
    $lang->resource->requirement->import         = 'importCase';
    $lang->resource->requirement->exportTemplate = 'exportTemplate';
}

$lang->resource->user->import     = 'import';
