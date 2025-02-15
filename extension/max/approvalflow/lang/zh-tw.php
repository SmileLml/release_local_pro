<?php
$lang->approvalflow->browse        = '流程列表';
$lang->approvalflow->create        = '創建審批流';
$lang->approvalflow->edit          = '編輯審批流';
$lang->approvalflow->view          = '審批流詳情';
$lang->approvalflow->delete        = '刪除審批流';
$lang->approvalflow->design        = '設計審批流';
$lang->approvalflow->roleList      = '審批角色';
$lang->approvalflow->createRole    = '創建角色';
$lang->approvalflow->editRole      = '編輯角色';
$lang->approvalflow->deleteRole    = '刪除角色';

$lang->approvalflow->common        = '審批流';
$lang->approvalflow->id            = 'ID';
$lang->approvalflow->name          = '名稱';
$lang->approvalflow->createdBy     = '創建人';
$lang->approvalflow->createdDate   = '創建日期';
$lang->approvalflow->noFlow        = '當前沒有審批流';
$lang->approvalflow->title         = '標題';
$lang->approvalflow->reviewer      = '審批人';
$lang->approvalflow->type          = '審批類型';
$lang->approvalflow->desc          = '描述';
$lang->approvalflow->basicInfo     = '基本信息';
$lang->approvalflow->confirmDelete = '您確認要刪除嗎？';
$lang->approvalflow->setNode       = '節點設置';

$lang->approvalflow->nameList = array();
$lang->approvalflow->nameList['stage']  = '階段審批';

$lang->approvalflow->typeList = array();
if(helper::hasFeature('waterfall') or helper::hasFeature('waterfallplus')) $lang->approvalflow->typeList['project'] = $lang->projectCommon . '審批';
$lang->approvalflow->typeList['workflow'] = '工作流審批';

$lang->approvalflow->nodeTypeList = array();
$lang->approvalflow->nodeTypeList['branch']    = '分支';
$lang->approvalflow->nodeTypeList['condition'] = '條件';
$lang->approvalflow->nodeTypeList['default']   = '預設';
$lang->approvalflow->nodeTypeList['other']     = '其他';
$lang->approvalflow->nodeTypeList['approval']  = '審批';
$lang->approvalflow->nodeTypeList['cc']        = '抄送';
$lang->approvalflow->nodeTypeList['start']     = '發起';
$lang->approvalflow->nodeTypeList['end']       = '結束';

$lang->approvalflow->userTypeList = array();
$lang->approvalflow->userTypeList['cc']        = '抄送人';
$lang->approvalflow->userTypeList['submitter'] = '發起人';
$lang->approvalflow->userTypeList['reviewer']  = '審批人';

$lang->approvalflow->noticeTypeList = array();
$lang->approvalflow->noticeTypeList['setReviewer']   = '設置審批人';
$lang->approvalflow->noticeTypeList['setCondition']  = '設置條件';
$lang->approvalflow->noticeTypeList['addCondition']  = '添加條件分支';
$lang->approvalflow->noticeTypeList['addParallel']   = '添加並行分支';
$lang->approvalflow->noticeTypeList['addCond']       = '添加條件';
$lang->approvalflow->noticeTypeList['addReviewer']   = '添加審批人';
$lang->approvalflow->noticeTypeList['addCC']         = '添加抄送人';
$lang->approvalflow->noticeTypeList['setCC']         = '設置抄送人';
$lang->approvalflow->noticeTypeList['setNode']       = '設置節點';
$lang->approvalflow->noticeTypeList['defaultBranch'] = '所有條件都會執行此流程';
$lang->approvalflow->noticeTypeList['otherBranch']   = '其他條件進入此流程';
$lang->approvalflow->noticeTypeList['conditionOr']   = '不設置條件或者滿足其中一個條件即可執行';
$lang->approvalflow->noticeTypeList['when']          = '當';
$lang->approvalflow->noticeTypeList['type']          = '類型';
$lang->approvalflow->noticeTypeList['confirm']       = '確定';
$lang->approvalflow->noticeTypeList['reviewType']    = '審批類型';
$lang->approvalflow->noticeTypeList['ccType']        = '抄送類型';
$lang->approvalflow->noticeTypeList['set']           = '設置';
$lang->approvalflow->noticeTypeList['node']          = '節點';
$lang->approvalflow->noticeTypeList['title']         = '標題';
$lang->approvalflow->noticeTypeList['multipleType']  = '多人審批時採用的審批方式';
$lang->approvalflow->noticeTypeList['multipleAnd']   = '會簽(需所有審批人同意)';
$lang->approvalflow->noticeTypeList['multipleOr']    = '或簽(一名審批人同意即可)';
$lang->approvalflow->noticeTypeList['agentType']     = '當審批人為空時';
$lang->approvalflow->noticeTypeList['agentPass']     = '自動通過';
$lang->approvalflow->noticeTypeList['agentUser']     = '指定人員';
$lang->approvalflow->noticeTypeList['agentAdmin']    = '管理員';

