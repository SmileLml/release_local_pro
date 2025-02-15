<?php
$lang->execution->editrelation     = 'Manage Task Relation';
$lang->execution->maintainRelation = 'Manage Relation';
$lang->execution->deleterelation   = 'Delete Relation';
$lang->execution->viewrelation     = 'View Relation';
$lang->execution->ganttchart       = 'Gantt Chart';
$lang->execution->ganttSetting     = 'Setting';
$lang->execution->ganttEdit        = 'Gantt Edit';

$lang->execution->gantt->common     = 'Gantt Chart';
$lang->execution->gantt->id         = 'ID';
$lang->execution->gantt->pretask    = 'Task';
$lang->execution->gantt->condition  = 'Action';
$lang->execution->gantt->task       = 'Task';
$lang->execution->gantt->action     = 'Action';
$lang->execution->gantt->type       = 'Type';

$lang->execution->gantt->createRelationOfTasks    = 'Add Task Relation';
$lang->execution->gantt->newCreateRelationOfTasks = 'Add Task Relation';
$lang->execution->gantt->editRelationOfTasks      = 'Edit Task Relation';
$lang->execution->gantt->relationOfTasks          = 'View Task Relation';
$lang->execution->gantt->relation                 = 'Task Relation';
$lang->execution->gantt->showCriticalPath         = 'Show Critical Path';
$lang->execution->gantt->hideCriticalPath         = 'Hide Critical Path';
$lang->execution->gantt->fullScreen               = 'Full Screen';

$lang->execution->gantt->zooming['day']   = 'Day';
$lang->execution->gantt->zooming['week']  = 'Week';
$lang->execution->gantt->zooming['month'] = 'Month';

$lang->execution->gantt->assignTo  = 'AssignedTo';
$lang->execution->gantt->duration  = 'Duration';
$lang->execution->gantt->comp      = 'Progress';
$lang->execution->gantt->startDate = 'Start Date';
$lang->execution->gantt->endDate   = 'End Date';
$lang->execution->gantt->days      = ' Days';
$lang->execution->gantt->format    = 'Format';

$lang->execution->gantt->preTaskStatus['']      = '';
$lang->execution->gantt->preTaskStatus['end']   = 'is finished, then';
$lang->execution->gantt->preTaskStatus['begin'] = 'is started, then';

$lang->execution->gantt->taskActions[''] = '';
$lang->execution->gantt->taskActions['begin'] = 'can be started.';
$lang->execution->gantt->taskActions['end']   = 'can be finished.';

$lang->execution->gantt->browseType['type']       = 'Group by Task Type';
$lang->execution->gantt->browseType['module']     = 'Group by Module';
$lang->execution->gantt->browseType['assignedTo'] = 'Group by AssignedTo';
$lang->execution->gantt->browseType['story']      = 'Group by Story';

$lang->execution->gantt->confirmDelete = 'Do you want to delete this relation?';
$lang->execution->gantt->tmpNotWrite   = 'Not Writable';

$lang->execution->gantt->showList[0] = 'Hide';
$lang->execution->gantt->showList[1] = 'Show';

$lang->execution->gantt->warning                 = new stdclass();
$lang->execution->gantt->warning->noEditSame     = "Tasks before and after the existing ID %s should not be the same.";
$lang->execution->gantt->warning->noEditRepeat   = "Task relation between the existing ID %s and ID %s is duplicated!";
$lang->execution->gantt->warning->noEditContrary = "Task relation between the existing ID %s and ID %s conflict!";
$lang->execution->gantt->warning->noRepeat       = "Task relation between the existing ID %s and the added ID %s is duplicated!";
$lang->execution->gantt->warning->noContrary     = "Task relation between the existing ID %s and the added ID %s conflict!";
$lang->execution->gantt->warning->noNewSame      = "Tasks before and after the added ID %s should not be the same.";
$lang->execution->gantt->warning->noNewRepeat    = "Task relation between the added ID %s and ID %s is duplicated!";
$lang->execution->gantt->warning->noNewContrary  = "Task relation between the added ID %s and ID %s conflict!";
$lang->execution->gantt->warning->noCreateLink   = "The task relationship already exists and cannot be created!";

$lang->execution->error = new stdClass();
$lang->execution->error->wrongGanttRelation       = 'Dependencies can only be created for tasks.';
$lang->execution->error->wrongGanttRelationSource = 'The first object you choose is not a task.';
$lang->execution->error->wrongGanttRelationTarget = 'The second object you choose is not a task.';

$lang->execution->ganttCustom['id']         = 'ID';
$lang->execution->ganttCustom['branch']     = 'Branch';
$lang->execution->ganttCustom['assignedTo'] = 'AssignedTo';
$lang->execution->ganttCustom['progress']   = 'Progress';
$lang->execution->ganttCustom['begin']      = 'Begin';
$lang->execution->ganttCustom['realBegan']  = 'Real Begin';
$lang->execution->ganttCustom['deadline']   = 'Deadline';
$lang->execution->ganttCustom['realEnd']    = 'Real End';
$lang->execution->ganttCustom['duration']   = 'Duration';
$lang->execution->ganttCustom['estimate']   = 'Estimate';
$lang->execution->ganttCustom['consumed']   = 'Consumed';
$lang->execution->ganttCustom['left']       = 'Left';
$lang->execution->ganttCustom['delay']      = 'Delay';
$lang->execution->ganttCustom['delayDays']  = 'Delay Days';
$lang->execution->ganttCustom['openedBy']   = 'Opened By';
$lang->execution->ganttCustom['finishedBy'] = 'Finished By';
