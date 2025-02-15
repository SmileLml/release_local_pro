<?php
$lang->solution->market = new stdclass;
$lang->solution->market->browse = '解決方案市場';
$lang->solution->market->view   = '解決方案詳情';

$lang->solution->name = '名稱';

$lang->solution->browse        = '已安裝';
$lang->solution->view          = '解決方案詳情';
$lang->solution->detail        = '查看';
$lang->solution->progress      = '安裝進度';
$lang->solution->install       = '安裝';
$lang->solution->background    = '後台安裝';
$lang->solution->cancelInstall = '取消安裝';
$lang->solution->uninstall     = '卸載';
$lang->solution->retryInstall  = '重試';
$lang->solution->nextStep      = '下一步';
$lang->solution->config        = '配置';

$lang->solution->introduction = '基本介紹';
$lang->solution->scenes       = '適用場景';
$lang->solution->diagram      = '架構圖';
$lang->solution->includedApp  = '包含應用';
$lang->solution->features     = '方案亮點';
$lang->solution->relatedLinks = '相關連結';
$lang->solution->customers    = '典型客戶';
$lang->solution->apps         = '安裝的應用';
$lang->solution->externalApps = '外部應用';
$lang->solution->resources    = '資源占用';

$lang->solution->editName = '修改名稱';

$lang->solution->chooseApp           = '請選擇要安裝的應用';
$lang->solution->noInstalledSolution = '還沒有安裝解決方案';
$lang->solution->toInstall           = '去安裝';

$lang->solution->notices = new stdclass;
$lang->solution->notices->fail                 = '失敗';
$lang->solution->notices->success              = '成功';
$lang->solution->notices->creatingSolution     = '正在創建解決方案。';
$lang->solution->notices->uninstallingSolution = '正在卸載解決方案';
$lang->solution->notices->installingApp        = '正在安裝：';
$lang->solution->notices->installationSuccess  = '解決方案安裝成功！';
$lang->solution->notices->cancelInstall        = '確定要取消安裝嗎？';
$lang->solution->notices->confirmToUninstall   = '確定要卸載嗎？';
$lang->solution->notices->confirmReinstall     = '確定要重試安裝嗎？';

$lang->solution->errors = new stdclass;
$lang->solution->errors->error                = '錯誤';
$lang->solution->errors->notFound             = '找不到相關數據';
$lang->solution->errors->failToInstallApp     = '安裝%s應用失敗';
$lang->solution->errors->timeout              = '安裝超時';
$lang->solution->errors->failToUninstallApp   = '卸載%s應用失敗';
$lang->solution->errors->hasInstallationError = '安裝過程中發生錯誤';
$lang->solution->errors->notFoundAppByVersion = '找不到%s版本的%s應用';
$lang->solution->errors->notEnoughResource    = '資源不足, 請增加配置或釋放其它資源後重試。';

$lang->solution->installationErrors = array();
$lang->solution->installationErrors['waiting']           = '安裝未開始。';
$lang->solution->installationErrors['uninstalling']      = '安裝已取消。';
$lang->solution->installationErrors['cneError']          = '安裝失敗。';
$lang->solution->installationErrors['timeout']           = '安裝超時。';
$lang->solution->installationErrors['notFoundApp']       = '找不到待安裝的應用。';
$lang->solution->installationErrors['notEnoughResource'] = '資源不足, 請增加配置或釋放其它資源後重試。';
