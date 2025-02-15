<?php
/**
 * The zh-tw file of block module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禪道軟件（青島）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     block
 * @version     $Id$
 * @link        http://www.zentao.net
 */
global $config;
$lang->block->id         = '編號';
$lang->block->params     = '參數';
$lang->block->name       = '區塊名稱';
$lang->block->style      = '外觀';
$lang->block->grid       = '位置';
$lang->block->color      = '顏色';
$lang->block->reset      = '恢復預設';
$lang->block->story      = '需求';
$lang->block->investment = '投入';
$lang->block->estimate   = '預計工時';
$lang->block->last       = '近期';

$lang->block->account = '所屬用戶';
$lang->block->module  = '所屬模組';
$lang->block->title   = '區塊名稱';
$lang->block->source  = '來源模組';
$lang->block->block   = '來源區塊';
$lang->block->order   = '排序';
$lang->block->height  = '高度';
$lang->block->role    = '角色';

$lang->block->lblModule    = '模組';
$lang->block->lblBlock     = '區塊';
$lang->block->lblNum       = '條數';
$lang->block->lblHtml      = 'HTML內容';
$lang->block->dynamic      = '最新動態';
$lang->block->assignToMe   = '待處理';
$lang->block->wait         = '未開始';
$lang->block->doing        = '進行中';
$lang->block->done         = '已完成';
$lang->block->lblFlowchart = '流程圖';
$lang->block->welcome      = '歡迎總覽';
$lang->block->lblTesttask  = '查看測試詳情';
$lang->block->contribute   = '我的貢獻';
$lang->block->finish       = '已完成';
$lang->block->guide        = '使用幫助';

$lang->block->leftToday           = '今天剩餘工作總計';
$lang->block->myTask              = '我的任務';
$lang->block->myStory             = "我的{$lang->SRCommon}";
$lang->block->myBug               = '我的BUG';
$lang->block->myExecution         = '未關閉的' . $lang->executionCommon;
$lang->block->myProduct           = '未關閉的' . $lang->productCommon;
$lang->block->delayed             = '已延期';
$lang->block->noData              = '當前統計類型下暫無數據';
$lang->block->emptyTip            = '暫無數據';
$lang->block->createdTodos        = '創建的待辦數';
$lang->block->createdRequirements = '創建的' . $lang->URCommon . '數';
$lang->block->createdStories      = '創建的' . $lang->SRCommon . '數';
$lang->block->finishedTasks       = '完成的任務數';
$lang->block->createdBugs         = '提交的Bug數';
$lang->block->resolvedBugs        = '解決的Bug數';
$lang->block->createdCases        = '創建的用例數';
$lang->block->createdRisks        = '創建的風險數';
$lang->block->resolvedRisks       = '解決的風險數';
$lang->block->createdIssues       = '創建的問題數';
$lang->block->resolvedIssues      = '解決的問題數';
$lang->block->createdDocs         = '創建的文檔數';
$lang->block->allExecutions       = '所有' . $lang->executionCommon;
$lang->block->doingExecution      = '進行中的' . $lang->executionCommon;
$lang->block->finishExecution     = '累積' . $lang->executionCommon;
$lang->block->estimatedHours      = '預計';
$lang->block->consumedHours       = '已消耗';
$lang->block->time                = '第';
$lang->block->week                = '周';
$lang->block->month               = '月';
$lang->block->selectProduct       = "選擇{$lang->productCommon}";
$lang->block->of                  = '的';
$lang->block->remain              = '剩餘工時';
$lang->block->allStories          = '總需求';

$lang->block->createBlock        = '添加區塊';
$lang->block->editBlock          = '編輯區塊';
$lang->block->ordersSaved        = '排序已保存';
$lang->block->confirmRemoveBlock = '確定隱藏區塊嗎？';
$lang->block->noticeNewBlock     = '10.0版本以後各個視圖主頁提供了全新的視圖，您要啟用新的視圖佈局嗎？';
$lang->block->confirmReset       = '是否恢復預設佈局？';
$lang->block->closeForever       = '永久關閉';
$lang->block->confirmClose       = '確定永久關閉該區塊嗎？關閉後所有人都將無法使用該區塊，可以在後台自定義中打開。';
$lang->block->remove             = '移除';
$lang->block->refresh            = '刷新';
$lang->block->nbsp               = '';
$lang->block->hidden             = '隱藏';
$lang->block->dynamicInfo        = "<span class='timeline-tag'>%s</span> <span class='timeline-text'>%s<span class='label-action'>%s</span>%s<a href='%s' title='%s'>%s</a></span>";
$lang->block->noLinkDynamic      = "<span class='timeline-tag'>%s</span> <span class='timeline-text' title='%s'>%s<span class='label-action'>%s</span>%s<span class='label-name'>%s</span></span>";
$lang->block->cannotPlaceInLeft  = '此區塊無法放置在左側。';
$lang->block->cannotPlaceInRight = '此區塊無法放置在右側。';
$lang->block->tutorial           = '進入新手教程';

