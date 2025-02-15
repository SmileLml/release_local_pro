<?php
$config->effort         = new stdclass();
$config->effort->create = new stdclass();
$config->effort->edit   = new stdclass();
$config->effort->times  = new stdclass();
$config->effort->list   = new stdclass();

$config->effort->create->requiredFields = 'work';
$config->effort->edit->requiredFields   = 'work';
$config->effort->times->delta           = 10;

$config->effort->editor = new stdclass();
$config->effort->editor->view = array('id' => 'comment,lastComment', 'tools' => 'simpleTools');

$config->effort->list->exportFields  = 'id,date,dept,account,work,consumed,left,objectType,product,execution,project';
$config->effort->list->defaultFields = 'id,date,work,consumed,left,objectType,product,project,execution';

$config->effort->datatable = new stdclass();
$config->effort->datatable->defaultField = array('id', 'date', 'work', 'account', 'consumed', 'left', 'objectType', 'product', 'project', 'execution');

$config->effort->datatable->fieldList['id']['title']    = 'idAB';
$config->effort->datatable->fieldList['id']['fixed']    = 'left';
$config->effort->datatable->fieldList['id']['width']    = '50';
$config->effort->datatable->fieldList['id']['required'] = 'yes';

$config->effort->datatable->fieldList['date']['title']    = 'date';
$config->effort->datatable->fieldList['date']['fixed']    = 'left';
$config->effort->datatable->fieldList['date']['width']    = '90';
$config->effort->datatable->fieldList['date']['required'] = 'yes';

$config->effort->datatable->fieldList['dept']['title']    = 'dept';
$config->effort->datatable->fieldList['dept']['fixed']    = 'no';
$config->effort->datatable->fieldList['dept']['width']    = '100';
$config->effort->datatable->fieldList['dept']['required'] = 'no';

$config->effort->datatable->fieldList['account']['title']    = 'account';
$config->effort->datatable->fieldList['account']['fixed']    = 'no';
$config->effort->datatable->fieldList['account']['width']    = '80';
$config->effort->datatable->fieldList['account']['required'] = 'no';

$config->effort->datatable->fieldList['work']['title']    = 'work';
$config->effort->datatable->fieldList['work']['fixed']    = 'no';
$config->effort->datatable->fieldList['work']['width']    = 'auto';
$config->effort->datatable->fieldList['work']['required'] = 'no';

$config->effort->datatable->fieldList['consumed']['title']    = 'consumed';
$config->effort->datatable->fieldList['consumed']['fixed']    = 'no';
$config->effort->datatable->fieldList['consumed']['width']    = '92';
$config->effort->datatable->fieldList['consumed']['required'] = 'no';

$config->effort->datatable->fieldList['left']['title']    = 'left';
$config->effort->datatable->fieldList['left']['fixed']    = 'no';
$config->effort->datatable->fieldList['left']['width']    = '88';
$config->effort->datatable->fieldList['left']['required'] = 'no';

$config->effort->datatable->fieldList['objectType']['title']    = 'objectType';
$config->effort->datatable->fieldList['objectType']['fixed']    = 'n20';
$config->effort->datatable->fieldList['objectType']['width']    = '150';
$config->effort->datatable->fieldList['objectType']['required'] = 'no';

$config->effort->datatable->fieldList['product']['title']    = 'product';
$config->effort->datatable->fieldList['product']['fixed']    = 'no';
$config->effort->datatable->fieldList['product']['width']    = '150';
$config->effort->datatable->fieldList['product']['required'] = 'no';

$config->effort->datatable->fieldList['execution']['title']    = 'execution';
$config->effort->datatable->fieldList['execution']['fixed']    = 'no';
$config->effort->datatable->fieldList['execution']['width']    = '120';
$config->effort->datatable->fieldList['execution']['required'] = 'no';

$config->effort->datatable->fieldList['project']['title']    = 'project';
$config->effort->datatable->fieldList['project']['fixed']    = 'no';
$config->effort->datatable->fieldList['project']['width']    = '120';
$config->effort->datatable->fieldList['project']['required'] = 'no';
