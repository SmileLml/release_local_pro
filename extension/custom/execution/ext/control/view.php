<?php

class myexecution extends execution
{
    /**
     * View a execution.
     *
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function view($executionID)
    {
        $this->app->session->set('teamList', $this->app->getURI(true), 'execution');

        $executionID = $this->execution->saveState((int)$executionID, $this->executions);
        $execution   = $this->execution->getById($executionID, true);

        $type = $this->config->vision == 'lite' ? 'kanban' : 'stage,sprint,kanban';
        if(empty($execution) || strpos($type, $execution->type) === false) return print(js::error($this->lang->notFound) . js::locate('back'));

        if(!$this->loadModel('common')->checkPrivByObject('execution', $executionID)) return print(js::error($this->lang->execution->accessDenied) . js::locate($this->createLink('execution', 'all')));

        $execution->projectInfo = $this->loadModel('project')->getByID($execution->project);

        $programList = array_filter(explode(',', $execution->projectInfo->path));
        array_pop($programList);
        $this->view->programList = $this->loadModel('program')->getPairsByList($programList);

        if($execution->type == 'kanban' and defined('RUN_MODE') and RUN_MODE == 'api') return print($this->fetch('execution', 'kanban', "executionID=$executionID"));

        $this->app->loadLang('program');

        /* Execution not found to prevent searching for .*/
        if(!isset($this->executions[$execution->id])) $this->executions = $this->execution->getPairs($execution->project, 'all', 'nocode');

        $products = $this->loadModel('product')->getProducts($execution->id);
        $linkedBranches = array();
        foreach($products as $product)
        {
            if(isset($product->branches))
            {
                foreach($product->branches as $branchID) $linkedBranches[$branchID] = $branchID;
            }
        }

        /* Set menu. */
        $this->execution->setMenu($execution->id);
        $this->app->loadLang('bug');

        if($execution->type == 'kanban')
        {
            $this->app->loadClass('date');

            list($begin, $end) = $this->execution->getBeginEnd4CFD($execution);
            $dateList  = date::getDateList($begin, $end, 'Y-m-d', 'noweekend');
            $chartData = $this->execution->buildCFDData($executionID, $dateList, 'task');
            if(isset($chartData['line'])) $chartData['line'] = array_reverse($chartData['line']);

            $this->view->begin = helper::safe64Encode(urlencode($begin));
            $this->view->end   = helper::safe64Encode(urlencode($end));
        }
        else
        {
            $type = 'noweekend';
            if(((strpos('closed,suspended', $execution->status) === false and helper::today() > $execution->end)
                    or ($execution->status == 'closed'    and substr($execution->closedDate, 0, 10) > $execution->end)
                    or ($execution->status == 'suspended' and $execution->suspendedDate > $execution->end))
                and strpos($type, 'delay') === false)
            {
                $type .= ',withdelay';
            }

            $deadline = $execution->status == 'closed' ? substr($execution->closedDate, 0, 10) : $execution->suspendedDate;
            $deadline = strpos('closed,suspended', $execution->status) === false ? helper::today() : $deadline;
            $endDate  = strpos($type, 'withdelay') !== false ? $deadline : $execution->end;
            list($dateList, $interval) = $this->execution->getDateList($execution->begin, $endDate, $type, 0, 'Y-m-d', $execution->end);
            
            $executionEnd = strpos($type, 'withdelay') !== false ? $execution->end : '';
            $chartData    = $this->execution->buildBurnData($executionID, $dateList, $type, 'left', $executionEnd);
        }

        $this->executeHooks($executionID);
        if(!$execution->projectInfo->hasProduct) $this->lang->execution->PO = $this->lang->common->story . $this->lang->execution->owner;

        $project = $this->loadModel('project')->getByID($execution->project);

        $this->view->title        = $this->lang->execution->view;
        $this->view->execution    = $execution;
        $this->view->products     = $products;
        $this->view->branchGroups = $this->loadModel('branch')->getByProducts(array_keys($products), '', $linkedBranches);
        $this->view->planGroups   = $this->execution->getPlans($products);
        $this->view->actions      = $this->loadModel('action')->getList($this->objectType, $executionID);
        $this->view->dynamics     = $this->loadModel('action')->getDynamic('all', 'all', 'date_desc', 30, 'all', 'all', $executionID);
        $this->view->users        = $this->loadModel('user')->getPairs('noletter');
        $this->view->teamMembers  = $this->execution->getTeamMembers($executionID);
        $this->view->docLibs      = $this->loadModel('doc')->getLibsByObject('execution', $executionID);
        $this->view->statData     = $this->execution->statRelatedData($executionID);
        $this->view->chartData    = $chartData;
        $this->view->type         = $type;
        $this->view->features     = $this->execution->getExecutionFeatures($execution);
        $this->view->project      = $project;
        $this->view->canBeChanged = common::canModify('execution', $execution, $project); // Determines whether an object is editable.

        $this->display();
    }
}