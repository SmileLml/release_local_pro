<?php
$lang->action->objectTypes['feedback'] = '反馈';
$lang->action->objectTypes['ticket']   = '工单';

$lang->action->label->feedback = '反馈|feedback|view|id=%s';
$lang->action->label->ticket   = '工单|ticket|view|id=%s';

$lang->action->desc->asked        = '$date, 由 <strong>$actor</strong> 追问。' . "\n";
$lang->action->desc->replied      = '$date, 由 <strong>$actor</strong> 回复。' . "\n";
$lang->action->desc->tobug        = '$date, 由 <strong>$actor</strong> 转为Bug <strong>$extra</strong>。' . "\n";
$lang->action->desc->tostory      = '$date, 由 <strong>$actor</strong> 转为' . $lang->SRCommon . ' <strong>$extra</strong>。' . "\n";
$lang->action->desc->touserstory  = '$date, 由 <strong>$actor</strong> 转为' . $lang->URCommon . ' <strong>$extra</strong>。' . "\n";
$lang->action->desc->totask       = '$date, 由 <strong>$actor</strong> 转为任务 <strong>$extra</strong>。' . "\n";
$lang->action->desc->fromfeedback = '$date, 由 <strong>$actor</strong> 从<strong>反馈</strong>转化而来，反馈编号为 <strong>$extra</strong>。' . "\n";
$lang->action->desc->fromticket   = '$date, 由 <strong>$actor</strong> 从<strong>工单</strong>转化而来，工单编号为 <strong>$extra</strong>。' . "\n";
$lang->action->desc->totodo       = '$date, 由 <strong>$actor</strong> 转待办 <strong>$extra</strong>。' . "\n";
$lang->action->desc->toticket     = '$date, 由 <strong>$actor</strong> 转为工单 <strong>$extra</strong>。' . "\n";
$lang->action->desc->todemand     = '$date, 由 <strong>$actor</strong> 转为需求池需求 <strong>$extra</strong>。' . "\n";
$lang->action->desc->processed    = '$date, 由系统更新状态为已处理。' . "\n";
$lang->action->desc->syncmodule   = '$date, 合并到模块 <strong>$extra</strong>。' . "\n";

$lang->action->label->asked        = '追问了';
$lang->action->label->replied      = '回复了';
$lang->action->label->tobug        = '转bug';
$lang->action->label->tostory      = '转' . $lang->SRCommon;
$lang->action->label->touserstory  = '转' . $lang->URCommon;
$lang->action->label->totask       = '转任务';
$lang->action->label->totodo       = '转待办';
$lang->action->label->toticket     = '转工单';
$lang->action->label->todemand     = '转需求池需求';
$lang->action->label->fromfeedback = '由反馈创建';
$lang->action->label->syncmodule   = '合并了模块';
$lang->action->label->fromticket   = '由工单创建';
