<?php
$config->message->objectTypes = array();
$config->message->objectTypes['product']    = array('opened', 'edited', 'closed', 'undeleted');
$config->message->objectTypes['story']      = array('opened', 'edited', 'commented', 'changed', 'reviewed', 'closed', 'activated', 'assigned');
$config->message->objectTypes['demandpool'] = array('created', 'edited', 'closed', 'activated');
$config->message->objectTypes['demand']     = array('created', 'edited', 'commented', 'changed', 'reviewed', 'closed', 'activated', 'distributed', 'assigned');

$config->message->available = array();
$config->message->available['mail']['story']    = $config->message->objectTypes['story'];
$config->message->available['webhook']          = $config->message->objectTypes;
$config->message->available['message']['story'] = $config->message->objectTypes['story'];

$config->message->available['mail']['demandpool']     = $config->message->objectTypes['demandpool'];
$config->message->available['message']['demandpool']  = $config->message->objectTypes['demandpool'];
$config->message->available['sms']['demandpool']      = $config->message->objectTypes['demandpool'];
$config->message->available['xuanxuan']['demandpool'] = $config->message->objectTypes['demandpool'];

$config->message->available['mail']['demand']     = $config->message->objectTypes['demand'];
$config->message->available['message']['demand']  = $config->message->objectTypes['demand'];
$config->message->available['sms']['demand']      = $config->message->objectTypes['demand'];
$config->message->available['xuanxuan']['demand'] = $config->message->objectTypes['demand'];
