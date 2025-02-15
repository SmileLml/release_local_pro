<?php
/**
 * The convert module zh-tw file of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禪道軟件（青島）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     convert
 * @version     $Id: zh-tw.php 4129 2013-01-18 01:58:14Z wwccss $
 * @link        http://www.zentao.net
 */
$lang->convert->common  = '從其他系統導入';
$lang->convert->index   = '首頁';

$lang->convert->start   = '開始轉換';
$lang->convert->desc    = <<<EOT
<p>歡迎使用系統轉換嚮導，本程序會幫助您將其他系統的數據轉換到禪道項目管理系統中。</p>
<strong>轉換存在一定的風險，轉換之前，我們強烈建議您備份資料庫及相應的數據檔案，並保證轉換的時候，沒有其他人進行操作。</strong>
EOT;

$lang->convert->setConfig      = '來源系統配置';
$lang->convert->setBugfree     = 'Bugfree配置';
$lang->convert->setRedmine     = 'Redmine配置';
$lang->convert->checkBugFree   = '檢查Bugfree';
$lang->convert->checkRedmine   = '檢查Redmine';
$lang->convert->convertRedmine = '轉換Redmine';
$lang->convert->convertBugFree = '轉換BugFree';

$lang->convert->selectSource     = '選擇來源系統及版本';
$lang->convert->mustSelectSource = "必須選擇一個來源。";

$lang->convert->direction             = "請選擇{$lang->executionCommon}問題轉換方向";
$lang->convert->questionTypeOfRedmine = 'Redmine中問題類型';
$lang->convert->aimTypeOfZentao       = '轉化為Zentao中的類型';

$lang->convert->directionList['bug']   = 'Bug';
$lang->convert->directionList['task']  = '任務';
$lang->convert->directionList['story'] = $lang->SRCommon;

$lang->convert->sourceList['BugFree'] = array('bugfree_1' => '1.x', 'bugfree_2' => '2.x');
$lang->convert->sourceList['Redmine'] = array('Redmine_1.1' => '1.1');

$lang->convert->setting     = '設置';
$lang->convert->checkConfig = '檢查配置';

$lang->convert->ok          = '<span class="text-success"><i class="icon-check-sign"></i> 檢查通過</span>';
$lang->convert->fail        = '<span class="text-danger"><i class="icon-remove-sign"></i> 檢查失敗</span>';

$lang->convert->dbHost      = '資料庫伺服器';
$lang->convert->dbPort      = '伺服器連接埠';
$lang->convert->dbUser      = '資料庫用戶名';
$lang->convert->dbPassword  = '資料庫密碼';
$lang->convert->dbName      = '%s使用的庫';
$lang->convert->dbCharset   = '%s資料庫編碼';
$lang->convert->dbPrefix    = '%s表首碼';
$lang->convert->installPath = '%s安裝的根目錄';

$lang->convert->checkDB    = '資料庫';
$lang->convert->checkTable = '表';
$lang->convert->checkPath  = '安裝路徑';

$lang->convert->execute    = '執行轉換';
$lang->convert->item       = '轉換項';
$lang->convert->count      = '轉換數量';
$lang->convert->info       = '轉換信息';

$lang->convert->bugfree = new stdclass();
$lang->convert->bugfree->users      = '用戶';
$lang->convert->bugfree->executions = $lang->executionCommon;
$lang->convert->bugfree->modules    = '模組';
$lang->convert->bugfree->bugs       = 'Bug';
$lang->convert->bugfree->cases      = '測試用例';
$lang->convert->bugfree->results    = '測試結果';
$lang->convert->bugfree->actions    = '歷史記錄';
$lang->convert->bugfree->files      = '附件';

$lang->convert->redmine = new stdclass();
$lang->convert->redmine->users        = '用戶';
$lang->convert->redmine->groups       = '用戶分組';
$lang->convert->redmine->products     = $lang->productCommon;
$lang->convert->redmine->executions   = $lang->executionCommon;
$lang->convert->redmine->stories      = $lang->SRCommon;
$lang->convert->redmine->tasks        = '任務';
$lang->convert->redmine->bugs         = 'Bug';
$lang->convert->redmine->productPlans = $lang->productCommon . '計劃';
$lang->convert->redmine->teams        = '團隊';
$lang->convert->redmine->releases     = '發佈';
$lang->convert->redmine->builds       = 'Build';
$lang->convert->redmine->docLibs      = '文檔庫';
$lang->convert->redmine->docs         = '文檔';
$lang->convert->redmine->files        = '附件';

