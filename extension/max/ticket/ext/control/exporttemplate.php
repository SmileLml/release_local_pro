<?php
helper::importControl('ticket');
class myticket extends ticket
{
    /**
     * Export template.
     *
     * @access public
     * @return void
     */
    public function exportTemplate()
    {
        $productID = (isset($_SESSION['ticketProduct']) and $_SESSION['ticketProduct'] != 'all') ? $_SESSION['ticketProduct'] : 0;

        if($_POST)
        {
            $this->ticket->setListValue();
            $this->config->ticket->datatable->fieldList['openedBuild']['dataSource'] = array('module' => 'build', 'method' =>'getBuildPairs', 'params' => $productID);
            $this->fetch('transfer', 'exportTemplate', 'model=ticket');
        }

        $this->loadModel('transfer');

        $this->display();
    }
}
