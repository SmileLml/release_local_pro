<?php
$lang->project->approval = '審批';
$lang->project->previous = '上一步';

$lang->project->approvalflow = new stdclass();
$lang->project->approvalflow->flow   = '審批流程';
$lang->project->approvalflow->object = '審批對象';

$lang->project->approvalflow->objectList[''] = '';
$lang->project->approvalflow->objectList['stage'] = '階段';
$lang->project->approvalflow->objectList['task']  = '任務';

$lang->project->copyProjectConfirm   = '完善' . $lang->projectCommon . '信息';
$lang->project->executionInfoConfirm = '完善' . $lang->projectCommon . '信息';
$lang->project->stageInfoConfirm     = '完善階段信息';

$lang->project->executionInfoTips = "為了避免重複，請修改{$lang->executionCommon}名稱和{$lang->executionCommon}代號，設置計劃開始時間和計劃完成時間。";

$lang->project->chosenProductStage = '請為 “%s”' . $lang->productCommon . '選擇要複製的對應' . $lang->productCommon . '的階段' . $lang->productCommon . '：%s';
$lang->project->notCopyStage       = '不複製';
$lang->project->completeCopy       = '複製完成';

$lang->project->copyProject->code               = '『' . $lang->projectCommon . '』代號不可重複需要修改';
$lang->project->copyProject->select             = '選擇要複製的' . $lang->projectCommon;
$lang->project->copyProject->confirmData        = '確認要複製的數據';
$lang->project->copyProject->improveData        = '完善新' . $lang->projectCommon . '的數據';
$lang->project->copyProject->completeData       = '完成' . $lang->projectCommon . '複製';
$lang->project->copyProject->selectPlz          = '請選擇要複製的' . $lang->projectCommon;
$lang->project->copyProject->cancel             = '取消複製';
$lang->project->copyProject->all                = '全部數據';
$lang->project->copyProject->basic              = '基礎數據';
$lang->project->copyProject->allList            = array($lang->projectCommon . '自身的數據', $lang->projectCommon . '所包含的%s', $lang->projectCommon . '和%s的文檔目錄', $lang->projectCommon . '%s所包含的任務', 'QA質量保證計劃', '過程裁剪設置', '團隊成員安排與權限');
$lang->project->copyProject->ipdAllList         = array($lang->projectCommon . '自身的數據', $lang->projectCommon . '所包含的%s', $lang->projectCommon . '和%s的文檔目錄', $lang->projectCommon . '%s所包含的任務', '團隊成員安排與權限');
$lang->project->copyProject->toComplete         = '去完善';
$lang->project->copyProject->selectProjectPlz   = '請選擇' . $lang->projectCommon;
$lang->project->copyProject->confirmCopyDataTip = '請確認要複製的數據：';
$lang->project->copyProject->basicInfo          = $lang->projectCommon . '數據（所屬' . $lang->projectCommon . '集，' . $lang->projectCommon . '名稱，' . $lang->projectCommon . '代號，所屬' . $lang->productCommon . '）';
$lang->project->copyProject->selectProgram      = '請選擇' . $lang->projectCommon . '集';
$lang->project->copyProject->sprint             = $lang->executionCommon;

global $config;
if($config->systemMode == 'light') $lang->project->copyProject->basicInfo = $lang->projectCommon . '數據（' . $lang->projectCommon . '名稱，' . $lang->projectCommon . '代號，所屬' . $lang->productCommon . '）';
