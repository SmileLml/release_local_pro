<?php
$lang->action->objectTypes['waterfail'] = '瀑布/IPD' . $lang->projectCommon . '審批';

$lang->action->label->retracted           = "撤回了用戶需求";
$lang->action->label->retractclosed       = '執行撤回操作關閉了';
$lang->action->search->label['retracted'] = $lang->action->label->retracted;

$lang->action->dynamicAction->story['retracted']  = "撤回需求";

$lang->action->label->confirmedretract = "確認用戶需求撤銷操作";
$lang->action->label->confirmedunlink  = "確認用戶需求移除操作";

$lang->action->desc->confirmedretract  = '$date, 由 <strong>$actor</strong> 確認用戶需求撤銷操作。';
$lang->action->desc->confirmedunlink   = '$date, 由 <strong>$actor</strong> 確認用戶需求移除操作。';
