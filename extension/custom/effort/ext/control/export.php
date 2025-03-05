<?php
helper::importControl('effort');

class myeffort extends effort
{
    public function export($userID, $orderBy = 'id_desc', $date = '', $executionID = 0)
    {
        if($date)
        {
            $date  = str_replace('_', '-', $date);
            $time  = strtotime($date);
            $begin = date('Y-m', $time) . '-01';
            $end   = date('Y-m', strtotime('+1 month', $time)) . '-01';
            if($this->session->effortReportCondition) $this->session->effortReportCondition .= ' AND ';
            $this->session->effortReportCondition .= "(t1.date >= '$begin' AND t1.date < '$end')";
        }
        if($executionID)
        {
            $execution = $this->loadModel('execution')->getByID($executionID);
            $this->view->fileName = $execution->name . $this->lang->dash . $this->lang->effort->common;
        }
        if($_POST)
        {
            $effortLang   = $this->lang->effort;
            $effortConfig = $this->config->effort;

            /* Create field lists. */
            $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $effortConfig->list->defaultFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($effortLang->$fieldName) ? $effortLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            /* Get efforts. */
            $efforts = $this->dao->select('t1.*,t2.dept')->from(TABLE_EFFORT)->alias('t1')
                ->leftJoin(TABLE_USER)->alias('t2')->on('t1.account=t2.account')
                ->where($this->session->effortReportCondition)
                ->andWhere('t1.deleted')->eq(0)
                ->beginIF($this->post->exportType == 'selected')->andWhere('t1.id')->in($this->cookie->checkedItem)->fi()
                ->orderBy("$orderBy, account_desc")->fetchAll('id');

            $accounts   = array();
            $products   = array();
            $executions = array();
            $projects   = array();
	        $depts      = array();

            $fieldGetTitle = array();
            $fieldGetName  = array();

            if(isset($fields['dept']))      $depts      = $this->loadModel('dept')->getOptionMenu();
            if(isset($fields['product']))   $products   = $this->loadModel('product')->getPairsForEffortExport();
            if(isset($fields['execution'])) $executions = $this->loadModel('execution')->getPairsForEffortExport();
            if(isset($fields['project']) || isset($fields['projectStatus'])) $projects = $this->loadModel('project')->getAllProjects();
            foreach($efforts as $effort) $accounts[] = $effort->account;
            $users = $this->loadModel('user')->getPairsByAccount(array_unique($accounts));
            $objectTypes = array();
            foreach($efforts as $effort)
            {
                if(isset($fields['dept']))      $effort->dept      = zget($depts, $effort->dept, '');
                if(isset($fields['taskDesc']))  $effort->taskDesc  = '';
                if(isset($fields['projectStatus'])) $effort->projectStatus = !empty($effort->project) && isset($projects[$effort->project]) ? $this->lang->project->statusList[$projects[$effort->project]->status] : '';
                if(isset($fields['project']))   $effort->project   = !empty($effort->project) && isset($projects[$effort->project]) ? '#' . $effort->project . ' ' . $projects[$effort->project]->name : '';
                if(isset($fields['execution'])) $effort->execution = zget($executions, $effort->execution, '');
                if(isset($fields['product']))
                {
                    $effortProducts  = explode(',', trim($effort->product, ','));
                    $effort->product = '';
                    foreach($effortProducts as $productID) $effort->product .= zget($products, $productID, '') . ' ';
                }

                if(empty($effort->objectType)) continue;
                if($effort->objectType == 'custom') continue;
                if(strpos(',story,bug,case,doc,productplan,', ',' . $effort->objectType . ',') !== false)
                {
                    $fieldGetTitle[] = $effort->objectType;
                }
                elseif(strpos(',release,task,build,testtask', ',' . $effort->objectType . ',') !== false)
                {
                    $fieldGetName[]  = $effort->objectType;
                }
                if(!isset($objectTypes[$effort->objectType])) $objectTypes[$effort->objectType]['table'] = $this->config->objectTables[$effort->objectType];
                $objectTypes[$effort->objectType]['id'][] = $effort->objectID;
            }

            $objectTitles = array();

            foreach($objectTypes as $type => $objectType)
            {
                if(in_array($type, $fieldGetTitle))
                {
                    $objectTitles[$type] = $this->dao->select('id, title')->from($objectType['table'])->where('id')->in($objectType['id'])->fetchAll('id');
                }
                else if(in_array($type, $fieldGetName))
                {
                    if($type && isset($fields['taskDesc']))
                    {
                        $objectTitles[$type] = $this->dao->select('id, name, `desc`')->from($objectType['table'])->where('id')->in($objectType['id'])->fetchAll('id');
                    }
                    else
                    {
                        $objectTitles[$type] = $this->dao->select('id, name')->from($objectType['table'])->where('id')->in($objectType['id'])->fetchAll('id');
                    }
                }
                else
                {
                    $objectTitles[$type] = $this->dao->select('*')->from($objectType['table'])->where('id')->in($objectType['id'])->fetchAll('id');
                }
            }
            if(isset($objectTitles['todo']))
            {
                $linkTodoObjects = array();
                foreach($objectTitles['todo'] as $todoid => $todo)
                {
                    if($todo->type == 'bug' or $todo->type == 'task') $linkTodoObjects[$todo->type][] = $todo->idvalue;
                }

                $todoTitles = array();
                foreach($linkTodoObjects as $type => $linkObjectIDs) $todoTitles[$type] = $this->dao->select('*')->from('`' . $this->config->db->prefix . $type . '`')->where('id')->in($linkObjectIDs)->fetchAll('id');
            }

            foreach($efforts as $effort)
            {
                /* fill some field with useful value. */
                if(isset($users[$effort->account])) $effort->account = (!empty($users[$effort->account]['slack']) ? '【' . $users[$effort->account]['slack'] . '】' : '【-】') . $users[$effort->account]['realname'];
                $effort->work = htmlspecialchars_decode($effort->work);

                if($effort->objectType != 'custom')
                {
                    if(strpos(',story,bug,case,doc,productplan,', ',' . $effort->objectType . ',') !==false)
                    {
                        $objectTitle = isset($objectTitles[$effort->objectType][$effort->objectID]) ? $objectTitles[$effort->objectType][$effort->objectID]->title : '';
                    }
                    elseif(strpos(',release,task,build,testtask', ',' . $effort->objectType . ',') !==false)
                    {
                        $objectTitle = isset($objectTitles[$effort->objectType][$effort->objectID]) ? $objectTitles[$effort->objectType][$effort->objectID]->name : '';
                        if(isset($fields['taskDesc']) && $effort->objectType == 'task')
                        {
                            $effort->taskDesc = $objectTitles[$effort->objectType][$effort->objectID]->desc;
                            if(in_array($this->post->fileType, ['xlsx', 'csv', 'xls'])) $effort->taskDesc = $this->loadModel('file')->excludeHtml($effort->taskDesc, 'keepNL');
                        }
                    }
                    elseif($effort->objectType == 'todo')
                    {
                        $objectTitle = ' ';
                        if(!empty($objectTitles[$effort->objectType][$effort->objectID]))
                        {
                            $todo        = $objectTitles[$effort->objectType][$effort->objectID];
                            $objectTitle = $todo->name;
                            if($todo->type != 'custom')
                            {
                                if($todo->type == 'bug') $objectTitle = isset($todoTitles['bug'][$todo->idvalue]) ? $todoTitles['bug'][$todo->idvalue]->title : $objectTitle;
                                if($todo->type == 'task') $objectTitle = isset($todoTitles['task'][$todo->idvalue]) ? $todoTitles['task'][$todo->idvalue]->name : $objectTitle;
                            }
                        }
                    }
                    if(isset($effortLang->objectTypeList[$effort->objectType])) $effort->objectType = $effortLang->objectTypeList[$effort->objectType] . " : #{$effort->objectID} " . $objectTitle;
                }
                else
                {
                    $effort->objectType = $effortLang->objectTypeList[$effort->objectType];
                }
            }

            $width['account']    = 11;
            $width['date']       = 11;
            $width['consumed']   = 15;
            $width['left']       = 15;
            $width['work']       = 40;
            $width['objectType'] = 40;

            if($this->config->edition != 'open') list($fields, $efforts) = $this->loadModel('workflowfield')->appendDataFromFlow($fields, $efforts);

            $this->post->set('fields', $fields);
            $this->post->set('rows', $efforts);
            $this->post->set('kind', $this->lang->effort->common);
            $this->post->set('width', $width);
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        /* Unset product field when under a independent project. */
        if($this->session->effortReportCondition and strpos($this->session->effortReportCondition, 'execution') !== false)
        {
            preg_match("/execution  = '([0-9]+)'/i", $this->session->effortReportCondition, $matches);
            $executionID = isset($matches[1]) ? (int)$matches[1] : 0;
            if($executionID)
            {
                $execution = $this->loadModel('execution')->getByID($executionID);
                $project   = $this->loadModel('project')->getByID($execution->project);

                if($project)
                {
                    if(!$project->hasProduct)
                    {
                        $this->config->effort->list->exportFields  = str_replace(',product,', ',', $this->config->effort->list->exportFields);
                        $this->config->effort->list->defaultFields = str_replace(',product,', ',', $this->config->effort->list->defaultFields);
                    }

                    if(!$project->multiple)
                    {
                        $this->config->effort->list->exportFields  = str_replace(',execution,', ',', $this->config->effort->list->exportFields);
                        $this->config->effort->list->defaultFields = str_replace(',execution,', ',', $this->config->effort->list->defaultFields);
                    }
                }
            }
        }

        $this->view->fileName        = $this->app->user->realname . ' - ' . $this->lang->effort->common;
        $this->view->allExportFields = $this->config->effort->list->exportFields;
        $this->view->selectedFields  = $this->config->effort->list->defaultFields;
        $this->view->customExport    = true;
        $this->display();
    }
}
