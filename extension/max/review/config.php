<?php
$config->review = new stdclass();
$config->review->editor = new stdclass();
$config->review->editor->create  = array('id' => 'comment', 'tools' => 'simpleTools');
$config->review->editor->edit    = array('id' => 'comment', 'tools' => 'simpleTools');
$config->review->editor->submit  = array('id' => 'comment', 'tools' => 'simpleTools');
$config->review->editor->toaudit = array('id' => 'comment', 'tools' => 'simpleTools');
$config->review->editor->assess  = array('id' => 'opinion', 'tools' => 'simpleTools');
$config->review->editor->audit   = array('id' => 'opinion', 'tools' => 'simpleTools');

$config->review->create = new stdclass();
$config->review->create->requiredFields = 'product,title,object';

$config->review->edit = new stdclass();
$config->review->edit->requiredFields = 'title';

$config->review->datatable = new stdclass();
$config->review->datatable->defaultField = array('id', 'title', 'product', 'category', 'version', 'status', 'reviewedBy', 'createdBy', 'createdDate', 'deadline', 'lastReviewedDate', 'result', 'actions');

$config->review->datatable->fieldList['id']['title']    = 'idAB';
$config->review->datatable->fieldList['id']['fixed']    = 'left';
$config->review->datatable->fieldList['id']['width']    = '60';
$config->review->datatable->fieldList['id']['required'] = 'yes';

$config->review->datatable->fieldList['title']['title']    = 'title';
$config->review->datatable->fieldList['title']['fixed']    = 'left';
$config->review->datatable->fieldList['title']['width']    = 'auto';
$config->review->datatable->fieldList['title']['required'] = 'yes';

$config->review->datatable->fieldList['product']['title']    = 'product';
$config->review->datatable->fieldList['product']['fixed']    = 'no';
$config->review->datatable->fieldList['product']['width']    = '200';
$config->review->datatable->fieldList['product']['required'] = 'no';

$config->review->datatable->fieldList['category']['title']    = 'object';
$config->review->datatable->fieldList['category']['fixed']    = 'no';
$config->review->datatable->fieldList['category']['width']    = '120';
$config->review->datatable->fieldList['category']['required'] = 'no';

$config->review->datatable->fieldList['version']['title']    = 'version';
$config->review->datatable->fieldList['version']['fixed']    = 'no';
$config->review->datatable->fieldList['version']['width']    = '180';
$config->review->datatable->fieldList['version']['required'] = 'no';

$config->review->datatable->fieldList['status']['title']    = 'status';
$config->review->datatable->fieldList['status']['fixed']    = 'no';
$config->review->datatable->fieldList['status']['width']    = '100';
$config->review->datatable->fieldList['status']['required'] = 'no';

$config->review->datatable->fieldList['reviewedBy']['title']    = 'reviewedBy';
$config->review->datatable->fieldList['reviewedBy']['fixed']    = 'no';
$config->review->datatable->fieldList['reviewedBy']['width']    = '150';
$config->review->datatable->fieldList['reviewedBy']['required'] = 'no';

$config->review->datatable->fieldList['reviewer']['title']    = 'reviewer';
$config->review->datatable->fieldList['reviewer']['fixed']    = 'no';
$config->review->datatable->fieldList['reviewer']['width']    = '150';
$config->review->datatable->fieldList['reviewer']['required'] = 'no';
$config->review->datatable->fieldList['reviewer']['sort']     = 'no';

$config->review->datatable->fieldList['createdBy']['title']    = 'createdBy';
$config->review->datatable->fieldList['createdBy']['fixed']    = 'no';
$config->review->datatable->fieldList['createdBy']['width']    = '120';
$config->review->datatable->fieldList['createdBy']['required'] = 'no';

$config->review->datatable->fieldList['createdDate']['title']    = 'createdDate';
$config->review->datatable->fieldList['createdDate']['fixed']    = 'no';
$config->review->datatable->fieldList['createdDate']['width']    = '120';
$config->review->datatable->fieldList['createdDate']['required'] = 'no';

$config->review->datatable->fieldList['deadline']['title']    = 'deadline';
$config->review->datatable->fieldList['deadline']['fixed']    = 'no';
$config->review->datatable->fieldList['deadline']['width']    = '120';
$config->review->datatable->fieldList['deadline']['required'] = 'no';

$config->review->datatable->fieldList['lastReviewedDate']['title']    = 'lastReviewedDate';
$config->review->datatable->fieldList['lastReviewedDate']['fixed']    = 'no';
$config->review->datatable->fieldList['lastReviewedDate']['width']    = '120';
$config->review->datatable->fieldList['lastReviewedDate']['required'] = 'no';

$config->review->datatable->fieldList['result']['title']    = 'result';
$config->review->datatable->fieldList['result']['fixed']    = 'no';
$config->review->datatable->fieldList['result']['width']    = '120';
$config->review->datatable->fieldList['result']['required'] = 'no';

$config->review->datatable->fieldList['lastAuditedDate']['title']    = 'lastAuditedDate';
$config->review->datatable->fieldList['lastAuditedDate']['fixed']    = 'no';
$config->review->datatable->fieldList['lastAuditedDate']['width']    = '120';
$config->review->datatable->fieldList['lastAuditedDate']['required'] = 'no';

$config->review->datatable->fieldList['auditResult']['title']    = 'auditResult';
$config->review->datatable->fieldList['auditResult']['fixed']    = 'no';
$config->review->datatable->fieldList['auditResult']['width']    = '120';
$config->review->datatable->fieldList['auditResult']['required'] = 'no';

$config->review->datatable->fieldList['actions']['title']    = 'actions';
$config->review->datatable->fieldList['actions']['fixed']    = 'right';
$config->review->datatable->fieldList['actions']['width']    = '285';
$config->review->datatable->fieldList['actions']['required'] = 'yes';
