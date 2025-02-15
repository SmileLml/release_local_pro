<?php
helper::importControl('user');
class myuser extends user
{
    /**
     * Show import of user template.
     *
     * @param  int    $pagerID
     * @param  int    $maxImport
     * @param  string $insert
     * @access public
     * @return void
     */
    public function showImport($pagerID = 1, $maxImport = 0, $insert = '')
    {
        $file    = $this->session->fileImportFileName;
        $tmpPath = $this->loadModel('file')->getPathOfImportedFile();
        $tmpFile = $tmpPath . DS . md5(basename($file));

        if($_POST)
        {
            $userIDList = $this->user->createFromImport();
            if($this->post->isEndPage)
            {
                unlink($tmpFile);
                return print(js::locate($this->createLink('company','browse'), 'parent'));
            }
            else
            {
                return print(js::locate(inlink('showImport', "pagerID=" . ($this->post->pagerID + 1) . "&maxImport=$maxImport&insert=" . zget($_POST, 'insert', '')), 'parent'));
            }
        }

        $userConfig = $this->config->user;
        if(!empty($maxImport) and file_exists($tmpFile))
        {
            $userData = unserialize(file_get_contents($tmpFile));
        }
        else
        {
            $pagerID    = 1;

            /* Get the key-valve array of fields in the config file. */
            $userLang   = $this->lang->user;
            $fields     = explode(',', $userConfig->list->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($userLang->$fieldName) ? $userLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            $rows = $this->file->getRowsFromExcel($file);
            if(is_string($rows))
            {
                unlink($this->session->fileImportFileName);
                unset($_SESSION['fileImportFileName']);
                unset($_SESSION['fileImportExtension']);
                echo js::alert($rows);
                return print(js::locate($this->createLink('execution','user', "executionID=$executionID")));
            }

            $userData     = array();
            foreach($rows as $currentRow => $row)
            {
                $user = new stdclass();
                $user->type = 'inside';
                foreach($row as $currentColumn => $cellValue)
                {
                    /* Get the key-value array of the fields in the excel file. */
                    if($currentRow == 1)
                    {
                        $field = array_search($cellValue, $fields);
                        $columnKey[$currentColumn] = $field ? $field : '';
                        continue;
                    }

                    if(empty($columnKey[$currentColumn]))
                    {
                        $currentColumn++;
                        continue;
                    }
                    $field = $columnKey[$currentColumn];
                    $currentColumn++;

                    /* Check empty data. */
                    if(empty($cellValue))
                    {
                        $user->$field = '';
                        continue;
                    }

                    /* Assign the value to the fields. */
                    if(in_array($field, $userConfig->export->listFields))
                    {
                        if(strrpos($cellValue, '(#') === false)
                        {
                            $user->$field = $cellValue;
                            if(!isset($userLang->{$field . 'List'}) or !is_array($userLang->{$field . 'List'})) continue;

                            /* When the cell value is key of list then eq the key. */
                            $listKey = array_keys($userLang->{$field . 'List'});
                            unset($listKey[0]);
                            unset($listKey['']);

                            $fieldKey = array_search($cellValue, $userLang->{$field . 'List'});
                            if($fieldKey) $user->$field = array_search($cellValue, $userLang->{$field . 'List'});
                        }
                        else
                        {
                            $id = trim(substr($cellValue, strrpos($cellValue,'(#') + 2), ')');
                            $user->$field = $id;
                        }
                    }
                    elseif($field == 'type')
                    {
                        $user->$field = $userLang->inside == $cellValue ? 'inside' : ($userLang->outside == $cellValue ? 'outside' : 'inside');
                    }
                    elseif($field == 'join')
                    {
                        $user->$field = strtotime($cellValue) <= 0 ? '' : date("Y-m-d", strtotime($cellValue));
                    }
                    else
                    {
                        $user->$field = $cellValue;
                    }
                }

                if(empty($user->realname)) continue;
                $userData[$currentRow] = $user;
                unset($user);
            }

            file_put_contents($tmpFile, serialize($userData));
        }

        if(empty($userData))
        {
            unlink($this->session->fileImportFileName);
            unset($_SESSION['fileImportFileName']);
            unset($_SESSION['fileImportExtension']);
            echo js::alert($this->lang->excel->noData);
            return print(js::locate($this->createLink('company','browse', "executionID=$executionID")));
        }

        $allCount = count($userData);
        $allPager = 1;
        if($allCount > $this->config->file->maxImport)
        {
            if(empty($maxImport))
            {
                $this->view->allCount  = $allCount;
                $this->view->maxImport = $maxImport;
                return $this->display();
            }

            $allPager = ceil($allCount / $maxImport);
            $userData = array_slice($userData, ($pagerID - 1) * $maxImport, $maxImport, true);
        }

        if(empty($userData)) return print(js::locate($this->createLink('company','browse')));

        /* Judge whether the editedTasks is too large and set session. */
        $countInputVars  = count($userData) * 11;
        $showSuhosinInfo = common::judgeSuhosinSetting($countInputVars);
        $showAllModule   = isset($this->config->execution->user->allModule) ? $this->config->execution->user->allModule : '';
        if($showSuhosinInfo) $this->view->suhosinInfo = extension_loaded('suhosin') ? sprintf($this->lang->suhosinInfo, $countInputVars) : sprintf($this->lang->maxVarsInfo, $countInputVars);

        $groups = $this->dao->select('id, name, role')
            ->from(TABLE_GROUP)
            ->where('vision')->eq($this->config->vision)
            ->fetchAll();
        $groupList = array('' => '');
        $roleGroup = array();
        foreach($groups as $group)
        {
            $groupList[$group->id] = $group->name;
            if($group->role) $roleGroup[$group->role] = $group->id;
        }

        foreach(explode(',', $userConfig->list->exportFields) as $field) $showFields[$field] = $field;

        $this->view->title          = $this->lang->user->common . $this->lang->colon . $this->lang->user->showImport;
        $this->view->position[]     = $this->lang->user->showImport;
        $this->view->requiredFields = ',' . $this->config->user->create->requiredFields;
        $this->view->depts          = $this->dept->getOptionMenu();
        $this->view->groupList      = $groupList;
        $this->view->roleGroup      = $roleGroup;
        $this->view->rand           = $this->user->updateSessionRandom();
        $this->view->visionList     = $this->user->getVisionList();
        $this->view->showFields     = join(',', $showFields);
        $this->view->userData       = $userData;
        $this->view->allCount       = $allCount;
        $this->view->allPager       = $allPager;
        $this->view->pagerID        = $pagerID;
        $this->view->isEndPage      = $pagerID >= $allPager;
        $this->view->maxImport      = $maxImport;
        $this->view->dataInsert     = $insert;
        $this->view->userAddWarning = $this->config->edition != 'open' ? $this->user->getAddUserWarning() : '';
        $this->view->properties     = function_exists('ioncube_license_properties') ? ioncube_license_properties() : '';
        $this->display();
    }
}
