<?php

class mycaselib extends caselib
{
    public function exportCase($libID, $orderBy, $browseType = '')
    {
        if($_POST)
        {
            if(strpos($orderBy, 'case') !== false)
            {
                list($field, $sort) = explode('_', $orderBy);
                $orderBy = '`' . $field . '`_' . $sort;
            }
            $this->loadModel('file');
            $this->loadModel('testcase');
            $caseLang   = $this->lang->testcase;
            $caseConfig = $this->config->testcase;
            $fields = explode(',', $this->config->caselib->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($this->lang->caselib->$fieldName) ? $this->lang->caselib->$fieldName : $fieldName;
                unset($fields[$key]);
            }
            $queryCondition = preg_replace("/AND\s+t[0-9]\.scene\s+=\s+'0'/i", '', $this->session->testcaseQueryCondition);
            if($this->session->testcaseOnlyCondition)
            {
                $cases = $this->dao->select('*')->from(TABLE_CASE)->where($queryCondition)
                    ->beginIF($this->post->exportType == 'selected')->andWhere('id')->in($this->post->checkedItem)->fi()
                    ->orderBy($orderBy)
                    ->beginIF($this->post->limit)->limit($this->post->limit)->fi()
                    ->fetchAll('id');
            }
            else
            {
                $cases   = array();
                $orderBy = " ORDER BY " . str_replace(array('|', '^A', '_'), ' ', $orderBy);
                $stmt    = $this->dao->query($queryCondition . $orderBy . ($this->post->limit ? ' LIMIT ' . $this->post->limit : ''));
                while($row = $stmt->fetch())
                {
                    $caseID = isset($row->case) ? $row->case : $row->id;
                    if($this->post->exportType == 'selected' and strpos(",{$this->post->checkedItem},", ",$caseID,") === false) continue;
                    $cases[$caseID] = $row;
                    $row->id        = $caseID;
                }
            }
            $users    = $this->loadModel('user')->getPairs('noletter');
            $relatedModules = $this->loadModel('tree')->getOptionMenu($libID, $viewType = 'caselib', $startModuleID = 0);
            $relatedSteps   = $this->dao->select('id,parent,`case`,version,type,`desc`,expect')->from(TABLE_CASESTEP)->where('`case`')->in(@array_keys($cases))->orderBy('version desc,id')->fetchGroup('case', 'id');
            $relatedFiles   = $this->dao->select('id, objectID, pathname, title')->from(TABLE_FILE)->where('objectType')->eq('testcase')->andWhere('objectID')->in(@array_keys($cases))->andWhere('extra')->ne('editor')->fetchGroup('objectID');
            foreach($cases as $case)
            {
                $case->stepDesc   = '';
                $case->stepExpect = '';
                $case->openedDate     = !helper::isZeroDate($case->openedDate)     ? $case->openedDate     : '';
                $case->lastEditedDate = !helper::isZeroDate($case->lastEditedDate) ? $case->lastEditedDate : '';

                if(isset($relatedSteps[$case->id]))
                {
                    $i = $childId = 0;
                    foreach($relatedSteps[$case->id] as $step)
                    {
                        $stepId = 0;
                        if($step->type == 'group' or $step->type == 'step')
                        {
                            $i++;
                            $childId = 0;
                            $stepId  = $i;
                        }
                        else
                        {
                            $stepId = $i . '.' . $childId;
                        }
                        if($step->version != $case->version) continue;
                        $sign = (in_array($this->post->fileType, array('html', 'xml'))) ? '<br />' : "\n";
                        $case->stepDesc   .= $stepId . ". " . htmlspecialchars_decode($step->desc) . $sign;
                        $case->stepExpect .= $stepId . ". " . htmlspecialchars_decode($step->expect) . $sign;
                        $childId ++;
                    }
                }
                $case->stepDesc     = trim($case->stepDesc);
                $case->stepExpect   = trim($case->stepExpect);

                if($this->post->fileType == 'csv')
                {
                    $case->stepDesc   = str_replace('"', '""', $case->stepDesc);
                    $case->stepExpect = str_replace('"', '""', $case->stepExpect);
                }

                $case->module  = !isset($relatedModules[$case->module])? '' : $relatedModules[$case->module] . "(#$case->module)";

                if(isset($caseLang->priList[$case->pri]))              $case->pri           = $caseLang->priList[$case->pri];
                if(isset($caseLang->typeList[$case->type]))            $case->type          = $caseLang->typeList[$case->type];
                if(isset($caseLang->statusList[$case->status]))        $case->status        = $this->processStatus('testcase', $case);
                if(isset($users[$case->openedBy]))                     $case->openedBy      = $users[$case->openedBy];
                if(isset($users[$case->lastEditedBy]))                 $case->lastEditedBy  = $users[$case->lastEditedBy];

                $case->stage = explode(',', $case->stage);
                foreach($case->stage as $key => $stage) $case->stage[$key] = isset($caseLang->stageList[$stage]) ? $caseLang->stageList[$stage] : $stage;
                $case->stage = join("\n", $case->stage);

                $case->openedDate     = substr($case->openedDate, 0, 10);
                $case->lastEditedDate = substr($case->lastEditedDate, 0, 10);

                $case->files = '';
                if(isset($relatedFiles[$case->id]))
                {
                    foreach($relatedFiles[$case->id] as $file)
                    {
                        $fileURL = common::getSysURL() . $this->createLink('file', 'download', "fileID={$file->id}");
                        $case->files .= html::a($fileURL, $file->title, '_blank') . '<br />';
                    }
                }
            }
            $this->post->set('fields', $fields);
            $this->post->set('rows', $cases);
            $this->post->set('kind', 'testcase');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }
        $fileName = $this->lang->testcase->common;
        $browseType  = isset($this->lang->caselib->featureBar['browse'][$browseType]) ? $this->lang->caselib->featureBar['browse'][$browseType] : '';

        $libraries = $this->caselib->getLibraries();
        $libID = $this->caselib->saveLibState($libID, $libraries);

        unset($this->lang->exportFileTypeList['xml']);
        unset($this->lang->exportFileTypeList['html']);
        unset($this->lang->exportFileTypeList['mht']);

        $this->view->fileName        = $libraries[$libID] . $this->lang->dash . $browseType . $fileName;
        $this->view->allExportFields = $this->config->caselib->exportFields;
        $this->display();
    }


}