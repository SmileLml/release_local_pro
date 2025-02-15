<?php

class mybug extends bug
{
    public function batchAdjust($productID = 0, $branchID = 0)
    {
        /* if(!$this->post->bugIDList) return print(js::locate($this->session->bugList, 'parent'));

        $bugIDList = array_unique($this->post->bugIDList);
        $bugs      = $this->dao->select('*')->from(TABLE_BUG)->where('id')->in($bugIDList)->fetchAll('id');
        
        if($this->post->project)
        {
            $allChanges = $this->bug->batchAdjust($productID, $branchID);

            foreach($allChanges as $bugID => $changes)
            {
                if(empty($changes)) continue;

                $actionID = $this->action->create('bug', $bugID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }
            return print(js::locate($this->session->bugList, 'parent'));
        }
        $this->loadModel('project');
        $this->loadModel('execution');
        
        if($this->app->tab == 'project')
        {
            $projectID  = $this->session->project;
            $project    = $this->project->getByID($projectID);

            $title      = $project->name . $this->lang->colon . $this->lang->bug->common . $this->lang->bug->batchAdjust;
            $position[] = html::a($this->createLink('project', 'browse', "projectID=$projectID"), $project->name);
            $position[] = $this->lang->bug->common;

            $this->project->setMenu($projectID);

            $this->config->bug->batchAdjustFields = str_replace('branch,', '', $this->config->bug->batchAdjustFields);

        }
        else if($this->app->tab == 'execution')
        {
            $executionID = $this->session->execution;
            $execution   = $this->execution->getByID($executionID);

            $title       = $execution->name . $this->lang->colon . $this->lang->bug->common . $this->lang->bug->batchAdjust;
            $position[]  = html::a($this->createLink('execution', 'browse', "executionID=$executionID"), $execution->name);
            $position[]  = $this->lang->execution->bug;

            $this->execution->setMenu($executionID);

            $this->config->bug->batchAdjustFields = str_replace('branch,', '', $this->config->bug->batchAdjustFields);

        }
        else
        {
            $product    = $this->product->getByID($productID);

            $title      = $product->name . $this->lang->colon . $this->lang->bug->common . $this->lang->bug->batchAdjust;
            $position[] =  html::a($this->createLink('bug', 'browse', "productID=$productID&branch=$branchID"), $this->products[$productID]);

            $this->qa->setMenu($this->products, $productID, $branchID);

            $this->config->bug->batchAdjustFields = str_replace('product,', '', $this->config->bug->batchAdjustFields);
            $this->config->bug->batchAdjustFields = str_replace('branch,', '', $this->config->bug->batchAdjustFields);
        }
        
        $countInputVars  = count($bugs) * (count(explode(',', $this->config->bug->batchAdjustFields)) + 1);
        $showSuhosinInfo = common::judgeSuhosinSetting($countInputVars);
        if($showSuhosinInfo) $this->view->suhosinInfo = extension_loaded('suhosin') ? sprintf($this->lang->suhosinInfo, $countInputVars) : sprintf($this->lang->maxVarsInfo, $countInputVars);

        $products = $this->product->getPairs((empty($this->config->CRProduct) || empty($this->config->CRProject)) ? 'noclosed' : '', 0, 'all');
        $projects = $this->product->getProjectPairsByProduct($productID, $branchID, '', (isset($this->config->CRProject) && empty($this->config->CRProject)) ? 'unclosed' : '');

        foreach($bugs as $bug)
        {
            $branches = array();
            if($this->app->tab != 'qa')
            {
                $product = $this->product->getByID($bug->product);
                $branches = $product->type != 'normal' ? $this->loadModel('branch')->getPairs($bug->product, 'active') : array();

                $projects = $this->product->getProjectPairsByProduct($bug->product, $bug->branch, (isset($this->config->CRProject) && empty($this->config->CRProject)) ? 'unclosed' : '');
                
                if(!empty($bug->project) and empty($projects[$bug->project]))
                {
                    $project = $this->loadModel('project')->getByID($bug->project);
                    $projects[$project->id] = $project->name . "({$this->lang->bug->deleted})";
                }
                $bug->productWithBranch = $product->type != 'normal';

            }
            $bug->branches     = $branches;
            $bug->projects     = $projects;
            $bug->openedBuilds = $this->loadModel('build')->getBuildPairs($bug->product, $branchID, 'noempty,noterminate,nodone,noreleased',$bug->project, 'project', $bug->openedBuild);
        }

        $this->view->title      = $title;
        $this->view->position   = $position;
        $this->view->products   = $this->products = $products;
        $this->view->showFields = $this->config->bug->batchAdjustFields;
        $this->view->productID  = $productID;
        $this->view->branchID   = $branchID;
        $this->view->bugs       = $bugs;
        $this->display(); */
        if(!$this->post->bugIDList) return print(js::locate($this->session->bugList, 'parent'));

        if($this->post->adjustProject)
        {
            $allChanges = $this->bug->batchSetAdjust();

            foreach($allChanges as $bugID => $changes)
            {
                if(empty($changes)) continue;

                $actionID = $this->action->create('bug', $bugID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }
            return print(js::locate($this->session->bugList, 'parent'));
        }
        else
        {
            return print(js::alert($this->lang->bug->project . $this->lang->bug->noempty) . js::locate($this->session->bugList));
        }
    }
}