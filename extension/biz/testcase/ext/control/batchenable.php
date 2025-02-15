<?php

class mytestcase extends testcase
{
    /**
     * Edit a case.
     *
     * @param  int   $caseID
     * @access public
     * @return void
     */
     public function batchenable($productID=0)
    {
        if(!$this->post->caseList) return print(js::locate($this->session->caseList, 'parent'));
        $caseIdList = array_unique($this->post->caseList);
        $this->testcase->batchEnable($caseIdList, 'enable');
        if(dao::isError()) return print(js::error(dao::getError()));
        echo js::locate($this->session->caseList);
    }
}
