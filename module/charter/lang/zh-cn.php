<?php
$lang->charter->id           = '编号';
$lang->charter->idAB         = 'ID';
$lang->charter->category     = '类型';
$lang->charter->status       = '状态';
$lang->charter->name         = '项目名称';
$lang->charter->nameAB       = '名称';
$lang->charter->level        = '级别';
$lang->charter->market       = '适用市场';
$lang->charter->product      = '产品';
$lang->charter->roadmap      = '路标';
$lang->charter->createdBy    = '由谁创建';
$lang->charter->createdDate  = '创建时间';
$lang->charter->spec         = '描述';
$lang->charter->mailto       = '抄送给';
$lang->charter->reviewer     = '评审人员';
$lang->charter->reviewedBy   = '由谁评审';
$lang->charter->reviewedDate = '评审时间';
$lang->charter->appliedBy    = '申请人';
$lang->charter->appliedDate  = '申请时间';
$lang->charter->budget       = '预算';
$lang->charter->meetingDate     = '会议日期';
$lang->charter->meetingLocation = '会议地点';
$lang->charter->meetingMinutes  = '会议记录';
$lang->charter->closedBy        = '由谁关闭';
$lang->charter->closedDate      = '关闭时间';
$lang->charter->closedReason    = '关闭原因';
$lang->charter->activatedBy     = '由谁激活';
$lang->charter->activatedDate   = '激活时间';

$lang->charter->create   = '提交立项';
$lang->charter->edit     = '编辑';
$lang->charter->view     = '立项详情';
$lang->charter->delete   = '删除';
$lang->charter->browse   = '立项列表';
$lang->charter->review   = '评审结果';
$lang->charter->add      = '添加';
$lang->charter->noData   = '暂无数据';
$lang->charter->colorTag = '颜色标签';
$lang->charter->deleted  = "已删除";
$lang->charter->close    = "关闭";
$lang->charter->activate = "激活";

$lang->charter->reviewedResult = '评审结果';
$lang->charter->check          = '复选框';

$lang->charter->charterFiles = '立项资料';
$lang->charter->loadStories  = '查看路标需求';
$lang->charter->roadmapStory = '路标需求';

$lang->charter->confirmDelete = '您确定要删除该立项信息吗？';
$lang->charter->confirmActivate       = '您确定要激活该立项申请吗？';
$lang->charter->confirmLaunchedNotice = '已立项的内容不允许删除';
$lang->charter->roadmapConflict       = "提示：该立项对应的路标已经在【ID:%s %s】立项流程中通过立项，请修改路标后再操作";

$lang->charter->levelList[0] = '';
$lang->charter->levelList[1] = '1';
$lang->charter->levelList[2] = '2';
$lang->charter->levelList[3] = '3';
$lang->charter->levelList[4] = '4';

$lang->charter->categoryList['IPD'] = '产品研发项目';
$lang->charter->categoryList['TPD'] = '预研项目';
$lang->charter->categoryList['CBB'] = '平台项目';
$lang->charter->categoryList['CPD'] = '定制项目';

$lang->charter->marketList['domestic'] = '国内';
$lang->charter->marketList['foreign']  = '国外';

$lang->charter->charterList['charter'] = '项目任务书';
$lang->charter->charterList['BP']      = '业务计划书';
$lang->charter->charterList['other']   = '其他';

$lang->charter->statusList['wait']     = '待立项';
$lang->charter->statusList['launched'] = '已立项';
$lang->charter->statusList['failed']   = '未通过';
$lang->charter->statusList['closed']   = '已关闭';

$lang->charter->closeReasonList['done']     = '已完成';
$lang->charter->closeReasonList['canceled'] = '已取消';

$lang->charter->reviewResultList['failed']   = '未通过';
$lang->charter->reviewResultList['launched'] = '立项通过';

$lang->charter->IPMT  = '您可以进入立项二级导航的设置维护IPMT人员';

$lang->charter->action = new stdclass();
$lang->charter->action->reviewbycharter = array('main' => '$date, 由 <strong>$actor</strong> 记录评审结果，结果为 <strong>$extra</strong>。', 'extra' => 'reviewResultList');
