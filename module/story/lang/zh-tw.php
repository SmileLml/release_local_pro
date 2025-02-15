<?php
/**
 * The story module zh-tw file of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禪道軟件（青島）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     story
 * @version     $Id: zh-tw.php 5141 2013-07-15 05:57:15Z chencongzhi520@gmail.com $
 * @link        http://www.zentao.net
 */
global $config;
$lang->story->create            = "提{$lang->SRCommon}";

$lang->story->requirement       = zget($lang, 'URCommon', "需求");
$lang->story->story             = zget($lang, 'SRCommon', "故事");
$lang->story->createStory       = '添加' . $lang->story->story;
$lang->story->createRequirement = '添加' . $lang->story->requirement;
$lang->story->affectedStories   = "影響的{$lang->story->story}";

$lang->story->batchCreate        = "批量創建";
$lang->story->change             = "變更";
$lang->story->changed            = "{$lang->SRCommon}變更";
$lang->story->assignTo           = '指派';
$lang->story->review             = '評審';
$lang->story->submitReview       = "提交評審";
$lang->story->recall             = '撤銷評審';
$lang->story->recallChange       = '撤銷變更';
$lang->story->recallAction       = '撤銷';
$lang->story->needReview         = '需要評審';
$lang->story->batchReview        = '批量評審';
$lang->story->edit               = "編輯";
$lang->story->editDraft          = "編輯草稿";
$lang->story->batchEdit          = "批量編輯";
$lang->story->subdivide          = '細分';
$lang->story->subdivideSR        = $lang->SRCommon . '細分';
$lang->story->link               = '關聯';
$lang->story->unlink             = '移除';
$lang->story->track              = '跟蹤矩陣';
$lang->story->trackAB            = '矩陣';
$lang->story->processStoryChange = '確認需求變動';
$lang->story->splitRequirent     = '拆分';
$lang->story->close              = '關閉';
$lang->story->batchClose         = '批量關閉';
$lang->story->activate           = '激活';
$lang->story->delete             = "刪除";
$lang->story->view               = "{$lang->SRCommon}詳情";
$lang->story->setting            = "設置";
$lang->story->tasks              = "相關任務";
$lang->story->bugs               = "相關Bug";
$lang->story->cases              = "相關用例";
$lang->story->taskCount          = '任務數';
$lang->story->bugCount           = 'Bug數';
$lang->story->caseCount          = '用例數';
$lang->story->taskCountAB        = 'T';
$lang->story->bugCountAB         = 'B';
$lang->story->caseCountAB        = 'C';
$lang->story->linkStory          = "關聯{$lang->URCommon}";
$lang->story->unlinkStory        = "移除相關{$lang->SRCommon}";
$lang->story->linkStoriesAB      = "關聯相關{$lang->SRCommon}";
$lang->story->linkRequirementsAB = "關聯相關{$lang->URCommon}";
$lang->story->export             = "導出數據";
$lang->story->zeroCase           = "零用例{$lang->SRCommon}";
$lang->story->zeroTask           = "只列零任務{$lang->SRCommon}";
$lang->story->reportChart        = "統計報表";
$lang->story->copyTitle          = "同{$lang->SRCommon}名稱";
$lang->story->batchChangePlan    = "批量修改計劃";
$lang->story->batchChangeBranch  = "批量修改分支";
$lang->story->batchChangeStage   = "批量修改階段";
$lang->story->batchAssignTo      = "批量指派";
$lang->story->batchChangeModule  = "批量修改模組";
$lang->story->viewAll            = '查看全部';
$lang->story->toTask             = '轉任務';
$lang->story->batchToTask        = '批量轉任務';
$lang->story->convertRelations   = '換算關係';
$lang->story->undetermined       = '待定';
$lang->story->order              = '排序';
$lang->story->saveDraft          = '存為草稿';
$lang->story->doNotSubmit        = '保存暫不提交';
$lang->story->currentBranch      = '當前%s';
$lang->story->twins              = '孿生需求';
$lang->story->relieved           = '解除';
$lang->story->relievedTwins      = '解除孿生需求';
$lang->story->loadAllStories     = '加載所有需求';
$lang->story->hasDividedTask     = '已經分解任務';

