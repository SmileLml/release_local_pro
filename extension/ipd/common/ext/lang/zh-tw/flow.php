<?php
$lang->navIcons['workflow'] = "<i class='icon icon-flow'></i>";

/* Workflow */
$lang->workflow = new stdclass();
$lang->workflow->common = '工作流';

$lang->mainNav->workflow = "{$lang->navIcons['workflow']} {$lang->workflow->common}|workflow|browseFlow|";
$lang->mainNav->menuOrder[80] = 'workflow';

$lang->semicolon        = '；';
$lang->view             = '查看';
$lang->detail           = '詳情';
$lang->basicInfo        = '基本信息';
$lang->extInfo          = '擴展信息';
$lang->chooseUserToMail = '選擇要發送提醒的用戶...';
$lang->importIcon       = "<i class='icon-import'> </i>";
$lang->exportIcon       = "<i class='icon-export'> </i>";
$lang->determine        = '確定';

$lang->workflow->menu = new stdclass();
$lang->workflow->menu->flow       = array('link' => '流程|workflow|browseflow|', 'alias' => 'browse', 'subModule' => 'workflowaction,workflowcondition,workflowlabel,workflowlayout,workflowlinkage,workflowhook');
$lang->workflow->menu->datasource = array('link' => '數據源|workflowdatasource|browse|');
$lang->workflow->menu->rule       = array('link' => '驗證規則|workflowrule|browse|');

$lang->workflow->menuOrder[5]  = 'flow';
$lang->workflow->menuOrder[10] = 'datasource';
$lang->workflow->menuOrder[15] = 'rule';

$lang->navGroup->workflow           = 'workflow';
$lang->navGroup->workflowrule       = 'workflow';
$lang->navGroup->workflowaction     = 'workflow';
$lang->navGroup->workflowhook       = 'workflow';
$lang->navGroup->workflowlinkage    = 'workflow';
$lang->navGroup->workflowlayout     = 'workflow';
$lang->navGroup->workflowlabel      = 'workflow';
$lang->navGroup->workflowfield      = 'workflow';
$lang->navGroup->workflowdatasource = 'workflow';
$lang->navGroup->workflowcondition  = 'workflow';
$lang->navGroup->workflowrelation   = 'workflow';
$lang->navGroup->workflowreport     = 'workflow';

/* Makes the main menu high light. */

/* Init flow module. */
$lang->flow = new stdclass();

/* Add lang from ranzhi. */
$lang->exportAll      = '導出全部記錄';
$lang->exportThisPage = '導出本頁記錄';
$lang->setFileNum     = '記錄數';
$lang->setFileType    = '檔案類型';
$lang->flowNotRelease = '該工作流還沒有發佈';
