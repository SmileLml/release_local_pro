<?php
/**
 * The build module zh-tw file of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禪道軟件（青島）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     build
 * @version     $Id: zh-tw.php 4129 2013-01-18 01:58:14Z wwccss $
 * @link        http://www.zentao.net
 */
$lang->build->common           = "版本";
$lang->build->create           = "創建版本";
$lang->build->edit             = "編輯版本";
$lang->build->linkStory        = "關聯{$lang->SRCommon}";
$lang->build->linkBug          = "關聯Bug";
$lang->build->delete           = "刪除版本";
$lang->build->deleted          = "已刪除";
$lang->build->view             = "版本詳情";
$lang->build->batchUnlink      = '批量移除';
$lang->build->batchUnlinkStory = "批量移除{$lang->SRCommon}";
$lang->build->batchUnlinkBug   = '批量移除Bug';
$lang->build->linkArtifactRepo = '關聯製品庫';

$lang->build->confirmDelete      = "您確認刪除該版本嗎？";
$lang->build->confirmUnlinkStory = "您確認移除該{$lang->SRCommon}嗎？";
$lang->build->confirmUnlinkBug   = "您確認移除該Bug嗎？";

$lang->build->basicInfo = '基本信息';

$lang->build->id             = 'ID';
$lang->build->product        = '所屬' . $lang->productCommon;
$lang->build->project        = '所屬' . $lang->projectCommon;
$lang->build->branch         = '平台/分支';
$lang->build->branchAll      = '所有關聯%s';
$lang->build->branchName     = '所屬%s';
$lang->build->execution      = '所屬' . $lang->executionCommon;
$lang->build->executionAB    = '所屬執行';
$lang->build->integrated     = '整合版本';
$lang->build->singled        = '單一版本';
$lang->build->builds         = '包含版本';
$lang->build->released       = '發佈';
$lang->build->name           = '名稱編號';
$lang->build->nameAB         = '名稱';
$lang->build->date           = '打包日期';
$lang->build->builder        = '構建者';
$lang->build->url            = '地址';
$lang->build->scmPath        = '原始碼地址';
$lang->build->filePath       = '下載地址';
$lang->build->desc           = '描述';
$lang->build->mailto         = 'Mailto';
$lang->build->files          = '上傳發行包';
$lang->build->last           = '上個版本';
$lang->build->packageType    = '包類型';
$lang->build->unlinkStory    = "移除{$lang->SRCommon}";
$lang->build->unlinkBug      = '移除Bug';
$lang->build->stories        = "完成的{$lang->SRCommon}";
$lang->build->bugs           = '解決的Bug';
$lang->build->generatedBugs  = '產生的Bug';
$lang->build->noProduct      = " <span id='noProduct' style='color:red'>該{$lang->executionCommon}沒有關聯{$lang->productCommon}，無法創建版本，請先<a href='%s' data-app='%s' data-toggle='modal' data-type='iframe'>關聯{$lang->productCommon}</a></span>";
$lang->build->noBuild        = '暫時沒有版本。';
$lang->build->emptyExecution = $lang->executionCommon . '不能為空。';
$lang->build->linkedBuild    = '關聯版本';

$lang->build->notice = new stdclass();
$lang->build->notice->changeProduct   = "已經關聯{$lang->SRCommon}、Bug或提交測試單的版本，不能修改其所屬{$lang->productCommon}";
$lang->build->notice->changeExecution = "提交測試單的版本，不能修改其所屬{$lang->executionCommon}";
$lang->build->notice->changeBuilds    = "提交測試單的版本，不能修改關聯版本";
$lang->build->notice->autoRelation    = "相關版本下完成的需求、解決的Bug、產生的Bug將會自動關聯到{$lang->projectCommon}版本中";
$lang->build->notice->createTest      = "該版本所屬執行已刪除，不能提交測試";

$lang->build->confirmChangeBuild = "%s『%s』解除關聯後，%s下 %s個{$lang->SRCommon}和%s個Bug將同步從版本移除，是否解除？";
$lang->build->confirmRemoveStory = "%s『%s』解除關聯後，%s下 %s個{$lang->SRCommon}將同步從計劃中移除，是否解除？";
$lang->build->confirmRemoveBug   = "%s『%s』解除關聯後，%s下 %s個Bug將同步從計劃中移除，是否解除？";
$lang->build->confirmRemoveTips  = "確認刪除%s『%s』嗎？";

$lang->build->finishStories = " 本次共完成 %s 個{$lang->SRCommon}";
$lang->build->resolvedBugs  = ' 本次共解決 %s 個Bug';
$lang->build->createdBugs   = ' 本次共產生 %s 個Bug';

$lang->build->placeholder = new stdclass();
$lang->build->placeholder->scmPath        = ' 軟件原始碼庫，如Subversion、Git庫地址';
$lang->build->placeholder->filePath       = ' 該版本軟件包下載存儲地址';
$lang->build->placeholder->multipleSelect = "版本支持多選";

$lang->build->action = new stdclass();
$lang->build->action->buildopened = '$date, 由 <strong>$actor</strong> 創建版本 <strong>$extra</strong>。' . "\n";

$lang->backhome = '返回';

$lang->build->isIntegrated = array();
$lang->build->isIntegrated['no']  = '否';
$lang->build->isIntegrated['yes'] = '是';