$lang->convert->errorFileNotExits  = '檔案 %s 不存在';
$lang->convert->errorUserExists    = '用戶 %s 已存在';
$lang->convert->errorGroupExists   = '分組 %s 已存在';
$lang->convert->errorBuildExists   = 'Build %s 已存在';
$lang->convert->errorReleaseExists = '發佈 %s 已存在';
$lang->convert->errorCopyFailed    = '檔案 %s 拷貝失敗';

$lang->convert->setParam = '請設置轉換參數';

$lang->convert->statusType = new stdclass();
$lang->convert->priType    = new stdclass();

$lang->convert->aimType           = '問題類型轉換';
$lang->convert->statusType->bug   = '狀態類型轉換(Bug狀態)';
$lang->convert->statusType->story = '狀態類型轉換(Story狀態)';
$lang->convert->statusType->task  = '狀態類型轉換(Task狀態)';
$lang->convert->priType->bug      = '優先順序類型轉換(Bug狀態)';
$lang->convert->priType->story    = '優先順序類型轉換(Story狀態)';
$lang->convert->priType->task     = '優先順序類型轉換(Task狀態)';

$lang->convert->issue = new stdclass();
$lang->convert->issue->redmine = 'Redmine';
$lang->convert->issue->zentao  = '禪道';
$lang->convert->issue->goto    = '轉換為';

$lang->convert->jira = new stdclass();
$lang->convert->jira->method           = '選擇導入方式';
$lang->convert->jira->next             = '下一步';
$lang->convert->jira->importFromDB     = '從資料庫導入';
$lang->convert->jira->importFromFile   = '從檔案導入';
$lang->convert->jira->mapJira2Zentao   = '設置Jira與禪道數據對應關係';
$lang->convert->jira->dbNameNotice     = '請輸入Jira資料庫名字';
$lang->convert->jira->importNotice     = '注意：導入數據有風險！請務必確保如下操作步驟依次完成，再進行合併。';
$lang->convert->jira->dbDesc           = '<p>如果您的Jira使用Mysql資料庫, 請選擇此方式</p>';
$lang->convert->jira->fileDesc         = '<p>如果您的Jira使用非Mysql資料庫, 請選擇此方式</p>';
$lang->convert->jira->jiraObject       = 'Jira Issues';
$lang->convert->jira->zentaoObject     = '禪道對象';
$lang->convert->jira->jiraLinkType     = 'Jira 關聯關係';
$lang->convert->jira->zentaoLinkType   = '禪道關聯關係';
$lang->convert->jira->jiraResolution   = 'Jira 解決方案';
$lang->convert->jira->zentaoResolution = '禪道Bug解決方案';
$lang->convert->jira->zentaoReason     = '禪道需求關閉原因';
$lang->convert->jira->jiraStatus       = 'Jira Issues 狀態';
$lang->convert->jira->storyStatus      = '禪道需求狀態';
$lang->convert->jira->storyStage       = '禪道需求階段';
$lang->convert->jira->bugStatus        = '禪道Bug狀態';
$lang->convert->jira->taskStatus       = '禪道任務狀態';
$lang->convert->jira->initJiraUser     = '設置Jira用戶';
$lang->convert->jira->importJira       = '導入Jira';
$lang->convert->jira->start            = '開始導入';