$lang->block->productName  = $lang->productCommon . '名稱';
$lang->block->totalStory   = '總' . $lang->SRCommon;
$lang->block->totalBug     = '總Bug';
$lang->block->totalRelease = '發佈次數';
$lang->block->totalTask    = '總' . $lang->task->common;

$lang->block->totalInvestment = '總投入';
$lang->block->totalPeople     = '總人數';
$lang->block->spent           = '已花費';
$lang->block->budget          = '預算';
$lang->block->left            = '剩餘';

$lang->block->titleList['flowchart']      = '流程圖';
$lang->block->titleList['guide']          = '使用幫助';
$lang->block->titleList['statistic']      = "{$lang->projectCommon}統計";
$lang->block->titleList['recentproject']  = "我近期參與的{$lang->projectCommon}";
$lang->block->titleList['assigntome']     = '待處理';
$lang->block->titleList['projectteam']    = "{$lang->projectCommon}人力投入";
$lang->block->titleList['project']        = "{$lang->projectCommon}列表";
$lang->block->titleList['dynamic']        = '最新動態';
$lang->block->titleList['list']           = '我的待辦';
$lang->block->titleList['contribute']     = '我的貢獻';
$lang->block->titleList['scrumoverview']  = "{$lang->projectCommon}概況";
$lang->block->titleList['scrumtest']      = '待測版本';
$lang->block->titleList['scrumlist']      = '迭代列表';
$lang->block->titleList['sprint']         = '迭代總覽';
$lang->block->titleList['projectdynamic'] = '最新動態';
$lang->block->titleList['bug']            = '指派給我的Bug';
$lang->block->titleList['case']           = '指派給我的用例';
$lang->block->titleList['testtask']       = '待測版本列表';

$lang->block->default['waterfall']['project']['3']['title']  = "{$lang->projectCommon}計劃";
$lang->block->default['waterfall']['project']['3']['block']  = 'waterfallgantt';
$lang->block->default['waterfall']['project']['3']['source'] = 'project';
$lang->block->default['waterfall']['project']['3']['grid']   = 8;

$lang->block->default['waterfall']['project']['6']['title']  = '最新動態';
$lang->block->default['waterfall']['project']['6']['block']  = 'projectdynamic';
$lang->block->default['waterfall']['project']['6']['grid']   = 4;
$lang->block->default['waterfall']['project']['6']['source'] = 'project';

$lang->block->default['waterfallplus'] = $lang->block->default['waterfall'];
$lang->block->default['ipd']           = $lang->block->default['waterfall'];

$lang->block->default['scrum']['project']['1']['title'] = $lang->projectCommon . '概況';
$lang->block->default['scrum']['project']['1']['block'] = 'scrumoverview';
$lang->block->default['scrum']['project']['1']['grid']  = 8;

$lang->block->default['scrum']['project']['2']['title'] = $lang->executionCommon . '列表';
$lang->block->default['scrum']['project']['2']['block'] = 'scrumlist';
$lang->block->default['scrum']['project']['2']['grid']  = 8;

$lang->block->default['scrum']['project']['2']['params']['type']    = 'undone';
$lang->block->default['scrum']['project']['2']['params']['count']   = '20';
$lang->block->default['scrum']['project']['2']['params']['orderBy'] = 'id_desc';

$lang->block->default['scrum']['project']['3']['title'] = '待測版本';
$lang->block->default['scrum']['project']['3']['block'] = 'scrumtest';
$lang->block->default['scrum']['project']['3']['grid']  = 8;

$lang->block->default['scrum']['project']['3']['params']['type']    = 'wait';
$lang->block->default['scrum']['project']['3']['params']['count']   = '15';
$lang->block->default['scrum']['project']['3']['params']['orderBy'] = 'id_desc';

$lang->block->default['scrum']['project']['4']['title'] = $lang->executionCommon . '總覽';
$lang->block->default['scrum']['project']['4']['block'] = 'sprint';
$lang->block->default['scrum']['project']['4']['grid']  = 4;

$lang->block->default['scrum']['project']['5']['title'] = '最新動態';
$lang->block->default['scrum']['project']['5']['block'] = 'projectdynamic';
$lang->block->default['scrum']['project']['5']['grid']  = 4;
$lang->block->default['kanban']    = $lang->block->default['scrum'];
$lang->block->default['agileplus'] = $lang->block->default['scrum'];

$lang->block->default['product']['1']['title'] = $lang->productCommon . '統計';
$lang->block->default['product']['1']['block'] = 'statistic';
$lang->block->default['product']['1']['grid']  = 8;

$lang->block->default['product']['1']['params']['type']  = 'all';
$lang->block->default['product']['1']['params']['count'] = '20';

$lang->block->default['product']['2']['title'] = $lang->productCommon . '總覽';
$lang->block->default['product']['2']['block'] = 'overview';
$lang->block->default['product']['2']['grid']  = 4;

