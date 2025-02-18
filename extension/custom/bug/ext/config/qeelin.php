<?php

$config->bug->batchAdjustFields = 'product,branch,project,build';

$config->bug->exportFields .= ',plan';
$config->bug->listFields   .= ',plan';

$config->bug->datatable->fieldList['plan']['dataSource'] = array('module' => 'productplan', 'method' =>'getPairs', 'params' => '$productID');

$config->bug->batchCheckMax = 50;

$config->bug->editor->browse  = array('id' => 'comment', 'tools' => 'simpleTools');