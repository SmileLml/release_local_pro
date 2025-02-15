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
     * Browse page.
     *
     * @param  string $type table|view
     * @access public
     * @return void
     */
    public function browse($type = 'view', $table = '')
    {
        $this->session->set('dataViewList', $this->app->getURI(true));

        $this->loadModel('dev');
        $this->loadModel('tree');
        $this->loadModel('setting');

        $recPerPage = $this->setting->getItem("owner={$this->app->user->account}&module=dataview&section=browse&key=recPerPage");
        if(!$recPerPage)
        {
            $recPerPage = 25;
            $this->setting->setItem($this->app->user->account . '.dataview.browse.recPerPage', $recPerPage);
        }

        $dataview = $type == 'view' ? $this->dataview->getByID($table) : null;
        if(!empty($table)) $fields = $type == 'table' ? $this->dev->getFields($table) : $this->dataview->getFields($table);

        $this->view->title         = $this->lang->dataview->common;
        $this->view->tab           = 'db';
        $this->view->tables        = $this->dev->getTables();
        $this->view->selectedTable = $table;
        $this->view->dataview      = $dataview;
        $this->view->dataTitle     = $type == 'view' ? (!empty($dataview->name) ? $dataview->name : '') : $this->dataview->getTableName($table);
        $this->view->fields        = !empty($fields) ? $fields : array();
        $this->view->data          = !empty($fields) ? $this->dataview->getTableData($table, $type, $recPerPage) : array();
        $this->view->type          = $type;
        $this->view->groups        = $this->tree->getOptionMenu(0, 'dataview');
        $this->view->groupTree     = $this->tree->getGroupTree(0, 'dataview');
        $this->view->originTable   = $this->dataview->getOriginTreeMenu($table);
        $this->view->clientLang    = $this->app->getClientLang();
        $this->view->table         = $table;

        $this->view->pageID     = 1;
        $this->view->recPerPage = $recPerPage;
        $this->view->recTotal   = !empty($fields) ? count($this->dataview->getTableData($table, $type, 0)) : 0;
        $this->display();
    }
}
