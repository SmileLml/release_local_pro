<?php
/**
 * The control file of dataview module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chenxuan Song <chunsheng@cnezsoft.com>
 * @package     dataview
 * @version     $Id: control.php 5086 2023-06-06 02:25:22Z
 * @link        http://www.zentao.net
 */
class dataview extends control
{
    /**
     * Ajax query, get fields and result.
     *
     * @access public
     * @return void
     */
    public function ajaxQuery()
    {
        $this->loadModel('chart');
        $filters    = (isset($_POST['filters']) and is_array($this->post->filters)) ? $this->post->filters : array();
        $recPerPage = isset($_POST['recPerPage']) ? $this->post->recPerPage : 25;
        $pageID     = isset($_POST['pageID'])     ? $this->post->pageID     : 1;

        $cloneFilters = $filters;
        foreach($filters as $index => $filter)
        {
            if(empty($filter['default'])) continue;

            $filters[$index]['default'] = $this->loadModel('pivot')->processDateVar($filter['default']);
        }
        $querySQL = $this->chart->parseSqlVars($this->post->sql, $filters);

        if(empty($querySQL)) return $this->send(array('result' => 'fail', 'message' => $this->lang->dataview->empty));

        $this->app->loadClass('sqlparser', true);
        $parser = new sqlparser($querySQL);

        if(count($parser->statements) == 0) return $this->send(array('result' => 'fail', 'message' => $this->lang->dataview->empty));
        if(count($parser->statements) > 1)  return $this->send(array('result' => 'fail', 'message' => $this->lang->dataview->onlyOne));

        $statement = $parser->statements[0];
        if($statement instanceof PhpMyAdmin\SqlParser\Statements\SelectStatement == false) return $this->send(array('result' => 'fail', 'message' => $this->lang->dataview->allowSelect));

        // check origin sql error.
        try
        {
            $rows = $this->dbh->query("EXPLAIN $querySQL")->fetchAll();
        }
        catch(Exception $e)
        {

            return $this->send(array('result' => 'fail', 'message' => $e));
        }

        $sqlColumns = $this->dao->getColumns($querySQL);
        list($isUnique, $repeatColumn) = $this->dataview->checkUniColumn($querySQL, true, $sqlColumns);

        if(!$isUnique) return $this->send(array('result' => 'fail', 'message' => sprintf($this->lang->dataview->duplicateField, implode(',', $repeatColumn))));

        $columns      = $this->dataview->getColumns($querySQL, $sqlColumns);
        $columnFields = array();
        foreach($columns as $column => $type) $columnFields[$column] = $column;

        $tableAndFields = $this->chart->getTables($querySQL);
        $tables   = $tableAndFields['tables'];
        $fields   = $tableAndFields['fields'];
        $querySQL = $tableAndFields['sql'];

        $moduleNames = array();
        $aliasNames  = array();
        if($tables)
        {
            $moduleNames = $this->dataview->getModuleNames($tables);
            $aliasNames  = $this->dataview->getAliasNames($statement, $moduleNames);
        }

        list($fieldPairs, $relatedObject) = $this->dataview->mergeFields($columnFields, $fields, $moduleNames, $aliasNames);

        /* Limit 100. */
        if(!$statement->limit)
        {
            $statement->limit = new stdclass();
        }
        $statement->limit->offset   = $recPerPage * ($pageID - 1);
        $statement->limit->rowCount = $recPerPage;

        $statement->options->options[] = 'SQL_CALC_FOUND_ROWS';

        $limitSql = $statement->build();

        try
        {
            $rows      = $this->dbh->query($limitSql)->fetchAll();
            $rowsCount = $this->dbh->query("SELECT FOUND_ROWS() as count")->fetch();
        }
        catch(Exception $e)
        {
            return $this->send(array('result' => 'fail', 'message' => $e));
        }

        foreach ($columns as $key => $value) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                return $this->send(array('result' => 'fail', 'message' => sprintf($this->lang->dataview->errorField, $key)));
            }
        }

        return $this->send(array('result' => 'success', 'rows' => $rows, 'fields' => $fieldPairs, 'columns' => $columns, 'filters' => $cloneFilters, 'lineCount' => $rowsCount->count, 'columnCount' => count($fieldPairs), 'relatedObject' => $relatedObject, 'recPerPage' => $recPerPage, 'pageID' => $pageID));
    }
}
