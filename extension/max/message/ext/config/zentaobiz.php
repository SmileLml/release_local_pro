<?php
$config->message->objectTypes['feedback'] = array('opened', 'edited', 'assigned', 'tostory', 'tobug', 'totask', 'totodo', 'asked', 'replied', 'closed', 'deleted', 'reviewed', 'processed');

$config->message->available['mail']['feedback']    = $config->message->objectTypes['feedback'];
$config->message->available['webhook']['feedback'] = $config->message->objectTypes['feedback'];
$config->message->available['message']['feedback'] = $config->message->objectTypes['feedback'];
$config->message->available['sms']['feedback']     = $config->message->objectTypes['feedback'];
$config->message->available['xuanxuan']['feedback'] = $config->message->objectTypes['feedback'];

$config->message->objectTypes['ticket'] = array('opened', 'edited', 'assigned', 'started', 'finished', 'closed', 'activated');
$config->message->available['mail']['ticket']    = $config->message->objectTypes['ticket'];
$config->message->available['webhook']['ticket'] = array('opened', 'edited', 'assigned', 'started', 'finished', 'activated');
$config->message->available['message']['ticket'] = array('opened', 'edited', 'assigned', 'started', 'finished', 'activated');
$config->message->available['sms']['ticket']     = array('opened', 'edited', 'assigned', 'started', 'finished', 'activated');