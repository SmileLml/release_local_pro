<?php
/**
 * The model file of excel module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     excel
 * @link        https://www.zentao.net
 */
helper::importControl('ticket');
class myticket extends ticket
{
    /**
     * Show import.
     *
     * @param  int    $pagerID
     * @param  int    $maxImport
     * @param  string $insert
     * @access public
     * @return void
     */
    public function showImport($pagerID = 1, $maxImport = 0, $insert = '')
    {
        $this->loadModel('transfer');

        if($_POST)
        {
            $this->ticket->createFromImport();

            if($this->post->isEndPage) $locate = inlink('browse');

            return print(js::locate($locate, 'parent'));
        }

        $title      = $this->lang->ticket->common . $this->lang->colon . $this->lang->ticket->showImport;
        $ticketData = $this->transfer->readExcel('ticket', $pagerID, $insert);

        $this->view->title     = $title;
        $this->view->datas     = $ticketData;
        $this->view->backLink  = inlink('browse');

        $this->display();
    }
}
