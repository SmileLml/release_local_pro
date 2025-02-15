$(document).ready(function()
{
    var sql = {};
    sql.tables    = [];
    sql.from      = '';
    sql.fields    = '';
    sql.condition = 'WHERE 1=1';
    sql.orderBy   = '';
    sql.groupBy   = '';
    sql.limit     = '';

    $(document).on('click', '.icon-plus', function()
    {
        var that = $(this);
        var newJoin = that.closest('div.form-box').clone();
        newJoin.find('select').val('');
        newJoin.find('input').val('');
        that.closest('div.form-box').after(newJoin);
        that.closest('div.form-box').next().find('div.chosen-container').remove();
        that.closest('div.form-box').next().find('select:not(.nochosen)').each(function(){$(this).chosen()});

        if(newJoin.find('.form-group').hasClass('slave-field-row'))
        {
            newJoin.find('.form-group.slave-field-row').addClass('hidden');
            newJoin.find('.form-group.slave-field-row .decimal-box').addClass('hidden');
            newJoin.find('.form-group.slave-field-row .decimal-box select').val(0);
        }

        /* 如果是条件设置里的加减行，每次加减完需要重新生成下条件。*/
        if($(this).closest('div.form-box').find('select[name^="andOr"]').size > 0)
        {
            $('select[name^="andOr"]').change();
        }
    });

    $(document).on('change', 'select[name^="mainTable"], select[name^="slaveTable"]', function()
    {
        sql.tables = [];
        sql.tables.push($('#mainTable').val());
        $('select[name^="slaveTable"]').each(function()
        {
            var table = $(this).val();
            sql.tables.push(table);
        });

        updateTableSelect('firstTable', sql.tables);
        updateTableSelect('secondTable', sql.tables);
        updateTableSelect('queryTable', sql.tables);
        updateTableSelect('conditionTable', sql.tables);
        updateTableSelect('orderTable', sql.tables);
        updateTableSelect('groupTable', sql.tables);
    });

    $(document).on('click', '.icon-close', function()
    {
        if($(this).closest('div.form-box').parent().find('.icon-close').size() > 1) $(this).closest('div.form-box').remove();
    });

    $(document).on('change', 'select[name^="firstTable"]', function()
    {
        updateFieldSelect($(this), 'firstField');
    });
    $(document).on('change', 'select[name^="secondTable"]', function()
    {
        updateFieldSelect($(this), 'secondField');
    });
    $(document).on('change', 'select[name^="queryTable"]', function()
    {
        updateFieldSelect($(this), 'queryField', 'yes');
    });
    $(document).on('change', 'select[name^="conditionTable"]', function()
    {
        updateFieldSelect($(this), 'conditionField');
    });
    $(document).on('change', 'select[name^="orderTable"]', function()
    {
        updateFieldSelect($(this), 'orderField');
    });
    $(document).on('change', 'select[name^="groupTable"]', function()
    {
        updateFieldSelect($(this), 'groupField');
    });

    $(document).on('change', '#tableBox select', function()
    {
        if(!$('#mainTable').val()) return false;

        sql.from = "FROM " + '`' + $('#mainTable').val() + '`' + "\n";
        $('#tableBox select[name^="secondField"]').each(function()
        {
            var secondField = $(this).val();
            var firstField  = $(this).closest('.input-group').find('select[name^="firstField"]').val();

            var slaveTable  = $(this).closest('.form-group').find('select[name^="slaveTable"]').val();
            var firstTable  = $(this).closest('.input-group').find('select[name^="firstTable"]').val();
            var secondTable = $(this).closest('.input-group').find('select[name^="secondTable"]').val();

            if(!(firstField && secondField)) return true;

            sql.from += "LEFT JOIN `" + slaveTable + "` ON `" + firstTable + '`.`' + firstField + '` = `' + secondTable + '`.`' + secondField + '`' + "\n";
        });

        buildSQL(sql);
    });

    $(document).on('change', '#fieldBox select, #fieldBox input', function()
    {
        sql.fields = '';
        $('#fieldBox .main-field-row select[name^="queryField"]').each(function()
        {
            $fieldRow     = $(this).closest('.input-group');
            $nextFieldRow = $(this).closest('.main-field-row.form-group').next();

            var computeOperate = $fieldRow.find('select[name^="computeOperate"]').val();
            if(computeOperate)
            {
                $fieldRow.find('input[name^="aliasName"]').val('');//如果是数学运算的话，将别名清空，否则会有错。
                $nextFieldRow.removeClass('hidden');
                if(computeOperate == '/')
                {
                    $nextFieldRow.find('.decimal-box').removeClass('hidden');
                }
                else
                {
                    $nextFieldRow.find('.decimal-box').addClass('hidden');
                }
            }
            else
            {
                $nextFieldRow.addClass('hidden');
            }

            var queryTable     = $fieldRow.find('select[name^="queryTable"]').val();
            var queryField     = $fieldRow.find('select[name^="queryField"]').val();
            var funcOperate    = $fieldRow.find('select[name^="funcOperate"]').val();
            var aliasName      = $fieldRow.find('input[name^="aliasName"]').val();

            if(!queryField) return true;
            if(sql.fields != '') sql.fields += ',';

            queryField = '`' + queryField + '`';
            queryTable = '`' + queryTable + '`';

            var querySql = '';
            querySql = queryTable + '.' + queryField;
            if(funcOperate && queryField == "`*`") querySql = '"' + querySql + '"';
            if(funcOperate) querySql = funcOperate + '(' + querySql + ')';
            if(aliasName != '') querySql += ' AS ' + aliasName;

            if(computeOperate)
            {
                var slaveQueryTable     = $nextFieldRow.find('select[name^="queryTable"]').val();
                var slaveQueryField     = $nextFieldRow.find('select[name^="queryField"]').val();
                var slaveFuncOperate    = $nextFieldRow.find('select[name^="funcOperate"]').val();
                var slaveComputeOperate = $nextFieldRow.find('select[name^="computeOperate"]').val();
                var slaveAliasName      = $nextFieldRow.find('input[name^="aliasName"]').val();

                slaveQueryField = '`' + slaveQueryField + '`';
                slaveQueryTable = '`' + slaveQueryTable + '`';

                var slaveQuerySql = '';
                slaveQuerySql = slaveQueryTable + '.' + slaveQueryField;
                if(slaveFuncOperate && slaveQueryField == "`*`") slaveQuerySql = '"' + slaveQuerySql + '"';
                if(slaveFuncOperate) slaveQuerySql = slaveFuncOperate + '(' + slaveQuerySql + ')';

                querySql = '(' + querySql + computeOperate + slaveQuerySql + ')';

                if(computeOperate == '/')
                {
                    var decimal = $nextFieldRow.find('select[name^="decimal"]').val();
                    querySql = 'round(' + querySql + ',' + decimal + ')';
                }

                if(slaveAliasName) querySql += ' AS ' + slaveAliasName;
            }

            sql.fields += querySql;

        });

        buildSQL(sql);
    });

    $(document).on('change', '#conditionBox select, #conditionBox input', function()
    {
        sql.condition = 'WHERE 1=1';
        $('#conditionBox select[name^="andOr"]').each(function()
        {
            var firstOperate  = $(this).closest('.input-group').find('input[name^="firstOperate"]').val();
            var secondOperate = $(this).closest('.input-group').find('input[name^="secondOperate"]').val();

            var conditionTable   = $(this).closest('.input-group').find('select[name^="conditionTable"]').val();
            var conditionField   = $(this).closest('.input-group').find('select[name^="conditionField"]').val();
            var conditionOperate = $(this).closest('.input-group').find('select[name^="conditionOperate"]').val();
            var conditionValue   = $(this).closest('.input-group').find('input[name^="conditionValue"]').val();
            var andOr            = $(this).closest('.input-group').find('select[name^="andOr"]').val();

            if(!conditionField) return true;

            conditionTable = '`' + conditionTable + '`';
            conditionField = '`' + conditionField + '`';
            if(conditionValue.indexOf('$') == -1) conditionValue = "'" + conditionValue + "'";

            if($(this).closest('div.form-box').next().find('select[name^="andOr"]').size() == 0) andOr = '';

            if(sql.condition == 'WHERE 1=1') sql.condition += "\n" + 'AND ';

            sql.condition += firstOperate + conditionTable + '.' + conditionField + ' ' + conditionOperate + ' ' + conditionValue + secondOperate + "\n" + andOr + ' ';
        });

        buildSQL(sql);
    });

    $(document).on('change', '#orderByBox select', function()
    {
        sql.orderBy = '';
        $('#orderByBox select[name^="orderField"]').each(function()
        {
            var orderTable = $(this).closest('.input-group').find('select[name^="orderTable"]').val();

            var orderField = $(this).closest('.input-group').find('select[name^="orderField"]').val();
            var orderRule  = $(this).closest('.input-group').find('select[name^="orderRule"]').val();

            if(!orderField) return true;

            if(sql.orderBy != '')  sql.orderBy += ',';
            if(sql.orderBy == '') sql.orderBy   = 'ORDER BY ';

            var aliasName = getFieldAliasName(orderTable, orderField);
            if(aliasName != '')
            {
                sql.orderBy += aliasName + ' ' + orderRule;
            }
            else
            {
                orderField = '`' + orderField + '`';
                orderTable = '`' + orderTable + '`';
                sql.orderBy += orderTable + '.' + orderField + ' ' + orderRule;
            }
        });

        buildSQL(sql);
    });

    $(document).on('change', '#groupAndLimtBox select, #groupAndLimtBox input', function()
    {
        sql.groupBy = '';
        sql.limit   = '';

        var groupTable = $('#groupTable').val();
        var groupField = $('#groupField').val();
        var limit      = $('#limit').val();

        if(groupField != '')
        {
            var aliasName = getFieldAliasName(groupTable, groupField);

            if(aliasName != '')
            {
                sql.groupBy = 'GROUP BY ' + aliasName;
            }
            else
            {
                groupTable  = '`' + groupTable + '`';
                groupField  = '`' + groupField + '`';
                sql.groupBy = 'GROUP BY ' + groupTable + '.' + groupField;
            }
        }
        if(limit != '')
        {
            sql.limit = 'LIMIT ' + limit;
        }

        buildSQL(sql);
    });
});

