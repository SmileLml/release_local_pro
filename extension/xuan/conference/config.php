<?php
$config->conference->owtTabList = array('server', 'video');

$config->conference->require = new stdclass();
$config->conference->require->edit  = 'enabled,serviceId,serviceKey,serverAddr,apiPort,mgmtPort';

$config->conference->configItems = new stdclass();
$config->conference->configItems->common = array('resolutionWidth', 'resolutionHeight', 'https', 'detachedConference');
$config->conference->configItems->owt    = array('serviceId', 'serviceKey', 'serverAddr', 'apiPort', 'mgmtPort');
$config->conference->configItems->srs    = array('serverAddr', 'apiPort', 'rtcPort');
