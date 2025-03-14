<?php
/**
 * The control file of pivot module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     pivot
 * @link        https://www.zentao.net
 */
helper::importControl('pivot');
class myPivot extends pivot
{
    /**
     * Test case statistics table.
     *
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function testcase($productID = 0)
    {
        $products = $this->loadModel('product')->getPairs('', 0, '', 'all');
        if(!$productID) $productID = key($products);

        $this->app->loadLang('testcase');
        $this->view->title     = $this->lang->pivot->testcase;
        $this->view->products  = $products;
        $this->view->productID = $productID;
        $this->view->modules   = $this->pivot->getTestcases($productID);
        $this->view->submenu   = 'test';
        $this->display();
    }
}
