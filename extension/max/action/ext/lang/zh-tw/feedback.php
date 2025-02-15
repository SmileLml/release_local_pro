<?php
$lang->action->objectTypes['feedback'] = '反饋';
$lang->action->objectTypes['ticket']   = '工單';

$lang->action->label->feedback = '反饋|feedback|view|id=%s';
$lang->action->label->ticket   = '工單|ticket|view|id=%s';

$lang->action->desc->asked        = '$date, 由 <strong>$actor</strong> 追問。' . "\n";
$lang->action->desc->replied      = '$date, 由 <strong>$actor</strong> 回覆。' . "\n";
$lang->action->desc->tobug        = '$date, 由 <strong>$actor</strong> 轉為Bug <strong>$extra</strong>。' . "\n";
$lang->action->desc->tostory      = '$date, 由 <strong>$actor</strong> 轉為' . $lang->SRCommon . ' <strong>$extra</strong>。' . "\n";
$lang->action->desc->touserstory  = '$date, 由 <strong>$actor</strong> 轉為' . $lang->URCommon . ' <strong>$extra</strong>。' . "\n";
$lang->action->desc->totask       = '$date, 由 <strong>$actor</strong> 轉為任務 <strong>$extra</strong>。' . "\n";
$lang->action->desc->fromfeedback = '$date, 由 <strong>$actor</strong> 從<strong>反饋</strong>轉化而來，反饋編號為 <strong>$extra</strong>。' . "\n";
$lang->action->desc->fromticket   = '$date, 由 <strong>$actor</strong> 從<strong>工單</strong>轉化而來，工單編號為 <strong>$extra</strong>。' . "\n";
$lang->action->desc->totodo       = '$date, 由 <strong>$actor</strong> 轉待辦 <strong>$extra</strong>。' . "\n";
$lang->action->desc->toticket     = '$date, 由 <strong>$actor</strong> 轉為工單 <strong>$extra</strong>。' . "\n";
$lang->action->desc->todemand     = '$date, 由 <strong>$actor</strong> 轉為需求池需求 <strong>$extra</strong>。' . "\n";
$lang->action->desc->processed    = '$date, 由系統更新狀態為已處理。' . "\n";
$lang->action->desc->syncmodule   = '$date, 合併到模組 <strong>$extra</strong>。' . "\n";

$lang->action->label->asked        = '追問了';
$lang->action->label->replied      = '回覆了';
$lang->action->label->tobug        = '轉bug';
$lang->action->label->tostory      = '轉' . $lang->SRCommon;
$lang->action->label->touserstory  = '轉' . $lang->URCommon;
$lang->action->label->totask       = '轉任務';
$lang->action->label->totodo       = '轉待辦';
$lang->action->label->toticket     = '轉工單';
$lang->action->label->todemand     = '轉需求池需求';
$lang->action->label->fromfeedback = '由反饋創建';
$lang->action->label->syncmodule   = '合併了模組';
$lang->action->label->fromticket   = '由工單創建';
