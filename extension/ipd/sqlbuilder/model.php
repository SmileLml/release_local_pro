<?php
/**
 * The model file of sqlBuilder module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Fei Chen <chenfei@cnezsoft.com>, Xiying Guan <guanxiying@cnezsoft.com>
 * @package     effort
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php
class sqlbuilderModel extends model
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Create SQL View
     *
     * @access public
     * @return void
     */
    public function createSQLView($sql)
    {
        $table = fixer::input('post')
            ->setDefault('createdBy', $this->app->user->account)
            ->setDefault('createdDate', helper::now())
            ->remove('action,sql')
            ->add('sql', $sql)
            ->skipSpecial('sql')
            ->get();

        if(!empty($table->code))
        {
            if(!validater::checkREG($table->code, '|^[A-Za-z_]+$|'))
            {
                dao::$errors['code'] = $this->lang->sqlbuilder->tips->wrongCode;
                return false;
            }

            $tableCode = $this->config->sqlbuilder->sqlviewPrefix . $table->code;
            $tables    = $this->getTables();
            if(isset($tables[$tableCode]))
            {
                dao::$errors['code'] = $this->lang->sqlbuilder->tips->duplicateCode;
                return false;
            }
        }

        /* Check sql.*/
        $result = $this->loadModel('report')->checkBlackList($sql);
        if($result)
        {
            dao::$errors['sql'] = $result == 'noselect' ? $this->lang->crystal->noticeSelect : sprintf($this->lang->crystal->noticeBlack, $result);
            return false;
        }

        $this->dao->insert(TABLE_SQLVIEW)->data($table)->autoCheck()
            ->batchCheck($this->config->sqlbuilder->createsqlview->requiredFields, 'notempty')
            ->check('name', 'unique', "deleted = '0'")
            ->check('code', 'unique', "deleted = '0'")
            ->exec();

        if(dao::isError()) return false;
        return $this->dao->lastInsertID();
    }

    /**
     * Edit SQL View
     *
     * @access public
     * @return void
     */
    public function editSQLView($viewID, $sql)
    {
        $table = fixer::input('post')
            ->setDefault('editedBy', $this->app->user->account)
            ->setDefault('editedDate', helper::now())
            ->remove('action,code,sql')
            ->add('sql', $sql)
            ->skipSpecial('sql')
            ->get();

        if(!empty($table->code))
        {
            if(!validater::checkREG($table->code, '|^[A-Za-z_]+$|'))
            {
                dao::$errors['code'] = $this->lang->sqlbuilder->tips->wrongCode;
                return false;
            }
        }

        /* Check sql.*/
        $result = $this->loadModel('report')->checkBlackList($sql);
        if($result)
        {
            dao::$errors['sql'] = $result == 'noselect' ? $this->lang->crystal->noticeSelect : sprintf($this->lang->crystal->noticeBlack, $result);
            return false;
        }

        $this->dao->update(TABLE_SQLVIEW)->data($table)->autoCheck()
            ->batchCheck($this->config->sqlbuilder->createsqlview->requiredFields, 'notempty')
            ->check('name', 'unique', "id != $viewID and deleted = '0'")
            ->check('code', 'unique', "id != $viewID and deleted = '0'")
			->where('id')->eq($viewID)
            ->exec();
    }

	/**
	 * Delete SQLView
	 *
	 * @param mixed $viewID
	 * @access public
	 * @return void
	 */
	public function deleteSQLView($viewID)
	{
		$SQLView = $this->getSQLViewByID($viewID);

		$sql = "DROP VIEW `" . $this->config->sqlbuilder->sqlviewPrefix . $SQLView->code . "`";
		$this->execute($sql);
		if(dao::isError())
        {
            $errors = dao::getError();
            $errors = addslashes($errors['sql']);
            die(js::error("$errors"));
        }

        $this->dao->update(TABLE_SQLVIEW)->set('deleted')->eq('1')->where('id')->eq($viewID)->exec();
	}

    /**
     * Get tables according to database tables.
     *
     * @access public
     * @return array
     */
    public function getTables()
    {
        $zdb    = $this->app->loadClass('zdb');
        $tables = $zdb->getAllTables('all');

        $tablePairs = array();
        $flowTables = $this->loadModel('workflow', 'flow')->getPairs();
        foreach($tables as $table => $tableType)
        {
            if(isset($this->config->sqlbuilder->filterTableList[$table])) continue;//Filter useless tables.

            if(strpos($table, $this->config->sqlbuilder->sqlviewPrefix) === 0)
            {
                /* SQL view tables. */
                $sqlViewPairs = $this->getSQLViewPairs('yes');
                $tablePairs[$table] = zget($sqlViewPairs, $table, $table);
            }
            else
            {
                /*
                 * 1. System module's table.
                 * 2. Workflow module's table.
                 * 3. Support table like zt_storyspec.
                 */
                $module = substr($table, strpos($table, '_') + 1);
                $modulePath = $this->app->getModuleRoot() . $module;
                if(is_dir($modulePath))
                {
                    $this->app->loadLang($module);
                    $tablePairs[$table] = isset($this->lang->$module->common) ? $this->lang->$module->common : $table;
                }
                elseif(strpos($module, 'flow_') === 0)
                {
                    $module = substr($module, strpos($module, '_') + 1);
                    $tablePairs[$table] = zget($flowTables, $module, $table);
                }
                else
                {
                    $tablePairs[$table] = zget($this->config->sqlbuilder->tableList, $module, $table);
                }
            }
        }

        return $tablePairs;
    }

    public function getSQLViewByID($viewID)
    {
        return $this->dao->select('*')->from(TABLE_SQLVIEW)->where('id')->eq($viewID)->fetch();
    }

    /**
     * Get SQL Views.
     *
     * @param mixed $pager
     * @access public
     * @return array
     */
    public function getSQLViews($pager = null)
    {
        return $this->dao->select('*')->from(TABLE_SQLVIEW)->where('deleted')->eq(0)->page($pager)->fetchAll('id');
    }

    /**
     * Get SQL view pairs.
     *
     * @param  string $needType
     * @access public
     * @return array
     */
    public function getSQLViewPairs($needType = 'no')
    {
        $views = $this->dao->select('code, name')->from(TABLE_SQLVIEW)->where('deleted')->eq(0)->fetchPairs();

        $pairs = array();
        foreach($views as $code => $name)
        {
            $code = $this->config->sqlbuilder->sqlviewPrefix . $code;
            if($needType == 'yes') $name = '[' . $this->lang->sqlview->common . ']' . $name;
            $pairs[$code] = $name;
        }

        return $pairs;
    }

    /**
     * Get table fields.
     *
     * @param string $table
     * @param string $includeAll
     * @access public
     * @return void
     */
    public function getTableFields($table, $includeAll = 'no')
    {
        if(strpos($table, $this->config->db->prefix . 'flow_') === 0)
        {
            return $this->getFlowTableFields($table, $includeAll);
        }
        elseif(strpos($table, $this->config->sqlbuilder->sqlviewPrefix) === 0)
        {
            return $this->getSQLViewTableFields($table, $includeAll);
        }
        else
        {
            return $this->getSysTableFields($table, $includeAll);
        }
    }

    /**
     * Get flow table fields.
     *
     * @param string $table
     * @param string $includeAll
     * @access public
     * @return void
     */
    public function getFlowTableFields($table, $includeAll)
    {
        $subTable = substr($table, strpos($table, '_') + 1);
        $module   = substr($subTable, strpos($subTable, '_') + 1);
        $fields = $this->loadModel('workflowhook', 'flow')->getTableFields($module);

        $fieldPairs = array();
        foreach($fields as $fieldKey => $field)
        {
            $fieldPairs[$fieldKey] = empty($field) ? '' : $field . '|' . $fieldKey;
        }

        if($includeAll == 'yes') $fieldPairs = array('*' => '*') + $fieldPairs;

        return $fieldPairs;
    }

    public function getSQLViewTableFields($table, $includeAll = 'no')
    {
        $zdb    = $this->app->loadClass('zdb');
        $fields = $zdb->getTableFields($table);

        $fieldPairs = array();
        foreach($fields as $field)
        {
            $fieldPairs[$field->field] = $field->field . '|' . $field->field;
        }

        if($includeAll == 'yes') $fieldPairs = array('*' => '*') + $fieldPairs;

        return $fieldPairs;
    }

    /**
     * Get sys table fields.
     *
     * @param string $table
     * @param string $includeAll
     * @access public
     * @return void
     */
    public function getSysTableFields($table, $includeAll = 'no')
    {
        $zdb    = $this->app->loadClass('zdb');
        $fields = $zdb->getTableFields($table);

        $module      = substr($table, strpos($table, '_') + 1);
        $aliasModule = $subLang = '';
        try
        {
            if(isset($this->config->dev->tableMap[$module])) $aliasModule = $this->config->dev->tableMap[$module];
            if(strpos($aliasModule, '-') !== false) list($aliasModule, $subLang) = explode('-', $aliasModule);
            $this->app->loadLang($aliasModule ? $aliasModule : $module);
        }
        catch(PDOException $e)
        {
            $this->lang->$module = new stdclass();
        }

        $fieldPairs = array();
        foreach($fields as $field)
        {
            $fieldName = isset($this->lang->$module->{$field->field}) ? $this->lang->$module->{$field->field} : '';
            if(empty($fieldName) and $aliasModule) $fieldName = isset($this->lang->$aliasModule->{$field->field}) ? $this->lang->$aliasModule->{$field->field} : '';
            if($subLang) $fieldName = isset($this->lang->$aliasModule->$subLang->{$field->field}) ? $this->lang->$aliasModule->$subLang->{$field->field} : $fieldName;
            if(!is_string($fieldName)) $fieldName = '';
            $fieldPairs[$field->field] = empty($fieldName) ? $field->field . '|' . $field->field : $fieldName . '|' . $field->field;
        }

        if($includeAll == 'yes') $fieldPairs = array('*' => '*') + $fieldPairs;

        return $fieldPairs;
    }

    /**
     * Execute SQL
     *
     * @param string $sql
     * @access public
     * @return void
     */
    public function execute($sql)
    {
        try
        {
            $this->dbh->query($sql);
        }
        catch(PDOException $exception)
        {
            dao::$errors['sql'] = $exception->getMessage();
        }
    }
}
