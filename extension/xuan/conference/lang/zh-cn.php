<?php
$lang->conference->common                       = '音视频';
$lang->conference->admin                        = '音视频参数设置';
$lang->conference->notset                       = '未设置';
$lang->conference->settings                     = '音视频参数配置';
$lang->conference->enabled                      = '会议功能';
$lang->conference->enabledTip                   = '开启会议功能';
$lang->conference->serverAddr                   = '音视频服务器地址';
$lang->conference->serverAddrTip                = '';
$lang->conference->apiPort                      = '音视频 API 端口';
$lang->conference->apiPortOwtTip                = '默认为 3004';
$lang->conference->apiPortSrsTip                = '默认为 1985';
$lang->conference->mgmtPort                     = 'OWT 管理端口';
$lang->conference->mgmtPortTip                  = '默认为 3300';
$lang->conference->rtcPort                      = 'SRS 信令端口';
$lang->conference->rtcPortTip                   = '默认为 1989';
$lang->conference->https                        = '是否启用 HTTPS';
$lang->conference->httpsTip                     = 'SRS 默认部署关闭 HTTPS';
$lang->conference->serviceId                    = 'OWT ID';
$lang->conference->serviceIdTip                 = '';
$lang->conference->serviceKey                   = 'OWT 密钥';
$lang->conference->serviceKeyTip                = '';
$lang->conference->configGuideTip               = '';
$lang->conference->backendTypeTip               = '';
$lang->conference->detachedConference           = '增强版会议';
$lang->conference->detachedConferenceUrl        = '增强版会议功能包';
$lang->conference->detachedConferenceTip        = ' 需要您部署SRS音视频服务';
$lang->conference->detachedConferencewarning    = '启用新版会议需要将系统用户的客户端进行升级，旧版客户端会议将不再使用，是否确定启用增强版会议？';

$lang->conference->setupTitle       = '音视频服务器部署指南';
$lang->conference->setupDescription = '喧喧提供音频会议功能，需要部署额外的音视频服务端。音视频服务器端分为 OWT 和 SRS ，推荐使用 SRS 音视频服务端。';
$lang->conference->setupDoc         = '部署文档';
$lang->conference->configDoc        = '配置文档';
$lang->conference->download         = '下载地址';
$lang->conference->srsSetupTitle    = 'SRS 音视频服务端';
$lang->conference->owtSetupTitle    = 'OWT 音视频服务端';


$lang->conference->backend = new stdclass();
$lang->conference->backend->type  = '后端类型';
$lang->conference->backend->types = array('owt' => 'OWT', 'srs' => 'SRS');

$lang->conference->inputError = new stdClass();
$lang->conference->inputError->serviceId        = 'OWT ID 不能为空';
$lang->conference->inputError->serviceKey       = 'OWT 密钥不能为空';
$lang->conference->inputError->serverAddr       = '服务器地址不能为空';
$lang->conference->inputError->apiPort          = 'API 端口不能为空';
$lang->conference->inputError->mgmtPort         = 'OWT 管理端口不能为空';
$lang->conference->inputError->rtcPort          = '信令端口不能为空';
$lang->conference->inputError->resolutionWidth  = '请填写分辨率宽度';
$lang->conference->inputError->resolutionHeight = '请填写分辨率高度';

$lang->conference->server = '服务配置';
$lang->conference->video  = '视频配置';

$lang->conference->resolutionWidth     = '分辨率宽度';
$lang->conference->resolutionWidthTip  = '单位：像素，建议值：最低 320 最高 1280';
$lang->conference->resolutionHeight    = '分辨率高度';
$lang->conference->resolutionHeightTip = '单位：像素，建议值：最低 240 最高 720';

$lang->conference->placeholder                   = new stdClass();
$lang->conference->placeholder->resolutionWidth  = '640';
$lang->conference->placeholder->resolutionHeight = '480';

$lang->conference->settingsError = new stdclass();
$lang->conference->settingsError->hasOpenConferences = '保存失败，存在用户正在会议';

$lang->conference->detachedConferenceModal = new stdclass();
$lang->conference->detachedConferenceModal->title               = '喧喧会议解决方案';
$lang->conference->detachedConferenceModal->freeVersionTitle    = '免费版';
$lang->conference->detachedConferenceModal->freeVersionTip      = '基础功能全覆盖';
$lang->conference->detachedConferenceModal->freeFeature         = '基础功能：';
$lang->conference->detachedConferenceModal->freeButton          = '更多详情';
$lang->conference->detachedConferenceModal->enhanceVersionTitle = '增强版';
$lang->conference->detachedConferenceModal->enhanceVersionTip   = '玩转会议更灵活';
$lang->conference->detachedConferenceModal->enhanceFeature      = '增强功能：';
$lang->conference->detachedConferenceModal->commingSoon         = '敬请期待：';
$lang->conference->detachedConferenceModal->enhanceButton       = '购买咨询';

$lang->conference->detachedConferenceModal->feature = new stdclass();
$lang->conference->detachedConferenceModal->feature->free           = '所有免费功能';
$lang->conference->detachedConferenceModal->feature->audio          = '语音通话';
$lang->conference->detachedConferenceModal->feature->video          = '视频通话';
$lang->conference->detachedConferenceModal->feature->shareScreen    = '桌面共享屏幕';
$lang->conference->detachedConferenceModal->feature->status         = '语音、视频状态实时标识';
$lang->conference->detachedConferenceModal->feature->layout         = '多种会议布局';
$lang->conference->detachedConferenceModal->feature->limitedNumber  = '一场会议最多 3 人';
$lang->conference->detachedConferenceModal->feature->unlimitNumber  = '无人数限制';
$lang->conference->detachedConferenceModal->feature->detached       = '脱离会话的自由式会议';
$lang->conference->detachedConferenceModal->feature->flexibility    = '更灵活的入会方式';
$lang->conference->detachedConferenceModal->feature->moreInfo       = '更全面的会议信息';
$lang->conference->detachedConferenceModal->feature->independentApp = '独立会议应用';
$lang->conference->detachedConferenceModal->feature->appointMeeting = '预约会议功能';