$lang->block->default['product']['3']['title'] = '未關閉的' . $lang->productCommon;
$lang->block->default['product']['3']['block'] = 'list';
$lang->block->default['product']['3']['grid']  = 8;

$lang->block->default['product']['3']['params']['count'] = 15;
$lang->block->default['product']['3']['params']['type']  = 'noclosed';

$lang->block->default['product']['4']['title'] = "指派給我的{$lang->SRCommon}";
$lang->block->default['product']['4']['block'] = 'story';
$lang->block->default['product']['4']['grid']  = 4;

$lang->block->default['product']['4']['params']['count']   = 15;
$lang->block->default['product']['4']['params']['orderBy'] = 'id_desc';
$lang->block->default['product']['4']['params']['type']    = 'assignedTo';

$lang->block->default['execution']['1']['title'] = '執行統計';
$lang->block->default['execution']['1']['block'] = 'statistic';
$lang->block->default['execution']['1']['grid']  = 8;

$lang->block->default['execution']['1']['params']['type']  = 'all';
$lang->block->default['execution']['1']['params']['count'] = '20';

$lang->block->default['execution']['2']['title'] = '執行總覽';
$lang->block->default['execution']['2']['block'] = 'overview';
$lang->block->default['execution']['2']['grid']  = 4;

$lang->block->default['execution']['3']['title'] = '未關閉的執行';
$lang->block->default['execution']['3']['block'] = 'list';
$lang->block->default['execution']['3']['grid']  = 8;

$lang->block->default['execution']['3']['params']['count']   = 15;
$lang->block->default['execution']['3']['params']['orderBy'] = 'id_desc';
$lang->block->default['execution']['3']['params']['type']    = 'undone';

$lang->block->default['execution']['4']['title'] = '指派給我的任務';
$lang->block->default['execution']['4']['block'] = 'task';
$lang->block->default['execution']['4']['grid']  = 4;

$lang->block->default['execution']['4']['params']['count']   = 15;
$lang->block->default['execution']['4']['params']['orderBy'] = 'id_desc';
$lang->block->default['execution']['4']['params']['type']    = 'assignedTo';

$lang->block->default['execution']['5']['title'] = '版本列表';
$lang->block->default['execution']['5']['block'] = 'build';
$lang->block->default['execution']['5']['grid']  = 8;

$lang->block->default['execution']['5']['params']['count']   = 15;
$lang->block->default['execution']['5']['params']['orderBy'] = 'id_desc';

$lang->block->default['qa']['1']['title'] = '測試統計';
$lang->block->default['qa']['1']['block'] = 'statistic';
$lang->block->default['qa']['1']['grid']  = 8;

$lang->block->default['qa']['1']['params']['type']  = 'noclosed';
$lang->block->default['qa']['1']['params']['count'] = '20';

//$lang->block->default['qa']['2']['title'] = '測試用例總覽';
//$lang->block->default['qa']['2']['block'] = 'overview';
//$lang->block->default['qa']['2']['grid']  = 4;

$lang->block->default['qa']['2']['title'] = '指派給我的Bug';
$lang->block->default['qa']['2']['block'] = 'bug';
$lang->block->default['qa']['2']['grid']  = 4;

$lang->block->default['qa']['2']['params']['count']   = 15;
$lang->block->default['qa']['2']['params']['orderBy'] = 'id_desc';
$lang->block->default['qa']['2']['params']['type']    = 'assignedTo';

$lang->block->default['qa']['3']['title'] = '指派給我的用例';
$lang->block->default['qa']['3']['block'] = 'case';
$lang->block->default['qa']['3']['grid']  = 4;

$lang->block->default['qa']['3']['params']['count']   = 15;
$lang->block->default['qa']['3']['params']['orderBy'] = 'id_desc';
$lang->block->default['qa']['3']['params']['type']    = 'assigntome';

$lang->block->default['qa']['4']['title'] = '待測版本列表';
$lang->block->default['qa']['4']['block'] = 'testtask';
$lang->block->default['qa']['4']['grid']  = 8;

$lang->block->default['qa']['4']['params']['count']   = 15;
$lang->block->default['qa']['4']['params']['orderBy'] = 'id_desc';
$lang->block->default['qa']['4']['params']['type']    = 'wait';

$lang->block->default['full']['my']['1']['title']  = '歡迎';
$lang->block->default['full']['my']['1']['block']  = 'welcome';
$lang->block->default['full']['my']['1']['grid']   = 8;
$lang->block->default['full']['my']['1']['source'] = '';

$lang->block->default['full']['my']['2']['title']  = '最新動態';
$lang->block->default['full']['my']['2']['block']  = 'dynamic';
$lang->block->default['full']['my']['2']['grid']   = 4;
$lang->block->default['full']['my']['2']['source'] = '';

$lang->block->default['full']['my']['3']['title']  = '使用幫助';
$lang->block->default['full']['my']['3']['block']  = 'guide';
$lang->block->default['full']['my']['3']['source'] = '';
$lang->block->default['full']['my']['3']['grid']   = 8;