$lang->story->editAction      = "編輯{$lang->SRCommon}";
$lang->story->changeAction    = "變更{$lang->SRCommon}";
$lang->story->assignAction    = "指派{$lang->SRCommon}";
$lang->story->reviewAction    = "評審{$lang->SRCommon}";
$lang->story->subdivideAction = "細分{$lang->SRCommon}";
$lang->story->closeAction     = "關閉{$lang->SRCommon}";
$lang->story->activateAction  = "激活{$lang->SRCommon}";
$lang->story->deleteAction    = "刪除{$lang->SRCommon}";
$lang->story->exportAction    = "導出{$lang->SRCommon}";
$lang->story->reportAction    = "統計報表";

$lang->story->skipStory        = '需求：%s 為父需求，將不會被關閉。';
$lang->story->closedStory      = '需求：%s 已關閉，將不會被關閉。';
$lang->story->batchToTaskTips  = "已關閉的需求不會轉為任務。";
$lang->story->successToTask    = '批量轉任務成功';
$lang->story->storyRound       = '第 %s 輪估算';
$lang->story->float            = "『%s』應當是正數，可以是小數。";
$lang->story->saveDraftSuccess = '存為草稿成功';

$lang->story->changeSyncTip       = "該需求的修改會同步到如下的孿生需求";
$lang->story->syncTip             = "孿生需求間除{$lang->productCommon}、分支 、模組、計劃、階段外均同步，孿生關係解除後不再同步";
$lang->story->relievedTip         = "孿生關係解除後無法恢復，需求的內容不再同步，是否解除？";
$lang->story->assignSyncTip       = "孿生需求均同步修改指派人";
$lang->story->closeSyncTip        = "孿生需求均同步關閉";
$lang->story->activateSyncTip     = "孿生需求均同步激活";
$lang->story->relievedTwinsTip    = "{$lang->productCommon}調整後，本需求自動解除孿生關係，需求不再同步，是否保存？";
$lang->story->batchEditTip        = "{$lang->SRCommon} %s為孿生需求，本次操作已被過濾。";

$lang->story->id               = '編號';
$lang->story->parent           = '父需求';
$lang->story->product          = "所屬{$lang->productCommon}";
$lang->story->project          = "所屬{$lang->projectCommon}";
$lang->story->branch           = "平台/分支";
$lang->story->module           = '所屬模組';
$lang->story->moduleAB         = '模組';
$lang->story->roadmap          = '所屬路標';
$lang->story->source           = "來源";
$lang->story->sourceNote       = '來源備註';
$lang->story->fromBug          = '來源Bug';
$lang->story->title            = "{$lang->SRCommon}名稱";
$lang->story->type             = "需求類型";
$lang->story->category         = "類別";
$lang->story->color            = '標題顏色';
$lang->story->toBug            = '轉Bug';
$lang->story->spec             = "描述";
$lang->story->assign           = '指派給';
$lang->story->verify           = '驗收標準';
$lang->story->pri              = '優先順序';
$lang->story->estimate         = "預計{$lang->hourCommon}";
$lang->story->estimateAB       = '預計';
$lang->story->hour             = $lang->hourCommon;
$lang->story->consumed         = '耗時';
$lang->story->status           = '當前狀態';
$lang->story->statusAB         = '狀態';
$lang->story->subStatus        = '子狀態';
$lang->story->stage            = '所處階段';
$lang->story->stageAB          = '階段';
$lang->story->stagedBy         = '設置階段者';
$lang->story->mailto           = '抄送給';
$lang->story->openedBy         = '由誰創建';
$lang->story->openedByAB       = '創建者';
$lang->story->openedDate       = '創建日期';
$lang->story->assignedTo       = '指派給';
$lang->story->assignedToAB     = '指派';
$lang->story->assignedDate     = '指派日期';
$lang->story->lastEditedBy     = '最後修改';
$lang->story->lastEditedByAB   = '最後修改者';
$lang->story->lastEditedDate   = '最後修改日期';
$lang->story->closedBy         = '由誰關閉';
$lang->story->closedDate       = '關閉日期';
$lang->story->closedReason     = '關閉原因';
$lang->story->rejectedReason   = '拒絶原因';
$lang->story->changedBy        = '由誰變更';
$lang->story->changedDate      = '變更時間';
$lang->story->reviewedBy       = '由誰評審';
$lang->story->reviewer         = '評審者';
$lang->story->reviewers        = '評審人員';
$lang->story->reviewedDate     = '評審時間';
$lang->story->activatedDate    = '激活日期';
$lang->story->version          = '版本號';
$lang->story->feedbackBy       = '反饋者';
$lang->story->notifyEmail      = '通知郵箱';
$lang->story->plan             = "所屬計劃";
$lang->story->planAB           = '計劃';
$lang->story->comment          = '備註';
$lang->story->children         = "子需求";
$lang->story->childrenAB       = "子";
$lang->story->linkStories      = "相關{$lang->SRCommon}";
$lang->story->linkRequirements = "相關{$lang->URCommon}";
$lang->story->childStories     = "細分{$lang->SRCommon}";
$lang->story->duplicateStory   = "重複{$lang->SRCommon}";
$lang->story->reviewResult     = '評審意見';
$lang->story->reviewResultAB   = '評審結果';
$lang->story->preVersion       = '之前版本';
$lang->story->keywords         = '關鍵詞';
$lang->story->newStory         = "繼續添加{$lang->SRCommon}";
$lang->story->colorTag         = '顏色標籤';
$lang->story->files            = '附件';
$lang->story->copy             = "複製{$lang->SRCommon}";
$lang->story->total            = "總{$lang->SRCommon}";
$lang->story->draft            = '草稿';
$lang->story->unclosed         = '未關閉';
$lang->story->deleted          = '已刪除';
$lang->story->released         = "已發佈{$lang->SRCommon}數";
$lang->story->URChanged        = '用需變更';
$lang->story->design           = '相關設計';
$lang->story->case             = '相關用例';
$lang->story->bug              = '相關Bug';
$lang->story->repoCommit       = '相關提交';
$lang->story->one              = '一個';
$lang->story->field            = '同步的欄位';
$lang->story->completeRate     = '完成率';
$lang->story->reviewed         = '已評審';
$lang->story->toBeReviewed     = '待評審';
$lang->story->linkMR           = '相關合併請求';
$lang->story->linkCommit       = '相關代碼版本';

