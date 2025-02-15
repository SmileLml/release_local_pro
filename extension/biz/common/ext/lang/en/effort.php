<?php
$lang->effort = new stdclass();

$lang->my->menu->effort = array('link' => 'Effort|effort|calendar|', 'exclude' => 'my-todo');

/* Insert effort into $lang->user->menu.*/
$lang->system->menu->effort = array('link' => 'Effort|company|effort', 'subModule' => 'effort');

$lang->my->menuOrder[11]        = 'effort';
$lang->execution->menuOrder[44] = 'effort';
$lang->system->menuOrder[16]    = 'effort';

$lang->execution->menu->view['subMenu']->taskeffort = 'Task Effort|execution|taskeffort|executionID=%s';
