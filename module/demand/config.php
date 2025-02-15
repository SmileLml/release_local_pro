<?php
$config->demand = new stdclass();

$config->demand->create      = new stdclass();
$config->demand->edit        = new stdclass();
$config->demand->change      = new stdclass();
$config->demand->close       = new stdclass();
$config->demand->review      = new stdclass();
$config->demand->batchCreate = new stdclass();
$config->demand->distribute  = new stdclass();
$config->demand->retract     = new stdclass();

$config->demand->create->requiredFields      = 'title';
$config->demand->edit->requiredFields        = 'title';
$config->demand->batchCreate->requiredFields = 'title';
$config->demand->change->requiredFields      = 'title';
$config->demand->close->requiredFields       = 'closedReason';
$config->demand->review->requiredFields      = '';
$config->demand->distribute->requiredFields  = 'product';
$config->demand->retract->requiredFields     = 'retractedReason';

$config->demand->list = new stdclass();
$config->demand->list->customBatchCreateFields = 'product,verify,source,duration,BSA,keywords';
$config->demand->list->customCreateFields      = 'source,verify,pri,mailto,keywords';

$config->demand->custom = new stdclass();
$config->demand->custom->createFields      = $config->demand->list->customCreateFields;
$config->demand->custom->batchCreateFields = 'spec,pri,estimate,review';

$config->demand->editor = new stdclass();
$config->demand->editor->create     = array('id' => 'spec,verify', 'tools' => 'simpleTools');
$config->demand->editor->change     = array('id' => 'spec,verify,comment', 'tools' => 'simpleTools');
$config->demand->editor->edit       = array('id' => 'spec,verify,comment', 'tools' => 'simpleTools');
$config->demand->editor->view       = array('id' => 'comment,lastComment', 'tools' => 'simpleTools');
$config->demand->editor->close      = array('id' => 'comment', 'tools' => 'simpleTools');
$config->demand->editor->review     = array('id' => 'comment', 'tools' => 'simpleTools');
$config->demand->editor->activate   = array('id' => 'comment', 'tools' => 'simpleTools');
$config->demand->editor->assignto   = array('id' => 'comment', 'tools' => 'simpleTools');
$config->demand->editor->distribute = array('id' => 'comment', 'tools' => 'simpleTools');
$config->demand->editor->retract    = array('id' => 'comment', 'tools' => 'simpleTools');

$config->demand->excludeCheckFileds = ',uploadImage,category,pri,';

global $lang;
$config->demand->search['module'] = 'demand';
$config->demand->search['fields']['title']          = $lang->demand->title;
$config->demand->search['fields']['keywords']       = $lang->demand->keywords;
$config->demand->search['fields']['pri']            = $lang->demand->pri;
$config->demand->search['fields']['status']         = $lang->demand->status;
$config->demand->search['fields']['assignedTo']     = $lang->demand->assignedTo;
$config->demand->search['fields']['assignedDate']   = $lang->demand->assignedDate;
$config->demand->search['fields']['category']       = $lang->demand->category;
$config->demand->search['fields']['duration']       = $lang->demand->duration;
$config->demand->search['fields']['BSA']            = $lang->demand->BSA;
$config->demand->search['fields']['pool']           = $lang->demand->pool;
$config->demand->search['fields']['product']        = $lang->demand->product;
$config->demand->search['fields']['source']         = $lang->demand->source;
$config->demand->search['fields']['sourceNote']     = $lang->demand->sourceNote;
$config->demand->search['fields']['feedbackedBy']   = $lang->demand->feedbackedBy;
$config->demand->search['fields']['email']          = $lang->demand->email;
$config->demand->search['fields']['reviewedBy']     = $lang->demand->reviewedBy;
$config->demand->search['fields']['reviewedDate']   = $lang->demand->reviewedDate;
$config->demand->search['fields']['createdBy']      = $lang->demand->createdBy;
$config->demand->search['fields']['createdDate']    = $lang->demand->createdDate;
$config->demand->search['fields']['closedBy']       = $lang->demand->closedBy;
$config->demand->search['fields']['closedDate']     = $lang->demand->closedDate;
$config->demand->search['fields']['closedReason']   = $lang->demand->closedReason;
$config->demand->search['fields']['lastEditedBy']   = $lang->demand->lastEditedBy;
$config->demand->search['fields']['lastEditedDate'] = $lang->demand->lastEditedDate;
$config->demand->search['fields']['activatedDate']  = $lang->demand->activatedDate;
$config->demand->search['fields']['mailto']         = $lang->demand->mailto;
$config->demand->search['fields']['version']        = $lang->demand->version;
$config->demand->search['fields']['id']             = $lang->demand->id;

