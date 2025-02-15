<?php
/**
 * The control file of sqlBuilder module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Fei Chen <chenfei@cnezsoft.com>, Xiying Guan <guanxiying@cnezsoft.com>
 * @package     sqlBuilder
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class sqlbuilder extends control
{
    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create SQL
     *
     * @param string $type
     * @access public
     * @return void
     */
    public function create($type = '')
    {
        $tables = $this->sqlbuilder->getTables();

        $this->view->title      = $this->lang->sqlbuilder->common;
        $this->view->position[] = $this->lang->sqlbuilder->common;

        $this->view->type   = $type;
        $this->view->tables = array('' => '') + $tables;
        $this->view->fields = array('' => '');
        $this->display();
    }

    /**
     * Browse SQL view.
     *
     * @access public
     * @return void
     */
    public function browseSQLView($recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $sqlViews = $this->sqlbuilder->getSQLViews($pager);

        $this->view->title      = $this->lang->sqlbuilder->browseSQLView;
        $this->view->position[] = $this->lang->sqlbuilder->browseSQLView;

        $this->view->users    = $this->loadModel('user')->getPairs('noletter');
        $this->view->sqlViews = $sqlViews;
        $this->view->pager    = $pager;
        $this->display();
    }

    /**
     * Create SQL view.
     *
     * @access public
     * @return void
     */
    public function createSQLView()
    {
        if(!empty($_POST))
        {
            $sql = str_replace("\t", '', stripslashes(trim($this->post->sql)));

            $tableID = $this->sqlbuilder->createSQLView($sql);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->loadModel('action')->create('sqlview', $tableID, 'opened');

            $defaultAction = str_replace($this->config->sqlbuilder->sqlviewPrefix, $this->config->sqlbuilder->sqlviewPrefix . $this->post->code, $this->config->sqlbuilder->defaultAction);
            $sql           = $defaultAction . $sql;
            $this->sqlbuilder->execute($sql);
            if(dao::isError())
            {
                $errors = dao::getError();
                $this->dao->delete()->from(TABLE_SQLVIEW)->where('id')->eq($tableID)->exec();
                return $this->send(array('result' => 'fail', 'message' => $errors));
            }

            $locate = $this->createLink('sqlbuilder', 'browseSQLView');
            return $this->send(array('result' => 'success', 'message' => $this->lang->sqlbuilder->tips->createSuccess, 'locate' => $locate));
        }

        $this->view->title      = $this->lang->sqlbuilder->createSQLView;
        $this->view->position[] = $this->lang->sqlbuilder->common;
        $this->view->position[] = $this->lang->sqlbuilder->createSQLView;

        $this->display();
    }

    /**
     * Edit SQL view.
     *
     * @access public
     * @return void
     */
    public function editSQLView($viewID)
    {
        $oldSQLView = $this->sqlbuilder->getSQLViewByID($viewID);

        if(!empty($_POST))
        {
            $sql = str_replace("\t", '', stripslashes(trim($this->post->sql)));

            $this->sqlbuilder->editSQLView($viewID, $sql);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->loadModel('action')->create('sqlview', $viewID, 'edited');

            $defaultAction = str_replace($this->config->sqlbuilder->sqlviewPrefix, $this->config->sqlbuilder->sqlviewPrefix . $oldSQLView->code, $this->config->sqlbuilder->defaultAction);
            $sql = $defaultAction . $sql;
            $this->sqlbuilder->execute($sql);
            if(dao::isError())
            {
                $errors = dao::getError();
                return $this->send(array('result' => 'fail', 'message' => $errors));
            }

            $locate = $this->createLink('sqlbuilder', 'browseSQLView');
            return $this->send(array('result' => 'success', 'message' => $this->lang->sqlbuilder->tips->editSuccess, 'locate' => $locate));
        }

        $this->view->title      = $this->lang->sqlbuilder->editSQLView;
        $this->view->position[] = $this->lang->sqlbuilder->common;
        $this->view->position[] = $this->lang->sqlbuilder->editSQLView;

        $this->view->SQLView = $oldSQLView;

        $this->display();
    }

    /**
     * Delete SQL View.
     *
     * @param int $viewID
     * @param string $confirm
     * @access public
     * @return void
     */
    public function deleteSQLView($viewID = 0, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->sqlview->confirmDelete, $this->createLink('sqlbuilder', 'deleteSQLView', "viewID=$viewID&confirm=yes")));
        }

		$this->sqlbuilder->deleteSQLView($viewID);

		if(dao::isError()) die(js::error(dao::getError()));

        die(js::reload('parent'));
    }

    /**
     * Ajax get tables.
     *
     * @param mixed $type
     * @param mixed $selectedTables
     * @access public
     * @return void
     */
    public function ajaxGetTables($type = '', $selectedTables = '')
    {
        if($this->post->type)   $type = $this->post->type;
        if($this->post->tables) $selectedTables = $this->post->tables;

        $selectedTables = explode(',', $selectedTables);
        $tables = $this->sqlbuilder->getTables();

        $tablePairs = array('' => '');
        foreach($selectedTables as $selectTable)
        {
            if(!isset($tables[$selectTable])) continue;
            $tablePairs[$selectTable] = $tables[$selectTable];
        }

        $type = $type . "[]";
        die(html::select($type, $tablePairs, '', "class='form-control'"));
    }

    /**
     * Ajax get table fields.
     *
     * @param mixed $table
     * @param mixed $type
     * @param string $includeAll
     * @access public
     * @return void
     */
    public function ajaxGetTableFields($table, $type, $includeAll = 'no')
    {
        if(empty($table))
        {
            $fields = array('' => '');
        }
        else
        {
            $fields = $this->sqlbuilder->getTableFields($table, $includeAll);
            $fields = array('' => '') + $fields;
        }

        $type = $type . "[]";
        die(html::select($type, $fields, '', "class='form-control'"));
    }

    /**
     * Ajax build SQL.
     *
     * @access public
     * @return void
     */
    public function ajaxBuildSQL()
    {
        die($this->fetch('sqlbuilder', 'create'));
    }
}
