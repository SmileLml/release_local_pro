<?php
/**
 * The control file of pivot module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     pivot
 * @version     $Id: model.php 5086 2013-07-10 02:25:22Z wyd621@gmail.com $
 * @link        http://www.zentao.net
 */
class pivot extends control
{
    /**
     * Delete a pivot.
     *
     * @param  int    $pivotID
     * @param  string $confirm  yes|no
     * @param  string $from browse|preview
     * @access public
     * @return void
     */
    public function delete($pivotID, $confirm = 'no', $from = 'browse')
    {
        if($confirm == 'no')
        {
            $used          = $this->loadModel('screen')->checkIFChartInUse($pivotID, 'pivot');
            $confirmDelete = $used ? $this->lang->pivot->confirm->delete : $this->lang->pivot->confirmDelete;
            return print(js::confirm($confirmDelete, inlink('delete', "id=$pivotID&confirm=yes&from=$from")));
        }
        else
        {
            $this->pivot->delete(TABLE_PIVOT, $pivotID);

            if(isonlybody()) return print(js::reload('parent.parent'));

            if($from == 'preview')
            {
                $dimension = $this->session->backDimension ? $this->session->backDimension : 0;
                $group     = $this->session->backGroup ? $this->session->backGroup : '';
                unset($_SESSION['backDimension']);
                unset($_SESSION['backGroup']);

                $locateLink = inlink('preview', "dimension=$dimension&group=$group");
            }
            else
            {
                $locateLink = $this->session->pivotList ? $this->session->pivotList : inlink('browse');
            }
            return print(js::locate($locateLink, 'parent'));
        }
    }
}