function updateTableSelect(type, tables)
{
    /* 更新 condition table */
    $.post(createLink('sqlbuilder', 'ajaxGetTables'), {'type': type, 'tables': tables.join(',')}, function(data)
    {
        $('select[name^="' + type + '"]').each(function()
        {
            var that       = $(this);
            var tableValue = that.val();
            var tableDiv   = that.closest('div');
            tableDiv.html(data);
            tableDiv.find('select').val(tableValue);
            tableDiv.find('select').chosen();
        });
    });
}

function getFieldAliasName(table, field)
{
    if(table == '' || field == '') return '';

    var name = '';
    $('#fieldBox select[name^="queryField"]').each(function()
    {
        var queryTable = $(this).closest('.input-group').find('select[name^="queryTable"]').val();
        var queryField = $(this).closest('.input-group').find('select[name^="queryField"]').val();
        var aliasName  = $(this).closest('.form-group').find('input[name^="aliasName"]').val();

        if(queryTable == table && queryField == field && aliasName != '')
        {
            name = aliasName;
            return false;
        }
    });

    return name;
}

function updateFieldSelect(that, type, includeAll = 'no')
{
    var table = that.val();
    var link  = createLink('sqlbuilder', 'ajaxGetTableFields', "table=" + table + "&type=" + type + "&includeAll=" + includeAll);
    that.closest('.input-group').find('select[name^="' + type + '"]').closest('div').load(link, function()
    {
        that.closest('.input-group').find('select[name^="' + type + '"]').chosen();
    });
}

function buildSQL(sql)
{
    $('#sql').val("SELECT " + sql.fields + "\n" + sql.from + ' ' + sql.condition + ' ' + sql.orderBy + ' ' + sql.groupBy + ' ' + sql.limit);
}

function useSQL()
{
    var sql = $('#sql').val();
    $("#sql", parent.document.body).val(sql);
    $.closeModal();
}
