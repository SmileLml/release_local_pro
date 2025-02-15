<?php

class mybug extends bug
{
    /**
     * Edit a bug.
     *
     * @param  int    $bugID
     * @param  bool   $comment
     * @param  string $kanbanGroup
     * @access public
     * @return void
     */
    public function edit($bugID, $comment = false, $kanbanGroup = 'default')
    {
        if(!empty($_POST))
        {
            $changes = array();
            $files   = array();
            if($comment == false)
            {
                $changes = $this->bug->update($bugID);
                if(dao::isError())
                {
                    if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'error', 'message' => dao::getError()));
                    return print(js::error(dao::getError()));
                }
            }

            if($this->post->comment != '' or !empty($changes))
            {
                $action = !empty($changes) ? 'Edited' : 'Commented';
                $actionID = $this->action->create('bug', $bugID, $action, $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success', 'data' => $bugID));
            $bug = $this->bug->getById($bugID);

            $this->executeHooks($bugID);

            if($bug->toTask != 0)
            {
                foreach($changes as $change)
                {
                    if($change['field'] == 'status')
                    {
                        $confirmURL = $this->createLink('task', 'view', "taskID=$bug->toTask");
                        $cancelURL  = $this->server->HTTP_REFERER;
                        return print(js::confirm(sprintf($this->lang->bug->remindTask, $bug->Task), $confirmURL, $cancelURL, 'parent', 'parent'));
                    }
                }
            }
            if(isonlybody())
            {
                $execution = $this->loadModel('execution')->getByID($bug->execution);
                if($this->app->tab == 'execution')
                {
                    $execLaneType = $this->session->execLaneType ? $this->session->execLaneType : 'all';
                    $execGroupBy  = $this->session->execGroupBy ? $this->session->execGroupBy : 'default';

                    if(isset($execution->type) and $execution->type == 'kanban')
                    {
                        $rdSearchValue = $this->session->rdSearchValue ? $this->session->rdSearchValue : '';
                        $kanbanData    = $this->loadModel('kanban')->getRDKanban($bug->execution, $execLaneType, 'id_desc', 0, $kanbanGroup, $rdSearchValue);
                        $kanbanData    = json_encode($kanbanData);
                        return print(js::closeModal('parent.parent', '', "parent.parent.updateKanban($kanbanData)"));
                    }
                    else
                    {
                        $taskSearchValue = $this->session->taskSearchValue ? $this->session->taskSearchValue : '';
                        $kanbanData      = $this->loadModel('kanban')->getExecutionKanban($bug->execution, $execLaneType, $execGroupBy, $taskSearchValue);
                        $kanbanType      = $execLaneType == 'all' ? 'bug' : key($kanbanData);
                        $kanbanData      = $kanbanData[$kanbanType];
                        $kanbanData      = json_encode($kanbanData);
                        return print(js::closeModal('parent.parent', '', "parent.parent.updateKanban(\"bug\", $kanbanData)"));
                    }
                }
                else
                {
                    return print(js::closeModal('parent.parent'));
                }
            }
            return print(js::locate($this->createLink('bug', 'view', "bugID=$bugID"), 'parent'));
        }

        /* Get the info of bug, current product and modue. */
        $bug             = $this->bug->getById($bugID);
        $productID       = $bug->product;
        $executionID     = $bug->execution;
        $projectID       = $bug->project;
        $currentModuleID = $bug->module;
        $product         = $this->loadModel('product')->getByID($productID);
        $execution       = $this->loadModel('execution')->getByID($executionID);
        $this->bug->checkBugExecutionPriv($bug);

        if(!isset($this->products[$bug->product]))
        {
            $this->products[$bug->product] = $product->name;
            $this->view->products = $this->products;
        }

        /* Set the menu. */
        if($this->app->tab == 'project')   $this->loadModel('project')->setMenu($bug->project);
        if($this->app->tab == 'execution') $this->loadModel('execution')->setMenu($bug->execution);
        if($this->app->tab == 'qa')        $this->qa->setMenu($this->products, $productID, $bug->branch);
        if($this->app->tab == 'devops')
        {
            session_write_close();

            $repos = $this->loadModel('repo')->getRepoPairs('project', $bug->project);
            $this->repo->setMenu($repos);

            $this->lang->navGroup->bug = 'devops';
        }

        /* Unset discarded types. */
        foreach($this->config->bug->discardedTypes as $type)
        {
            if($bug->type != $type) unset($this->lang->bug->typeList[$type]);
        }

        if($this->app->tab == 'qa')
        {
            $this->view->products = $this->config->CRProduct ? $this->products : $this->product->getPairs('noclosed');
        }
        if($this->app->tab == 'project')
        {
            $products    = array();
            $productList = $this->config->CRProduct ? $this->product->getOrderedProducts('all', 40, $bug->project) : $this->product->getOrderedProducts('normal', 40, $bug->project);
            foreach($productList as $productInfo) $products[$productInfo->id] = $productInfo->name;

            $this->view->products = $products;
        }

        /* Set header and position. */
        $this->view->title      = $this->lang->bug->edit . "BUG #$bug->id $bug->title - " . $this->products[$productID];
        $this->view->position[] = html::a($this->createLink('bug', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[] = $this->lang->bug->edit;

        $projectID = $this->lang->navGroup->bug == 'project' ? $this->session->project : 0;

        if($this->app->tab == 'execution' or $this->app->tab == 'project')
        {
            $objectID = $this->app->tab == 'project' ? $bug->project : $bug->execution;
        }

        /* Display status of branch. */
        $branches = $this->loadModel('branch')->getList($productID, isset($objectID) ? $objectID : 0, 'all');
        $branchOption    = array();
        $branchTagOption = array();
        foreach($branches as $branchInfo)
        {
            $branchOption[$branchInfo->id]    = $branchInfo->name;
            $branchTagOption[$branchInfo->id] = $branchInfo->name . ($branchInfo->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : '');
        }
        if(!isset($branchTagOption[$bug->branch]))
        {
            $bugBranch = $this->branch->getById($bug->branch, $bug->product, '');
            $branchTagOption[$bug->branch] = $bug->branch == BRANCH_MAIN ? $bugBranch : ($bugBranch->name . ($bugBranch->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : ''));
        }

        $moduleOptionMenu = $this->tree->getOptionMenu($productID, $viewType = 'bug', $startModuleID = 0, $bug->branch);
        if(!isset($moduleOptionMenu[$bug->module])) $moduleOptionMenu += $this->tree->getModulesName($bug->module);

        $cases = array();
        if($bug->case)
        {
            $case  = $this->loadModel('testcase')->getByID($bug->case);
            $cases = array($case->id => $case->id . $case->title);
        }

        $this->config->moreLinks['case'] = inlink('ajaxGetProductCases', "bugID={$bugID}");

        /* Get assigned to member. */
        if($bug->execution)
        {
            $assignedToList = $this->user->getTeamMemberPairs($bug->execution, 'execution');
        }
        elseif($bug->project)
        {
            $assignedToList = $this->loadModel('project')->getTeamMemberPairs($bug->project);
        }
        else
        {
            $assignedToList = $this->bug->getProductMemberPairs($bug->product, $bug->branch);
            $assignedToList = array_filter($assignedToList);
            if(empty($assignedToList)) $assignedToList = $this->user->getPairs('devfirst|noclosed');
        }
        if($bug->assignedTo and !isset($assignedToList[$bug->assignedTo]) and $bug->assignedTo != 'closed')
        {
            /* Fix bug #28378. */
            $assignedTo = $this->user->getById($bug->assignedTo);
            $assignedToList[$bug->assignedTo] = $assignedTo->realname;
        }
        if($bug->status == 'closed') $assignedToList['closed'] = 'Closed';

        $branch = $product->type == 'branch' ? ($bug->branch > 0 ? $bug->branch . ',0' : '0') : '';

        $productBugs = array();
        if($bug->resolution and $bug->duplicateBug)
        {
            $duplicateBug = $this->bug->getByID($bug->duplicateBug);
            $productBugs  = $this->bug->getProductBugPairs($productID, $branch, $duplicateBug->title);
        }
        $this->config->moreLinks['duplicateBug'] = inlink('ajaxGetProductBugs', "productID={$productID}&bugID={$bugID}&type=json");

        unset($productBugs[$bugID]);

        $executions = array(0 => '') + $this->product->getExecutionPairsByProduct($bug->product, $bug->branch, 'id_desc', $bug->project, (isset($this->config->CRProject) && empty($this->config->CRProject)) ? 'projectclosefilter' : '');
        if(!empty($bug->execution) and empty($executions[$bug->execution])) $executions[$execution->id] = $execution->name . "({$this->lang->bug->deleted})";

        $projects = array(0 => '') + $this->product->getProjectPairsByProduct($productID, $bug->branch, '', (isset($this->config->CRProject) && empty($this->config->CRProject)) ? 'unclosed' : '');
        if(!empty($bug->project) and empty($projects[$bug->project]))
        {
            $project = $this->loadModel('project')->getByID($bug->project);
            $projects[$project->id] = $project->name . "({$this->lang->bug->deleted})";
        }

        if($product->shadow) $this->view->project = $this->loadModel('project')->getByShadowProduct($bug->product);
        $this->app->loadLang('build');

        $this->view->bug                   = $bug;
        $this->view->product               = $product;
        $this->view->execution             = $execution;
        $this->view->productBugs           = $productBugs;
        $this->view->productName           = $this->products[$productID];
        $this->view->plans                 = $this->loadModel('productplan')->getPairs($productID, $bug->branch, '', true);
        $this->view->projects              = $projects;
        $this->view->projectExecutionPairs = $this->loadModel('project')->getProjectExecutionPairs();
        $this->view->moduleOptionMenu      = $moduleOptionMenu;
        $this->view->currentModuleID       = $currentModuleID;
        $this->view->executions            = $executions;
        $this->view->branchOption          = $branchOption;
        $this->view->branchTagOption       = $branchTagOption;
        $this->view->testtasks             = $this->loadModel('testtask')->getPairs($bug->product, $bug->execution, $bug->testtask);
        $this->view->users                 = $this->user->getPairs('noclosed', "$bug->assignedTo,$bug->resolvedBy,$bug->closedBy,$bug->openedBy");
        $this->view->assignedToList        = $assignedToList;
        $this->view->cases                 = array('' => '') + $cases;
        $this->view->actions               = $this->action->getList('bug', $bugID);

        $this->display();
    }
}