$lang->story->ditto       = '同上';
$lang->story->dittoNotice = "該{$lang->SRCommon}與上一{$lang->SRCommon}不屬於同一{$lang->productCommon}！";

$lang->story->needNotReviewList[0] = '需要評審';
$lang->story->needNotReviewList[1] = '不需要評審';

$lang->story->useList[0] = '不使用';
$lang->story->useList[1] = '使用';

$lang->story->statusList['']          = '';
$lang->story->statusList['draft']     = '草稿';
$lang->story->statusList['reviewing'] = '評審中';
$lang->story->statusList['active']    = '激活';
$lang->story->statusList['closed']    = '已關閉';
$lang->story->statusList['changing']  = '變更中';

if($config->systemMode == 'PLM')
{
    $lang->story->statusList['launched']   = '已立項';
    $lang->story->statusList['developing'] = '研發中';
}

$lang->story->stageList = array();
$lang->story->stageList['']           = '';
$lang->story->stageList['wait']       = '未開始';
$lang->story->stageList['planned']    = "已計劃";
$lang->story->stageList['projected']  = '已立項';
$lang->story->stageList['developing'] = '研發中';
$lang->story->stageList['developed']  = '研發完畢';
$lang->story->stageList['testing']    = '測試中';
$lang->story->stageList['tested']     = '測試完畢';
$lang->story->stageList['verified']   = '已驗收';
$lang->story->stageList['released']   = '已發佈';
$lang->story->stageList['closed']     = '已關閉';

$lang->story->reasonList['']           = '';
$lang->story->reasonList['done']       = '已完成';
$lang->story->reasonList['subdivided'] = '已細分';
$lang->story->reasonList['duplicate']  = '重複';
$lang->story->reasonList['postponed']  = '延期';
$lang->story->reasonList['willnotdo']  = '不做';
$lang->story->reasonList['cancel']     = '已取消';
$lang->story->reasonList['bydesign']   = '設計如此';
//$lang->story->reasonList['isbug']      = '是個Bug';

$lang->story->reviewResultList['']        = '';
$lang->story->reviewResultList['pass']    = '確認通過';
$lang->story->reviewResultList['revert']  = '撤銷變更';
$lang->story->reviewResultList['clarify'] = '有待明確';
$lang->story->reviewResultList['reject']  = '拒絶';

$lang->story->reviewList[0] = '否';
$lang->story->reviewList[1] = '是';

