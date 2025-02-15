<?php
$lang->sqlbuilder->common        = 'sql構建器';
$lang->sqlbuilder->create        = '構建sql';
$lang->sqlbuilder->browseSQLView = '瀏覽視圖';
$lang->sqlbuilder->createSQLView = '創建視圖';
$lang->sqlbuilder->editSQLView   = '編輯視圖';
$lang->sqlbuilder->deleteSQLView = '刪除視圖';
$lang->sqlbuilder->buildSQL      = '構建查詢語句';

$lang->sqlbuilder->from           = '從';
$lang->sqlbuilder->mainTable      = '主表';
$lang->sqlbuilder->createSQL      = '創建語句';
$lang->sqlbuilder->querySQL       = '查詢語句';
$lang->sqlbuilder->fromTable      = '從表';
$lang->sqlbuilder->query          = '查詢';
$lang->sqlbuilder->handle         = '處理';
$lang->sqlbuilder->operate        = '操作';
$lang->sqlbuilder->condition      = '條件';
$lang->sqlbuilder->table          = '表';
$lang->sqlbuilder->field          = '欄位';
$lang->sqlbuilder->leftJoin       = 'LEFT JOIN';
$lang->sqlbuilder->on             = 'ON';
$lang->sqlbuilder->equal          = '=';
$lang->sqlbuilder->where          = '當';
$lang->sqlbuilder->value          = '值';
$lang->sqlbuilder->alias          = 'AS';
$lang->sqlbuilder->orderBy        = 'ORDER BY';
$lang->sqlbuilder->orderRule      = '排序規則';
$lang->sqlbuilder->groupBy        = 'GROUP BY';
$lang->sqlbuilder->limit          = 'LIMIT';
$lang->sqlbuilder->use            = '使用';
$lang->sqlbuilder->funcOperate    = '函數';
$lang->sqlbuilder->computeOperate = '運算';
$lang->sqlbuilder->reserve        = '保留';
$lang->sqlbuilder->decimal        = '位小數';

$lang->sqlbuilder->orderRuleList['asc']  = '遞增';
$lang->sqlbuilder->orderRuleList['desc'] = '遞減';

$lang->sqlbuilder->funcOperateList['']         = '';
$lang->sqlbuilder->funcOperateList['distinct'] = '去重';
$lang->sqlbuilder->funcOperateList['count']    = '計數';
$lang->sqlbuilder->funcOperateList['sum']      = '求和';
$lang->sqlbuilder->funcOperateList['avg']      = '取平均';

$lang->sqlbuilder->computeOperateList['']         = '';
$lang->sqlbuilder->computeOperateList['+']        = '加';
$lang->sqlbuilder->computeOperateList['-']        = '減';
$lang->sqlbuilder->computeOperateList['*']        = '乘以';
$lang->sqlbuilder->computeOperateList['/']        = '除以';

$lang->sqlbuilder->roundList[0] = '0';
$lang->sqlbuilder->roundList[1] = '1';
$lang->sqlbuilder->roundList[2] = '2';
$lang->sqlbuilder->roundList[3] = '3';
$lang->sqlbuilder->roundList[4] = '4';
$lang->sqlbuilder->roundList[5] = '5';

$lang->sqlbuilder->andOrList['AND'] = '並且';
$lang->sqlbuilder->andOrList['OR']  = '或者';

$lang->sqlbuilder->conditionOperateList['=']    = '=';
$lang->sqlbuilder->conditionOperateList['!=']   = '!=';
$lang->sqlbuilder->conditionOperateList['>']    = '>';
$lang->sqlbuilder->conditionOperateList['>=']   = '>=';
$lang->sqlbuilder->conditionOperateList['<']    = '<';
$lang->sqlbuilder->conditionOperateList['<=']   = '<=';
$lang->sqlbuilder->conditionOperateList['LIKE'] = 'like';

$lang->sqlview = new stdclass();
$lang->sqlview->common        = '視圖';
$lang->sqlview->id            = 'ID';
$lang->sqlview->name          = '表名';
$lang->sqlview->code          = '代號';
$lang->sqlview->desc          = '描述';
$lang->sqlview->sql           = 'sql語句';
$lang->sqlview->createdBy     = '由誰創建';
$lang->sqlview->createdDate   = '創建時間';
$lang->sqlview->editedBy      = '由誰編輯';
$lang->sqlview->editedDate    = '編輯時間';
$lang->sqlview->confirmDelete = '確定刪除此視圖嗎？';

$lang->sqlbuilder->tips = new stdclass();
$lang->sqlbuilder->tips->onlyLetter      = '只能包含英文字母或下劃線，保存後不可更改';
$lang->sqlbuilder->tips->duplicateCode   = '系統已經存在相同代號的數據表，請使用其他代號';
$lang->sqlbuilder->tips->wrongCode       = '代號只能包含英文字母或下劃線';
$lang->sqlbuilder->tips->createViewFail  = '創建視圖失敗';
$lang->sqlbuilder->tips->createSuccess   = '創建成功';
$lang->sqlbuilder->tips->editSuccess     = '編輯成功';
