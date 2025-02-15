<?php
global $config;
$lang->admin->property = new stdclass();
$lang->admin->property->companyName     = 'Company Name';
$lang->admin->property->startDate       = 'Start';
$lang->admin->property->expireDate      = 'License Expired on';
$lang->admin->property->serviceDeadline = 'Technical Support Service Expired on';
$lang->admin->property->user            = 'User';
$lang->admin->property->ip              = 'IP';
$lang->admin->property->mac             = 'MAC';
$lang->admin->property->domain          = 'Domain';

$lang->admin->menuList->system['subMenu']['libreoffice'] = array('link' => 'Office|custom|libreoffice|');
$lang->admin->menuList->system['menuOrder']['60']        = 'libreoffice';

$lang->admin->menuList->feature['subMenu']['feedback'] = array('link' => "Feedback|custom|required|module=feedback", 'exclude' => 'set,required');
$lang->admin->menuList->feature['menuOrder']['35']     = 'feedback';

$lang->admin->menuList->feature['tabMenu']['feedback']['feedback'] = array('link' => "Feedback|custom|required|module=feedback", 'links' => array('custom|set|module=feedback&field=review'), 'exclude' => 'custom-set,custom-required');
$lang->admin->menuList->feature['tabMenu']['feedback']['ticket']   = array('link' => "Ticket|custom|required|module=ticket", 'exclude' => 'custom-set,custom-required');

if($config->vision == 'lite') unset($lang->admin->menuList->feature['subMenu']['feedback'], $lang->admin->menuList->feature['menuOrder']['35']);