$lang->story->sourceList['']           = '';
$lang->story->sourceList['customer']   = '客戶';
$lang->story->sourceList['user']       = '用戶';
$lang->story->sourceList['po']         = $lang->productCommon . '經理';
$lang->story->sourceList['market']     = '市場';
$lang->story->sourceList['service']    = '客服';
$lang->story->sourceList['operation']  = '運營';
$lang->story->sourceList['support']    = '技術支持';
$lang->story->sourceList['competitor'] = '競爭對手';
$lang->story->sourceList['partner']    = '合作夥伴';
$lang->story->sourceList['dev']        = '開發人員';
$lang->story->sourceList['tester']     = '測試人員';
$lang->story->sourceList['bug']        = 'Bug';
$lang->story->sourceList['forum']      = '論壇';
$lang->story->sourceList['other']      = '其他';

$lang->story->priList[]  = '';
$lang->story->priList[1] = '1';
$lang->story->priList[2] = '2';
$lang->story->priList[3] = '3';
$lang->story->priList[4] = '4';

$lang->story->changeList = array();
$lang->story->changeList['no']  = '不變更';
$lang->story->changeList['yes'] = '變更';

$lang->story->legendBasicInfo      = '基本信息';
$lang->story->legendLifeTime       = "需求的一生";
$lang->story->legendRelated        = '相關信息';
$lang->story->legendMailto         = '抄送給';
$lang->story->legendAttatch        = '附件';
$lang->story->legendProjectAndTask = $lang->executionCommon . '任務';
$lang->story->legendBugs           = '相關Bug';
$lang->story->legendFromBug        = '來源Bug';
$lang->story->legendCases          = '相關用例';
$lang->story->legendBuilds         = '相關版本';
$lang->story->legendReleases       = '相關發佈';
$lang->story->legendLinkStories    = "相關{$lang->SRCommon}";
$lang->story->legendChildStories   = "細分{$lang->SRCommon}";
$lang->story->legendSpec           = "需求描述";
$lang->story->legendVerify         = '驗收標準';
$lang->story->legendMisc           = '其他相關';
$lang->story->legendInformation    = '需求信息';

$lang->story->lblChange            = "變更{$lang->SRCommon}";
$lang->story->lblReview            = "評審{$lang->SRCommon}";
$lang->story->lblActivate          = "激活{$lang->SRCommon}";
$lang->story->lblClose             = "關閉{$lang->SRCommon}";
$lang->story->lblTBC               = '任務Bug用例';

$lang->story->checkAffection       = '影響範圍';
$lang->story->affectedProjects     = "影響的{$lang->project->common}或{$lang->execution->common}";
$lang->story->affectedBugs         = '影響的Bug';
$lang->story->affectedCases        = '影響的用例';
$lang->story->affectedTwins        = '影響的孿生需求';

$lang->story->specTemplate          = "建議參考的模板：作為一名<某種類型的用戶>，我希望<達成某些目的>，這樣可以<開發的價值>。";
$lang->story->needNotReview         = '不需要評審';
$lang->story->successSaved          = "{$lang->SRCommon}成功添加，";
$lang->story->confirmDelete         = "您確認刪除該{$lang->SRCommon}嗎?";
$lang->story->confirmRecall         = "您確認撤銷該{$lang->SRCommon}嗎?";
$lang->story->errorEmptyChildStory  = "『細分{$lang->SRCommon}』不能為空。";
$lang->story->errorNotSubdivide     = "狀態不是激活，或者階段不是未開始的{$lang->SRCommon}，或者是子需求，則不能細分。";
$lang->story->errorEmptyReviewedBy  = "『評審者』不能為空。";
$lang->story->mustChooseResult      = '必須選擇評審意見';
$lang->story->mustChoosePreVersion  = '必須選擇回溯的版本';
$lang->story->noStory               = "暫時沒有{$lang->SRCommon}。";
$lang->story->noRequirement         = "暫時沒有{$lang->URCommon}。";
$lang->story->noRelatedRequirement  = "暫無相關{$lang->URCommon}。";
$lang->story->ignoreChangeStage     = "{$lang->SRCommon} %s 狀態為草稿或已關閉，本次操作已被過濾。";
$lang->story->cannotDeleteParent    = "不能刪除父{$lang->SRCommon}";
$lang->story->moveChildrenTips      = "修改父{$lang->SRCommon}的所屬{$lang->productCommon}會將其下的子{$lang->SRCommon}也移動到所選{$lang->productCommon}下。";
$lang->story->changeTips            = "該{$lang->SRCommon}關聯的{$lang->URCommon}有變更，點擊“不變更”忽略此條變更，點擊“變更”來進行該{$lang->SRCommon}的變更。";
$lang->story->estimateMustBeNumber  = '估算值必須是數字';
$lang->story->estimateMustBePlus    = '估算值不能是負數';
$lang->story->confirmChangeBranch   = $lang->SRCommon . '%s已關聯在之前所屬分支的計劃中，調整分支後，' . $lang->SRCommon . '將從之前所屬分支的計劃中移除，請確認是否繼續修改上述' . $lang->SRCommon . '的分支。';
$lang->story->confirmChangePlan     = $lang->SRCommon . '%s已關聯在之前計劃的所屬分支中，調整分支後，' . $lang->SRCommon . '將會從計劃中移除，請確認是否繼續修改計劃的所屬分支。';
$lang->story->errorDuplicateStory   = $lang->SRCommon . '%s不存在';
$lang->story->confirmRecallChange   = "撤銷變更後，需求內容會回退至變更前的版本，您確定要撤銷嗎？";
$lang->story->confirmRecallReview   = "您確定要撤回評審嗎？";
$lang->story->noStoryToTask         = "只有激活的{$lang->SRCommon}才能轉為任務！";
$lang->story->ignoreClosedStory     = "{$lang->SRCommon} %s 狀態為已關閉，本次操作已被過濾。";

