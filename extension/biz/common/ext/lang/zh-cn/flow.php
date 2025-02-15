<?php
$lang->navIcons['workflow'] = "<i class='icon icon-flow'></i>";

/* Workflow */
$lang->workflow = new stdclass();
$lang->workflow->common = '工作流';

$lang->mainNav->workflow = "{$lang->navIcons['workflow']} {$lang->workflow->common}|workflow|browseFlow|";
$lang->mainNav->menuOrder[80] = 'workflow';

$lang->semicolon        = '；';
$lang->view             = '查看';
$lang->detail           = '详情';
$lang->basicInfo        = '基本信息';
$lang->extInfo          = '扩展信息';
$lang->chooseUserToMail = '选择要发送提醒的用户...';
$lang->importIcon       = "<i class='icon-import'> </i>";
$lang->exportIcon       = "<i class='icon-export'> </i>";
$lang->determine        = '确定';

$lang->workflow->menu = new stdclass();
$lang->workflow->menu->flow       = array('link' => '流程|workflow|browseflow|', 'alias' => 'browse', 'subModule' => 'workflowaction,workflowcondition,workflowlabel,workflowlayout,workflowlinkage,workflowhook');
$lang->workflow->menu->datasource = array('link' => '数据源|workflowdatasource|browse|');
$lang->workflow->menu->rule       = array('link' => '验证规则|workflowrule|browse|');

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
$lang->exportAll      = '导出全部记录';
$lang->exportThisPage = '导出本页记录';
$lang->setFileNum     = '记录数';
$lang->setFileType    = '文件类型';
$lang->flowNotRelease = '该工作流还没有发布';
