<?php
$config->user->export = new stdclass();

$config->user->export->listFields     = explode(',', "dept,role,gender,type");
$config->user->export->templateFields = explode(',', "account,realname,dept,gender,type,role,join,email,phone,mobile,weixin,qq,address");


$config->user->list = new stdClass();
$config->user->list->exportFields  = 'id,account,realname,dept,gender,type,role,join,email,phone,mobile,weixin,qq,address';
$config->user->list->defaultFields = 'id,severity,pri,title,openedBy,assignedTo,resolvedBy,resolution';
