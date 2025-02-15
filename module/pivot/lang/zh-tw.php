<?php
/**
 * The pivot module zh-tw file of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禪道軟件（青島）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     pivot
 * @version     $Id: zh-tw.php 5080 2013-07-10 00:46:59Z wyd621@gmail.com $
 * @link        http://www.zentao.net
 */
$lang->pivot->index        = '透視表首頁';
$lang->pivot->list         = '透視表';
$lang->pivot->preview      = '查看透視表';
$lang->pivot->create       = '創建透視表';
$lang->pivot->edit         = '編輯透視表';
$lang->pivot->browse       = '瀏覽透視表';
$lang->pivot->delete       = '刪除透視表';
$lang->pivot->design       = '設計透視表';
$lang->pivot->export       = '導出透視表';
$lang->pivot->query        = '查詢';
$lang->pivot->browseAction = '進入透視表設計';
$lang->pivot->designAB     = '設計';
$lang->pivot->exportType   = '導出格式';
$lang->pivot->exportRange  = '導出範圍';

$lang->pivot->rangeList['current'] = '當前頁';
$lang->pivot->rangeList['all']     = '全部';

$lang->pivot->id          = 'ID';
$lang->pivot->name        = '名稱';
$lang->pivot->dataset     = '數據集';
$lang->pivot->dataview    = '數據';
$lang->pivot->type        = '類型';
$lang->pivot->config      = '參數配置';
$lang->pivot->desc        = '描述';
$lang->pivot->xaxis       = 'X軸';
$lang->pivot->yaxis       = 'Y軸';
$lang->pivot->group       = '所屬分組';
$lang->pivot->metric      = '指標';
$lang->pivot->column      = '列';
$lang->pivot->field       = '關聯欄位';
$lang->pivot->operator    = '條件';
$lang->pivot->orderby     = '排序';
$lang->pivot->order       = '排序';
$lang->pivot->add         = '添加';
$lang->pivot->valOrAgg    = '值/彙總';
$lang->pivot->value       = '值';
$lang->pivot->agg         = '彙總';
$lang->pivot->display     = '顯示名稱';
$lang->pivot->filterValue = '篩選值';
$lang->pivot->save        = '保存';
$lang->pivot->cancel      = '取消';
$lang->pivot->run         = '執行查詢';
$lang->pivot->must        = '請選擇';
$lang->pivot->split       = '分列顯示';
$lang->pivot->chooseField = '選擇欄位';
$lang->pivot->aggType     = '統計方式';
$lang->pivot->other       = '其他';
$lang->pivot->publish     = '發佈';
$lang->pivot->draft       = '存為草稿';
$lang->pivot->draftIcon   = '草稿';
$lang->pivot->nextStep    = '下一步';
$lang->pivot->saveSetting = '保存設置';
$lang->pivot->add         = '添加';
$lang->pivot->baseSetting = '基礎設置';
$lang->pivot->setLang     = '設置語言項';
$lang->pivot->toDesign    = '進入設計';
$lang->pivot->toPreview   = '退出設計';
$lang->pivot->variable    = '變數名稱';
$lang->pivot->varCode     = '變數代號';
$lang->pivot->varLabel    = '變數標籤';
$lang->pivot->monopolize  = '獨占一列';
$lang->pivot->varNameTip  = '請輸入字母';
$lang->pivot->item        = '條目';
$lang->pivot->percent     = '百分比';
$lang->pivot->undefined   = '未設定';
$lang->pivot->project     = $lang->projectCommon;
$lang->pivot->PO          = 'PO';
$lang->pivot->showPivot   = '查看透視表';
$lang->pivot->showOrigin  = '查看原始數據';

$lang->pivot->showOriginItem = '展示原始條目';
$lang->pivot->recTotalTip    = '共 <strong> %s </strong> 項';
$lang->pivot->recPerPageTip  = "每頁 <strong>%s</strong> 項";

$lang->pivot->showOriginPlaceholder = new stdclass();
$lang->pivot->showOriginPlaceholder->slice    = '展示原始條目後無需配置切片';
$lang->pivot->showOriginPlaceholder->calcMode = '展示原始條目後無需配置計算方式';
$lang->pivot->showOriginPlaceholder->showMode = '展示原始條目後無需配置顯示方式';

