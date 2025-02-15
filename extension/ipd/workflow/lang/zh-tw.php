<?php
$lang->workflow->common         = '工作流流程';
$lang->workflow->browseFlow     = '瀏覽流程';
$lang->workflow->browseDB       = '瀏覽子表';
$lang->workflow->create         = '新增流程';
$lang->workflow->copy           = '複製流程';
$lang->workflow->edit           = '編輯流程';
$lang->workflow->view           = '流程詳情';
$lang->workflow->delete         = '刪除流程';
$lang->workflow->fullTextSearch = '全文檢索';
$lang->workflow->buildIndex     = '重建索引';
$lang->workflow->custom         = '自定義';
$lang->workflow->setApproval    = '審批設置';
$lang->workflow->setJS          = 'JS';
$lang->workflow->setCSS         = 'CSS';
$lang->workflow->backup         = '備份';
$lang->workflow->upgrade        = '升級';
$lang->workflow->upgradeAction  = '升級';
$lang->workflow->preview        = '預覽';
$lang->workflow->design         = '設計';
$lang->workflow->release        = '發佈';
$lang->workflow->deactivate     = '停用';
$lang->workflow->activate       = '啟用';
$lang->workflow->createApp      = '新建';
$lang->workflow->cover          = '覆蓋';
$lang->workflow->approval       = '審批';
$lang->workflow->delimiter      = '、';

$lang->workflow->id            = '編號';
$lang->workflow->parent        = '父流程';
$lang->workflow->type          = '類型';
$lang->workflow->app           = '所屬應用';
$lang->workflow->position      = '位置';
$lang->workflow->module        = '流程代號';
$lang->workflow->table         = '流程表';
$lang->workflow->name          = '流程名';
$lang->workflow->titleField    = '標題欄位';
$lang->workflow->contentField  = '內容欄位';
$lang->workflow->flowchart     = '流程圖';
$lang->workflow->ui            = '界面設計';
$lang->workflow->js            = 'JS';
$lang->workflow->css           = 'CSS';
$lang->workflow->order         = '順序';
$lang->workflow->buildin       = '內置';
$lang->workflow->administrator = '白名單';
$lang->workflow->desc          = '描述';
$lang->workflow->version       = '版本';
$lang->workflow->status        = '狀態';
$lang->workflow->createdBy     = '由誰創建';
$lang->workflow->createdDate   = '創建時間';
$lang->workflow->editedBy      = '由誰編輯';
$lang->workflow->editedDate    = '編輯時間';
$lang->workflow->currentTime   = '當前時間';

$lang->workflow->actionFlowWidth = 165;

$lang->workflow->copyFlow         = '複製';
$lang->workflow->source           = '源流程';
$lang->workflow->field            = '欄位';
$lang->workflow->action           = '動作';
$lang->workflow->label            = '標籤';
$lang->workflow->mainTable        = '主表';
$lang->workflow->subTable         = '子表';
$lang->workflow->relation         = '跨流程設置';
$lang->workflow->report           = '報表';
$lang->workflow->export           = '導出';
$lang->workflow->subTableSettings = '子表及欄位屬性設置';

$lang->workflow->statusList['wait']   = '待發佈';
$lang->workflow->statusList['normal'] = '使用中';
$lang->workflow->statusList['pause']  = '停用';

$lang->workflow->positionList['before'] = '之前';
$lang->workflow->positionList['after']  = '之後';

$lang->workflow->buildinList['0'] = '否';
$lang->workflow->buildinList['1'] = '是';

$lang->workflow->fullTextSearch = new stdclass();
$lang->workflow->fullTextSearch->common       = '全文檢索';
$lang->workflow->fullTextSearch->titleField   = '標題欄位';
$lang->workflow->fullTextSearch->contentField = '內容欄位';

$lang->workflow->upgrade = new stdclass();
$lang->workflow->upgrade->common         = '升級';
$lang->workflow->upgrade->backup         = '備份';
$lang->workflow->upgrade->backupSuccess  = '備份成功';
$lang->workflow->upgrade->newVersion     = '發現新版本！';
$lang->workflow->upgrade->clickme        = '點擊升級';
$lang->workflow->upgrade->start          = '開始升級';
$lang->workflow->upgrade->currentVersion = '當前版本';
$lang->workflow->upgrade->selectVersion  = '選擇版本';
$lang->workflow->upgrade->confirm        = '確認要執行的SQL語句';
$lang->workflow->upgrade->upgrade        = '升級現有模組';
$lang->workflow->upgrade->upgradeFail    = '升級失敗';
$lang->workflow->upgrade->upgradeSuccess = '升級成功';
$lang->workflow->upgrade->install        = '安裝一個新模組';
$lang->workflow->upgrade->installFail    = '安裝失敗';
$lang->workflow->upgrade->installSuccess = '安裝成功';

