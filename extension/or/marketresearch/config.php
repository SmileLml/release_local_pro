<?php
$config->marketresearch = new stdclass();
$config->marketresearch->create = new stdclass();
$config->marketresearch->edit   = new stdclass();
$config->marketresearch->close  = new stdclass();

$config->marketresearch->create->requiredFields = 'name,PM,begin,end';
$config->marketresearch->edit->requiredFields   = $config->marketresearch->create->requiredFields;
$config->marketresearch->close->requiredFields  = 'realEnd,closedReason';

$config->marketresearch->editor = new stdclass();
$config->marketresearch->editor->create     = array('id' => 'desc', 'tools' => 'simpleTools');
$config->marketresearch->editor->edit       = array('id' => 'desc', 'tools' => 'simpleTools');
$config->marketresearch->editor->close      = array('id' => 'comment', 'tools' => 'simpleTools');
$config->marketresearch->editor->start      = array('id' => 'comment', 'tools' => 'simpleTools');
$config->marketresearch->editor->activate   = array('id' => 'comment', 'tools' => 'simpleTools');
$config->marketresearch->editor->view       = array('id' => 'lastComment', 'tools' => 'simpleTools');
$config->marketresearch->editor->createtask = array('id' => 'desc', 'tools' => 'simpleTools');
$config->marketresearch->editor->edittask   = array('id' => 'desc', 'tools' => 'simpleTools');
$config->marketresearch->editor->closestage = array('id' => 'comment', 'tools' => 'simpleTools');

$config->marketresearch->datatable = new stdclass();
$config->marketresearch->datatable->defaultField = array('id', 'name', 'status', 'market', 'PM', 'begin', 'end', 'progress', 'actions');

$config->marketresearch->datatable->fieldList['id']['title']    = 'idAB';
$config->marketresearch->datatable->fieldList['id']['fixed']    = 'left';
$config->marketresearch->datatable->fieldList['id']['width']    = '70';
$config->marketresearch->datatable->fieldList['id']['required'] = 'yes';

$config->marketresearch->datatable->fieldList['name']['title']    = 'name';
$config->marketresearch->datatable->fieldList['name']['fixed']    = 'left';
$config->marketresearch->datatable->fieldList['name']['width']    = 'auto';
$config->marketresearch->datatable->fieldList['name']['required'] = 'yes';

$config->marketresearch->datatable->fieldList['status']['title']    = 'status';
$config->marketresearch->datatable->fieldList['status']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['status']['width']    = '60';
$config->marketresearch->datatable->fieldList['status']['required'] = 'no';

$config->marketresearch->datatable->fieldList['market']['title']    = 'market';
$config->marketresearch->datatable->fieldList['market']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['market']['width']    = '100';
$config->marketresearch->datatable->fieldList['market']['required'] = 'no';

$config->marketresearch->datatable->fieldList['PM']['title']      = 'PM';
$config->marketresearch->datatable->fieldList['PM']['fixed']      = 'no';
$config->marketresearch->datatable->fieldList['PM']['width']      = '100';
$config->marketresearch->datatable->fieldList['PM']['required']   = 'no';

$config->marketresearch->datatable->fieldList['begin']['title']    = 'begin';
$config->marketresearch->datatable->fieldList['begin']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['begin']['width']    = '100';
$config->marketresearch->datatable->fieldList['begin']['required'] = 'no';

$config->marketresearch->datatable->fieldList['end']['title']    = 'end';
$config->marketresearch->datatable->fieldList['end']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['end']['width']    = '100';
$config->marketresearch->datatable->fieldList['end']['required'] = 'no';

$config->marketresearch->datatable->fieldList['realBegan']['title']    = 'realBegan';
$config->marketresearch->datatable->fieldList['realBegan']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['realBegan']['width']    = '100';
$config->marketresearch->datatable->fieldList['realBegan']['required'] = 'no';

$config->marketresearch->datatable->fieldList['realEnd']['title']    = 'realEnd';
$config->marketresearch->datatable->fieldList['realEnd']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['realEnd']['width']    = '100';
$config->marketresearch->datatable->fieldList['realEnd']['required'] = 'no';

$config->marketresearch->datatable->fieldList['progress']['title']    = 'progress';
$config->marketresearch->datatable->fieldList['progress']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['progress']['width']    = '90';
$config->marketresearch->datatable->fieldList['progress']['required'] = 'no';

$config->marketresearch->datatable->fieldList['openedBy']['title']    = 'openedBy';
$config->marketresearch->datatable->fieldList['openedBy']['fixed']    = 'no';
$config->marketresearch->datatable->fieldList['openedBy']['width']    = '90';
$config->marketresearch->datatable->fieldList['openedBy']['required'] = 'no';

$config->marketresearch->datatable->fieldList['actions']['title']    = 'actions';
$config->marketresearch->datatable->fieldList['actions']['fixed']    = 'right';
$config->marketresearch->datatable->fieldList['actions']['width']    = '180';
$config->marketresearch->datatable->fieldList['actions']['required'] = 'yes';

$config->marketresearch->custom = new stdclass();
$config->marketresearch->custom->createFields = 'PM,acl,realBegan,realEnd';
$config->marketresearch->customCreateFields   = $config->marketresearch->custom->createFields;

$config->marketresearch->task = new stdclass();
$config->marketresearch->task->customBatchCreateFields = 'assignedTo,estimate,estStarted,deadline,desc,pri';
$config->marketresearch->task->batchCreateFields       = 'assignedTo,estimate,estStarted,deadline,desc,pri';
