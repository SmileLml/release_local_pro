<?php
$lang->action->objectTypes['waterfail'] = '瀑布/IPD' . $lang->projectCommon . '审批';

$lang->action->label->retracted           = "撤回了用户需求";
$lang->action->label->retractclosed       = '执行撤回操作关闭了';
$lang->action->search->label['retracted'] = $lang->action->label->retracted;

$lang->action->dynamicAction->story['retracted']  = "撤回需求";

$lang->action->label->confirmedretract = "确认用户需求撤销操作";
$lang->action->label->confirmedunlink  = "确认用户需求移除操作";

$lang->action->desc->confirmedretract  = '$date, 由 <strong>$actor</strong> 确认用户需求撤销操作。';
$lang->action->desc->confirmedunlink   = '$date, 由 <strong>$actor</strong> 确认用户需求移除操作。';