$lang->block->default['full']['my']['4']['title']           = '我的待辦';
$lang->block->default['full']['my']['4']['block']           = 'list';
$lang->block->default['full']['my']['4']['grid']            = 4;
$lang->block->default['full']['my']['4']['source']          = 'todo';
$lang->block->default['full']['my']['4']['params']['count'] = '20';

$lang->block->default['full']['my']['5']['title']           = "{$lang->projectCommon}統計";
$lang->block->default['full']['my']['5']['block']           = 'statistic';
$lang->block->default['full']['my']['5']['source']          = 'project';
$lang->block->default['full']['my']['5']['grid']            = 8;
$lang->block->default['full']['my']['5']['params']['count'] = '20';

$lang->block->default['full']['my']['6']['title']  = '我的貢獻';
$lang->block->default['full']['my']['6']['block']  = 'contribute';
$lang->block->default['full']['my']['6']['source'] = '';
$lang->block->default['full']['my']['6']['grid']   = 4;

$lang->block->default['full']['my']['7']['title']  = "我近期參與的{$lang->projectCommon}";
$lang->block->default['full']['my']['7']['block']  = 'recentproject';
$lang->block->default['full']['my']['7']['source'] = 'project';
$lang->block->default['full']['my']['7']['grid']   = 8;

$lang->block->default['full']['my']['8']['title']  = '我的待處理';
$lang->block->default['full']['my']['8']['block']  = 'assigntome';
$lang->block->default['full']['my']['8']['source'] = '';
$lang->block->default['full']['my']['8']['grid']   = 8;

$lang->block->default['full']['my']['8']['params']['todoCount']     = '20';
$lang->block->default['full']['my']['8']['params']['taskCount']     = '20';
$lang->block->default['full']['my']['8']['params']['bugCount']      = '20';
$lang->block->default['full']['my']['8']['params']['riskCount']     = '20';
$lang->block->default['full']['my']['8']['params']['issueCount']    = '20';
$lang->block->default['full']['my']['8']['params']['storyCount']    = '20';
$lang->block->default['full']['my']['8']['params']['reviewCount']   = '20';
$lang->block->default['full']['my']['8']['params']['meetingCount']  = '20';
$lang->block->default['full']['my']['8']['params']['feedbackCount'] = '20';

$lang->block->default['full']['my']['9']['title']  = "{$lang->projectCommon}人力投入";
$lang->block->default['full']['my']['9']['block']  = 'projectteam';
$lang->block->default['full']['my']['9']['source'] = 'project';
$lang->block->default['full']['my']['9']['grid']   = 8;

$lang->block->default['full']['my']['10']['title']  = "{$lang->projectCommon}列表";
$lang->block->default['full']['my']['10']['block']  = 'project';
$lang->block->default['full']['my']['10']['source'] = 'project';
$lang->block->default['full']['my']['10']['grid']   = 8;

$lang->block->default['full']['my']['10']['params']['orderBy'] = 'id_desc';
$lang->block->default['full']['my']['10']['params']['count']   = '15';

/* Doc module block. */
$lang->block->default['doc']['1']['title'] = '文檔統計';
$lang->block->default['doc']['1']['block'] = 'docstatistic';
$lang->block->default['doc']['1']['grid']  = 8;

$lang->block->default['doc']['2']['title'] = '文檔動態';
$lang->block->default['doc']['2']['block'] = 'docdynamic';
$lang->block->default['doc']['2']['grid']  = 4;

$lang->block->default['doc']['3']['title'] = '我收藏的文檔';
$lang->block->default['doc']['3']['block'] = 'docmycollection';
$lang->block->default['doc']['3']['grid']  = 8;

$lang->block->default['doc']['4']['title'] = '最近更新的文檔';
$lang->block->default['doc']['4']['block'] = 'docrecentupdate';
$lang->block->default['doc']['4']['grid']  = 8;

$lang->block->default['doc']['5']['title'] = '瀏覽排行榜';
$lang->block->default['doc']['5']['block'] = 'docviewlist';
$lang->block->default['doc']['5']['grid']  = 4;

if($config->vision == 'rnd')
{
    $lang->block->default['doc']['6']['title'] = $lang->productCommon . '文檔';
    $lang->block->default['doc']['6']['block'] = 'productdoc';
    $lang->block->default['doc']['6']['grid']  = 8;

    $lang->block->default['doc']['6']['params']['count'] = '20';
}

$lang->block->default['doc']['7']['title'] = '收藏排行榜';
$lang->block->default['doc']['7']['block'] = 'doccollectlist';
$lang->block->default['doc']['7']['grid']  = 4;

$lang->block->default['doc']['8']['title'] = $lang->projectCommon . '文檔';
$lang->block->default['doc']['8']['block'] = 'projectdoc';
$lang->block->default['doc']['8']['grid']  = 8;

$lang->block->default['doc']['8']['params']['count'] = '20';