/* Tips */
$lang->workflow->tips = new stdclass();
$lang->workflow->tips->noCSSTag       = '不需要&lt;style&gt;&lt;/style&gt;標籤';
$lang->workflow->tips->noJSTag        = '不需要&lt;script&gt;&lt;/script&gt;標籤';
$lang->workflow->tips->flowCSS        = '，加載到所有頁面';
$lang->workflow->tips->flowJS         = '，加載到所有頁面';
$lang->workflow->tips->actionCSS      = '，僅加載到當前動作的頁面';
$lang->workflow->tips->actionJS       = '，僅加載到當前動作的頁面';
$lang->workflow->tips->deactivate     = '您確定要停用此流程嗎？';
$lang->workflow->tips->create         = '太棒了！您已經成功創建了一個新流程，現在要去設計您的流程嗎？';
$lang->workflow->tips->subTable       = '填寫的表單中，還需要填寫具體的明細信息時，可以通過子表來實現。場景舉例：提交報銷申請時，還需填寫報銷明細。此時，可通過在報銷中新增子表"報銷明細"來實現。';
$lang->workflow->tips->flowchart      = '流程圖中判斷和結果不會控制流程，需要通過高級模式的擴展動作實現。';
$lang->workflow->tips->buildinFlow    = '內置流程暫不支持使用快捷編輯器。';
$lang->workflow->tips->fullTextSearch = '使用全文檢索功能需要設置哪些欄位的內容可以被檢索到。標題欄位在全文檢索中的權重較大，內容欄位權重較小。<br/>權重越大，在搜索結果中越靠前。<br/>設置欄位後需要重建索引才能生效。重建索引的速度和內容數量成正比，請耐心等待索引重建完成。';
$lang->workflow->tips->buildIndex     = '重建索引可能需要一段時間，確定執行操作嗎？';
$lang->workflow->tips->deleteConfirm  = array('您確定要刪除該流程嗎？', '刪除流程後，關聯的數據都會被刪除，如歷史記錄、審批記錄等。', '該操作是不可逆的，刪除的內容無法恢復！');

$lang->workflow->notNow   = '暫不';
$lang->workflow->toDesign = '去設計';

/* Title */
$lang->workflow->title = new stdclass();
$lang->workflow->title->subTable   = '明細表用來存儲%s記錄的明細';
$lang->workflow->title->noCopy     = '內置流程不能複製。';
$lang->workflow->title->noLabel    = '內置流程不能添加標籤。';
$lang->workflow->title->noSubTable = '內置流程不能添加明細表。';
$lang->workflow->title->noRelation = '內置流程不能設置跨流程。';
$lang->workflow->title->noJS       = '內置流程不能設置js。';
$lang->workflow->title->noCSS      = '內置流程不能設置css。';

/* Placeholder */
$lang->workflow->placeholder = new stdclass();
$lang->workflow->placeholder->module       = '只能包含英文字母，保存後不可更改';
$lang->workflow->placeholder->titleField   = '標題欄位只能有一個，在全文檢索中的權重較小';
$lang->workflow->placeholder->contentField = '內容欄位可以有多個，在全文檢索中的權重較大';

/* Error */
$lang->workflow->error = new stdclass();
$lang->workflow->error->createTableFail = '自定義流程數據表創建失敗。';
$lang->workflow->error->buildInModule   = '不能使用系統內置模組作為流程代號。';
$lang->workflow->error->wrongCode       = '<strong> %s </strong>只能包含英文字母。';
$lang->workflow->error->conflict        = '<strong> %s </strong>與系統語言衝突。';
$lang->workflow->error->notFound        = '流程<strong> %s </strong>未找到。';
$lang->workflow->error->flowLimit       = '您只能創建 %s 個流程。';
$lang->workflow->error->buildIndexFail  = '重建索引失敗。';

$lang->workflowtable = new stdclass();
$lang->workflowtable->common = '明細表';
$lang->workflowtable->browse = '瀏覽表';
$lang->workflowtable->create = '新增表';
$lang->workflowtable->edit   = '編輯表';
$lang->workflowtable->view   = '表詳情';
$lang->workflowtable->delete = '刪除表';
$lang->workflowtable->module = '表代號';
$lang->workflowtable->name   = '表名';

