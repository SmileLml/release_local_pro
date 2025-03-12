<?php
helper::importControl('testcase');
class mytestcase extends testcase
{
    /**
     * Synchronize use case updates in batches.
     * 
     * @param int $taskID
     * @param string $confirm
     * @return mixed
     */
    public function batchConfirmCaseUpdate($taskID = 0, $confirm = 'no')
    {
        if(!$this->post->caseIDList && !$this->session->confirmCaseList) return print(js::alert($this->lang->testcase->confirmCaseUpdateError) . js::locate($this->session->caseList));

        if($confirm == 'no')
        {
            $this->session->set('confirmCaseList', $this->post->caseIDList, 'testcase');
            return print(js::confirm($this->lang->testcase->confirmCaseUpdate, $this->inlink('batchConfirmCaseUpdate', "taskID=$taskID&confirm=yes"), $this->session->caseList));
        }

        $confirmCaseList = $this->session->confirmCaseList;

        if($confirmCaseList)
        {
            $caseIDList = array_filter($confirmCaseList);
            if($taskID)
            {
                $runs = $this->testcase->getCasesByTask($taskID, $caseIDList);
                foreach($runs as $runID => $run) if($run->caseVersion > $run->version) $this->dao->update(TABLE_TESTRUN)->set('version')->eq($run->caseVersion)->where('id')->eq($runID)->exec();
            }
            else
            {
                $caseList       = $this->testcase->getByList($caseIDList);
                $libCaseIDList  = [];
                $caseListFilter = [];
                $libCaseList    = [];
                foreach($caseList as $caseID => $case)
                {
                    if(isset($case->fromCaseVersion) and $case->fromCaseVersion > $case->version and !empty($case->product))
                    {
                        $caseListFilter[$caseID] = $case;
                        if(isset($case->fromCaseID) && !empty($case->fromCaseID)) $libCaseIDList[] = $case->fromCaseID;
                    }
                }
                if(!empty($caseListFilter))
                {
                    $libCaseIDList = array_unique($libCaseIDList);
                    if(!empty($libCaseIDList))
                    {
                        $libCaseList = $this->testcase->getByList($libCaseIDList, '', true);
                        $this->testcase->batchConfirmCaseUpdate($caseListFilter, $libCaseList);
                    }
                }
            }
        }
        echo js::locate($this->session->caseList);
    }
}