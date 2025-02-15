<?php
$config->charter->create = new stdclass();
$config->charter->edit   = new stdclass();
$config->charter->close  = new stdclass();

$config->charter->create->requiredFields = 'name,product,roadmap';
$config->charter->edit->requiredFields   = 'name,product,roadmap';
$config->charter->close->requiredFields  = 'closedReason';

$config->charter->editor = new stdclass();
$config->charter->editor->create = array('id' => 'spec', 'tools' => 'simpleTools');
$config->charter->editor->edit   = array('id' => 'spec', 'tools' => 'simpleTools');
$config->charter->editor->close  = array('id' => 'comment', 'tools' => 'simpleTools');
$config->charter->editor->review = array('id' => 'meetingMinutes', 'tools' => 'simpleTools');
$config->charter->editor->view   = array('id' => 'comment,lastComment', 'tools' => 'simpleTools');
