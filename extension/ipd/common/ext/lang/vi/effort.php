<?php
$lang->effort = new stdclass();

$lang->my->menu->effort = array('link' => 'Effort|my|calendar|');

/* Insert effort into $lang->user->menu.*/
$lang->user->menu->effort = array('link' => 'Chấm công|company|effort', 'subModule' => 'effort');

$lang->effort->menuOrder   = new stdclass();
$lang->my->menuOrder[11]   = 'effort';
$lang->company->menuOrder[21] = 'effort';
$lang->execution->menuOrder[44] = 'effort';
$lang->user->menuOrder[16] = 'effort';
$lang->todo->menuOrder  = $lang->my->menuOrder;
$lang->effort->menuOrder   = $lang->my->menuOrder;
$lang->task->menuOrder  = $lang->execution->menuOrder;
$lang->build->menuOrder    = $lang->execution->menuOrder;
$lang->dept->menuOrder  = $lang->company->menuOrder;
$lang->group->menuOrder    = $lang->company->menuOrder;
$lang->user->menuOrder  = $lang->company->menuOrder;

$lang->dept->menu   = $lang->company->menu;
$lang->group->menu  = $lang->company->menu;
$lang->todo->menu   = $lang->my->menu;
$lang->effort->menu = $lang->my->menu;
$lang->user->menu   = $lang->company->menu;

$lang->execution->subMenu->list = new stdclass();
$lang->execution->subMenu->list->taskeffort = 'Task Effort|execution|taskeffort|executionID=%s';
