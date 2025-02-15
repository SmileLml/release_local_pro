<?php
$config->approvalflow->create = new stdclass();
$config->approvalflow->edit   = new stdclass();

$config->approvalflow->create->requiredFields = 'name,type';
$config->approvalflow->edit->requiredFields   = 'name,type';

$config->approvalflow->editor          = new stdclass();
$config->approvalflow->editor->create  = array('id' => 'desc', 'tools' => 'simpleTools');
$config->approvalflow->editor->edit    = array('id' => 'desc', 'tools' => 'simpleTools');
