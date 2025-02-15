<?php
$lang->approvalflow->browse        = '流程列表';
$lang->approvalflow->create        = '创建审批流';
$lang->approvalflow->edit          = '编辑审批流';
$lang->approvalflow->view          = '审批流详情';
$lang->approvalflow->delete        = '删除审批流';
$lang->approvalflow->design        = '设计审批流';
$lang->approvalflow->roleList      = '审批角色';
$lang->approvalflow->createRole    = '创建角色';
$lang->approvalflow->editRole      = '编辑角色';
$lang->approvalflow->deleteRole    = '删除角色';

$lang->approvalflow->common        = '审批流';
$lang->approvalflow->id            = 'ID';
$lang->approvalflow->name          = '名称';
$lang->approvalflow->createdBy     = '创建人';
$lang->approvalflow->createdDate   = '创建日期';
$lang->approvalflow->noFlow        = '当前没有审批流';
$lang->approvalflow->title         = '标题';
$lang->approvalflow->reviewer      = '审批人';
$lang->approvalflow->type          = '审批类型';
$lang->approvalflow->desc          = '描述';
$lang->approvalflow->basicInfo     = '基本信息';
$lang->approvalflow->confirmDelete = '您确认要删除吗？';
$lang->approvalflow->setNode       = '节点设置';

$lang->approvalflow->nameList = array();
$lang->approvalflow->nameList['stage']  = '阶段审批';

$lang->approvalflow->typeList = array();
if(helper::hasFeature('waterfall') or helper::hasFeature('waterfallplus')) $lang->approvalflow->typeList['project'] = $lang->projectCommon . '审批';
$lang->approvalflow->typeList['workflow'] = '工作流审批';

$lang->approvalflow->nodeTypeList = array();
$lang->approvalflow->nodeTypeList['branch']    = '分支';
$lang->approvalflow->nodeTypeList['condition'] = '条件';
$lang->approvalflow->nodeTypeList['default']   = '默认';
$lang->approvalflow->nodeTypeList['other']     = '其他';
$lang->approvalflow->nodeTypeList['approval']  = '审批';
$lang->approvalflow->nodeTypeList['cc']        = '抄送';
$lang->approvalflow->nodeTypeList['start']     = '发起';
$lang->approvalflow->nodeTypeList['end']       = '结束';

$lang->approvalflow->userTypeList = array();
$lang->approvalflow->userTypeList['cc']        = '抄送人';
$lang->approvalflow->userTypeList['submitter'] = '发起人';
$lang->approvalflow->userTypeList['reviewer']  = '审批人';

$lang->approvalflow->noticeTypeList = array();
$lang->approvalflow->noticeTypeList['setReviewer']   = '设置审批人';
$lang->approvalflow->noticeTypeList['setCondition']  = '设置条件';
$lang->approvalflow->noticeTypeList['addCondition']  = '添加条件分支';
$lang->approvalflow->noticeTypeList['addParallel']   = '添加并行分支';
$lang->approvalflow->noticeTypeList['addCond']       = '添加条件';
$lang->approvalflow->noticeTypeList['addReviewer']   = '添加审批人';
$lang->approvalflow->noticeTypeList['addCC']         = '添加抄送人';
$lang->approvalflow->noticeTypeList['setCC']         = '设置抄送人';
$lang->approvalflow->noticeTypeList['setNode']       = '设置节点';
$lang->approvalflow->noticeTypeList['defaultBranch'] = '所有条件都会执行此流程';
$lang->approvalflow->noticeTypeList['otherBranch']   = '其他条件进入此流程';
$lang->approvalflow->noticeTypeList['conditionOr']   = '不设置条件或者满足其中一个条件即可执行';
$lang->approvalflow->noticeTypeList['when']          = '当';
$lang->approvalflow->noticeTypeList['type']          = '类型';
$lang->approvalflow->noticeTypeList['confirm']       = '确定';
$lang->approvalflow->noticeTypeList['reviewType']    = '审批类型';
$lang->approvalflow->noticeTypeList['ccType']        = '抄送类型';
$lang->approvalflow->noticeTypeList['set']           = '设置';
$lang->approvalflow->noticeTypeList['node']          = '节点';
$lang->approvalflow->noticeTypeList['title']         = '标题';
$lang->approvalflow->noticeTypeList['multipleType']  = '多人审批时采用的审批方式';
$lang->approvalflow->noticeTypeList['multipleAnd']   = '会签(需所有审批人同意)';
$lang->approvalflow->noticeTypeList['multipleOr']    = '或签(一名审批人同意即可)';
$lang->approvalflow->noticeTypeList['agentType']     = '当审批人为空时';
$lang->approvalflow->noticeTypeList['agentPass']     = '自动通过';
$lang->approvalflow->noticeTypeList['agentUser']     = '指定人员';
$lang->approvalflow->noticeTypeList['agentAdmin']    = '管理员';