$lang->block->count   = '數量';
$lang->block->type    = '類型';
$lang->block->orderBy = '排序';

$lang->block->availableBlocks              = new stdclass();
$lang->block->availableBlocks->todo        = '日程';
$lang->block->availableBlocks->task        = '任務';
$lang->block->availableBlocks->bug         = 'Bug';
$lang->block->availableBlocks->case        = '用例';
$lang->block->availableBlocks->story       = "{$lang->SRCommon}";
$lang->block->availableBlocks->requirement = "{$lang->URCommon}";
$lang->block->availableBlocks->product     = $lang->productCommon . '列表';
$lang->block->availableBlocks->execution   = $lang->executionCommon . '列表';
$lang->block->availableBlocks->plan        = "計劃列表";
$lang->block->availableBlocks->release     = '發佈列表';
$lang->block->availableBlocks->build       = '版本列表';
$lang->block->availableBlocks->testtask    = '測試版本列表';
$lang->block->availableBlocks->risk        = '風險';
$lang->block->availableBlocks->issue       = '問題';
$lang->block->availableBlocks->meeting     = '會議';
$lang->block->availableBlocks->feedback    = '反饋';
$lang->block->availableBlocks->ticket      = '工單';

$lang->block->moduleList['product']   = $lang->productCommon;
$lang->block->moduleList['project']   = $lang->projectCommon;
$lang->block->moduleList['execution'] = $lang->execution->common;
$lang->block->moduleList['qa']        = '測試';
$lang->block->moduleList['todo']      = '待辦';
$lang->block->moduleList['doc']       = '文檔';

$lang->block->modules['project'] = new stdclass();
$lang->block->modules['project']->availableBlocks = new stdclass();
$lang->block->modules['project']->availableBlocks->project       = "{$lang->projectCommon}列表";
$lang->block->modules['project']->availableBlocks->recentproject = "近期{$lang->projectCommon}";
$lang->block->modules['project']->availableBlocks->statistic     = "{$lang->projectCommon}統計";
$lang->block->modules['project']->availableBlocks->projectteam   = "{$lang->projectCommon}人力投入";

$lang->block->modules['scrum']['index'] = new stdclass();
$lang->block->modules['scrum']['index']->availableBlocks = new stdclass();
$lang->block->modules['scrum']['index']->availableBlocks->scrumoverview  = "{$lang->projectCommon}概況";
$lang->block->modules['scrum']['index']->availableBlocks->scrumlist      = $lang->executionCommon . '列表';
$lang->block->modules['scrum']['index']->availableBlocks->sprint         = $lang->executionCommon . '總覽';
$lang->block->modules['scrum']['index']->availableBlocks->scrumtest      = '待測版本';
$lang->block->modules['scrum']['index']->availableBlocks->projectdynamic = '最新動態';

$lang->block->modules['agileplus']['index'] = $lang->block->modules['scrum']['index'];

$lang->block->modules['waterfall']['index'] = new stdclass();
$lang->block->modules['waterfall']['index']->availableBlocks = new stdclass();
$lang->block->modules['waterfall']['index']->availableBlocks->waterfallgantt    = "{$lang->projectCommon}計劃";
$lang->block->modules['waterfall']['index']->availableBlocks->projectdynamic    = '最新動態';

$lang->block->modules['waterfallplus']['index'] = $lang->block->modules['waterfall']['index'];
$lang->block->modules['ipd']['index']           = $lang->block->modules['waterfall']['index'];

$lang->block->modules['product'] = new stdclass();
$lang->block->modules['product']->availableBlocks = new stdclass();
$lang->block->modules['product']->availableBlocks->overview  = $lang->productCommon . '總覽';
if($this->config->vision != 'or')
{
    $lang->block->modules['product']->availableBlocks->statistic = $lang->productCommon . '統計';
    $lang->block->modules['product']->availableBlocks->list      = $lang->productCommon . '列表';
    $lang->block->modules['product']->availableBlocks->story     = "{$lang->SRCommon}列表";
    $lang->block->modules['product']->availableBlocks->plan      = "計劃列表";
    $lang->block->modules['product']->availableBlocks->release   = '發佈列表';
}

$lang->block->modules['execution'] = new stdclass();
$lang->block->modules['execution']->availableBlocks = new stdclass();
$lang->block->modules['execution']->availableBlocks->statistic = $lang->execution->common . '統計';
$lang->block->modules['execution']->availableBlocks->overview  = $lang->execution->common . '總覽';
$lang->block->modules['execution']->availableBlocks->list      = $lang->execution->common . '列表';
$lang->block->modules['execution']->availableBlocks->task      = '任務列表';
$lang->block->modules['execution']->availableBlocks->build     = '版本列表';

