<?php
$config->effort->createEffortType = 'task, bug';

$config->effort->datatable->defaultField = array('id', 'date', 'work', 'account', 'consumed', 'left', 'objectType', 'taskDesc', 'product', 'project', 'projectStatus', 'execution');

$config->effort->datatable->fieldList['work']['width']        = '240';
$config->effort->datatable->fieldList['account']['width']     = '110';
$config->effort->datatable->fieldList['consumed']['width']    = '65';
$config->effort->datatable->fieldList['left']['width']        = '65';

$config->effort->datatable->fieldList['projectStatus']['title']    = 'projectStatus';
$config->effort->datatable->fieldList['projectStatus']['fixed']    = 'no';
$config->effort->datatable->fieldList['projectStatus']['width']    = '90';
$config->effort->datatable->fieldList['projectStatus']['required'] = 'no';
$config->effort->datatable->fieldList['projectStatus']['sort']     = 'yes';

$config->effort->datatable->fieldList['taskDesc']['title']    = 'taskDesc';
$config->effort->datatable->fieldList['taskDesc']['fixed']    = 'no';
$config->effort->datatable->fieldList['taskDesc']['width']    = '205';
$config->effort->datatable->fieldList['taskDesc']['required'] = 'no';
$config->effort->datatable->fieldList['taskDesc']['sort']     = 'no';

$config->effort->allowCreateEffortLinkObject = [];
$config->effort->allowCreateEffortLinkObject[] = "stak";
$config->effort->allowCreateEffortLinkObject[] = "bug";

$config->effort->list->exportFields  .= ',projectStatus,taskDesc';
$config->effort->list->defaultFields .= ',account,projectStatus,taskDesc';

$config->effort->create->requiredFields = 'work,execution';
$config->effort->edit->requiredFields   = 'work,execution';