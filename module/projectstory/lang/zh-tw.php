<?php
/* Field. */
$lang->projectstory->project = "{$lang->projectCommon}ID";
$lang->projectstory->product = "{$lang->productCommon}ID";
$lang->projectstory->story   = "需求ID";
$lang->projectstory->version = "版本";
$lang->projectstory->order   = "排序";

$lang->projectstory->storyCommon = $lang->projectCommon . '需求';
$lang->projectstory->storyList   = $lang->projectCommon . '需求列表';
$lang->projectstory->storyView   = $lang->projectCommon . '需求詳情';

$lang->projectstory->common            = "{$lang->projectCommon}{$lang->SRCommon}";
$lang->projectstory->index             = "{$lang->SRCommon}主頁";
$lang->projectstory->view              = "{$lang->SRCommon}詳情";
$lang->projectstory->story             = "{$lang->SRCommon}列表";
$lang->projectstory->track             = '矩陣';
$lang->projectstory->linkStory         = '關聯' . $lang->SRCommon;
$lang->projectstory->unlinkStory       = '移除' . $lang->SRCommon;
$lang->projectstory->batchUnlinkStory  = '批量移除' . $lang->SRCommon;
$lang->projectstory->importplanstories = '按計劃關聯' . $lang->SRCommon;
$lang->projectstory->trackAction       = '跟蹤矩陣';
$lang->projectstory->confirm           = '確定';

/* Notice. */
$lang->projectstory->whyNoStories   = "看起來沒有{$lang->SRCommon}可以關聯。請檢查下{$lang->projectCommon}關聯的{$lang->productCommon}中有沒有{$lang->SRCommon}，而且要確保它們已經審核通過。";
$lang->projectstory->batchUnlinkTip = "其他需求已經移除，如下需求已與該{$lang->projectCommon}下執行相關聯，請從執行中移除後再操作。";

$lang->projectstory->featureBar['story']['allstory']          = '全部';
$lang->projectstory->featureBar['story']['unclosed']          = '未關閉';
$lang->projectstory->featureBar['story']['draft']             = '草稿';
$lang->projectstory->featureBar['story']['reviewing']         = '評審中';
$lang->projectstory->featureBar['story']['changing']          = '變更中';
$lang->projectstory->featureBar['story']['closed']            = '已關閉';
$lang->projectstory->featureBar['story']['linkedExecution']   = '已關聯' . $lang->execution->common;
$lang->projectstory->featureBar['story']['unlinkedExecution'] = '未關聯' . $lang->execution->common;
