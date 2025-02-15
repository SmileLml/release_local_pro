<?php
$lang->effort = new stdclass();

$lang->my->menu->effort = array('link' => '日誌|effort|calendar|', 'exclude' => 'my-todo');

/* Insert effort into $lang->user->menu.*/
$lang->system->menu->effort = array('link' => '日誌|company|effort', 'subModule' => 'effort');

$lang->my->menuOrder[11]        = 'effort';
$lang->execution->menuOrder[44] = 'effort';
$lang->system->menuOrder[16]    = 'effort';

$lang->execution->menu->view['subMenu']->taskeffort = '工時明細表|execution|taskeffort|executionID=%s';