$lang->pivot->colors[] = 'AFD8F8';
$lang->pivot->colors[] = 'F6BD0F';
$lang->pivot->colors[] = '8BBA00';
$lang->pivot->colors[] = 'FF8E46';
$lang->pivot->colors[] = '008E8E';
$lang->pivot->colors[] = 'D64646';
$lang->pivot->colors[] = '8E468E';
$lang->pivot->colors[] = '588526';
$lang->pivot->colors[] = 'B3AA00';
$lang->pivot->colors[] = '008ED6';
$lang->pivot->colors[] = '9D080D';
$lang->pivot->colors[] = 'A186BE';

$lang->pivot->assign['noassign'] = '未指派';
$lang->pivot->assign['assign']   = '已指派';

$lang->pivot->singleColor[] = 'F6BD0F';

$lang->pivot->projectDeviation = "{$lang->execution->common}偏差報表";
$lang->pivot->productSummary   = $lang->productCommon . '彙總表';
$lang->pivot->bugCreate        = 'Bug創建表';
$lang->pivot->bugAssign        = '未解決Bug指派表';
$lang->pivot->workload         = '員工負載表';
$lang->pivot->workloadAB       = '工作負載';
$lang->pivot->bugOpenedDate    = 'Bug創建時間';
$lang->pivot->beginAndEnd      = '起止時間';
$lang->pivot->begin            = '起始日期';
$lang->pivot->end              = '結束日期';
$lang->pivot->dept             = '部門';
$lang->pivot->deviationChart   = "{$lang->execution->common}偏差曲綫";

$lang->pivotList = new stdclass();
$lang->pivotList->product = new stdclass();
$lang->pivotList->project = new stdclass();
$lang->pivotList->test    = new stdclass();
$lang->pivotList->staff   = new stdclass();

$lang->pivotList->product->lists[10] = $lang->productCommon . '彙總表|pivot|productsummary';
$lang->pivotList->project->lists[10] = "{$lang->execution->common}偏差報表|pivot|projectdeviation";
$lang->pivotList->test->lists[10]    = 'Bug創建表|pivot|bugcreate';
$lang->pivotList->test->lists[13]    = '未解決Bug指派表|pivot|bugassign';
$lang->pivotList->staff->lists[10]   = '員工負載表|pivot|workload';

$lang->pivot->id            = '編號';
$lang->pivot->execution     = $lang->execution->common;
$lang->pivot->product       = $lang->productCommon;
$lang->pivot->user          = '姓名';
$lang->pivot->bugTotal      = 'Bug';
$lang->pivot->task          = '任務數';
$lang->pivot->estimate      = '總預計';
$lang->pivot->consumed      = '總消耗';
$lang->pivot->remain        = '剩餘工時';
$lang->pivot->deviation     = '偏差';
$lang->pivot->deviationRate = '偏差率';
$lang->pivot->total         = '總計';
$lang->pivot->to            = '至';
$lang->pivot->taskTotal     = "總任務數";
$lang->pivot->manhourTotal  = "總工時";
$lang->pivot->validRate     = "有效率";
$lang->pivot->validRateTips = "方案為已解決或延期/狀態為已解決或已關閉";
$lang->pivot->unplanned     = "未計劃";
$lang->pivot->workday       = '每天工時';
$lang->pivot->diffDays      = '工作日天數';

$lang->pivot->typeList['default'] = '預設';
$lang->pivot->typeList['pie']     = '餅圖';
$lang->pivot->typeList['bar']     = '柱狀圖';
$lang->pivot->typeList['line']    = '折線圖';

$lang->pivot->conditions    = '篩選條件：';
$lang->pivot->closedProduct = '關閉' . $lang->productCommon;
$lang->pivot->overduePlan   = "過期計劃";

$lang->pivot->idAB         = 'ID';
$lang->pivot->bugTitle     = 'Bug標題';
$lang->pivot->taskName     = '任務名稱';
$lang->pivot->todoName     = '待辦名稱';
$lang->pivot->testTaskName = '版本名稱';
$lang->pivot->deadline     = '截止日期';

