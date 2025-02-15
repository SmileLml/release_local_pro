<?php
$lang->admin->license       = 'License';
$lang->admin->uploadLicense = 'Replace License';

$lang->admin->licenseInfo['alllife'] = 'Permanent';
$lang->admin->licenseInfo['nouser']  = 'Unlimit';

$lang->admin->property = new stdclass();
$lang->admin->property->companyName = 'Company Name';
$lang->admin->property->startDate   = 'Start';
$lang->admin->property->expireDate  = 'Expiration';
$lang->admin->property->user        = 'User';
$lang->admin->property->ip          = 'IP';
$lang->admin->property->mac         = 'MAC';
$lang->admin->property->domain      = 'Domain';

$lang->admin->notWritable = '<code>%s</code> is not writable. Modify permissions and refresh.';
$lang->admin->notZip      = 'Please upload zip file.';

global $config;
if($config->vision == 'rnd')
{
    $lang->admin->menuList->system['subMenu']['license'] = array('link' => "License|admin|license|");
    $lang->admin->menuList->system['menuOrder']['25']    = 'license';
    $lang->admin->menuList->system['dividerMenu']        = str_replace(',safe,', ',license,', $lang->admin->menuList->system['dividerMenu']);
}
