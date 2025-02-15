<?php
$lang->conference->common                       = '音視頻';
$lang->conference->admin                        = '音視頻參數設置';
$lang->conference->notset                       = '未設置';
$lang->conference->settings                     = '音視頻參數配置';
$lang->conference->enabled                      = '會議功能';
$lang->conference->enabledTip                   = '開啟會議功能';
$lang->conference->serverAddr                   = '音視頻伺服器地址';
$lang->conference->serverAddrTip                = '';
$lang->conference->apiPort                      = '音視頻 API 連接埠';
$lang->conference->apiPortOwtTip                = '預設為 3004';
$lang->conference->apiPortSrsTip                = '預設為 1985';
$lang->conference->mgmtPort                     = 'OWT 管理連接埠';
$lang->conference->mgmtPortTip                  = '預設為 3300';
$lang->conference->rtcPort                      = 'SRS 信令連接埠';
$lang->conference->rtcPortTip                   = '預設為 1989';
$lang->conference->https                        = '是否啟用 HTTPS';
$lang->conference->httpsTip                     = 'SRS 預設部署關閉 HTTPS';
$lang->conference->serviceId                    = 'OWT ID';
$lang->conference->serviceIdTip                 = '';
$lang->conference->serviceKey                   = 'OWT 密鑰';
$lang->conference->serviceKeyTip                = '';
$lang->conference->configGuideTip               = '';
$lang->conference->backendTypeTip               = '';
$lang->conference->detachedConference           = '增強版會議';
$lang->conference->detachedConferenceUrl        = '增強版會議功能包';
$lang->conference->detachedConferenceTip        = ' 需要您部署SRS音視頻服務';
$lang->conference->detachedConferencewarning    = '啟用新版會議需要將系統用戶的客戶端進行升級，舊版客戶端會議將不再使用，是否確定啟用增強版會議？';

$lang->conference->setupTitle       = '音視頻伺服器部署指南';
$lang->conference->setupDescription = '喧喧提供音頻會議功能，需要部署額外的音視頻服務端。音視頻伺服器端分為 OWT 和 SRS ，推薦使用 SRS 音視頻服務端。';
$lang->conference->setupDoc         = '部署文檔';
$lang->conference->configDoc        = '配置文檔';
$lang->conference->download         = '下載地址';
$lang->conference->srsSetupTitle    = 'SRS 音視頻服務端';
$lang->conference->owtSetupTitle    = 'OWT 音視頻服務端';


$lang->conference->backend = new stdclass();
$lang->conference->backend->type  = '後端類型';
$lang->conference->backend->types = array('owt' => 'OWT', 'srs' => 'SRS');

$lang->conference->inputError = new stdClass();
$lang->conference->inputError->serviceId        = 'OWT ID 不能為空';
$lang->conference->inputError->serviceKey       = 'OWT 密鑰不能為空';
$lang->conference->inputError->serverAddr       = '伺服器地址不能為空';
$lang->conference->inputError->apiPort          = 'API 連接埠不能為空';
$lang->conference->inputError->mgmtPort         = 'OWT 管理連接埠不能為空';
$lang->conference->inputError->rtcPort          = '信令連接埠不能為空';
$lang->conference->inputError->resolutionWidth  = '請填寫分辨率寬度';
$lang->conference->inputError->resolutionHeight = '請填寫分辨率高度';

$lang->conference->server = '服務配置';
$lang->conference->video  = '視頻配置';

$lang->conference->resolutionWidth     = '分辨率寬度';
$lang->conference->resolutionWidthTip  = '單位：像素，建議值：最低 320 最高 1280';
$lang->conference->resolutionHeight    = '分辨率高度';
$lang->conference->resolutionHeightTip = '單位：像素，建議值：最低 240 最高 720';

$lang->conference->placeholder                   = new stdClass();
$lang->conference->placeholder->resolutionWidth  = '640';
$lang->conference->placeholder->resolutionHeight = '480';

$lang->conference->settingsError = new stdclass();
$lang->conference->settingsError->hasOpenConferences = '保存失敗，存在用戶正在會議';

$lang->conference->detachedConferenceModal = new stdclass();
$lang->conference->detachedConferenceModal->title               = '喧喧會議解決方案';
$lang->conference->detachedConferenceModal->freeVersionTitle    = '免費版';
$lang->conference->detachedConferenceModal->freeVersionTip      = '基礎功能全覆蓋';
$lang->conference->detachedConferenceModal->freeFeature         = '基礎功能：';
$lang->conference->detachedConferenceModal->freeButton          = '更多詳情';
$lang->conference->detachedConferenceModal->enhanceVersionTitle = '增強版';
$lang->conference->detachedConferenceModal->enhanceVersionTip   = '玩轉會議更靈活';
$lang->conference->detachedConferenceModal->enhanceFeature      = '增強功能：';
$lang->conference->detachedConferenceModal->commingSoon         = '敬請期待：';
$lang->conference->detachedConferenceModal->enhanceButton       = '購買諮詢';

$lang->conference->detachedConferenceModal->feature = new stdclass();
$lang->conference->detachedConferenceModal->feature->free           = '所有免費功能';
$lang->conference->detachedConferenceModal->feature->audio          = '語音通話';
$lang->conference->detachedConferenceModal->feature->video          = '視頻通話';
$lang->conference->detachedConferenceModal->feature->shareScreen    = '桌面共享屏幕';
$lang->conference->detachedConferenceModal->feature->status         = '語音、視頻狀態實時標識';
$lang->conference->detachedConferenceModal->feature->layout         = '多種會議佈局';
$lang->conference->detachedConferenceModal->feature->limitedNumber  = '一場會議最多 3 人';
$lang->conference->detachedConferenceModal->feature->unlimitNumber  = '無人數限制';
$lang->conference->detachedConferenceModal->feature->detached       = '脫離會話的自由式會議';
$lang->conference->detachedConferenceModal->feature->flexibility    = '更靈活的入會方式';
$lang->conference->detachedConferenceModal->feature->moreInfo       = '更全面的會議信息';
$lang->conference->detachedConferenceModal->feature->independentApp = '獨立會議應用';
$lang->conference->detachedConferenceModal->feature->appointMeeting = '預約會議功能';
