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
     * Query a dataview.
     *
     * @param int $viewID
     * @access public
     * @return void
     */
    public function query($viewID)
    {
        if(!empty($_POST))
        {
            $this->dataview->querySave($viewID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->loadModel('action')->create('dataview', $viewID, 'designed');

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "type=view&table=$viewID")));
        }

        $dataview = $this->dataview->getByID($viewID);

        $this->view->title         = $this->lang->dataview->design;
        $this->view->saveLink      = inlink('query', "viewID=$viewID");
        $this->view->backLink      = inlink('browse', "type=view&table=$viewID");
        $this->view->data          = $dataview;
        $this->view->fieldSettings = isset($dataview->fieldSettings) ? $dataview->fieldSettings : new stdclass();
        $this->display();
    }
}
