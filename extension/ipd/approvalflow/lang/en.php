<?php
$lang->approvalflow->browse        = 'Flow List';
$lang->approvalflow->create        = 'Create Flow';
$lang->approvalflow->edit          = 'Edit Flow';
$lang->approvalflow->view          = 'View Flow';
$lang->approvalflow->delete        = 'Delete Flow';
$lang->approvalflow->design        = 'Design Flow';
$lang->approvalflow->roleList      = 'Role List';
$lang->approvalflow->createRole    = 'Create Role';
$lang->approvalflow->editRole      = 'Edit Role';
$lang->approvalflow->deleteRole    = 'Delete Role';

$lang->approvalflow->common        = 'Approval Flow';
$lang->approvalflow->id            = 'ID';
$lang->approvalflow->name          = 'Name';
$lang->approvalflow->createdBy     = 'Created By';
$lang->approvalflow->createdDate   = 'Created Date';
$lang->approvalflow->noFlow        = 'No flows.';
$lang->approvalflow->title         = 'Title';
$lang->approvalflow->reviewer      = 'Reviewers';
$lang->approvalflow->type          = 'Type';
$lang->approvalflow->desc          = 'Description';
$lang->approvalflow->basicInfo     = 'Basic Infomation';
$lang->approvalflow->confirmDelete = 'Do you confirm delete it?';
$lang->approvalflow->setNode       = 'Set Node';

$lang->approvalflow->nameList = array();
$lang->approvalflow->nameList['stage']  = 'Stage';

$lang->approvalflow->typeList = array();
if(helper::hasFeature('waterfall') or helper::hasFeature('waterfallplus')) $lang->approvalflow->typeList['project'] = $lang->projectCommon;
$lang->approvalflow->typeList['workflow'] = 'Workflow';

$lang->approvalflow->nodeTypeList = array();
$lang->approvalflow->nodeTypeList['branch']    = 'Branch';
$lang->approvalflow->nodeTypeList['condition'] = 'Condition';
$lang->approvalflow->nodeTypeList['default']   = 'Default';
$lang->approvalflow->nodeTypeList['other']     = 'Other';
$lang->approvalflow->nodeTypeList['approval']  = 'Approval';
$lang->approvalflow->nodeTypeList['cc']        = 'Mail To';
$lang->approvalflow->nodeTypeList['start']     = 'Start';
$lang->approvalflow->nodeTypeList['end']       = 'Finish';

$lang->approvalflow->userTypeList = array();
$lang->approvalflow->userTypeList['cc']        = 'Mailto List';
$lang->approvalflow->userTypeList['submitter'] = 'Submitter';
$lang->approvalflow->userTypeList['reviewer']  = 'Reviewer';

$lang->approvalflow->noticeTypeList = array();
$lang->approvalflow->noticeTypeList['setReviewer']   = 'Set reviewer';
$lang->approvalflow->noticeTypeList['setCondition']  = 'Set condition';
$lang->approvalflow->noticeTypeList['addCondition']  = 'Add condition';
$lang->approvalflow->noticeTypeList['addParallel']   = 'Add parallel';
$lang->approvalflow->noticeTypeList['addCond']       = 'Add condition';
$lang->approvalflow->noticeTypeList['addReviewer']   = 'Add reviewer';
$lang->approvalflow->noticeTypeList['addCC']         = 'Add ccer';
$lang->approvalflow->noticeTypeList['setCC']         = 'Set ccer';
$lang->approvalflow->noticeTypeList['setNode']       = 'Set node';
$lang->approvalflow->noticeTypeList['defaultBranch'] = 'All conditions will be run';
$lang->approvalflow->noticeTypeList['otherBranch']   = 'Other condition will be run';
$lang->approvalflow->noticeTypeList['conditionOr']   = 'If one conditon is ok, run it';
$lang->approvalflow->noticeTypeList['when']          = 'When';
$lang->approvalflow->noticeTypeList['type']          = 'Type';
$lang->approvalflow->noticeTypeList['confirm']       = 'Confirm';
$lang->approvalflow->noticeTypeList['reviewType']    = 'Review type';
$lang->approvalflow->noticeTypeList['ccType']        = 'CC type';
$lang->approvalflow->noticeTypeList['set']           = 'Set';
$lang->approvalflow->noticeTypeList['node']          = 'node';
$lang->approvalflow->noticeTypeList['title']         = 'Title';
$lang->approvalflow->noticeTypeList['multipleType']  = 'If multiple person is reviewing';
$lang->approvalflow->noticeTypeList['multipleAnd']   = 'And(All reviewers must agree)';
$lang->approvalflow->noticeTypeList['multipleOr']    = 'Or(Only need one reviewer agree)';
$lang->approvalflow->noticeTypeList['agentType']     = 'When reviewer is empty';
$lang->approvalflow->noticeTypeList['agentPass']     = 'Pass';
$lang->approvalflow->noticeTypeList['agentUser']     = 'Appointer';
$lang->approvalflow->noticeTypeList['agentAdmin']    = 'Administrator';