$lang->approvalflow->warningList = array();
$lang->approvalflow->warningList['needReview']   = '請保留最少一個審批節點';
$lang->approvalflow->warningList['save']         = '您的修改內容還沒有保存，您確定離開嗎？';
$lang->approvalflow->warningList['selectUser']   = '請選擇人員';
$lang->approvalflow->warningList['selectDept']   = '請選擇部門';
$lang->approvalflow->warningList['selectRole']   = '請選擇角色';
$lang->approvalflow->warningList['needReviewer'] = '審批人不能為空';
$lang->approvalflow->warningList['oneSelect']    = '只能有一個類型是 "發起人自選"';

$lang->approvalflow->reviewTypeList = array();
$lang->approvalflow->reviewTypeList['manual'] = '人工審批';
$lang->approvalflow->reviewTypeList['pass']   = '自動同意';
$lang->approvalflow->reviewTypeList['reject'] = '自動拒絶';

$lang->approvalflow->errorList = array();
$lang->approvalflow->errorList['needReivewer'] = '請填寫全部審批人';
$lang->approvalflow->errorList['needCcer']     = '請填寫全部抄送人';

$lang->approvalflow->reviewerTypeList = array();
$lang->approvalflow->reviewerTypeList['select']    = array('name' => '發起人自選', 'options' => '',      'tips' => '');
$lang->approvalflow->reviewerTypeList['appointee'] = array('name' => '指定人員',   'options' => 'users', 'tips' => '選擇人員');
$lang->approvalflow->reviewerTypeList['role']      = array('name' => '角色',       'options' => 'roles', 'tips' => '選擇角色');
$lang->approvalflow->reviewerTypeList['upLevel']   = array('name' => '直屬上級',   'options' => '',      'tips' => '');

$lang->approvalflow->conditionTypeList = array();
$lang->approvalflow->conditionTypeList['user'] = array('name' => '發起人', 'selectType' => array());
$lang->approvalflow->conditionTypeList['user']['selectType']['account'] = array('name' => '姓名',     'type' => 'select', 'options' => 'users', 'tips' => '選擇人員');
$lang->approvalflow->conditionTypeList['user']['selectType']['dept']    = array('name' => '從屬部門', 'type' => 'select', 'options' => 'depts', 'tips' => '選擇部門');
$lang->approvalflow->conditionTypeList['user']['selectType']['role']    = array('name' => '所屬角色', 'type' => 'select', 'options' => 'roles', 'tips' => '選擇角色');

$lang->approvalflow->emptyName = '名稱不能為空！';

$lang->approvalflow->role = new stdclass();
$lang->approvalflow->role->create = '創建角色';
$lang->approvalflow->role->browse = '角色列表';
$lang->approvalflow->role->edit   = '編輯角色';
$lang->approvalflow->role->member = '角色成員';
$lang->approvalflow->role->delete = '刪除角色';

$lang->approvalflow->role->name   = '角色名稱';
$lang->approvalflow->role->code   = '角色代號';
$lang->approvalflow->role->desc   = '角色描述';

$lang->approvalflow->conditionText = new stdClass();
$lang->approvalflow->conditionText->user = '發起人姓名為';
$lang->approvalflow->conditionText->dept = '發起人的從屬部門為';
$lang->approvalflow->conditionText->role = '發起人的所屬角色為';
