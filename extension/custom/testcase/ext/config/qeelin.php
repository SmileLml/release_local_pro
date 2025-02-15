<?php

$config->testcase->datatable->fieldList['caseUpdateMark']['title']    = 'caseUpdateMark';
$config->testcase->datatable->fieldList['caseUpdateMark']['width']    = 'auto';
$config->testcase->datatable->fieldList['caseUpdateMark']['required'] = 'no';
$config->testcase->datatable->fieldList['caseUpdateMark']['sort']     = 'no';

$config->testcase->datatable->defaultField = array('id', 'title', 'pri', 'caseUpdateMark', 'openedBy', 'lastRunner', 'lastRunDate', 'lastRunResult', 'actions');

$config->testcase->batchCheckMax = 50;