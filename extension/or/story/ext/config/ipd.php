<?php
$config->story->datatable->defaultField = array('id', 'title', 'pri', 'roadmap', 'status', 'assignedTo', 'category', 'duration', 'BSA', 'actions');

$config->story->datatable->fieldList['duration']['title']    = 'duration';
$config->story->datatable->fieldList['duration']['fixed']    = 'no';
$config->story->datatable->fieldList['duration']['width']    = '90';
$config->story->datatable->fieldList['duration']['required'] = 'no';

$config->story->datatable->fieldList['BSA']['title']    = 'BSA';
$config->story->datatable->fieldList['BSA']['fixed']    = 'no';
$config->story->datatable->fieldList['BSA']['width']    = '90';
$config->story->datatable->fieldList['BSA']['required'] = 'no';

unset($config->story->datatable->fieldList['estimate']);
unset($config->story->datatable->fieldList['SRS']);

$config->story->list->customBatchCreateFields = 'source,duration,BSA,estimate,review,keywords';
$config->story->custom->batchCreateFields     = 'module,spec,verify,review,%s';

$config->story->list->customBatchEditFields = 'source,duration,BSA,keywords,mailto';
$config->story->custom->batchEditFields     = '';