$lang->pivot->deviationDesc = '按照已關閉執行統計偏差率（偏差率 = (總消耗 - 總預計) / 總預計），總預計為0時偏差率為n/a。';
$lang->pivot->workloadDesc  = '工作負載=用戶所有任務剩餘工時之和/選擇的時間天數*每天的工時。例如：起止時間設為1月1日~1月7日、工作日天數5天、每天工時8h，統計的是所有指派給該人員的未完成的任務，在5天內，每天8h的情況下的工作負載。';

$lang->pivot->featureBar = array();
$lang->pivot->featureBar['preview'] = array();

$lang->pivot->showProduct = '所有' . $lang->productCommon . '統計數據';
$lang->pivot->showProject = '所有' . $lang->projectCommon . '統計數據';

$lang->pivot->columnIndex  = '第 %s 列';
$lang->pivot->deleteColumn = '您確認刪除該列嗎？';
$lang->pivot->addColumn    = '添加列';
$lang->pivot->sliceField   = '選擇切片欄位';
$lang->pivot->calcMode     = '值的計算方式';
$lang->pivot->showMode     = '值的顯示方式';
$lang->pivot->showTotal    = '顯示行的彙總';
$lang->pivot->colLabel     = '{$field}的{$stat}';
$lang->pivot->colShowMode  = '(%s)';

$lang->pivot->errorList = new stdclass();
$lang->pivot->errorList->cantequal = '%s 取值不能與 %s 相同, 請重新設計';

$lang->pivot->pie = new stdclass();
$lang->pivot->pie->group  = '扇區分組';
$lang->pivot->pie->metric = '扇區數值';
$lang->pivot->pie->stat   = '統計方式';

$lang->pivot->cluBarX = new stdclass();
$lang->pivot->cluBarX->xaxis = 'X軸';
$lang->pivot->cluBarX->yaxis = 'Y軸';
$lang->pivot->cluBarX->stat  = '統計方式';

$lang->pivot->cluBarY = new stdclass();
$lang->pivot->cluBarY->yaxis = 'X軸';
$lang->pivot->cluBarY->xaxis = 'Y軸';
$lang->pivot->cluBarY->stat  = '統計方式';

$lang->pivot->radar = new stdclass();
$lang->pivot->radar->xaxis = '維度';
$lang->pivot->radar->yaxis = '極坐標軸';

$lang->pivot->line = $lang->pivot->cluBarX;

$lang->pivot->stackedBar  = $lang->pivot->cluBarX;
$lang->pivot->stackedBarY = $lang->pivot->cluBarY;

$lang->pivot->browseGroup = '維護分組';
$lang->pivot->allGroup    = '所有分組';
$lang->pivot->noGroup     = '暫時沒有分組';
$lang->pivot->groupName   = '分組名稱';
$lang->pivot->manageGroup = '維護分組';
$lang->pivot->dragAndSort = '拖放排序';
$lang->pivot->editGroup   = '編輯分組';
$lang->pivot->deleteGroup = '刪除分組';
$lang->pivot->childTitle  = '子分組';

$lang->pivot->filter          = '篩選器';
$lang->pivot->resultFilter    = '結果篩選器';
$lang->pivot->queryFilter     = '查詢篩選器';
$lang->pivot->noName          = '未命名';
$lang->pivot->filterName      = '名稱';
$lang->pivot->showAs          = '顯示為';
$lang->pivot->default         = '預設值';
$lang->pivot->unlimited       = '不限';
$lang->pivot->colon           = '至';
$lang->pivot->legendBasicInfo = '基礎信息';
$lang->pivot->legendConfig    = '全局設置';

$lang->pivot->fieldTypeList = array();
$lang->pivot->fieldTypeList['input']    = '文本框';
$lang->pivot->fieldTypeList['date']     = '日期';
$lang->pivot->fieldTypeList['datetime'] = '時間';
$lang->pivot->fieldTypeList['select']   = '下拉菜單';

