<?php
$lang->block->flowchart            = array();
$lang->block->flowchart['admin']   = array('管理員', '維護公司', '添加用戶', '維護權限');
$lang->block->flowchart['project'] = array('項目負責人', '創建項目', '維護團隊', "維護目標", '創建看板');
$lang->block->flowchart['dev']     = array('執行人員', '創建任務', '認領任務', '執行任務');

$lang->block->undone   = '未完成';
$lang->block->delaying = '即將到期';
$lang->block->delayed  = '已延期';

$lang->block->titleList['scrumlist'] = '看板列表';
$lang->block->titleList['sprint']    = '看板總覽';

$lang->block->myTask = '我的任務';

$lang->block->finishedTasks = '完成的任務數';

$lang->block->story = '目標';

$lang->block->storyCount = '目標數';

/* unset contribute and projectteam. */
unset($lang->block->default['full']['my']['9']);
unset($lang->block->default['full']['my']['6']);

$lang->block->default['full']['my']['5']['title']  = '看板列表';
$lang->block->default['full']['my']['5']['block']  = 'scrumlist';
$lang->block->default['full']['my']['5']['source'] = 'execution';

$lang->block->default['full']['my']['5']['params']['type']    = 'doing';
$lang->block->default['full']['my']['5']['params']['orderBy'] = 'id_desc';
$lang->block->default['full']['my']['5']['params']['count']   = '15';

$lang->block->modules['kanban']['index'] = new stdclass();
$lang->block->modules['kanban']['index']->availableBlocks = new stdclass();
$lang->block->modules['kanban']['index']->availableBlocks->scrumoverview  = "{$lang->projectCommon}概況";
$lang->block->modules['kanban']['index']->availableBlocks->scrumlist      = $lang->executionCommon . '列表';
$lang->block->modules['kanban']['index']->availableBlocks->sprint         = $lang->executionCommon . '總覽';
$lang->block->modules['kanban']['index']->availableBlocks->projectdynamic = '最新動態';

$lang->block->modules['project']->availableBlocks = new stdclass();
$lang->block->modules['project']->availableBlocks->project = "{$lang->projectCommon}列表";

$lang->block->modules['execution'] = new stdclass();
$lang->block->modules['execution']->availableBlocks = new stdclass();
$lang->block->modules['execution']->availableBlocks->statistic = $lang->execution->common . '統計';
$lang->block->modules['execution']->availableBlocks->overview  = $lang->execution->common . '總覽';
$lang->block->modules['execution']->availableBlocks->list      = $lang->execution->common . '列表';
$lang->block->modules['execution']->availableBlocks->task      = '任務列表';

unset($lang->block->moduleList['product']);
unset($lang->block->moduleList['qa']);
