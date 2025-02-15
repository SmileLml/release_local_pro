<?php
$lang->admin->license       = '授權信息';
$lang->admin->uploadLicense = '替換授權';

$lang->admin->licenseInfo['alllife'] = '終生';
$lang->admin->licenseInfo['nouser']  = '不限人數';

$lang->admin->property = new stdclass();
$lang->admin->property->companyName = '公司名稱';
$lang->admin->property->startDate   = '授權時間';
$lang->admin->property->expireDate  = '到期時間';
$lang->admin->property->user        = '授權人數';
$lang->admin->property->ip          = '授權IP';
$lang->admin->property->mac         = '授權MAC';
$lang->admin->property->domain      = '授權域名';

$lang->admin->notWritable = '<code>%s</code> 目錄不可寫，請修改目錄權限正確後，刷新。';
$lang->admin->notZip      = '請上傳zip檔案。';

global $config;
if($config->vision == 'rnd')
{
    $lang->admin->menuList->system['subMenu']['license'] = array('link' => "授權信息|admin|license|");
    $lang->admin->menuList->system['menuOrder']['25']    = 'license';
    $lang->admin->menuList->system['dividerMenu']        = str_replace(',safe,', ',license,', $lang->admin->menuList->system['dividerMenu']);
}
