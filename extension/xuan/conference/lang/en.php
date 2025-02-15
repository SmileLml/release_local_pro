<?php
$lang->conference->common                       = 'Conference';
$lang->conference->admin                        = 'Settings';
$lang->conference->notset                       = 'Not set';
$lang->conference->settings                     = 'Conference params';
$lang->conference->enabled                      = 'Conference';
$lang->conference->enabledTip                   = 'Enable conference';
$lang->conference->serverAddr                   = 'Server Address';
$lang->conference->serverAddrTip                = '';
$lang->conference->apiPort                      = 'API Port';
$lang->conference->apiPortOwtTip                = '3004 by default.';
$lang->conference->apiPortSrsTip                = '1985 by default.';
$lang->conference->mgmtPort                     = 'OWT Management Port';
$lang->conference->mgmtPortTip                  = '3300 by default';
$lang->conference->rtcPort                      = 'SRS Signaling Port';
$lang->conference->rtcPortTip                   = '1989 by default';
$lang->conference->https                        = 'Enable HTTPS';
$lang->conference->httpsTip                     = 'This feature is disabled by default on SRS server';
$lang->conference->serviceId                    = 'OWT ID';
$lang->conference->serviceIdTip                 = '';
$lang->conference->serviceKey                   = 'OWT Key';
$lang->conference->serviceKeyTip                = '';
$lang->conference->configGuideTip               = '';
$lang->conference->detachedConference           = 'New enhanced conference';
$lang->conference->detachedConferenceUrl        = 'The enhanced conferencing package';
$lang->conference->detachedConferenceTip        = ' requires you to deploy SRS audio and video services.';
$lang->conference->detachedConferencewarning    = 'Enabling the new version of the conference requires upgrading the clients of all users, the old version of the client conference will no longer be used, is it sure to enable the enhanced conference?';

$lang->conference->setupTitle       = 'Conference Server Setup';
$lang->conference->setupDescription = 'Xuanxuan need to setup conference server for providing conference feature. The conference server has two types: OWT and SRS. For now SRS is recommended.';
$lang->conference->setupDoc         = 'Setup';
$lang->conference->configDoc        = 'Configuration';
$lang->conference->download         = 'Download';
$lang->conference->srsSetupTitle    = 'SRS Server';
$lang->conference->owtSetupTitle    = 'OWT Server';

$lang->conference->backend = new stdclass();
$lang->conference->backend->type  = 'Backend Type';
$lang->conference->backend->types = array('owt' => 'OWT', 'srs' => 'SRS');

$lang->conference->inputError = new stdClass();
$lang->conference->inputError->serviceId        = 'Service ID cannot be empty';
$lang->conference->inputError->serviceKey       = 'Service Key cannot be empty';
$lang->conference->inputError->serverAddr       = 'Server address cannot be empty';
$lang->conference->inputError->apiPort          = 'API port cannot be empty';
$lang->conference->inputError->mgmtPort         = 'Management port cannot be empty';
$lang->conference->inputError->rtcPort          = 'Signaling port cannot be empty';
$lang->conference->inputError->resolutionWidth  = 'Resolution width can not be empty';
$lang->conference->inputError->resolutionHeight = 'Resolution height can not be empty';

$lang->conference->server = 'Server settings';
$lang->conference->video  = 'Video settings';

$lang->conference->resolutionWidth     = 'Resolution Width';
$lang->conference->resolutionWidthTip  = 'px, recommended value 320-1280';
$lang->conference->resolutionHeight    = 'Resolution Height';
$lang->conference->resolutionHeightTip = 'px, recommended value 240-720';

$lang->conference->placeholder                   = new stdClass();
$lang->conference->placeholder->resolutionWidth  = '640';
$lang->conference->placeholder->resolutionHeight = '480';

$lang->conference->settingsError = new stdclass();
$lang->conference->settingsError->hasOpenConferences = 'There are ongoing conferences, please close them first.';

$lang->conference->detachedConferenceModal = new stdclass();
$lang->conference->detachedConferenceModal->title               = 'Xuan Conference Solutions';
$lang->conference->detachedConferenceModal->freeVersionTitle    = 'Free Version';
$lang->conference->detachedConferenceModal->freeVersionTip      = 'Basic features fully covered';
$lang->conference->detachedConferenceModal->freeFeature         = 'Basic features:';
$lang->conference->detachedConferenceModal->freeButton          = 'More Details';
$lang->conference->detachedConferenceModal->enhanceVersionTitle = 'Enhanced Version';
$lang->conference->detachedConferenceModal->enhanceVersionTip   = 'More flexible conference management';
$lang->conference->detachedConferenceModal->enhanceFeature      = 'Enhanced features:';
$lang->conference->detachedConferenceModal->commingSoon         = 'Coming soon:';
$lang->conference->detachedConferenceModal->enhanceButton       = 'Purchase';

$lang->conference->detachedConferenceModal->feature = new stdclass();
$lang->conference->detachedConferenceModal->feature->free           = 'All free version features';
$lang->conference->detachedConferenceModal->feature->audio          = 'Audio Call';
$lang->conference->detachedConferenceModal->feature->video          = 'Video Call';
$lang->conference->detachedConferenceModal->feature->shareScreen    = 'Screen Sharing';
$lang->conference->detachedConferenceModal->feature->status         = 'Real-time status indication for audio and video';
$lang->conference->detachedConferenceModal->feature->layout         = 'Multiple meeting layouts';
$lang->conference->detachedConferenceModal->feature->limitedNumber  = 'Up to 3 participants per meeting';
$lang->conference->detachedConferenceModal->feature->unlimitNumber  = 'No limit on participants';
$lang->conference->detachedConferenceModal->feature->detached       = 'Freestyle meetings detached from conversation';
$lang->conference->detachedConferenceModal->feature->flexibility    = 'More flexible ways to join a meeting';
$lang->conference->detachedConferenceModal->feature->moreInfo       = 'More comprehensive meeting information';
$lang->conference->detachedConferenceModal->feature->independentApp = 'Independent meeting application';
$lang->conference->detachedConferenceModal->feature->appointMeeting = 'Scheduled meeting feature';