$config->demand->search['params']['pri']            = array('operator' => '=', 'control' => 'select',  'values' => $lang->demand->priList);
$config->demand->search['params']['title']          = array('operator' => 'include', 'control' => 'input',  'values' => '');
$config->demand->search['params']['keywords']       = array('operator' => 'include', 'control' => 'input',  'values' => '');
$config->demand->search['params']['status']         = array('operator' => '=', 'control' => 'select', 'values' => $lang->demand->statusList);
$config->demand->search['params']['assignedTo']     = array('operator' => '=', 'control' => 'select', 'values' => 'users');
$config->demand->search['params']['assignedDate']   = array('operator' => '=', 'control' => 'input', 'values' => '', 'class' => 'date');
$config->demand->search['params']['category']       = array('operator' => '=', 'control' => 'select', 'values' => $lang->demand->categoryList);
$config->demand->search['params']['duration']       = array('operator' => '=', 'control' => 'select', 'values' => $lang->demand->durationList);
$config->demand->search['params']['BSA']            = array('operator' => '=', 'control' => 'select', 'values' => $lang->demand->bsaList);
$config->demand->search['params']['pool']           = array('operator' => '=', 'control' => 'select', 'values' => '');
$config->demand->search['params']['product']        = array('operator' => '=', 'control' => 'select', 'values' => '');
$config->demand->search['params']['source']         = array('operator' => '=', 'control' => 'select', 'values' => $lang->demand->sourceList);
$config->demand->search['params']['sourceNote']     = array('operator' => 'include', 'control' => 'input', 'class' => '', 'values' => '');
$config->demand->search['params']['feedbackedBy']   = array('operator' => 'include', 'control' => 'input', 'values' => '');
$config->demand->search['params']['email']          = array('operator' => 'include', 'control' => 'input', 'values' => '');
$config->demand->search['params']['reviewedBy']     = array('operator' => 'include', 'control' => 'select', 'values' => 'users');
$config->demand->search['params']['reviewedDate']   = array('operator' => '=', 'control' => 'input', 'values' => '', 'class' => 'date');
$config->demand->search['params']['createdBy']      = array('operator' => '=', 'control' => 'select', 'values' => 'users');
$config->demand->search['params']['createdDate']    = array('operator' => '=', 'control' => 'input', 'values' => '', 'class' => 'date');
$config->demand->search['params']['closedBy']       = array('operator' => '=', 'control' => 'select', 'values' => 'users');
$config->demand->search['params']['closedDate']     = array('operator' => '=', 'control' => 'input', 'values' => '', 'class' => 'date');
$config->demand->search['params']['closedReason']   = array('operator' => '=', 'control' => 'select', 'values' => $lang->demand->reasonList);
$config->demand->search['params']['lastEditedBy']   = array('operator' => '=', 'control' => 'select', 'values' => 'users');
$config->demand->search['params']['lastEditedDate'] = array('operator' => '=', 'control' => 'input', 'values' => '', 'class' => 'date');
$config->demand->search['params']['activatedDate']  = array('operator' => '=', 'control' => 'input', 'values' => '', 'class' => 'date');
$config->demand->search['params']['mailto']         = array('operator' => 'include', 'control' => 'select', 'values' => 'users');
$config->demand->search['params']['version']        = array('operator' => '=', 'control' => 'input', 'values' => '');
$config->demand->search['params']['id']             = array('operator' => '=', 'control' => 'input', 'values' => '');

$config->demand->datatable = new stdclass();
$config->demand->datatable->defaultField = array('id', 'title', 'pri', 'status', 'assignedTo', 'category', 'duration', 'BSA', 'actions');

$config->demand->datatable->fieldList['id']['title']    = 'idAB';
$config->demand->datatable->fieldList['id']['fixed']    = 'left';
$config->demand->datatable->fieldList['id']['width']    = '60';
$config->demand->datatable->fieldList['id']['required'] = 'yes';

$config->demand->datatable->fieldList['title']['title']    = 'title';
$config->demand->datatable->fieldList['title']['fixed']    = 'left';
$config->demand->datatable->fieldList['title']['width']    = 'auto';
$config->demand->datatable->fieldList['title']['required'] = 'yes';

$config->demand->datatable->fieldList['pri']['title']    = 'priAB';
$config->demand->datatable->fieldList['pri']['fixed']    = 'left';
$config->demand->datatable->fieldList['pri']['width']    = '70';
$config->demand->datatable->fieldList['pri']['required'] = 'no';

$config->demand->datatable->fieldList['status']['title']      = 'status';
$config->demand->datatable->fieldList['status']['fixed']      = 'no';
$config->demand->datatable->fieldList['status']['width']      = '60';
$config->demand->datatable->fieldList['status']['required']   = 'no';
$config->demand->datatable->fieldList['status']['dataSource'] = array('lang' => 'statusList');

