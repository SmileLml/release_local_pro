<?php
$config->custom->canAdd['baseline']    = 'objectList';
$config->custom->canAdd['nc']          = 'typeList,severityList';
$config->custom->canAdd['issue']       = 'typeList,severityList,priList';
$config->custom->canAdd['risk']        = 'categoryList,sourceList';
$config->custom->canAdd['opportunity'] = 'sourceList,typeList';

array_splice($config->custom->allFeatures, 5, 0, 'scrumDetail');
array_splice($config->custom->allFeatures, 14, 0, 'assetlib');

$config->custom->dataFeatures[] = 'scrumIssue';
$config->custom->dataFeatures[] = 'scrumRisk';
$config->custom->dataFeatures[] = 'scrumOpportunity';
$config->custom->dataFeatures[] = 'scrumMeeting';
$config->custom->dataFeatures[] = 'scrumAuditplan';
$config->custom->dataFeatures[] = 'scrumProcess';
$config->custom->dataFeatures[] = 'assetlib';

$config->custom->scrumFeatures = array('issue', 'risk', 'opportunity', 'process', 'auditplan', 'meeting', 'measrecord');

$config->custom->notSetMethods[] = 'role';
