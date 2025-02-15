<?php
$lang->file->onlySupportXLSX = '只支持XLSX格式導入，請轉換XLSX格式再導入。';

$lang->excel->fileField = '附件';

$lang->excel->title            = new stdclass();
$lang->excel->title->testcase  = '用例';
$lang->excel->title->bug       = 'Bug';
$lang->excel->title->task      = '任務';
$lang->excel->title->story     = "{$lang->SRCommon}";
$lang->excel->title->caselib   = '用例庫';
$lang->excel->title->sysValue  = '系統數據';
$lang->excel->title->tree      = '樹狀圖';
$lang->excel->title->user      = '用戶';
$lang->excel->title->project   = '項目';

$lang->excel->error = new stdclass();
$lang->excel->error->info       = '您輸入的值不在下拉框列表內。';
$lang->excel->error->title      = '輸入有誤';
$lang->excel->error->noFile     = '沒有檔案';
$lang->excel->error->noData     = '沒有有效的數據';
$lang->excel->error->canNotRead = '不能解析該檔案';

$lang->excel->help           = new stdclass();
$lang->excel->help->testcase = '添加用例時，每個用例步驟在新行用數字 + ‘.’來標記。同樣的，預期也是用數字 + ‘.’與步驟對應。%s是必填欄位，如果不填導入時會忽略該條數據。';
$lang->excel->help->bug      = '添加Bug時，%s是必填欄位，如果不填導入時會忽略該條數據。';
$lang->excel->help->task     = '添加任務時，%s是必填欄位，如果不填導入時會忽略該條數據；如需添加子任務，請在任務名稱前用“>”標記。';
$lang->excel->help->multiple = "如需添加多人任務，請在“最初預計”列裡面，按照“用戶名:{$lang->hourCommon}”格式添加，多個用戶之間用換行分隔。用戶名在“系統數據”工作表的F列查看。";
$lang->excel->help->taskMode = "多人任務請填寫“任務模式”，非多人任務填寫“任務模式”導入時，系統會自動將“任務模式”置空。";
$lang->excel->help->taskTip  = '任務不能既是子任務又是多人任務。';
$lang->excel->help->story    = "添加{$lang->SRCommon}時，%s是必填欄位，如果不填導入時會忽略該條數據。";
$lang->excel->help->user     = "添加用戶時，“用戶名”和“姓名”是必填欄位，如果不填導入時會忽略該條數據。";
$lang->excel->help->feedback = "添加反饋時，%s是必填欄位，如果不填導入時會忽略該條數據。";
$lang->excel->help->ticket   = "添加工單時，標題，所屬' . $lang->productCommon . '，所屬模組是必填欄位，如果不填導入時會忽略該條數據。";
$lang->excel->help->demand   = "添加需求時，%s是必填欄位，如果不填導入時會忽略該條數據。";