$config->demand->datatable->fieldList['assignedTo']['title']      = 'assignedTo';
$config->demand->datatable->fieldList['assignedTo']['fixed']      = 'no';
$config->demand->datatable->fieldList['assignedTo']['width']      = '90';
$config->demand->datatable->fieldList['assignedTo']['required']   = 'no';
$config->demand->datatable->fieldList['assignedTo']['dataSource'] = array('module' => 'demandpool', 'method' => 'getAssignedTo', 'params' => '$poolID');

$config->demand->datatable->fieldList['category']['title']      = 'category';
$config->demand->datatable->fieldList['category']['fixed']      = 'no';
$config->demand->datatable->fieldList['category']['width']      = '60';
$config->demand->datatable->fieldList['category']['required']   = 'no';
$config->demand->datatable->fieldList['category']['dataSource'] = array('lang' => 'categoryList');

$config->demand->datatable->fieldList['duration']['title']      = 'duration';
$config->demand->datatable->fieldList['duration']['fixed']      = 'no';
$config->demand->datatable->fieldList['duration']['width']      = '90';
$config->demand->datatable->fieldList['duration']['required']   = 'no';
$config->demand->datatable->fieldList['duration']['dataSource'] = array('lang' => 'durationList');

$config->demand->datatable->fieldList['BSA']['title']      = 'BSA';
$config->demand->datatable->fieldList['BSA']['fixed']      = 'no';
$config->demand->datatable->fieldList['BSA']['width']      = '90';
$config->demand->datatable->fieldList['BSA']['required']   = 'no';
$config->demand->datatable->fieldList['BSA']['dataSource'] = array('lang' => 'bsaList');

$config->demand->datatable->fieldList['product']['title']      = 'product';
$config->demand->datatable->fieldList['product']['fixed']      = 'no';
$config->demand->datatable->fieldList['product']['width']      = '100';
$config->demand->datatable->fieldList['product']['required']   = 'no';
$config->demand->datatable->fieldList['product']['control']    = 'multiple';
$config->demand->datatable->fieldList['product']['dataSource'] = array('module' => 'product', 'method' => 'getProductByPool', 'params' => '$poolID');

$config->demand->datatable->fieldList['source']['title']    = 'source';
$config->demand->datatable->fieldList['source']['fixed']    = 'no';
$config->demand->datatable->fieldList['source']['width']    = '90';
$config->demand->datatable->fieldList['source']['required'] = 'no';

$config->demand->datatable->fieldList['sourceNote']['title']    = 'sourceNote';
$config->demand->datatable->fieldList['sourceNote']['fixed']    = 'no';
$config->demand->datatable->fieldList['sourceNote']['width']    = '90';
$config->demand->datatable->fieldList['sourceNote']['required'] = 'no';

$config->demand->datatable->fieldList['feedbackedBy']['title']      = 'feedbackedBy';
$config->demand->datatable->fieldList['feedbackedBy']['fixed']      = 'no';
$config->demand->datatable->fieldList['feedbackedBy']['width']      = '90';
$config->demand->datatable->fieldList['feedbackedBy']['required']   = 'no';
$config->demand->datatable->fieldList['feedbackedBy']['dataSource'] = array('module' => 'user', 'method' => 'getPairs');

$config->demand->datatable->fieldList['email']['title']      = 'email';
$config->demand->datatable->fieldList['email']['fixed']      = 'no';
$config->demand->datatable->fieldList['email']['width']      = '100';
$config->demand->datatable->fieldList['email']['required']   = 'no';

$config->demand->datatable->fieldList['reviewedBy']['title']      = 'reviewedBy';
$config->demand->datatable->fieldList['reviewedBy']['fixed']      = 'no';
$config->demand->datatable->fieldList['reviewedBy']['width']      = '90';
$config->demand->datatable->fieldList['reviewedBy']['required']   = 'no';
$config->demand->datatable->fieldList['reviewedBy']['dataSource'] = array('module' => 'user', 'method' => 'getPairs');

$config->demand->datatable->fieldList['reviewedDate']['title']    = 'reviewedDate';
$config->demand->datatable->fieldList['reviewedDate']['fixed']    = 'no';
$config->demand->datatable->fieldList['reviewedDate']['width']    = '90';
$config->demand->datatable->fieldList['reviewedDate']['required'] = 'no';

$config->demand->datatable->fieldList['createdBy']['title']      = 'createdBy';
$config->demand->datatable->fieldList['createdBy']['fixed']      = 'no';
$config->demand->datatable->fieldList['createdBy']['width']      = '90';
$config->demand->datatable->fieldList['createdBy']['required']   = 'no';
$config->demand->datatable->fieldList['createdBy']['dataSource'] = array('module' => 'user', 'method' => 'getPairs');

$config->demand->datatable->fieldList['createdDate']['title']    = 'createdDate';
$config->demand->datatable->fieldList['createdDate']['fixed']    = 'no';
$config->demand->datatable->fieldList['createdDate']['width']    = '90';
$config->demand->datatable->fieldList['createdDate']['required'] = 'no';

