<?php
helper::importControl('task');
class mytask extends task
{
    /**
     * Show import of task template.
     *
     * @param  int    $executionID
     * @param  int    $pagerID
     * @param  string $insert
     * @access public
     * @return void
     */
    public function showImport($executionID, $pagerID = 1, $insert = '')
    {
        $this->loadModel('execution')->setMenu($executionID);

        /* Set datasource params for initfieldsList.*/
        $this->session->set('taskTransferParams', array('executionID' => $executionID));
        $this->loadModel('transfer');

        if($_POST)
        {
            $this->task->createFromImport($executionID);

            $locate = inlink('showImport', "executionID=$executionID&pagerID=" . ($this->post->pagerID + 1) . "&maxImport=$maxImport&insert=" . zget($_POST, 'insert', ''));

            if($this->post->isEndPage) $locate = $this->createLink('execution','task', "executionID=$executionID");

            return print(js::locate($locate, 'parent'));
        }

        /* Get page by datas.*/
        $taskData    = $this->transfer->readExcel('task', $pagerID, $insert, 'estimate');

        $this->view->title       = $this->lang->task->common . $this->lang->colon . $this->lang->task->showImport;
        $this->view->datas       = $taskData;
        $this->view->backLink    = $this->createLink('execution', 'task', "executionID=$executionID");
        $this->view->executionID = $executionID;

        $this->display();
    }
}
