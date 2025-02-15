<?php
helper::importControl('caselib');
class mycaselib extends caselib
{
    public function showImport($libID, $pagerID = 1, $maxImport = 0, $insert = '')
    {
        $this->loadModel('testcase');
        $this->loadModel('transfer');

        if($_POST)
        {
            $this->caselib->createFromImport($libID);

            $locate = inlink('showImport', "libID=$libID&pagerID=" . ($this->post->pagerID + 1) . "&maxImport=$maxImport&insert=" . zget($_POST, 'insert', ''));
            if($this->post->isEndPage)
            {
                $locate = inlink('browse', "libID=$libID");
            }

            return print(js::locate($locate, 'parent'));
        }

        $libraries = $this->caselib->getLibraries();
        if(empty($libraries)) $this->locate(inlink('createLib'));

        $this->caselib->setLibMenu($libraries, $libID);

        $this->config->testcase->templateFields = 'module,title,precondition,keywords,pri,type,stage,stepDesc,stepExpect';
        $this->session->set('testcaseTransferParams', array('libID' => $libID));
        $this->config->testcase->datatable->fieldList['module']['dataSource'] = array('module' => 'tree', 'method' => 'getOptionMenu', 'params' => '$libID&caselib&0');

        $datas    = $this->transfer->readExcel('testcase', $pagerID, $insert);
        $stepData = $this->testcase->processDatas($datas);
        $title    = $this->lang->testcase->common . $this->lang->colon . $this->lang->testcase->showImport;

        $this->view->title     = $title;
        $this->view->stepData  = $stepData;
        $this->view->datas     = $datas;
        $this->view->libID     = $libID;
        $this->view->backLink  = inlink('browse', "libID=$libID");
        $this->display();
    }
}
