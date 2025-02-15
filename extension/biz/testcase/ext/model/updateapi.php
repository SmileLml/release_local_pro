<?php
//自动化平台调用api接口
function updateapi($caseID)
    {
        $now     = helper::now();
        $oldCase = $this->getById($caseID);

        $result = $this->getStatus('update', $oldCase);
        if(!$result or !is_array($result)) return $result;

        list($stepChanged, $status) = $result;

        $version = $stepChanged ? $oldCase->version + 1 : $oldCase->version;

        $case = fixer::input('post')
            ->get();

        $this->dao->update(TABLE_CASE)->data($case)->autoCheck()->batchCheck($requiredFields, 'notempty')->where('id')->eq((int)$caseID)->exec();
        if(!$this->dao->isError())
        {
            $isLibCase    = ($oldCase->lib and empty($oldCase->product));
            $autoChanged = ($case->auto != $oldCase->auto);
            if($isLibCase) {
                $this->dao->update(TABLE_CASE)->set('auto')->eq($case->auto)->where('`fromCaseID`')->eq($caseID)->exec(); 
            }
            $this->updateCase2Project($oldCase, $case, $caseID);
            return common::createChanges($oldCase, $case);
        }
    }
