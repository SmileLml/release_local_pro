<?php
$lang->effort = new stdclass();

$lang->my->menu->effort = array('link' => '日志|effort|calendar|', 'exclude' => 'my-todo');

/* Insert effort into $lang->user->menu.*/
$lang->system->menu->effort = array('link' => '日志|company|effort', 'subModule' => 'effort');

$lang->my->menuOrder[11]        = 'effort';
$lang->execution->menuOrder[44] = 'effort';
$lang->system->menuOrder[16]    = 'effort';

$lang->execution->menu->view['subMenu']->taskeffort = '工时明细表|execution|taskeffort|executionID=%s';
