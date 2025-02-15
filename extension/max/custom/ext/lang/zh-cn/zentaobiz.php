<?php
$lang->custom->libreOffice       = 'Office转换设置';
$lang->custom->libreOfficeTurnon = '功能开关';
$lang->custom->type              = '类型';
$lang->custom->libreOfficePath   = 'soffice路径';
$lang->custom->collaboraPath     = 'Collabora路径';

$lang->custom->errorSofficePath   = 'soffice文件不存在';
$lang->custom->errorRunSoffice    = "程序运行失败。错误信息：%s";
$lang->custom->errorRunCollabora  = "连接Collabora失败，请确认Collabora是否配置正确，或者网络是否能成功访问。";
$lang->custom->cannotUseCollabora = "要使用Collabora，必须配置禅道系统为静态访问。";

$lang->custom->turnonList[1] = '开启';
$lang->custom->turnonList[0] = '关闭';

$lang->custom->typeList['libreoffice'] = 'LibreOffice';
$lang->custom->typeList['collabora']   = 'Collabora Online';

$lang->custom->sofficePlaceholder   = '填写LibreOffice中的soffice文件路径。如 /opt/libreoffice/program/soffice';
$lang->custom->collaboraPlaceholder = '填写Collabora的访问URL，如 https://127.0.0.1:9980';

$lang->custom->feedback = new stdclass();
$lang->custom->feedback->fields['required']         = $lang->custom->required;
$lang->custom->feedback->fields['review']           = '评审流程';
$lang->custom->feedback->fields['closedReasonList'] = '关闭原因';
$lang->custom->feedback->fields['typeList']         = '反馈类型';
$lang->custom->feedback->fields['priList']          = '优先级';

$lang->custom->ticket = new stdclass();
$lang->custom->ticket->fields['required'] = $lang->custom->required;
$lang->custom->ticket->fields['priList']  = '优先级';
$lang->custom->ticket->fields['typeList'] = '工单类型';