$lang->workfloweditor = new stdclass();
$lang->workfloweditor->nextStep              = '下一步';
$lang->workfloweditor->prevStep              = '上一步';
$lang->workfloweditor->quickEditor           = '快捷編輯器';
$lang->workfloweditor->advanceEditor         = '高級編輯器';
$lang->workfloweditor->switchTo              = '切換至%s';
$lang->workfloweditor->switchConfirmMessage  = '將切換到高級工作流編輯器，<br>您可以在高級編輯器進行擴展設置、標籤設計和子表設計等操作。<br>確定切換嗎？';
$lang->workfloweditor->cancelSwitch          = '暫不切換';
$lang->workfloweditor->confirmSwitch         = '確認切換';
$lang->workfloweditor->flowchart             = '流程圖';
$lang->workfloweditor->elementCode           = '代號';
$lang->workfloweditor->elementType           = '類型';
$lang->workfloweditor->elementName           = '名稱';
$lang->workfloweditor->nameAndCodeRequired   = '名稱和代號不能為空';
$lang->workfloweditor->uiDesign              = '界面設計';
$lang->workfloweditor->selectField           = '欄位控制選取';
$lang->workfloweditor->uiPreview             = '界面預覽';
$lang->workfloweditor->fieldProperties       = '欄位屬性操作';
$lang->workfloweditor->uiControls            = '控件';
$lang->workfloweditor->showedFields          = '已有欄位';
$lang->workfloweditor->selectFieldToEditTip  = '點擊選擇表單欄位後在此處編輯';
$lang->workfloweditor->addFieldOption        = '添加選項';
$lang->workfloweditor->confirmReleaseMessage = '您還可以通過工作流高級編輯器進行例如擴展動作、篩選標籤等設置，您確定現在要發佈嗎？';
$lang->workfloweditor->switchMessage         = '您也可以通過此處進行編輯器的切換哦';
$lang->workfloweditor->continueRelease       = '繼續發佈';
$lang->workfloweditor->enterToAdvance        = '進入高級編輯器';
$lang->workfloweditor->labelAll              = '所有';
$lang->workfloweditor->confirmToDelete       = '確定刪除此%s？';
$lang->workfloweditor->startOrStopDuplicated = '只能在流程圖中添加一個開始節點和一個結束節點';
$lang->workfloweditor->leavePageTip          = '當前頁面有沒有保存的內容，確定要離開頁面嗎？';
$lang->workfloweditor->addFile               = '添加附件';
$lang->workfloweditor->fieldWidth            = '列寬度';
$lang->workfloweditor->fieldPosition         = '對齊方式';
$lang->workfloweditor->dragDropTip           = '拖放到這裡';
$lang->workfloweditor->moreSettingsLabel     = '更多設置';

$lang->workfloweditor->elementTypes = array();
$lang->workfloweditor->elementTypes['start']    = '開始';
$lang->workfloweditor->elementTypes['process']  = '動作';
$lang->workfloweditor->elementTypes['decision'] = '判定';
$lang->workfloweditor->elementTypes['result']   = '結果';
$lang->workfloweditor->elementTypes['stop']     = '結束';
$lang->workfloweditor->elementTypes['relation'] = '連接線';

$lang->workfloweditor->defaultFlowchartData = array();
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'start', 'text' => '開始', 'id' => 'start', 'readonly' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'process', 'text' => '新建', 'id' => 'create', 'code' => 'create', '_saved' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'process', 'text' => '編輯', 'id' => 'edit', 'code' => 'edit', '_saved' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'stop', 'text' => '結束', 'id' => 'stop', 'readonly' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'relation', 'from' => 'start', 'to' => 'create', 'id' => 'start-add');
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'relation', 'from' => 'create', 'to' => 'edit', 'id' => 'create-edit');

$lang->workfloweditor->quickSteps = array();
$lang->workfloweditor->quickSteps['flowchart'] = '流程圖|workflow|flowchart';
$lang->workfloweditor->quickSteps['ui']        = '界面設計|workflow|ui';

$lang->workfloweditor->advanceSteps = array();
$lang->workfloweditor->advanceSteps['mainTable'] = '主表設計|workflowfield|browse';
$lang->workfloweditor->advanceSteps['subTable']  = '子表設計|workflow|browsedb';
$lang->workfloweditor->advanceSteps['action']    = '動作設計|workflowaction|browse';
$lang->workfloweditor->advanceSteps['label']     = '標籤設計|workflowlabel|browse';
$lang->workfloweditor->advanceSteps['setting']   = array('link' => '更多設置|workflow|more', 'subMenu' => array('workflowrelation' => 'admin', 'workflowfield' => 'setValue,setExport,setSearch', 'workflow' => 'setJS,setCSS,setFulltextSearch,setApproval', 'workflowreport' => 'browse'));

