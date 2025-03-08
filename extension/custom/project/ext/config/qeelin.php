<?php
$config->project->editor->bug = array('id' => 'comment', 'tools' => 'simpleTools');
$config->project->list->exportFields = 'id,code,name,hasProduct,linkedProducts,status,begin,end,realBeganAB,realEndAB,closedDate,budget,PM,end,desc';

$config->project->datatable->defaultField = array('id', 'name', 'status', 'PM', 'budget', 'begin', 'end', 'progress', 'realBegan', 'realEnd', 'closedDate', 'actions');

$config->project->datatable->fieldList['realBegan']['title']    = 'realBeganAB';
$config->project->datatable->fieldList['realBegan']['fixed']    = 'no';
$config->project->datatable->fieldList['realBegan']['width']    = '115';
$config->project->datatable->fieldList['realBegan']['maxWidth'] = '120';
$config->project->datatable->fieldList['realBegan']['required'] = 'no';
$config->project->datatable->fieldList['realBegan']['sort']     = 'yes';
$config->project->datatable->fieldList['realBegan']['pri']      = '7';

$config->project->datatable->fieldList['realEnd']['title']    = 'realEndAB';
$config->project->datatable->fieldList['realEnd']['fixed']    = 'no';
$config->project->datatable->fieldList['realEnd']['width']    = '115';
$config->project->datatable->fieldList['realEnd']['maxWidth'] = '120';
$config->project->datatable->fieldList['realEnd']['required'] = 'no';
$config->project->datatable->fieldList['realEnd']['sort']     = 'yes';
$config->project->datatable->fieldList['realEnd']['pri']      = '7';

$config->project->datatable->fieldList['closedDate']['title']    = 'closedDate';
$config->project->datatable->fieldList['closedDate']['fixed']    = 'no';
$config->project->datatable->fieldList['closedDate']['width']    = '150';
$config->project->datatable->fieldList['closedDate']['maxWidth'] = '150';
$config->project->datatable->fieldList['closedDate']['required'] = 'no';
$config->project->datatable->fieldList['closedDate']['sort']     = 'yes';
$config->project->datatable->fieldList['closedDate']['pri']      = '7';