$config->demand->datatable->fieldList['assignedDate']['title']    = 'assignedDate';
$config->demand->datatable->fieldList['assignedDate']['fixed']    = 'no';
$config->demand->datatable->fieldList['assignedDate']['width']    = '90';
$config->demand->datatable->fieldList['assignedDate']['required'] = 'no';

$config->demand->datatable->fieldList['closedBy']['title']      = 'closedBy';
$config->demand->datatable->fieldList['closedBy']['fixed']      = 'no';
$config->demand->datatable->fieldList['closedBy']['width']      = '90';
$config->demand->datatable->fieldList['closedBy']['required']   = 'no';
$config->demand->datatable->fieldList['closedBy']['dataSource'] = array('module' => 'user', 'method' => 'getPairs');

$config->demand->datatable->fieldList['closedDate']['title']    = 'closedDate';
$config->demand->datatable->fieldList['closedDate']['fixed']    = 'no';
$config->demand->datatable->fieldList['closedDate']['width']    = '90';
$config->demand->datatable->fieldList['closedDate']['required'] = 'no';

$config->demand->datatable->fieldList['closedReason']['title']      = 'closedReason';
$config->demand->datatable->fieldList['closedReason']['fixed']      = 'no';
$config->demand->datatable->fieldList['closedReason']['width']      = '80';
$config->demand->datatable->fieldList['closedReason']['required']   = 'no';
$config->demand->datatable->fieldList['closedReason']['dataSource'] = array('lang' => 'reasonList');

$config->demand->datatable->fieldList['lastEditedBy']['title']      = 'lastEditedBy';
$config->demand->datatable->fieldList['lastEditedBy']['fixed']      = 'no';
$config->demand->datatable->fieldList['lastEditedBy']['width']      = '90';
$config->demand->datatable->fieldList['lastEditedBy']['required']   = 'no';
$config->demand->datatable->fieldList['lastEditedBy']['dataSource'] = array('module' => 'user', 'method' => 'getPairs');

$config->demand->datatable->fieldList['lastEditedDate']['title']    = 'lastEditedDate';
$config->demand->datatable->fieldList['lastEditedDate']['fixed']    = 'no';
$config->demand->datatable->fieldList['lastEditedDate']['width']    = '90';
$config->demand->datatable->fieldList['lastEditedDate']['required'] = 'no';

$config->demand->datatable->fieldList['activatedDate']['title']    = 'activatedDate';
$config->demand->datatable->fieldList['activatedDate']['fixed']    = 'no';
$config->demand->datatable->fieldList['activatedDate']['width']    = '90';
$config->demand->datatable->fieldList['activatedDate']['required'] = 'no';

$config->demand->datatable->fieldList['mailto']['title']      = 'mailto';
$config->demand->datatable->fieldList['mailto']['fixed']      = 'no';
$config->demand->datatable->fieldList['mailto']['width']      = '90';
$config->demand->datatable->fieldList['mailto']['required']   = 'no';
$config->demand->datatable->fieldList['mailto']['dataSource'] = array('module' => 'user', 'method' => 'getPairs');

$config->demand->datatable->fieldList['keywords']['title']    = 'keywords';
$config->demand->datatable->fieldList['keywords']['fixed']    = 'no';
$config->demand->datatable->fieldList['keywords']['width']    = '100';
$config->demand->datatable->fieldList['keywords']['required'] = 'no';

$config->demand->datatable->fieldList['version']['title']    = 'version';
$config->demand->datatable->fieldList['version']['fixed']    = 'no';
$config->demand->datatable->fieldList['version']['width']    = '70';
$config->demand->datatable->fieldList['version']['required'] = 'no';

$config->demand->datatable->fieldList['actions']['title']    = 'actions';
$config->demand->datatable->fieldList['actions']['fixed']    = 'right';
$config->demand->datatable->fieldList['actions']['width']    = '220';
$config->demand->datatable->fieldList['actions']['required'] = 'yes';

$config->demand->templateFields = 'product,title,spec,source,verify,keywords,category,pri,assignedTo,duration,BSA';
$config->demand->listFields     = 'product,source,category,pri,assignedTo,duration,BSA,status,feedbackedBy,reviewedBy,createdBy,closedBy,closedReason,lastEditedBy,mailto';
$config->demand->exportFields   = 'id,title,pri,status,spec,verify,keywords,assignedTo,category,duration,BSA,product,source,sourceNote,feedbackedBy,email,reviewedBy,reviewedDate,createdBy,createdDate,assignedDate,closedBy,closedDate,closedReason,lastEditedBy,lastEditedDate,activatedDate,mailto';
$config->demand->selectedFields = 'id,title,pri,status,spec,verify,keywords,assignedTo,category,duration,BSA';