$lang->workfloweditor->moreSettings = array();
$lang->workfloweditor->moreSettings['approval']  = "審批設置|workflow|setapproval|module=%s";
$lang->workfloweditor->moreSettings['relation']  = "跨流程設置|workflowrelation|admin|prev=%s";
$lang->workfloweditor->moreSettings['setReport'] = "報表設置|workflowreport|browse|module=%s";
$lang->workfloweditor->moreSettings['setValue']  = "顯示值設置|workflowfield|setValue|module=%s";
$lang->workfloweditor->moreSettings['setExport'] = "導出設置|workflowfield|setExport|module=%s";
$lang->workfloweditor->moreSettings['setSearch'] = "搜索設置|workflowfield|setSearch|module=%s";
$lang->workfloweditor->moreSettings['fulltext']  = "全文檢索|workflow|setFulltextSearch|id=%s";
$lang->workfloweditor->moreSettings['setJS']     = "JS|workflow|setJS|id=%s";
$lang->workfloweditor->moreSettings['setCSS']    = "CSS|workflow|setCSS|id=%s";

if(empty($this->config->openedApproval)) unset($lang->workfloweditor->moreSettings['approval']);

$lang->workfloweditor->validateMessages = array();
$lang->workfloweditor->validateMessages['nameRequired']        = '必須填寫欄位名稱';
$lang->workfloweditor->validateMessages['nameDuplicated']      = '存在重複的欄位名稱，請使用不同名稱';
$lang->workfloweditor->validateMessages['fieldRequired']       = '必須填寫欄位代號';
$lang->workfloweditor->validateMessages['fieldInvalid']        = '欄位代號只能包含英文字母';
$lang->workfloweditor->validateMessages['fieldDuplicated']     = '欄位代號與已有欄位“%s”重複，請使用不同的代號';
$lang->workfloweditor->validateMessages['lengthRequired']      = '必須指定類型長度';
$lang->workfloweditor->validateMessages['failSummary']         = '%s個欄位存在錯誤，請修改後再進行保存。';
$lang->workfloweditor->validateMessages['defaultNotInOptions'] = '預設值“%s”不在可選項中。';
$lang->workfloweditor->validateMessages['defaultNotOptionKey'] = '應該使用選項“%s”的“鍵”作為預設值。';
$lang->workfloweditor->validateMessages['widthInvalid']        = '寬度值必須為數值或者 “auto”';

$lang->workfloweditor->error = new stdclass();
$lang->workfloweditor->error->unknown = '未知的錯誤，請重新提交。如果重複多次仍無法保存，請刷新頁面後再嘗試。';

$lang->workflowapproval = new stdclass();
$lang->workflowapproval->enabled         = '啟用審批功能';
$lang->workflowapproval->approval        = '審批';
$lang->workflowapproval->approvalFlow    = '審批流';
$lang->workflowapproval->noApproval      = '沒有可以使用的審批流，';
$lang->workflowapproval->createTips      = array('您可以', '您可以聯繫管理員創建審批流。');
$lang->workflowapproval->createApproval  = '創建審批流';
$lang->workflowapproval->waiting         = '審批中';
$lang->workflowapproval->conflictField   = '欄位：';
$lang->workflowapproval->conflictAction  = '動作：';
$lang->workflowapproval->openLater       = '您也可以稍後在高級編輯器中開啟或關閉審批功能。';
$lang->workflowapproval->disableApproval = '該流程無法開啟審批功能。';
$lang->workflowapproval->conflict        = array('啟用審批', '啟用審批功能需要添加新的欄位和動作，系統檢測到以下欄位和動作存在衝突：', '您可以點擊【取消】自己解決衝突，如“修改欄位代號、刪除欄位、刪除動作”，之後重新啟用審批功能。', '您也可以點擊【覆蓋】由系統解決衝突。系統會刪除衝突的欄位和動作，並添加新的欄位和動作。', '注意：覆蓋操作是不可逆的，刪除的欄位和動作無法恢復！');

$lang->workflowapproval->approvalList = array('enabled' => '開啟', 'disabled' => '關閉');

$lang->workflowapproval->tips = new stdclass();
$lang->workflowapproval->tips->processesInProgress = '有審批流程正在進行中，請審批完成或撤回。';

$lang->workflowapproval->buildInFields = array('name' => array(), 'options' => array());
$lang->workflowapproval->buildInFields['name']['reviewers']     = '審批人';
$lang->workflowapproval->buildInFields['name']['reviewStatus']  = '審批狀態';
$lang->workflowapproval->buildInFields['name']['reviewResult']  = '審批結果';
$lang->workflowapproval->buildInFields['name']['reviewOpinion'] = '審批意見';

$lang->workflowapproval->buildInFields['options']['reviewStatus'] = array('wait' => '待審批', 'doing' => '審批中', 'pass' => '通過', 'reject' => '不通過');
$lang->workflowapproval->buildInFields['options']['reviewResult'] = array('pass' => '通過', 'reject' => '不通過');

$lang->workflowapproval->buildInActions = array('name' => array('submit' => '提交', 'cancel' => '撤回', 'review' => '評審'));
