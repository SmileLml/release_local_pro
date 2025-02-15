<?php
global $lang, $app;
$app->loadLang('story');
$config->projectstory->search = array();
$config->projectstory->search['module'] = 'projectstory';
$config->projectstory->search['fields']['id']         = $lang->story->id;
$config->projectstory->search['fields']['title']      = $lang->story->title;
$config->projectstory->search['fields']['keywords']   = $lang->story->keywords;
$config->projectstory->search['fields']['pri']        = $lang->story->pri;
$config->projectstory->search['fields']['estimate']   = $lang->story->estimate;
$config->projectstory->search['fields']['openedBy']   = $lang->story->openedBy;
$config->projectstory->search['fields']['openedDate'] = $lang->story->openedDate;

$config->projectstory->search['params']['id']         = array('operator' => '=',       'control' => 'input',  'values' => '');
$config->projectstory->search['params']['title']      = array('operator' => 'include', 'control' => 'input',  'values' => '');
$config->projectstory->search['params']['keywords']   = array('operator' => 'include', 'control' => 'input',  'values' => '');
$config->projectstory->search['params']['pri']        = array('operator' => '=',       'control' => 'select', 'values' => $this->lang->story->priList);
$config->projectstory->search['params']['estimate']   = array('operator' => '=',       'control' => 'input',  'values' => '');
$config->projectstory->search['params']['openedBy']   = array('operator' => '=',       'control' => 'select', 'values' => 'users');
$config->projectstory->search['params']['openedDate'] = array('operator' => '=',       'control' => 'input',  'values' => '', 'class' => 'date');