$lang->approvalflow->warningList = array();
$lang->approvalflow->warningList['needReview']   = 'Please add one node at least';
$lang->approvalflow->warningList['save']         = 'Are you sure leave?';
$lang->approvalflow->warningList['selectUser']   = 'Please select user';
$lang->approvalflow->warningList['selectDept']   = 'Please select department';
$lang->approvalflow->warningList['selectRole']   = 'Please select role';
$lang->approvalflow->warningList['needReviewer'] = 'Please select reviewer';
$lang->approvalflow->warningList['oneSelect']    = 'Only one type is "Submitter select"';

$lang->approvalflow->reviewTypeList = array();
$lang->approvalflow->reviewTypeList['manual'] = 'Manual';
$lang->approvalflow->reviewTypeList['pass']   = 'Auto Pass';
$lang->approvalflow->reviewTypeList['reject'] = 'Auto Reject';

$lang->approvalflow->errorList = array();
$lang->approvalflow->errorList['needReivewer'] = 'Need reviewers';
$lang->approvalflow->errorList['needCcer']     = 'Need mailtos';

$lang->approvalflow->reviewerTypeList = array();
$lang->approvalflow->reviewerTypeList['select']    = array('name' => 'Submitter select', 'options' => '',      'tips' => '');
$lang->approvalflow->reviewerTypeList['appointee'] = array('name' => 'Appointee',        'options' => 'users', 'tips' => 'Users');
$lang->approvalflow->reviewerTypeList['role']      = array('name' => 'Roles',            'options' => 'roles', 'tips' => 'Roles');
$lang->approvalflow->reviewerTypeList['upLevel']   = array('name' => 'Higher level',     'options' => '',      'tips' => '');

$lang->approvalflow->conditionTypeList = array();
$lang->approvalflow->conditionTypeList['user'] = array('name' => 'Submiter', 'selectType' => array());
$lang->approvalflow->conditionTypeList['user']['selectType']['account'] = array('name' => 'Users', 'type' => 'select', 'options' => 'users', 'tips' => 'Users');
$lang->approvalflow->conditionTypeList['user']['selectType']['dept']    = array('name' => 'Depts', 'type' => 'select', 'options' => 'depts', 'tips' => 'Depts');
$lang->approvalflow->conditionTypeList['user']['selectType']['role']    = array('name' => 'Roles', 'type' => 'select', 'options' => 'roles', 'tips' => 'Roles');

$lang->approvalflow->emptyName = 'Please input name!';

$lang->approvalflow->role = new stdclass();
$lang->approvalflow->role->create = 'Create Role';
$lang->approvalflow->role->browse = 'Role List';
$lang->approvalflow->role->edit   = 'Edit Role';
$lang->approvalflow->role->member = 'Member';
$lang->approvalflow->role->delete = 'Delete Role';

$lang->approvalflow->role->name   = 'Role Name';
$lang->approvalflow->role->code   = 'Role Code';
$lang->approvalflow->role->desc   = 'Role Description';

$lang->approvalflow->conditionText = new stdClass();
$lang->approvalflow->conditionText->user = 'The name of the initiateur is ';
$lang->approvalflow->conditionText->dept = 'The department of the initiateur is ';
$lang->approvalflow->conditionText->role = 'The role of the initiateur is ';
