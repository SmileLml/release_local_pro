<?php
$lang->marketresearch->create        = 'Create';
$lang->marketresearch->name          = 'Name';
$lang->marketresearch->market        = 'Market';
$lang->marketresearch->PM            = 'PM';
$lang->marketresearch->dateRange     = 'Plan Duration';
$lang->marketresearch->desc          = 'Description';
$lang->marketresearch->acl           = 'ACL';
$lang->marketresearch->begin         = 'Planned Begin';
$lang->marketresearch->to            = 'To';
$lang->marketresearch->end           = 'Planned End';
$lang->marketresearch->longTime      = 'Long-Term';
$lang->marketresearch->status        = 'Status';
$lang->marketresearch->realBegan     = 'Actual Begin';
$lang->marketresearch->realEnd       = 'Actual End';
$lang->marketresearch->progress      = 'Progress';
$lang->marketresearch->openedBy      = 'Creator';
$lang->marketresearch->mine          = 'My';
$lang->marketresearch->view          = 'Research Overview';
$lang->marketresearch->edit          = 'Edit Research';
$lang->marketresearch->activate      = 'Activate Research';
$lang->marketresearch->start         = 'Start Research';
$lang->marketresearch->close         = 'Close Research';
$lang->marketresearch->teamAction    = 'Team List';
$lang->marketresearch->report        = 'Report';
$lang->marketresearch->delete        = 'Delete Research';
$lang->marketresearch->all           = 'All Researches';
$lang->marketresearch->browse        = 'Researches';
$lang->marketresearch->team          = 'Team';
$lang->marketresearch->manageMembers = 'Manage Team';
$lang->marketresearch->unlinkMember  = 'Remove Member';
$lang->marketresearch->reports       = 'Research Report';
$lang->marketresearch->stage         = 'Research Task List';
$lang->marketresearch->createTask    = 'Create Task';
$lang->marketresearch->editTask      = 'Edit Task';
$lang->marketresearch->startTask     = 'Start Task';
$lang->marketresearch->closeTask     = 'Close Task';
$lang->marketresearch->finishTask    = 'Finish Task';
$lang->marketresearch->createStage   = 'Create Stage';
$lang->marketresearch->batchStage    = 'Batch Stage';
$lang->marketresearch->editStage     = 'Edit Stage';
$lang->marketresearch->startStage    = 'Start Stage';
$lang->marketresearch->closeStage    = 'Close Stage';
$lang->marketresearch->closedBy      = 'ClosedBy';
$lang->marketresearch->closedDate    = 'ClosedDate';
$lang->marketresearch->activateStage = 'Activate Stage';
$lang->marketresearch->deleteStage   = 'Delete Stage';
$lang->marketresearch->common        = 'Research';
$lang->marketresearch->whitelist     = 'Research Whitelist';
$lang->marketresearch->execution     = $lang->executionCommon;

$lang->researchstage = new stdclass();
$lang->researchstage->common = 'Research Stage';

$lang->researchtask = new stdclass();
$lang->researchtask->common = 'Research Task';

$lang->marketresearch->startTask          = 'Start Task';
$lang->marketresearch->finishTask         = 'Finish Task';
$lang->marketresearch->closeTask          = 'Close Task';
$lang->marketresearch->recordTaskEstimate = 'Effort';
$lang->marketresearch->editTask           = 'Edit Task';
$lang->marketresearch->activateTask       = 'Activate Task';
$lang->marketresearch->deleteTask         = 'Delete Task';
$lang->marketresearch->cancelTask         = 'Cancel Task';
$lang->marketresearch->viewTask           = 'View Task';
$lang->marketresearch->taskAssignTo       = 'TaskAssignTo';
$lang->marketresearch->batchCreateTask    = 'BatchCreateTask';