$lang->pivot->count      = '個數';
$lang->pivot->project    = '項目名稱';
$lang->pivot->customer   = '客戶名稱';
$lang->pivot->cusBuild   = '客戶版本';
$lang->pivot->period     = '測試周期';
$lang->pivot->purpose    = '測試目的';
$lang->pivot->stage      = '階段';
$lang->pivot->users      = '測試人數';
$lang->pivot->testtasks  = '提交測試單';
$lang->pivot->comment    = '備註';
$lang->pivot->major      = '軟件主測';
$lang->pivot->conclusion = '結論';
$lang->pivot->result     = '基本測試結果';
$lang->pivot->caseCount  = '用例總數';
$lang->pivot->runCount   = '完成數';
$lang->pivot->runRate    = '完成率';
$lang->pivot->manpower   = '投入人數';
$lang->pivot->bugs       = '提交Bug數';
$lang->pivot->day        = '天';
$lang->pivot->reportDate = '報告日期';
$lang->pivot->hours      = '耗時(小時)';
$lang->pivot->pastDays   = '距今天數';

$lang->pivot->saveAsDataview = '存為中間表';

$lang->pivot->confirmDelete = '您確認要刪除嗎?';
$lang->pivot->nameEmpty     = '『名稱』不能為空';

$lang->pivot->noPivotTip      = '保存設置後，即可顯示透視表';
$lang->pivot->noQueryTip      = '暫時沒有篩選器。';
$lang->pivot->noPivot         = '暫時沒有透視表';
$lang->pivot->dataError       = '"%s" 填寫的不是合法的值';
$lang->pivot->noChartSelected = '請選擇至少一個圖表。';
$lang->pivot->beginGtEnd      = '開始時間不得大於結束時間。';
$lang->pivot->resetSettings   = '查詢數據的配置已修改，是否清空透視表設計，重新設計。';
$lang->pivot->clearSettings   = '查詢數據的配置已修改，是否清空透視表設計並保存。';
$lang->pivot->draftSave       = '該透視表已發佈，將變為草稿態，是否繼續？';
$lang->pivot->cannotAddQuery  = '已添加結果篩選器，無法添加查詢篩選器';
$lang->pivot->cannotAddResult = '已添加查詢篩選器，無法添加結果篩選器';

$lang->pivot->confirm = new stdclass();
$lang->pivot->confirm->design  = '該透視表被已發佈的大屏引用，是否繼續？';
$lang->pivot->confirm->publish = '該透視表被已發佈的大屏引用，發佈後將在大屏上顯示為修改後的透視表，是否繼續？';
$lang->pivot->confirm->draft   = '該透視表被已發佈的大屏引用，存為草稿後將在大屏上顯示為提示信息，是否繼續？';
$lang->pivot->confirm->delete  = '該透視表被已發佈的大屏引用，刪除後將在大屏上顯示為提示信息，是否繼續？';

$lang->pivot->orderList = array();
$lang->pivot->orderList['asc']  = '正序';
$lang->pivot->orderList['desc'] = '倒序';

$lang->pivot->typeList = array();
$lang->pivot->typeList['pie']        = '餅圖';
$lang->pivot->typeList['line']       = '折線圖';
$lang->pivot->typeList['radar']      = '雷達圖';
$lang->pivot->typeList['cluBarY']    = '簇狀條形圖';
$lang->pivot->typeList['stackedBarY'] = '堆積條形圖';
$lang->pivot->typeList['cluBarX']    = '簇狀柱形圖';
$lang->pivot->typeList['stackedBar'] = '堆積柱形圖';

$lang->pivot->aggList = array();
$lang->pivot->aggList['count'] = '計數';
$lang->pivot->aggList['avg']   = '平均值';
$lang->pivot->aggList['sum']   = '求和';

$lang->pivot->operatorList = array();
$lang->pivot->operatorList['=']       = '=';
$lang->pivot->operatorList['!=']      = '!=';
$lang->pivot->operatorList['<']       = '<';
$lang->pivot->operatorList['>']       = '>';
$lang->pivot->operatorList['<=']      = '<=';
$lang->pivot->operatorList['>=']      = '>=';
$lang->pivot->operatorList['in']      = 'IN';
$lang->pivot->operatorList['notin']   = 'NOT IN';
$lang->pivot->operatorList['notnull'] = 'IS NOT NULL';
$lang->pivot->operatorList['null']    = 'IS NULL';

$lang->pivot->dateList = array();
$lang->pivot->dateList['day']   = '天';
$lang->pivot->dateList['week']  = '周';
$lang->pivot->dateList['month'] = '月';
$lang->pivot->dateList['year']  = '年';