$lang->story->form = new stdclass();
$lang->story->form->area     = "該{$lang->SRCommon}所屬範圍";
$lang->story->form->desc     = "描述及標準，什麼{$lang->SRCommon}？如何驗收？";
$lang->story->form->resource = '資源分配，有誰完成？需要多少時間？';
$lang->story->form->file     = "附件，如果該{$lang->SRCommon}有相關檔案，請點此上傳。";

$lang->story->action = new stdclass();
$lang->story->action->reviewed              = array('main' => '$date, 由 <strong>$actor</strong> 記錄評審意見，評審意見為 <strong>$extra</strong>。', 'extra' => 'reviewResultList');
$lang->story->action->rejectreviewed        = array('main' => '$date, 由 <strong>$actor</strong> 記錄評審意見，評審意見為 <strong>$extra</strong>，原因為 <strong>$reason</strong>。', 'extra' => 'reviewResultList', 'reason' => 'reasonList');
$lang->story->action->recalled              = array('main' => '$date, 由 <strong>$actor</strong> 撤銷評審。');
$lang->story->action->closed                = array('main' => '$date, 由 <strong>$actor</strong> 關閉，原因為 <strong>$extra</strong> $appendLink。', 'extra' => 'reasonList');
$lang->story->action->closedbysystem        = array('main' => '$date, 系統判定由於關閉了所有子需求，自動關閉父需求。');
$lang->story->action->reviewpassed          = array('main' => '$date, 由 <strong>系統</strong> 判定，結果為 <strong>確認通過</strong>。');
$lang->story->action->reviewrejected        = array('main' => '$date, 由 <strong>系統</strong> 關閉，原因為 <strong>拒絶</strong>。');
$lang->story->action->reviewclarified       = array('main' => '$date, 由 <strong>系統</strong> 判定，結果為 <strong>有待明確</strong>，請編輯後重新發起評審。');
$lang->story->action->reviewreverted        = array('main' => '$date, 由 <strong>系統</strong> 判定，結果為 <strong>撤銷變更</strong>。');
$lang->story->action->linked2plan           = array('main' => '$date, 由 <strong>$actor</strong> 關聯到計劃 <strong>$extra</strong>。');
$lang->story->action->unlinkedfromplan      = array('main' => '$date, 由 <strong>$actor</strong> 從計劃 <strong>$extra</strong> 移除。');
$lang->story->action->linked2execution      = array('main' => '$date, 由 <strong>$actor</strong> 關聯到' . $lang->executionCommon . ' <strong>$extra</strong>。');
$lang->story->action->unlinkedfromexecution = array('main' => '$date, 由 <strong>$actor</strong> 從' . $lang->executionCommon . ' <strong>$extra</strong> 移除。');
$lang->story->action->linked2kanban         = array('main' => '$date, 由 <strong>$actor</strong> 關聯到看板 <strong>$extra</strong>。');
$lang->story->action->linked2project        = array('main' => '$date, 由 <strong>$actor</strong> ' . "關聯到{$lang->projectCommon}" . ' <strong>$extra</strong>。');
$lang->story->action->unlinkedfromproject   = array('main' => '$date, 由 <strong>$actor</strong> ' . "從{$lang->projectCommon}" . '<strong>$extra</strong> 移除。');
$lang->story->action->linked2build          = array('main' => '$date, 由 <strong>$actor</strong> 關聯到版本 <strong>$extra</strong>。');
$lang->story->action->unlinkedfrombuild     = array('main' => '$date, 由 <strong>$actor</strong> 從版本 <strong>$extra</strong> 移除。');
$lang->story->action->linked2release        = array('main' => '$date, 由 <strong>$actor</strong> 關聯到發佈 <strong>$extra</strong>。');
$lang->story->action->unlinkedfromrelease   = array('main' => '$date, 由 <strong>$actor</strong> 從發佈 <strong>$extra</strong> 移除。');
$lang->story->action->linked2revision       = array('main' => '$date, 由 <strong>$actor</strong> 關聯到代碼提交 <strong>$extra</strong>');
$lang->story->action->unlinkedfromrevision  = array('main' => '$date, 由 <strong>$actor</strong> 取消關聯到代碼提交 <strong>$extra</strong>');
$lang->story->action->linkrelatedstory      = array('main' => "\$date, 由 <strong>\$actor</strong> 關聯相關{$lang->SRCommon} <strong>\$extra</strong>。");
$lang->story->action->subdividestory        = array('main' => "\$date, 由 <strong>\$actor</strong> 細分為{$lang->SRCommon}   <strong>\$extra</strong>。");
$lang->story->action->unlinkrelatedstory    = array('main' => "\$date, 由 <strong>\$actor</strong> 移除相關{$lang->SRCommon} <strong>\$extra</strong>。");
$lang->story->action->unlinkchildstory      = array('main' => "\$date, 由 <strong>\$actor</strong> 移除細分{$lang->SRCommon} <strong>\$extra</strong>。");
$lang->story->action->recalledchange        = array('main' => "\$date, 由 <strong>\$actor</strong> 撤銷變更。");
$lang->story->action->synctwins             = array('main' => "\$date, 系統判斷由於孿生需求 <strong>\$extra</strong> \$operate，本需求同步調整。", 'operate' => 'operateList');
$lang->story->action->linked2roadmap        = array('main' => '$date, 由 <strong>$actor</strong> 關聯到路標 <strong>$extra</strong>。');
$lang->story->action->unlinkedfromroadmap   = array('main' => '$date, 由 <strong>$actor</strong> 從路標 <strong>$extra</strong> 移除。');
$lang->story->action->changedbycharter      = array('main' => '$date, 由 <strong>$actor</strong> 通過立項申請 <strong>$extra</strong> ，需求狀態同步調整為已立項。');