$lang->marketresearch->marketNotEmpty      = '『market』not empty.';
$lang->marketresearch->readjustTime        = 'Realigning start&end research date';
$lang->marketresearch->cannotGe            = '%s『%s』not greater than %s『%s』';
$lang->marketresearch->closedReason        = 'Closed reason';
$lang->marketresearch->confirmDelete       = 'Are you sure delete the research \"%s\"? Tasks and reports related to this research will be hidden after deletion!';
$lang->marketresearch->stageConfirmDelete  = 'Are you sure delete the stage \"%s\"? Tasks related to this stage will be hidden after deletion.';
$lang->marketresearch->noMembers           = 'No team members yet.';
$lang->marketresearch->confirmUnlinkMember = 'Do you want to unlink this user from market research?';

$lang->marketresearch->create      = 'Create';
$lang->marketresearch->name        = 'Name';
$lang->marketresearch->market      = 'Market';
$lang->marketresearch->PM          = 'PM';
$lang->marketresearch->dateRange   = 'Date Range';
$lang->marketresearch->desc        = 'Desc';
$lang->marketresearch->acl         = 'Acl';
$lang->marketresearch->begin       = 'Begin';
$lang->marketresearch->to          = 'To';
$lang->marketresearch->end         = 'End';
$lang->marketresearch->longTime    = 'LongTime';
$lang->marketresearch->createTask  = 'Create Task';
$lang->marketresearch->createStage = 'Create Stage';

$lang->marketresearch->endList[31]  = 'One month';
$lang->marketresearch->endList[93]  = 'Trimester';
$lang->marketresearch->endList[186] = 'Half year';
$lang->marketresearch->endList[365] = 'One year';
$lang->marketresearch->endList[999] = 'Longtime';

$lang->marketresearch->aclList['private'] = "Private (For the research leader, team members only)";
$lang->marketresearch->aclList['open']    = "Open (accessible with research view permissions)";

$lang->marketresearch->featureBar = array();
$lang->marketresearch->featureBar['all'] = array();
$lang->marketresearch->featureBar['all']['all']    = 'All';
$lang->marketresearch->featureBar['all']['wait']   = 'Waiting';
$lang->marketresearch->featureBar['all']['doing']  = 'Doing';
$lang->marketresearch->featureBar['all']['closed'] = 'Closed';

$lang->marketresearch->statusList = array();
$lang->marketresearch->statusList['wait']   = 'Waiting';
$lang->marketresearch->statusList['doing']  = 'Doing';
$lang->marketresearch->statusList['closed'] = 'Closed';

$lang->marketresearch->reasonList = array();
$lang->marketresearch->reasonList['finished']  = 'finished';
$lang->marketresearch->reasonList['cancelled'] = 'cancelled';

$lang->marketresearch->featureBar['task']['all']          = 'All';
$lang->marketresearch->featureBar['task']['unclosed']     = 'Unclosed';
$lang->marketresearch->featureBar['task']['assignedtome'] = 'AssignedToMe';
$lang->marketresearch->featureBar['task']['myinvolved']   = 'MyInvolved';
$lang->marketresearch->featureBar['task']['assignedbyme'] = 'AssignedByMe';
$lang->marketresearch->featureBar['task']['status']       = $lang->more;

$lang->marketresearch->task = new stdclass();
$lang->marketresearch->task->afterChoices['continueAdding'] = "Continue Adding Tasks";
$lang->marketresearch->task->afterChoices['toTaskList']     = 'Go to Task List';

$lang->marketresearch->stageAcl['open'] = "Inherited {$lang->marketresearch->common} ACL (for who can access the current{$lang->marketresearch->common})";

$lang->marketresearch->summary    = "Top stage: <strong>{0}</strong>. Child stage: <strong>{1}</strong>. Task: <strong>{2}</strong>.";
$lang->marketresearch->allSummary = "Top stage: <strong>{0}</strong>. Child stage: <strong>{1}</strong>. Task: <strong>{2}</strong>. Unclosed: <strong>{3}</strong>. Waiting: <strong>{4}</strong>. Doing: <strong>{5}</strong>.";
