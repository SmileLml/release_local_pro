<?php
$lang->deploy->common           = 'Deployment Plan';
$lang->deploy->create           = 'Create Deployment Plan';
$lang->deploy->view             = 'Deploy Detail';
$lang->deploy->finish           = 'Finish';
$lang->deploy->finishAction     = 'Finish Deploy';
$lang->deploy->edit             = 'Edit';
$lang->deploy->editAction       = 'Edit Deploy';
$lang->deploy->delete           = 'Delete';
$lang->deploy->deleteAction     = 'Delete Deploy';
$lang->deploy->deleted          = 'Deleted';
$lang->deploy->activate         = 'Activate';
$lang->deploy->activateAction   = 'Activate Deploy';
$lang->deploy->browse           = 'Deployment';
$lang->deploy->browseAction     = 'Deploy List';
$lang->deploy->scope            = 'Deploy Scope';
$lang->deploy->manageScope      = 'Manage Scope';
$lang->deploy->cases            = 'Cases';
$lang->deploy->notify           = 'Notify';
$lang->deploy->casesAction      = 'Deploy Cases';
$lang->deploy->linkCases        = 'Link Case';
$lang->deploy->unlinkCase       = 'Unlink Case';
$lang->deploy->steps            = 'Deploy Step';
$lang->deploy->manageStep       = 'Manage Step';
$lang->deploy->finishStep       = 'Finish Step';
$lang->deploy->activateStep     = 'Activate Step';
$lang->deploy->assignTo         = 'AssignTo';
$lang->deploy->assignAction     = 'Assign Step';
$lang->deploy->editStep         = 'Edit Step';
$lang->deploy->deleteStep       = 'Delete Step';
$lang->deploy->viewStep         = 'Step Detail';
$lang->deploy->batchUnlinkCases = 'Batch Unlink Cases';
$lang->deploy->createdDate      = 'Created Date';

$lang->deploy->name       = 'Plan Name';
$lang->deploy->desc       = 'Description';
$lang->deploy->members    = 'Member';
$lang->deploy->hosts      = 'Hosts';
$lang->deploy->service    = 'Service';
$lang->deploy->product    = $lang->productCommon;
$lang->deploy->release    = 'Release';
$lang->deploy->package    = 'Package URL';
$lang->deploy->begin      = 'Begin';
$lang->deploy->end        = 'End';
$lang->deploy->status     = 'Status';
$lang->deploy->owner      = 'Owner';
$lang->deploy->stage      = 'Stage';
$lang->deploy->ditto      = 'Ditto';
$lang->deploy->manageAB   = 'Manage';
$lang->deploy->title      = 'Title';
$lang->deploy->content    = 'Content';
$lang->deploy->assignedTo = 'AssignedTo';
$lang->deploy->finishedBy = 'FinishedBy';
$lang->deploy->createdBy  = 'CreatedBy';
$lang->deploy->result     = 'Result';
$lang->deploy->updateHost = 'Update Host';
$lang->deploy->removeHost = 'ToBeRemoved Hosts';
$lang->deploy->addHost    = 'New Host';
$lang->deploy->hadHost    = 'Hosted';

$lang->deploy->lblBeginEnd = 'Duration';
$lang->deploy->lblBasic    = 'Basic Infomation';
$lang->deploy->lblProduct  = 'Link';
$lang->deploy->lblMonth    = 'Current';
$lang->deploy->toggle      = 'Toggle';

$lang->deploy->monthFormat = 'M Y';

$lang->deploy->statusList['wait'] = 'Waiting';
$lang->deploy->statusList['done'] = 'Done';

$lang->deploy->dateList['today']     = 'Today';
$lang->deploy->dateList['tomorrow']  = 'Tomorrow';
$lang->deploy->dateList['thisweek']  = 'This Week';
$lang->deploy->dateList['thismonth'] = 'This Month';
$lang->deploy->dateList['done']      = $lang->deploy->statusList['done'];

$lang->deploy->stageList['wait']    = 'Before Deploy';
$lang->deploy->stageList['doing']   = 'Deploying';
$lang->deploy->stageList['testing'] = 'Smoke Testing';
$lang->deploy->stageList['done']    = 'After Deploy';

$lang->deploy->resultList['']        = '';
$lang->deploy->resultList['success'] = 'Done';
$lang->deploy->resultList['fail']    = 'Failed';

$lang->deploy->confirmDelete     = 'Do you want to delete this deploy?';
$lang->deploy->confirmDeleteStep = 'Do you want to delete this step?';
$lang->deploy->errorTime         = 'The end time must be > start time!';
$lang->deploy->errorStatusWait   = 'If the status is Waiting, the FinishedBy should be empty';
$lang->deploy->errorStatusDone   = 'If the status is Done, the FinishedBy should be not empty';
$lang->deploy->errorOffline      = 'Hosts in Remove and Add for a service cannot be at the same time.';
$lang->deploy->resultNotEmpty    = 'Result cannot be empty';

$lang->deploystep = new stdClass();
$lang->deploystep->status       = $lang->deploy->status;
$lang->deploystep->assignedTo   = $lang->deploy->assignedTo;
$lang->deploystep->finishedBy   = $lang->deploy->finishedBy;
$lang->deploystep->finishedDate = 'Finished Date';
$lang->deploystep->begin        = $lang->deploy->begin;
$lang->deploystep->end          = $lang->deploy->end;