/* 統計報表。*/
$lang->story->report = new stdclass();
$lang->story->report->common = '報表';
$lang->story->report->select = '請選擇報表類型';
$lang->story->report->create = '生成報表';
$lang->story->report->value  = "需求數";

$lang->story->report->charts['storysPerProduct']        = $lang->productCommon . "{$lang->SRCommon}數量";
$lang->story->report->charts['storysPerModule']         = "模組{$lang->SRCommon}數量";
$lang->story->report->charts['storysPerSource']         = "按{$lang->SRCommon}來源統計";
$lang->story->report->charts['storysPerPlan']           = "按計划進行統計";
$lang->story->report->charts['storysPerStatus']         = '按狀態進行統計';
$lang->story->report->charts['storysPerStage']          = '按所處階段進行統計';
$lang->story->report->charts['storysPerPri']            = '按優先順序進行統計';
$lang->story->report->charts['storysPerEstimate']       = "按預計{$lang->hourCommon}進行統計";
$lang->story->report->charts['storysPerOpenedBy']       = '按由誰創建來進行統計';
$lang->story->report->charts['storysPerAssignedTo']     = '按當前指派來進行統計';
$lang->story->report->charts['storysPerClosedReason']   = '按關閉原因來進行統計';
$lang->story->report->charts['storysPerChange']         = '按變更次數來進行統計';

$lang->story->report->options = new stdclass();
$lang->story->report->options->graph  = new stdclass();
$lang->story->report->options->type   = 'pie';
$lang->story->report->options->width  = 500;
$lang->story->report->options->height = 140;

$lang->story->report->storysPerProduct      = new stdclass();
$lang->story->report->storysPerModule       = new stdclass();
$lang->story->report->storysPerSource       = new stdclass();
$lang->story->report->storysPerPlan         = new stdclass();
$lang->story->report->storysPerStatus       = new stdclass();
$lang->story->report->storysPerStage        = new stdclass();
$lang->story->report->storysPerPri          = new stdclass();
$lang->story->report->storysPerOpenedBy     = new stdclass();
$lang->story->report->storysPerAssignedTo   = new stdclass();
$lang->story->report->storysPerClosedReason = new stdclass();
$lang->story->report->storysPerEstimate     = new stdclass();
$lang->story->report->storysPerChange       = new stdclass();

