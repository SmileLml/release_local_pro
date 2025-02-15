<?php
$config->roadmap = new stdclass();
$config->roadmap->editor = new stdclass();
$config->roadmap->editor->create   = array('id' => 'desc', 'tools' => 'simpleTools');
$config->roadmap->editor->edit     = array('id' => 'desc', 'tools' => 'simpleTools');
$config->roadmap->editor->close    = array('id' => 'comment', 'tools' => 'simpleTools');
$config->roadmap->editor->activate = array('id' => 'comment', 'tools' => 'simpleTools');
$config->roadmap->editor->view     = array('id' => 'comment,lastComment', 'tools' => 'simpleTools');
$config->roadmap->editor->unlinkur = array('id' => 'comment,lastComment', 'tools' => 'simpleTools');

$config->roadmap->create   = new stdclass();
$config->roadmap->edit     = new stdclass();
$config->roadmap->close    = new stdclass();
$config->roadmap->unlinkur = new stdclass();

$config->roadmap->create->requiredFields   = 'name,begin,end';
$config->roadmap->edit->requiredFields     = 'name,begin,end';
$config->roadmap->close->requiredFields    = 'closedReason';
$config->roadmap->unlinkur->requiredFields = 'unlinkReason';

$config->roadmap->future = '2030-01-01';
