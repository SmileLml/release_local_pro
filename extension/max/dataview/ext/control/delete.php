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
     * Delete a dataview.
     *
     * @param  int    $dataviewID
     * @param  string $confirm  yes|no
     * @access public
     * @return int
     */
    public function delete($dataviewID, $confirm = 'no')
    {
        $dataview = $this->dataview->getByID($dataviewID);

        if($confirm == 'no')
        {
            $warningTip = $dataview->used ? $this->lang->dataview->error->warningDelete : $this->lang->dataview->confirmDelete;
            return print(js::confirm($warningTip, inlink('delete', "id=$dataviewID&confirm=yes")));
        }
        else
        {
            $this->dataview->delete(TABLE_DATAVIEW, $dataviewID);
            $this->dataview->deleteViewInDB($dataview->view);

            if(isonlybody()) return print(js::reload('parent.parent'));

            $locateLink = $this->session->dataviewList ? $this->session->dataviewList : inlink('browse', 'type=view');
            return print(js::locate($locateLink, 'parent'));
        }
    }
}