$lang->block->modules['qa'] = new stdclass();
$lang->block->modules['qa']->availableBlocks = new stdclass();
$lang->block->modules['qa']->availableBlocks->statistic = '測試統計';
//$lang->block->modules['qa']->availableBlocks->overview  = '測試用例總覽';
$lang->block->modules['qa']->availableBlocks->bug       = 'Bug列表';
$lang->block->modules['qa']->availableBlocks->case      = '用例列表';
$lang->block->modules['qa']->availableBlocks->testtask  = '版本列表';

$lang->block->modules['todo'] = new stdclass();
$lang->block->modules['todo']->availableBlocks = new stdclass();
$lang->block->modules['todo']->availableBlocks->list = '待辦列表';

$lang->block->modules['doc'] = new stdclass();
$lang->block->modules['doc']->availableBlocks = new stdclass();
$lang->block->modules['doc']->availableBlocks->docstatistic    = '文檔統計';
$lang->block->modules['doc']->availableBlocks->docdynamic      = '文檔動態';
$lang->block->modules['doc']->availableBlocks->docmycollection = '我的收藏';
$lang->block->modules['doc']->availableBlocks->docrecentupdate = '最近更新';
$lang->block->modules['doc']->availableBlocks->docviewlist     = '瀏覽排行榜';
if($config->vision == 'rnd') $lang->block->modules['doc']->availableBlocks->productdoc      = $lang->productCommon . '文檔';
$lang->block->modules['doc']->availableBlocks->doccollectlist  = '收藏排行榜';
$lang->block->modules['doc']->availableBlocks->projectdoc      = $lang->projectCommon . '文檔';

$lang->block->orderByList = new stdclass();

$lang->block->orderByList->product = array();
$lang->block->orderByList->product['id_asc']      = 'ID 遞增';
$lang->block->orderByList->product['id_desc']     = 'ID 遞減';
$lang->block->orderByList->product['status_asc']  = '狀態正序';
$lang->block->orderByList->product['status_desc'] = '狀態倒序';

$lang->block->orderByList->project = array();
$lang->block->orderByList->project['id_asc']      = 'ID 遞增';
$lang->block->orderByList->project['id_desc']     = 'ID 遞減';
$lang->block->orderByList->project['status_asc']  = '狀態正序';
$lang->block->orderByList->project['status_desc'] = '狀態倒序';

$lang->block->orderByList->execution = array();
$lang->block->orderByList->execution['id_asc']      = 'ID 遞增';
$lang->block->orderByList->execution['id_desc']     = 'ID 遞減';
$lang->block->orderByList->execution['status_asc']  = '狀態正序';
$lang->block->orderByList->execution['status_desc'] = '狀態倒序';

$lang->block->orderByList->task = array();
$lang->block->orderByList->task['id_asc']        = 'ID 遞增';
$lang->block->orderByList->task['id_desc']       = 'ID 遞減';
$lang->block->orderByList->task['pri_asc']       = '優先順序遞增';
$lang->block->orderByList->task['pri_desc']      = '優先順序遞減';
$lang->block->orderByList->task['estimate_asc']  = '預計時間遞增';
$lang->block->orderByList->task['estimate_desc'] = '預計時間遞減';
$lang->block->orderByList->task['status_asc']    = '狀態正序';
$lang->block->orderByList->task['status_desc']   = '狀態倒序';
$lang->block->orderByList->task['deadline_asc']  = '截止日期遞增';
$lang->block->orderByList->task['deadline_desc'] = '截止日期遞減';

$lang->block->orderByList->bug = array();
$lang->block->orderByList->bug['id_asc']        = 'ID 遞增';
$lang->block->orderByList->bug['id_desc']       = 'ID 遞減';
$lang->block->orderByList->bug['pri_asc']       = '優先順序遞增';
$lang->block->orderByList->bug['pri_desc']      = '優先順序遞減';
$lang->block->orderByList->bug['severity_asc']  = '級別遞增';
$lang->block->orderByList->bug['severity_desc'] = '級別遞減';

$lang->block->orderByList->case = array();
$lang->block->orderByList->case['id_asc']   = 'ID 遞增';
$lang->block->orderByList->case['id_desc']  = 'ID 遞減';
$lang->block->orderByList->case['pri_asc']  = '優先順序遞增';
$lang->block->orderByList->case['pri_desc'] = '優先順序遞減';

$lang->block->orderByList->story = array();
$lang->block->orderByList->story['id_asc']      = 'ID 遞增';
$lang->block->orderByList->story['id_desc']     = 'ID 遞減';
$lang->block->orderByList->story['pri_asc']     = '優先順序遞增';
$lang->block->orderByList->story['pri_desc']    = '優先順序遞減';
$lang->block->orderByList->story['status_asc']  = '狀態正序';
$lang->block->orderByList->story['status_desc'] = '狀態倒序';
$lang->block->orderByList->story['stage_asc']   = '階段正序';
$lang->block->orderByList->story['stage_desc']  = '階段倒序';

