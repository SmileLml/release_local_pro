<?php
global $lang;
$config->testtask->search['module']              = 'testtask';
$config->testtask->search['fields']['id']        = $lang->testtask->id;
$config->testtask->search['fields']['name']      = $lang->testtask->name;
$config->testtask->search['fields']['status']    = $lang->testtask->status;
$config->testtask->search['fields']['build']     = $lang->testtask->build;
$config->testtask->search['fields']['owner']     = $lang->testtask->owner;
$config->testtask->search['fields']['product']   = $lang->testtask->product;
$config->testtask->search['fields']['execution'] = $lang->testtask->execution;
$config->testtask->search['fields']['begin']     = $lang->testtask->begin;
$config->testtask->search['fields']['end']       = $lang->testtask->end;

$config->testtask->search['params']['name']      = array('operator' => 'include', 'control' => 'input',  'values' => '');
$config->testtask->search['params']['status']    = array('operator' => '=',       'control' => 'select', 'values' => array('' => '') + $lang->testtask->statusList);
$config->testtask->search['params']['build']     = array('operator' => '=',       'control' => 'select', 'values' => 'builds');
$config->testtask->search['params']['owner']     = array('operator' => '=',       'control' => 'select', 'values' => 'users');
$config->testtask->search['params']['product']   = array('operator' => '=',       'control' => 'select', 'values' => '');
$config->testtask->search['params']['execution'] = array('operator' => '=',       'control' => 'select', 'values' => 'executions');
$config->testtask->search['params']['begin']     = array('operator' => '='      , 'control' => 'input' , 'values' => '', 'class' => 'date');
$config->testtask->search['params']['end']       = array('operator' => '='      , 'control' => 'input' , 'values' => '', 'class' => 'date');