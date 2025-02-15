<?php
$config->domain->create = new stdclass();
$config->domain->edit   = new stdclass();
$config->domain->create->requiredFields = 'domain,adminURI,resolverURI,register,expiredDate,renew';
$config->domain->edit->requiredFields   = 'domain,adminURI,resolverURI,register,expiredDate,renew';

global $lang;
$config->domain->search['module'] = 'domain';
$config->domain->search['fields']['domain']        = $lang->domain->domain;
$config->domain->search['fields']['id']          = 'ID';
$config->domain->search['fields']['adminURI']    = $lang->domain->adminURI;
$config->domain->search['fields']['resolverURI'] = $lang->domain->resolverURI;
$config->domain->search['fields']['register']    = $lang->domain->register;
$config->domain->search['fields']['provider']    = $lang->domain->renew;

$config->domain->search['params']['domain']        = array('operator' => 'include', 'control' => 'input',  'values' => '');
$config->domain->search['params']['id']          = array('operator' => '=', 'control' => 'input',  'values' => '');
$config->domain->search['params']['adminURI']    = array('operator' => '=', 'control' => 'select',  'values' => $lang->domain->adminURI);
$config->domain->search['params']['resolverURI'] = array('operator' => '=', 'control' => 'select',  'values' => $lang->domain->resolverURI);
$config->domain->search['params']['register']    = array('operator' => 'include', 'control' => 'input',  'values' => $lang->domain->register);