$lang->story->report->storysPerProduct->item      = $lang->productCommon;
$lang->story->report->storysPerModule->item       = '模組';
$lang->story->report->storysPerSource->item       = '來源';
$lang->story->report->storysPerPlan->item         = '計劃';
$lang->story->report->storysPerStatus->item       = '狀態';
$lang->story->report->storysPerStage->item        = '階段';
$lang->story->report->storysPerPri->item          = '優先順序';
$lang->story->report->storysPerOpenedBy->item     = '由誰創建';
$lang->story->report->storysPerAssignedTo->item   = '指派給';
$lang->story->report->storysPerClosedReason->item = '原因';
$lang->story->report->storysPerEstimate->item     = "預計{$lang->hourCommon}";
$lang->story->report->storysPerChange->item       = '變更次數';

$lang->story->report->storysPerProduct->graph      = new stdclass();
$lang->story->report->storysPerModule->graph       = new stdclass();
$lang->story->report->storysPerSource->graph       = new stdclass();
$lang->story->report->storysPerPlan->graph         = new stdclass();
$lang->story->report->storysPerStatus->graph       = new stdclass();
$lang->story->report->storysPerStage->graph        = new stdclass();
$lang->story->report->storysPerPri->graph          = new stdclass();
$lang->story->report->storysPerOpenedBy->graph     = new stdclass();
$lang->story->report->storysPerAssignedTo->graph   = new stdclass();
$lang->story->report->storysPerClosedReason->graph = new stdclass();
$lang->story->report->storysPerEstimate->graph     = new stdclass();
$lang->story->report->storysPerChange->graph       = new stdclass();

$lang->story->report->storysPerProduct->graph->xAxisName      = $lang->productCommon;
$lang->story->report->storysPerModule->graph->xAxisName       = '模組';
$lang->story->report->storysPerSource->graph->xAxisName       = '來源';
$lang->story->report->storysPerPlan->graph->xAxisName         = '計劃';
$lang->story->report->storysPerStatus->graph->xAxisName       = '狀態';
$lang->story->report->storysPerStage->graph->xAxisName        = '所處階段';
$lang->story->report->storysPerPri->graph->xAxisName          = '優先順序';
$lang->story->report->storysPerOpenedBy->graph->xAxisName     = '由誰創建';
$lang->story->report->storysPerAssignedTo->graph->xAxisName   = '當前指派';
$lang->story->report->storysPerClosedReason->graph->xAxisName = '關閉原因';
$lang->story->report->storysPerEstimate->graph->xAxisName     = '預計時間';
$lang->story->report->storysPerChange->graph->xAxisName       = '變更次數';

$lang->story->placeholder = new stdclass();
$lang->story->placeholder->estimate = $lang->story->hour;

$lang->story->chosen = new stdClass();
$lang->story->chosen->reviewedBy = '選擇評審人...';

$lang->story->notice = new stdClass();
$lang->story->notice->closed           = "您選擇的{$lang->SRCommon}已經被關閉了！";
$lang->story->notice->reviewerNotEmpty = "該{$lang->SRCommon}需要評審，評審人員不能為空。";
$lang->story->notice->changePlan       = '所屬計劃只能改為一條，修改後才能保存成功。';

$lang->story->convertToTask = new stdClass();
$lang->story->convertToTask->fieldList = array();
$lang->story->convertToTask->fieldList['module']     = '所屬模組';
$lang->story->convertToTask->fieldList['spec']       = "{$lang->SRCommon}描述";
$lang->story->convertToTask->fieldList['pri']        = '優先順序';
$lang->story->convertToTask->fieldList['mailto']     = '抄送給';
$lang->story->convertToTask->fieldList['assignedTo'] = '指派給';

$lang->story->categoryList['feature']     = '功能';
$lang->story->categoryList['interface']   = '介面';
$lang->story->categoryList['performance'] = '性能';
$lang->story->categoryList['safe']        = '安全';
$lang->story->categoryList['experience']  = '體驗';
$lang->story->categoryList['improve']     = '改進';
$lang->story->categoryList['other']       = '其他';