$lang->block->todoCount     = '待辦數';
$lang->block->taskCount     = '任務數';
$lang->block->bugCount      = 'Bug數';
$lang->block->riskCount     = '風險數';
$lang->block->issueCount    = '問題數';
$lang->block->storyCount    = '需求數';
$lang->block->reviewCount   = '審批數';
$lang->block->meetingCount  = '會議數';
$lang->block->feedbackCount = '反饋數';
$lang->block->ticketCount   = '工單數';

$lang->block->typeList = new stdclass();

$lang->block->typeList->task['assignedTo'] = '指派給我';
$lang->block->typeList->task['openedBy']   = '由我創建';
$lang->block->typeList->task['finishedBy'] = '由我完成';
$lang->block->typeList->task['closedBy']   = '由我關閉';
$lang->block->typeList->task['canceledBy'] = '由我取消';

$lang->block->typeList->bug['assignedTo'] = '指派給我';
$lang->block->typeList->bug['openedBy']   = '由我創建';
$lang->block->typeList->bug['resolvedBy'] = '由我解決';
$lang->block->typeList->bug['closedBy']   = '由我關閉';

$lang->block->typeList->case['assigntome'] = '指派給我';
$lang->block->typeList->case['openedbyme'] = '由我創建';

$lang->block->typeList->story['assignedTo'] = '指派給我';
$lang->block->typeList->story['openedBy']   = '由我創建';
$lang->block->typeList->story['reviewedBy'] = '由我評審';
$lang->block->typeList->story['closedBy']   = '由我關閉';

$lang->block->typeList->product['noclosed'] = '未關閉';
$lang->block->typeList->product['closed']   = '已關閉';
$lang->block->typeList->product['all']      = '全部';
$lang->block->typeList->product['involved'] = '我參與';

$lang->block->typeList->project['undone']   = '未完成';
$lang->block->typeList->project['doing']    = '進行中';
$lang->block->typeList->project['all']      = '全部';
$lang->block->typeList->project['involved'] = '我參與的';

$lang->block->typeList->execution['undone']   = '未完成';
$lang->block->typeList->execution['doing']    = '進行中';
$lang->block->typeList->execution['all']      = '所有';
$lang->block->typeList->execution['involved'] = '我參與';

$lang->block->typeList->scrum['undone']   = '未完成';
$lang->block->typeList->scrum['doing']    = '進行中';
$lang->block->typeList->scrum['all']      = '全部';
$lang->block->typeList->scrum['involved'] = '我參與';

$lang->block->typeList->testtask['wait']    = '待測版本';
$lang->block->typeList->testtask['doing']   = '測試中版本';
$lang->block->typeList->testtask['blocked'] = '阻塞版本';
$lang->block->typeList->testtask['done']    = '已測版本';
$lang->block->typeList->testtask['all']     = '全部';

$lang->block->welcomeList['06:00'] = '%s，早上好！';
$lang->block->welcomeList['11:30'] = '%s，中午好！';
$lang->block->welcomeList['13:30'] = '%s，下午好！';
$lang->block->welcomeList['19:00'] = '%s，晚上好！';

$lang->block->gridOptions[8] = '左側';
$lang->block->gridOptions[4] = '右側';

$lang->block->flowchart            = array();
$lang->block->flowchart['admin']   = array('管理員', '維護部門', '添加用戶', '維護權限');
if($config->systemMode == 'ALM') $lang->block->flowchart['program'] = array('項目集負責人', '創建項目集', "關聯{$lang->productCommon}", "創建{$lang->projectCommon}", "制定預算和規劃", '添加干係人');
$lang->block->flowchart['product'] = array($lang->productCommon . '經理', '創建' . $lang->productCommon, '維護模組', "維護計劃", "維護需求", '創建發佈');
$lang->block->flowchart['project'] = array('項目經理', "創建{$lang->productCommon}、" . $lang->execution->common, '維護團隊', "關聯需求", '分解任務', '跟蹤進度');
$lang->block->flowchart['dev']     = array('研發人員', '領取任務和Bug', '設計實現方案', '更新狀態', '完成任務和Bug', '提交代碼');
$lang->block->flowchart['tester']  = array('測試人員', '撰寫用例', '執行用例', '提交Bug', '驗證Bug', '關閉Bug');

