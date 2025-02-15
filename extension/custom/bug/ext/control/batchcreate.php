<?php

class mybug extends bug
{
/**
     * Batch create.
     *
     * @param  int    $productID
     * @param  int    $executionID
     * @param  int    $moduleID
     * @param  string $extra
     * @access public
     * @return void
     */
    public function batchCreate($productID, $branch = '', $executionID = 0, $moduleID = 0, $extra = '')
    {
        $extra = str_replace(array(',', ' '), array('&', ''), $extra);
        parse_str($extra, $output);

        if(!empty($_POST))
        {
            $actions = $this->bug->batchCreate($productID, $branch, $extra);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            /* Return bug id list when call the API. */
            if($this->viewType == 'json')
            {
                $bugIDList = array_keys($actions);
                return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'idList' => $bugIDList));
            }

            setcookie('bugModule', 0, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);

            /* If link from no head then reload. */
            if(isonlybody() and $executionID)
            {
                $execution = $this->loadModel('execution')->getByID($executionID);
                if($this->app->tab == 'execution')
                {
                    $execLaneType = $this->session->execLaneType ? $this->session->execLaneType : 'all';
                    $execGroupBy  = $this->session->execGroupBy ? $this->session->execGroupBy : 'default';
                    if($execution->type == 'kanban')
                    {
                        $rdSearchValue = $this->session->rdSearchValue ? $this->session->rdSearchValue : '';
                        $kanbanData    = $this->loadModel('kanban')->getRDKanban($executionID, $execLaneType, 'id_desc', 0, $execGroupBy, $rdSearchValue);
                        $kanbanData    = json_encode($kanbanData);

                        return print(js::closeModal('parent.parent', '', "parent.parent.updateKanban($kanbanData)"));
                    }
                    else
                    {
                        $taskSearchValue = $this->session->taskSearchValue ? $this->session->taskSearchValue : '';
                        $kanbanData      = $this->loadModel('kanban')->getExecutionKanban($executionID, $execLaneType, $execGroupBy, $taskSearchValue);
                        $kanbanType      = $execLaneType == 'all' ? 'bug' : key($kanbanData);
                        $kanbanData      = $kanbanData[$kanbanType];
                        $kanbanData      = json_encode($kanbanData);
                        return print(js::closeModal('parent.parent', '', "parent.parent.updateKanban(\"bug\", $kanbanData)"));
                    }
                }
                else
                {
                    return print(js::reload('parent.parent'));
                }
            }

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('bug', 'browse', "productID={$productID}&branch=$branch&browseType=unclosed&param=0&orderBy=id_desc"), 'parent'));
        }

        /* Get product, then set menu. */
        $productID = $this->product->saveState($productID, $this->products);
        if($branch === '') $branch = (int)$this->cookie->preBranch;
        $this->qa->setMenu($this->products, $productID, $branch);

        /* If executionID is setted, get builds and stories of this execution. */
        if($executionID)
        {
            $builds    = $this->loadModel('build')->getBuildPairs($productID, $branch, 'noempty,noreleased', $executionID, 'execution');
            $stories   = $this->story->getExecutionStoryPairs($executionID);
            $execution = $this->loadModel('execution')->getById($executionID);
            if($execution->type == 'kanban')
            {
                $this->loadModel('kanban');
                $regionPairs = $this->kanban->getRegionPairs($executionID, 0, 'execution');
                $regionID    = !empty($output['regionID']) ? $output['regionID'] : key($regionPairs);
                $lanePairs   = $this->kanban->getLanePairsByRegion($regionID, 'bug');
                $laneID      = !empty($output['laneID']) ? $output['laneID'] : key($lanePairs);

                $this->view->executionType = $execution->type;
                $this->view->regionID      = $regionID;
                $this->view->laneID        = $laneID;
                $this->view->regionPairs   = $regionPairs;
                $this->view->lanePairs     = $lanePairs;
            }
        }
        else
        {
            $builds  = $this->loadModel('build')->getBuildPairs($productID, $branch, 'noempty,noreleased');
            $stories = $this->story->getProductStoryPairs($productID, $branch);
        }

        if($this->session->bugImagesFile)
        {
            $files = $this->session->bugImagesFile;
            foreach($files as $fileName => $file)
            {
                $title = $file['title'];
                $titles[$title] = $fileName;
            }
            $this->view->titles = $titles;
        }

        /* Set custom. */
        $product = $this->product->getById($productID);
        foreach(explode(',', $this->config->bug->list->customBatchCreateFields) as $field)
        {
            if($product->type != 'normal') $customFields[$product->type] = $this->lang->product->branchName[$product->type];
            $customFields[$field] = $this->lang->bug->$field;
        }

        if($product->type != 'normal')
        {
            $this->config->bug->custom->batchCreateFields = sprintf($this->config->bug->custom->batchCreateFields, $product->type);
        }
        else
        {
            $this->config->bug->custom->batchCreateFields = trim(sprintf($this->config->bug->custom->batchCreateFields, ''), ',');
        }

        $showFields = $this->config->bug->custom->batchCreateFields;
        if($product->type == 'normal')
        {
            $showFields = str_replace(array(0 => ",branch,", 1 => ",platform,"), '', ",$showFields,");
            $showFields = trim($showFields, ',');
        }

        $projectID = $this->lang->navGroup->bug == 'project' ? $this->session->project : (isset($execution) ? $execution->project : 0);
        $project   = $this->loadModel('project')->getByID($projectID);
        if(isset($project->model) && $project->model == 'kanban') $customFields['execution'] = $this->lang->bug->kanban;

        /* Get branches. */
        if($executionID)
        {
            $productBranches = $product->type != 'normal' ? $this->execution->getBranchByProduct($productID, $executionID) : array();
            $branches        = isset($productBranches[$productID]) ? $productBranches[$productID] : array();
            $branch          = key($branches);
        }
        else
        {
            $branches = $product->type != 'normal' ? $this->loadModel('branch')->getPairs($productID, 'active') : array();
        }

        $this->view->customFields = $customFields;
        $this->view->showFields   = $showFields;

        $this->view->title      = $this->products[$productID] . $this->lang->colon . $this->lang->bug->batchCreate;
        $this->view->position[] = html::a($this->createLink('bug', 'browse', "productID=$productID&branch=$branch"), $this->products[$productID]);
        $this->view->position[] = $this->lang->bug->batchCreate;

        $this->view->project          = $project;
        $this->view->product          = $product;
        $this->view->productID        = $productID;
        $this->view->stories          = $stories;
        $this->view->builds           = $builds;
        $this->view->users            = $this->user->getPairs('devfirst|noclosed');
        $this->view->projects         = array('' => '') + $this->product->getProjectPairsByProduct($productID, $branch ? "0,$branch" : 0, 0, (isset($this->config->CRProject) && empty($this->config->CRProject) ? 'unclosed' :''));
        $this->view->projectID        = $projectID;
        $this->view->executions       = array('' => '') + $this->product->getExecutionPairsByProduct($productID, $branch ? "0,$branch" : 0, 'id_desc', $projectID, (isset($this->config->CRProject) && empty($this->config->CRProject) ? 'multiple,stagefilter,projectclosefilter' : 'multiple,stagefilter'));
        $this->view->executionID      = $executionID;
        $this->view->moduleOptionMenu = $this->tree->getOptionMenu($productID, $viewType = 'bug', $startModuleID = 0, $branch === 'all' ? 0 : $branch);
        $this->view->moduleID         = $moduleID;
        $this->view->branch           = $branch;
        $this->view->branches         = $branches;

        $this->display();
    }    
}