$lang->story->ipdCategoryList['zhanlue']         = '戰略';
$lang->story->ipdCategoryList['maintainability'] = '可維護性';
$lang->story->ipdCategoryList['packing']         = '包裝';

$lang->story->changeTip = "只有激活狀態的{$lang->SRCommon}，才能進行變更";

$lang->story->reviewTip = array();
$lang->story->reviewTip['active']      = "該{$lang->SRCommon}已是激活狀態，無需評審";
$lang->story->reviewTip['notReviewer'] = "您不是該{$lang->SRCommon}的評審人員，無法進行評審操作";
$lang->story->reviewTip['reviewed']    = '您已評審';

$lang->story->recallTip = array();
$lang->story->recallTip['actived'] = "該{$lang->SRCommon}未發起評審流程，無需撤銷操作";

$lang->story->subDivideTip = array();
$lang->story->subDivideTip['subStory']   = "子{$lang->SRCommon}無法細分";
$lang->story->subDivideTip['notWait']    = "該{$lang->SRCommon}%s，無法進行細分操作";
$lang->story->subDivideTip['notActive']  = "%s不是激活狀態，無法進行細分操作";
$lang->story->subDivideTip['twinsSplit'] = '孿生需求不可細分';

$lang->story->featureBar['browse']['all']       = '全部';
$lang->story->featureBar['browse']['unclosed']  = $lang->story->unclosed;
$lang->story->featureBar['browse']['draft']     = $lang->story->statusList['draft'];
$lang->story->featureBar['browse']['reviewing'] = $lang->story->statusList['reviewing'];

$lang->story->operateList = array();
$lang->story->operateList['assigned']       = '指派';
$lang->story->operateList['closed']         = '關閉';
$lang->story->operateList['activated']      = '激活';
$lang->story->operateList['changed']        = '變更';
$lang->story->operateList['reviewed']       = '評審';
$lang->story->operateList['edited']         = '編輯';
$lang->story->operateList['submitreview']   = '提交評審';
$lang->story->operateList['recalledchange'] = '撤銷變更';
$lang->story->operateList['recalled']       = '撤銷評審';

$lang->requirement->common             = $lang->URCommon;
$lang->requirement->create             = "提{$lang->URCommon}";
$lang->requirement->batchCreate        = "批量創建";
$lang->requirement->editAction         = "編輯{$lang->URCommon}";
$lang->requirement->changeAction       = "變更{$lang->URCommon}";
$lang->requirement->assignAction       = "指派{$lang->URCommon}";
$lang->requirement->reviewAction       = "評審{$lang->URCommon}";
$lang->requirement->subdivideAction    = "細分{$lang->URCommon}";
$lang->requirement->closeAction        = "關閉{$lang->URCommon}";
$lang->requirement->activateAction     = "激活{$lang->URCommon}";
$lang->requirement->deleteAction       = "刪除{$lang->URCommon}";
$lang->requirement->exportAction       = "導出{$lang->URCommon}";
$lang->requirement->reportAction       = "統計報表";
$lang->requirement->recall             = $lang->story->recall;
$lang->requirement->batchReview        = '批量評審';
$lang->requirement->batchEdit          = "批量編輯";
$lang->requirement->batchClose         = '批量關閉';
$lang->requirement->view               = "{$lang->URCommon}詳情";
$lang->requirement->linkRequirementsAB = "關聯相關{$lang->URCommon}";
$lang->requirement->batchChangeBranch  = "批量修改分支";
$lang->requirement->batchAssignTo      = "批量指派";
$lang->requirement->batchChangeModule  = "批量修改模組";
$lang->requirement->submitReview       = $lang->story->submitReview;
$lang->requirement->linkStory          = "關聯{$lang->SRCommon}";

$lang->story->addBranch      = '添加%s';
$lang->story->deleteBranch   = '刪除%s';
$lang->story->notice->branch = "每個分支會建立一個需求，需求間互為孿生關係。孿生需求間除{$lang->productCommon}、分支、模組、計劃、階段欄位外均保持同步，後期您可以手動解除孿生關係。";

$lang->story->relievedTwinsRelation     = '解除孿生關係';
$lang->story->relievedTwinsRelationTips = '孿生關係解除後無法恢復，需求的關閉將不再同步。';
$lang->story->changeRelievedTwinsTips   = '孿生關係解除後無法恢復，孿生需求間內容不再同步。';
$lang->story->storyUnlinkRoadmap        = '該用戶需求立項通過後又從路標中進行了移除，需要再次立項通過後才能在IPD研發管理界面中查看。';
