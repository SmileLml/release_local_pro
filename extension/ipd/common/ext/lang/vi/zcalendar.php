<?php
/**
 * The lang file of calendar module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license  business(商业软件)
 * @author   Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package  calendar
 * @version  $Id$
 * @link  http://www.zentao.net
 */
$lang->execution->menu->view['alias'] .= ',calendar';

$lang->execution->subMenu->view->calendar = array('link' => 'Calendar|execution|calendar|executionID=%s', 'alias' => 'calendar');

$lang->execution->menu->effort = array('link' => 'Chấm công|execution|effortcalendar|executionID=%s', 'alias' => 'effort');

$lang->company->menu->todo = 'Việc làm|company|todo|';
$lang->company->menu->effort  = array('link' => 'Chấm công|company|calendar|', 'alias' => 'effort');

if(!isset($lang->effort))$lang->effort = new stdclass();
if(!isset($lang->effort->menuOrder))$lang->effort->menuOrder = new stdclass();
$lang->my->menuOrder[11]   = 'effort';
$lang->company->menuOrder[16] = 'todo';
$lang->company->menuOrder[17] = 'effort';
$lang->dept->menuOrder  = $lang->company->menuOrder;
$lang->group->menuOrder    = $lang->company->menuOrder;
$lang->todo->menuOrder  = $lang->my->menuOrder;
$lang->effort->menuOrder   = $lang->my->menuOrder;
$lang->task->menuOrder  = $lang->execution->menuOrder;
$lang->build->menuOrder    = $lang->execution->menuOrder;
$lang->user->menuOrder  = $lang->company->menuOrder;

$lang->dept->menu   = $lang->company->menu;
$lang->group->menu  = $lang->company->menu;
$lang->todo->menu   = $lang->my->menu;
$lang->effort->menu = $lang->my->menu;
$lang->task->menu   = $lang->execution->menu;
$lang->build->menu  = $lang->execution->menu;
$lang->user->menu   = $lang->company->menu;

$lang->today = 'Hôm nay';
$lang->textNetworkError = 'Network Error';
$lang->textHasMoreItems = 'Có {0} items...';
