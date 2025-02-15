<?php
/**
 * The control file of dimension module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2022 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chenxuan Song <1097180981@qq.com>
 * @package     dimension
 * @version     $Id: control.php 4157 2022-11-1 10:24:12Z $
 * @link        http://www.zentao.net
 */
class dimension extends control
{
    /**
     * Delete a dimension.
     *
     * @param  int    $dimensionID
     * @param  string $confirm  yes|no
     * @access public
     * @return void
     */
    public function delete($dimensionID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            if($this->dimension->checkBIContent($dimensionID)) return print(js::alert($this->lang->dimension->canNotDelete));

            return print(js::confirm($this->lang->dimension->confirmDelete, inlink('delete', "id=$dimensionID&confirm=yes")));
        }
        else
        {
            $this->dimension->delete(TABLE_DIMENSION, $dimensionID);

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate(inlink('browse'), 'parent'));
        }
    }
}
