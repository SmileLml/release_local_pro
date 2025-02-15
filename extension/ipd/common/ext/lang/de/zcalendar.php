<?php
/**
 * The lang file of calendar module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     calendar
 * @version     $Id$
 * @link        http://www.zentao.net
 */
$lang->execution->menu->view['subMenu']->calendar = array('link' => 'Calendar|execution|calendar|executionID=%s', 'alias' => 'calendar');

$lang->execution->menu->effort = array('link' => 'Effort|execution|effortcalendar|executionID=%s', 'alias' => 'effort');

$lang->system->menu->todo    = 'Calendar|company|todo|';
$lang->system->menu->effort  = array('link' => 'Effort|company|calendar|', 'alias' => 'effort');

if(!isset($lang->effort))$lang->effort = new stdclass();
$lang->my->menuOrder[11]    = 'effort';
$lang->system->menuOrder[6] = 'todo';
$lang->system->menuOrder[7] = 'effort';

$lang->today = 'Today';
$lang->textNetworkError = 'Network Error';
$lang->textHasMoreItems = 'There are {0} items...';

$lang->project->noMultiple->scrum->menu->effort  = $lang->execution->menu->effort;
$lang->project->noMultiple->scrum->menuOrder[31] = 'effort';
