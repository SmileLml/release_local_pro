<?php
$lang->action->objectTypes['feedback'] = 'Feedback';
$lang->action->objectTypes['ticket']   = 'Ticket';

$lang->action->label->feedback = 'Feedback|feedback|view|id=%s';
$lang->action->label->ticket   = 'Ticket|ticket|view|id=%s';

$lang->action->desc->asked        = '$date, doubted by <strong>$actor</strong>.' . "\n";
$lang->action->desc->replied      = '$date, replied by <strong>$actor</strong>.' . "\n";
$lang->action->desc->tobug        = '$date, converted to Bug by <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->tostory      = '$date, converted to ' . $lang->SRCommon . ' by <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->touserstory  = '$date, converted to ' . $lang->URCommon . ' by <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->totask       = '$date, converted to Task by <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->fromfeedback = '$date, created by <strong>$actor</strong> from <strong>feedback</strong>, the ID which was <strong>$extra</strong>.' . "\n";
$lang->action->desc->fromticket   = '$date, created by <strong>$actor</strong> from <strong>ticket</strong>, the ID which was <strong>$extra</strong>.' . "\n";
$lang->action->desc->totodo       = '$date, converted to todo from <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->toticket     = '$date, converted to ticket from <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->todemand     = '$date, converted to demand from  <strong>$actor</strong>. Its ID was <strong>$extra</strong>.' . "\n";
$lang->action->desc->processed    = '$date, processed by system.' . "\n";
$lang->action->desc->syncmodule   = '$date, merge to module <strong>$extra</strong>。' . "\n";

$lang->action->label->asked        = 'doubted';
$lang->action->label->replied      = 'replied';
$lang->action->label->tobug        = 'convert to bug';
$lang->action->label->tostory      = 'convert to ' . $lang->SRCommon;
$lang->action->label->touserstory  = 'convert to ' . $lang->URCommon;
$lang->action->label->totask       = 'convert to task';
$lang->action->label->totodo       = 'convert to todo';
$lang->action->label->toticket     = 'convert to ticket';
$lang->action->label->todemand     = 'convert to demand';
$lang->action->label->fromfeedback = 'from feedback';
$lang->action->label->syncmodule   = 'merged modules';
$lang->action->label->fromticket   = 'from ticket';
