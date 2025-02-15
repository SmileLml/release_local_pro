<?php
$lang->project->approval = '审批';
$lang->project->previous = '上一步';

$lang->project->approvalflow = new stdclass();
$lang->project->approvalflow->flow   = '审批流程';
$lang->project->approvalflow->object = '审批对象';

$lang->project->approvalflow->objectList[''] = '';
$lang->project->approvalflow->objectList['stage'] = '阶段';
$lang->project->approvalflow->objectList['task']  = '任务';

$lang->project->copyProjectConfirm   = '完善' . $lang->projectCommon . '信息';
$lang->project->executionInfoConfirm = '完善' . $lang->projectCommon . '信息';
$lang->project->stageInfoConfirm     = '完善阶段信息';

$lang->project->executionInfoTips = "为了避免重复，请修改{$lang->executionCommon}名称和{$lang->executionCommon}代号，设置计划开始时间和计划完成时间。";

$lang->project->chosenProductStage = '请为 “%s”' . $lang->productCommon . '选择要复制的对应' . $lang->productCommon . '的阶段' . $lang->productCommon . '：%s';
$lang->project->notCopyStage       = '不复制';
$lang->project->completeCopy       = '复制完成';

$lang->project->copyProject->code               = '『' . $lang->projectCommon . '』代号不可重复需要修改';
$lang->project->copyProject->select             = '选择要复制的' . $lang->projectCommon;
$lang->project->copyProject->confirmData        = '确认要复制的数据';
$lang->project->copyProject->improveData        = '完善新' . $lang->projectCommon . '的数据';
$lang->project->copyProject->completeData       = '完成' . $lang->projectCommon . '复制';
$lang->project->copyProject->selectPlz          = '请选择要复制的' . $lang->projectCommon;
$lang->project->copyProject->cancel             = '取消复制';
$lang->project->copyProject->all                = '全部数据';
$lang->project->copyProject->basic              = '基础数据';
$lang->project->copyProject->allList            = array($lang->projectCommon . '自身的数据', $lang->projectCommon . '所包含的%s', $lang->projectCommon . '和%s的文档目录', $lang->projectCommon . '%s所包含的任务', 'QA质量保证计划', '过程裁剪设置', '团队成员安排与权限');
$lang->project->copyProject->ipdAllList         = array($lang->projectCommon . '自身的数据', $lang->projectCommon . '所包含的%s', $lang->projectCommon . '和%s的文档目录', $lang->projectCommon . '%s所包含的任务', '团队成员安排与权限');
$lang->project->copyProject->toComplete         = '去完善';
$lang->project->copyProject->selectProjectPlz   = '请选择' . $lang->projectCommon;
$lang->project->copyProject->confirmCopyDataTip = '请确认要复制的数据：';
$lang->project->copyProject->basicInfo          = $lang->projectCommon . '数据（所属' . $lang->projectCommon . '集，' . $lang->projectCommon . '名称，' . $lang->projectCommon . '代号，所属' . $lang->productCommon . '）';
$lang->project->copyProject->selectProgram      = '请选择' . $lang->projectCommon . '集';
$lang->project->copyProject->sprint             = $lang->executionCommon;

global $config;
if($config->systemMode == 'light') $lang->project->copyProject->basicInfo = $lang->projectCommon . '数据（' . $lang->projectCommon . '名称，' . $lang->projectCommon . '代号，所属' . $lang->productCommon . '）';