$lang->approvalflow->warningList = array();
$lang->approvalflow->warningList['needReview']   = '请保留最少一个审批节点';
$lang->approvalflow->warningList['save']         = '您的修改内容还没有保存，您确定离开吗？';
$lang->approvalflow->warningList['selectUser']   = '请选择人员';
$lang->approvalflow->warningList['selectDept']   = '请选择部门';
$lang->approvalflow->warningList['selectRole']   = '请选择角色';
$lang->approvalflow->warningList['needReviewer'] = '审批人不能为空';
$lang->approvalflow->warningList['oneSelect']    = '只能有一个类型是 "发起人自选"';

$lang->approvalflow->reviewTypeList = array();
$lang->approvalflow->reviewTypeList['manual'] = '人工审批';
$lang->approvalflow->reviewTypeList['pass']   = '自动同意';
$lang->approvalflow->reviewTypeList['reject'] = '自动拒绝';

$lang->approvalflow->errorList = array();
$lang->approvalflow->errorList['needReivewer'] = '请填写全部审批人';
$lang->approvalflow->errorList['needCcer']     = '请填写全部抄送人';

$lang->approvalflow->reviewerTypeList = array();
$lang->approvalflow->reviewerTypeList['select']    = array('name' => '发起人自选', 'options' => '',      'tips' => '');
$lang->approvalflow->reviewerTypeList['appointee'] = array('name' => '指定人员',   'options' => 'users', 'tips' => '选择人员');
$lang->approvalflow->reviewerTypeList['role']      = array('name' => '角色',       'options' => 'roles', 'tips' => '选择角色');
$lang->approvalflow->reviewerTypeList['upLevel']   = array('name' => '直属上级',   'options' => '',      'tips' => '');

$lang->approvalflow->conditionTypeList = array();
$lang->approvalflow->conditionTypeList['user'] = array('name' => '发起人', 'selectType' => array());
$lang->approvalflow->conditionTypeList['user']['selectType']['account'] = array('name' => '姓名',     'type' => 'select', 'options' => 'users', 'tips' => '选择人员');
$lang->approvalflow->conditionTypeList['user']['selectType']['dept']    = array('name' => '从属部门', 'type' => 'select', 'options' => 'depts', 'tips' => '选择部门');
$lang->approvalflow->conditionTypeList['user']['selectType']['role']    = array('name' => '所属角色', 'type' => 'select', 'options' => 'roles', 'tips' => '选择角色');

$lang->approvalflow->emptyName = '名称不能为空！';

$lang->approvalflow->role = new stdclass();
$lang->approvalflow->role->create = '创建角色';
$lang->approvalflow->role->browse = '角色列表';
$lang->approvalflow->role->edit   = '编辑角色';
$lang->approvalflow->role->member = '角色成员';
$lang->approvalflow->role->delete = '删除角色';

$lang->approvalflow->role->name   = '角色名称';
$lang->approvalflow->role->code   = '角色代号';
$lang->approvalflow->role->desc   = '角色描述';

$lang->approvalflow->conditionText = new stdClass();
$lang->approvalflow->conditionText->user = '发起人姓名为';
$lang->approvalflow->conditionText->dept = '发起人的从属部门为';
$lang->approvalflow->conditionText->role = '发起人的所属角色为';