$lang->pivot->designStepNav = array();
$lang->pivot->designStepNav['1'] = '查詢數據';
$lang->pivot->designStepNav['2'] = '設計透視表';
$lang->pivot->designStepNav['3'] = '配置篩選器';
$lang->pivot->designStepNav['4'] = '準備發佈';

$lang->pivot->nextButton = array();
$lang->pivot->nextButton['1'] = '去設計';
$lang->pivot->nextButton['2'] = '去配置';
$lang->pivot->nextButton['3'] = '去準備';
$lang->pivot->nextButton['4'] = '發佈';

$lang->pivot->displayList = array();
$lang->pivot->displayList['value']   = '顯示值';
$lang->pivot->displayList['percent'] = '百分比';

$lang->pivot->typeOptions = array();
$lang->pivot->typeOptions['user']      = '用戶';
$lang->pivot->typeOptions['product']   = '產品';
$lang->pivot->typeOptions['project']   = '項目';
$lang->pivot->typeOptions['execution'] = '執行';
$lang->pivot->typeOptions['dept']      = '部門';

$lang->pivot->step2 = new stdclass();
$lang->pivot->step2->group       = '行分組';
$lang->pivot->step2->summary     = '彙總設置';
$lang->pivot->step2->column      = '列設置';
$lang->pivot->step2->groupTip    = '選擇欄位';
$lang->pivot->step2->groupNum    = array('一', '二', '三');
$lang->pivot->step2->selectField = '選擇欄位';
$lang->pivot->step2->selectStat  = '選擇統計方式';
$lang->pivot->step2->add         = '添加';
$lang->pivot->step2->delete      = '刪除';
$lang->pivot->step2->groupField  = '分組欄位';
$lang->pivot->step2->columnField = '欄位';
$lang->pivot->step2->calcMode    = '計算方式';

$lang->pivot->step2->moreThanOne = '至少選擇一個分組欄位。';
$lang->pivot->step2->summaryTip  = '勾選後，可配置行分組、列設置、顯示列的彙總。';
$lang->pivot->step2->groupsTip   = '通過選擇分組欄位，對SQL查詢的數據進行分組，並分層級顯示在透視表中。';
$lang->pivot->step2->columnsTip  = '在透視表中添加1列並對其進行設置。';

$lang->pivot->step2->columnTotal    = '顯示列的彙總';
$lang->pivot->step2->columnTotalTip = '增加一行顯示每一列的彙總數據。';
$lang->pivot->step2->total          = '總計';

$lang->pivot->step2->columnTotalList = array();
$lang->pivot->step2->columnTotalList['noShow'] = '不顯示';
$lang->pivot->step2->columnTotalList['sum']    = '求和';

$lang->pivot->step2->sliceFieldList = array();
$lang->pivot->step2->sliceFieldList['noSlice'] = '不切片';

$lang->pivot->step2->showModeList = array();
$lang->pivot->step2->showModeList['default'] = '預設（數值）';
$lang->pivot->step2->showModeList['total']   = '總計百分比';
$lang->pivot->step2->showModeList['row']     = '行彙總百分比';
$lang->pivot->step2->showModeList['column']  = '列彙總百分比';

$lang->pivot->step2->showTotalList = array();
$lang->pivot->step2->showTotalList['noShow'] = '不顯示';
$lang->pivot->step2->showTotalList['sum']    = '求和';

$lang->pivot->step2->statList = array();
$lang->pivot->step2->statList['']         = '';
$lang->pivot->step2->statList['count']    = '計數';
$lang->pivot->step2->statList['distinct'] = '去重後計數';
$lang->pivot->step2->statList['avg']      = '平均值';
$lang->pivot->step2->statList['sum']      = '求和';
$lang->pivot->step2->statList['max']      = '最大值';
$lang->pivot->step2->statList['min']      = '最小值';

$lang->datepicker->dpText->TEXT_WEEK_MONDAY = '本週一';
$lang->datepicker->dpText->TEXT_WEEK_SUNDAY = '本週日';
$lang->datepicker->dpText->TEXT_MONTH_BEGIN = '本月初';
$lang->datepicker->dpText->TEXT_MONTH_END   = '本月末';