$lang->block->zentaoapp = new stdclass();
$lang->block->zentaoapp->common               = '禪道移動端';
$lang->block->zentaoapp->thisYearInvestment   = '今年投入';
$lang->block->zentaoapp->sinceTotalInvestment = '從使用至今，總投入';
$lang->block->zentaoapp->myStory              = '我的需求';
$lang->block->zentaoapp->allStorySum          = '需求總數';
$lang->block->zentaoapp->storyCompleteRate    = '需求完成率';
$lang->block->zentaoapp->latestExecution      = '近期執行';
$lang->block->zentaoapp->involvedExecution    = '我參與的執行';
$lang->block->zentaoapp->mangedProduct        = "負責{$lang->productCommon}";
$lang->block->zentaoapp->involvedProject      = "參與{$lang->projectCommon}";
$lang->block->zentaoapp->customIndexCard      = '定製首頁卡片';
$lang->block->zentaoapp->createStory          = '提需求';
$lang->block->zentaoapp->createEffort         = '記日誌';
$lang->block->zentaoapp->createDoc            = '建文檔';
$lang->block->zentaoapp->createTodo           = '建待辦';
$lang->block->zentaoapp->workbench            = '工作台';
$lang->block->zentaoapp->notSupportKanban     = '移動端暫不支持研發看板模式';
$lang->block->zentaoapp->notSupportVersion    = '移動端暫不支持該禪道版本';
$lang->block->zentaoapp->incompatibleVersion  = '當前禪道版本較低，請升級至最新版本後再試';
$lang->block->zentaoapp->canNotGetVersion     = '獲取禪道版本失敗，請確認網址是否正確';
$lang->block->zentaoapp->desc                 = "禪道移動端為您提供移動辦公的環境，方便隨時管理個人待辦事務，跟進{$lang->projectCommon}進度，增強了{$lang->projectCommon}管理的靈活性和敏捷性。";
$lang->block->zentaoapp->downloadTip          = '掃瞄二維碼下載';

$lang->block->zentaoclient = new stdClass();
$lang->block->zentaoclient->common = '禪道客戶端';
$lang->block->zentaoclient->desc   = '您可以使用禪道桌面客戶端直接使用禪道，無需頻繁切換瀏覽器。除此之外，客戶端還提供了聊天，信息通知，機器人，內嵌禪道小程序等功能，團隊協作更方便。';

$lang->block->zentaoclient->edition = new stdclass();
$lang->block->zentaoclient->edition->win64   = 'Windows版';
$lang->block->zentaoclient->edition->linux64 = 'Linux版';
$lang->block->zentaoclient->edition->mac64   = 'Mac版';

$lang->block->guideTabs['flowchart']      = '流程圖';
//$lang->block->guideTabs['systemMode']     = '運行模式';
$lang->block->guideTabs['visionSwitch']   = '界面切換';
$lang->block->guideTabs['themeSwitch']    = '主題切換';
$lang->block->guideTabs['preference']     = '個性化設置';
$lang->block->guideTabs['downloadClient'] = '客戶端下載';
$lang->block->guideTabs['downloadMoblie'] = '移動端下載';

$lang->block->themes['default']    = '禪道藍';
$lang->block->themes['blue']       = '青春藍';
$lang->block->themes['green']      = '葉蘭綠';
$lang->block->themes['red']        = '赤誠紅';
$lang->block->themes['pink']       = '芙蕖粉';
$lang->block->themes['blackberry'] = '露莓黑';
$lang->block->themes['classic']    = '經典藍';
$lang->block->themes['purple']     = '玉煙紫';

$lang->block->visionTitle            = '禪道使用界面分為【研發綜合界面】和【運營管理界面】。';
$lang->block->visions['rnd']         = new stdclass();
$lang->block->visions['rnd']->key    = 'rnd';
$lang->block->visions['rnd']->title  = '研發綜合界面';
$lang->block->visions['rnd']->text   = "集項目集、{$lang->productCommon}、{$lang->projectCommon}、執行、測試等多維度管理於一體，提供全過程{$lang->projectCommon}管理解決方案。";
$lang->block->visions['lite']        = new stdclass();
$lang->block->visions['lite']->key   = 'lite';
$lang->block->visions['lite']->title = '運營管理界面';
$lang->block->visions['lite']->text  = "專為非研發團隊打造，主要以直觀、可視化的看板{$lang->projectCommon}管理模型為主。";
if($config->edition == 'ipd')
{
    $lang->block->visionTitle = '禪道使用界面分為【需求與市場管理界面】【IPD研發管理界面】和【運營管理界面】。';

    $lang->block->visions['rnd']->title = 'IPD研發管理界面';
    $lang->block->visions['rnd']->text  = "正確地做事，集項目集、{$lang->productCommon}、{$lang->projectCommon}、執行、測試等多維度管理於一體，提供全過程{$lang->projectCommon}管理解決方案。";

    $lang->block->visions['or']        = new stdclass();
    $lang->block->visions['or']->key   = 'or';
    $lang->block->visions['or']->title = '需求與市場管理界面';
    $lang->block->visions['or']->text  = "做正確的事，融合了需求池、需求、{$lang->productCommon}、路標規劃、立項等需求與市場管理功能。";
}

$lang->block->customModes['light'] = '輕量管理模式';
$lang->block->customModes['ALM']   = '全生命周期管理模式';

$lang->block->customModeTip = new stdClass();
$lang->block->customModeTip->common = '禪道運行模式分為【輕量級管理模式】和【全生命周期管理模式】。';
$lang->block->customModeTip->ALM    = '適用於中大型團隊的管理模式，概念更加完整、嚴謹，功能更豐富。';
$lang->block->customModeTip->light  = "適用於小型研發團隊的管理模式，提供{$lang->projectCommon}管理的核心功能。";
