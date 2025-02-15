<?php
$config->message->objectTypes['review']    = array('toaudit');
$config->message->objectTypes['waterfail'] = array('submit', 'cancel', 'review');

$config->message->available['mail']['review']    = $config->message->objectTypes['review'];
$config->message->available['mail']['waterfail'] = $config->message->objectTypes['waterfail'];

$config->message->objectTypes['meeting'] = array('opened', 'edited', 'minuted');

$config->message->available['mail']['meeting']    = $config->message->objectTypes['meeting'];
$config->message->available['webhook']['meeting'] = $config->message->objectTypes['meeting'];