$lang->convert->jira->dbNameEmpty        = 'Jira資料庫名字不能為空！';
$lang->convert->jira->invalidDB          = '無效的資料庫名！';
$lang->convert->jira->invalidTable       = '本資料庫非Jira資料庫！';
$lang->convert->jira->notReadAndWrite    = '權限不足！請修改%s目錄讀寫權限！';
$lang->convert->jira->notExistEntities   = '%s 檔案不存在！';
$lang->convert->jira->passwordNotice     = "<span class='text-red'>設置Jira用戶導入到禪道後的預設密碼，用戶後續可以在禪道中自行修改密碼。</span>";
$lang->convert->jira->groupNotice        = "<span class='text-red'>設置Jira用戶導入到禪道後的預設權限分組。</span>";
$lang->convert->jira->passwordDifferent  = '兩次密碼不一致！';
$lang->convert->jira->passwordEmpty      = '密碼不能為空！';
$lang->convert->jira->passwordLess       = '密碼不能少於六位！';
$lang->convert->jira->importSuccessfully = 'Jira導入完成！';
$lang->convert->jira->importResult       = "導入 <strong class='text-red'>%s</strong> 數據, 已處理 <strong class='%scount'>%s</strong> 條記錄；";
$lang->convert->jira->importing          = '數據導入中，請不要切換其它頁面';
$lang->convert->jira->importingAB        = '數據導入中';
$lang->convert->jira->imported           = '數據導入完成';

$lang->convert->jira->zentaoObjectList[''] = '';
$lang->convert->jira->zentaoObjectList['task']        = '任務';
$lang->convert->jira->zentaoObjectList['requirement'] = '用戶需求';
$lang->convert->jira->zentaoObjectList['story']       = '軟件需求';
$lang->convert->jira->zentaoObjectList['bug']         = 'Bug';

$lang->convert->jira->zentaoLinkTypeList['subTaskLink']  = '父-子任務';
$lang->convert->jira->zentaoLinkTypeList['subStoryLink'] = '父-子需求';
$lang->convert->jira->zentaoLinkTypeList['duplicate']    = '重複對象';
$lang->convert->jira->zentaoLinkTypeList['relates']      = '互相關聯';

$lang->convert->jira->steps[1] = '對象';
$lang->convert->jira->steps[2] = '對象關聯關係';
$lang->convert->jira->steps[3] = '解決方案';
$lang->convert->jira->steps[4] = '狀態';

$lang->convert->jira->importSteps['db'][1]   = '備份禪道資料庫，備份Jira資料庫。';
$lang->convert->jira->importSteps['db'][2]   = '導入數據時使用禪道會給伺服器造成性能壓力，請儘量保證導入數據時無人使用禪道。';
$lang->convert->jira->importSteps['db'][3]   = '將Jira資料庫導入到禪道使用的Mysql中，名字和禪道資料庫區別開來。';
$lang->convert->jira->importSteps['db'][4]   = "將Jira附件目錄<strong class='text-red'> attachments</strong> 放到 <strong class='text-red'>%s</strong> 下，確保禪道伺服器磁碟空間足夠。";

$lang->convert->jira->importSteps['db'][5]   = "上述步驟完成後，請輸入Jira資料庫名字進行下一步。";
$lang->convert->jira->importSteps['file'][1] = '備份禪道資料庫，備份Jira資料庫。';
$lang->convert->jira->importSteps['file'][2] = '導入數據時使用禪道會給伺服器造成性能壓力，請儘量保證導入數據時無人使用禪道。';
$lang->convert->jira->importSteps['file'][3] = "將Jira的備份檔案 <strong class='text-red'>entities.xml</strong> 放到 <strong class='text-red'>%s</strong> 下，並給該目錄讀寫權限。";
$lang->convert->jira->importSteps['file'][4] = "將Jira附件目錄<strong class='text-red'> attachments</strong> 放到 <strong class='text-red'>%s</strong> 下，確保禪道伺服器磁碟空間足夠。";
$lang->convert->jira->importSteps['file'][5]   = "上述步驟完成後，點擊下一步。";

$lang->convert->jira->objectList['user']      = '用戶';
$lang->convert->jira->objectList['project']   = '項目';
$lang->convert->jira->objectList['issue']     = 'Issue';
$lang->convert->jira->objectList['build']     = '版本';
$lang->convert->jira->objectList['issuelink'] = '關聯關係';
$lang->convert->jira->objectList['action']    = '歷史記錄';
$lang->convert->jira->objectList['file']      = '附件';
