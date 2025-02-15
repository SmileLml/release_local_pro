<?php
helper::importControl('testcase');
class mytestcase extends testcase
{
    /**
     * Ignore use case updates in batches.
     * 
     * @param string $confirm
     * @return mixed
     */
    public function batchIgnoreCaseUpdate($confirm = 'no')
    {
        if(!$this->post->caseIDList && !$this->session->ignoreCaseList) return print(js::alert($this->lang->testcase->ignoreCaseUpdateError) . js::locate($this->session->caseList));

        if($confirm == 'no')
        {
            $this->session->set('ignoreCaseList', $this->post->caseIDList, 'testcase');
            return print(js::confirm($this->lang->testcase->ignoreCaseUpdate, $this->inlink('batchIgnoreCaseUpdate', "confirm=yes"), $this->session->caseList));
        }

        $ignoreCaseList = $this->session->ignoreCaseList;

        if($ignoreCaseList)
        {
            $caseIDList     = array_filter($ignoreCaseList);
            $caseList       = $this->testcase->getByList($caseIDList);
            foreach($caseList as $caseID => $case)
            {
                $this->dao->update(TABLE_CASE)->set('fromCaseVersion')->eq($case->version)->where('id')->eq($caseID)->exec();
            }
        }
        echo js::locate($this->session->caseList);
    }
}