<?php
$lang->sqlbuilder->common        = 'sql构建器';
$lang->sqlbuilder->create        = '构建sql';
$lang->sqlbuilder->browseSQLView = '浏览视图';
$lang->sqlbuilder->createSQLView = '创建视图';
$lang->sqlbuilder->editSQLView   = '编辑视图';
$lang->sqlbuilder->deleteSQLView = '删除视图';
$lang->sqlbuilder->buildSQL      = '构建查询语句';

$lang->sqlbuilder->from           = '从';
$lang->sqlbuilder->mainTable      = '主表';
$lang->sqlbuilder->createSQL      = '创建语句';
$lang->sqlbuilder->querySQL       = '查询语句';
$lang->sqlbuilder->fromTable      = '从表';
$lang->sqlbuilder->query          = '查询';
$lang->sqlbuilder->handle         = '处理';
$lang->sqlbuilder->operate        = '操作';
$lang->sqlbuilder->condition      = '条件';
$lang->sqlbuilder->table          = '表';
$lang->sqlbuilder->field          = '字段';
$lang->sqlbuilder->leftJoin       = 'LEFT JOIN';
$lang->sqlbuilder->on             = 'ON';
$lang->sqlbuilder->equal          = '=';
$lang->sqlbuilder->where          = '当';
$lang->sqlbuilder->value          = '值';
$lang->sqlbuilder->alias          = 'AS';
$lang->sqlbuilder->orderBy        = 'ORDER BY';
$lang->sqlbuilder->orderRule      = '排序规则';
$lang->sqlbuilder->groupBy        = 'GROUP BY';
$lang->sqlbuilder->limit          = 'LIMIT';
$lang->sqlbuilder->use            = '使用';
$lang->sqlbuilder->funcOperate    = '函数';
$lang->sqlbuilder->computeOperate = '运算';
$lang->sqlbuilder->reserve        = '保留';
$lang->sqlbuilder->decimal        = '位小数';

$lang->sqlbuilder->orderRuleList['asc']  = '递增';
$lang->sqlbuilder->orderRuleList['desc'] = '递减';

$lang->sqlbuilder->funcOperateList['']         = '';
$lang->sqlbuilder->funcOperateList['distinct'] = '去重';
$lang->sqlbuilder->funcOperateList['count']    = '计数';
$lang->sqlbuilder->funcOperateList['sum']      = '求和';
$lang->sqlbuilder->funcOperateList['avg']      = '取平均';

$lang->sqlbuilder->computeOperateList['']         = '';
$lang->sqlbuilder->computeOperateList['+']        = '加';
$lang->sqlbuilder->computeOperateList['-']        = '减';
$lang->sqlbuilder->computeOperateList['*']        = '乘以';
$lang->sqlbuilder->computeOperateList['/']        = '除以';

$lang->sqlbuilder->roundList[0] = '0';
$lang->sqlbuilder->roundList[1] = '1';
$lang->sqlbuilder->roundList[2] = '2';
$lang->sqlbuilder->roundList[3] = '3';
$lang->sqlbuilder->roundList[4] = '4';
$lang->sqlbuilder->roundList[5] = '5';

$lang->sqlbuilder->andOrList['AND'] = '并且';
$lang->sqlbuilder->andOrList['OR']  = '或者';

$lang->sqlbuilder->conditionOperateList['=']    = '=';
$lang->sqlbuilder->conditionOperateList['!=']   = '!=';
$lang->sqlbuilder->conditionOperateList['>']    = '>';
$lang->sqlbuilder->conditionOperateList['>=']   = '>=';
$lang->sqlbuilder->conditionOperateList['<']    = '<';
$lang->sqlbuilder->conditionOperateList['<=']   = '<=';
$lang->sqlbuilder->conditionOperateList['LIKE'] = 'like';

$lang->sqlview = new stdclass();
$lang->sqlview->common        = '视图';
$lang->sqlview->id            = 'ID';
$lang->sqlview->name          = '表名';
$lang->sqlview->code          = '代号';
$lang->sqlview->desc          = '描述';
$lang->sqlview->sql           = 'sql语句';
$lang->sqlview->createdBy     = '由谁创建';
$lang->sqlview->createdDate   = '创建时间';
$lang->sqlview->editedBy      = '由谁编辑';
$lang->sqlview->editedDate    = '编辑时间';
$lang->sqlview->confirmDelete = '确定删除此视图吗？';

$lang->sqlbuilder->tips = new stdclass();
$lang->sqlbuilder->tips->onlyLetter      = '只能包含英文字母或下划线，保存后不可更改';
$lang->sqlbuilder->tips->duplicateCode   = '系统已经存在相同代号的数据表，请使用其他代号';
$lang->sqlbuilder->tips->wrongCode       = '代号只能包含英文字母或下划线';
$lang->sqlbuilder->tips->createViewFail  = '创建视图失败';
$lang->sqlbuilder->tips->createSuccess   = '创建成功';
$lang->sqlbuilder->tips->editSuccess     = '编辑成功';
