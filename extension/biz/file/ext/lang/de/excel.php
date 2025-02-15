<?php
$lang->file->onlySupportXLSX = 'Only xlsx format import is supported. Please convert xlsx format and import again.';

$lang->excel->fileField = 'File';

$lang->excel->title            = new stdclass();
$lang->excel->title->testcase  = 'Case';
$lang->excel->title->bug       = 'Bug';
$lang->excel->title->task      = 'Task';
$lang->excel->title->story     = 'Story';
$lang->excel->title->caselib   = 'Library';
$lang->excel->title->sysValue  = 'System';
$lang->excel->title->tree      = 'Tree';
$lang->excel->title->user      = 'User';
$lang->excel->title->project   = 'Project';

$lang->excel->error = new stdclass();
$lang->excel->error->info       = 'The value you entered is not in the dropdown.';
$lang->excel->error->title      = 'Input Error';
$lang->excel->error->noFile     = 'No File.';
$lang->excel->error->noData     = 'No Data.';
$lang->excel->error->canNotRead = 'It cannot parse this file.';

$lang->excel->help           = new stdclass();
$lang->excel->help->testcase = "Use + '.' to mark case steps in a new line. Use + '.' for expectations corresponded to each steps. %s are required. If left empty, data in the same row will be ommitted. ";
$lang->excel->help->bug      = "When adding bugs, %s are required. If left empty, data in the same row will be ommitted.";
$lang->excel->help->task     = "When adding tasks, %s are required. If left empty, data in the same row will be ommitted. Use '>'  before the titile to mark child tasks.";
$lang->excel->help->multiple = 'If you add multiple user tasks, please add it in the column of "Est.(h)" in "user name : Est.(h)" format, and each user will be in one line. User names are listed in the "F" column of the "system" worksheet.';
$lang->excel->help->taskMode = 'Please add the "task mode" for multiple uesr tasks, the system will be ommitted "task mode" when importing "non-multi user tasks"';
$lang->excel->help->taskTip  = 'A task cannot be a child task which is a multi-user task.';
$lang->excel->help->story    = "When adding stroies, %s are required. If left empty, data in the same row will be ommitted.";
$lang->excel->help->user     = "Account and name is required. If left empty, data in the same row will be ommitted.";
$lang->excel->help->feedback = "When adding feedbacks, %s are required. If left empty, data in the same row will be ommitted.";
$lang->excel->help->ticket   = "When adding tickets, title,' . $lang->productCommon . ',module are required. If left empty, data in the same row will be ommitted.";
$lang->excel->help->demand   = "When adding stories, %s are required. If left empty, data in the same row will be ommitted.";
