<?php

class myexecution extends execution
{
    /**
     * Import from Bug.
     *
     * @param  int    $executionID
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function importBug($executionID = 0, $browseType = 'all', $param = 0, $recTotal = 0, $recPerPage = 30, $pageID = 1)
    {
        $this->app->loadConfig('task');

        if(!empty($_POST))
        {
            $mails = $this->execution->importBug($executionID);
            if(dao::isError()) return print(js::error(dao::getError()));

            /* If link from no head then reload. */
            if(isonlybody())
            {
                $kanbanData = $this->loadModel('kanban')->getRDKanban($executionID, $this->session->execLaneType ? $this->session->execLaneType : 'all');
                return print(js::reload('parent'));
            }

            return print(js::locate($this->createLink('execution', 'importBug', "executionID=$executionID"), 'parent'));
        }

        /* Set browseType, productID, moduleID and queryID. */
        $browseType = strtolower($browseType);
        $queryID    = ($browseType == 'bysearch') ? (int)$param : 0;

        $this->loadModel('bug');
        $executions = $this->execution->getPairs(0, 'all', 'nocode');
        $this->execution->setMenu($executionID);

        $execution = $this->execution->getByID($executionID);
        $project   = $this->loadModel('project')->getByID($execution->project);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $title      = $executions[$executionID] . $this->lang->colon . $this->lang->execution->importBug;
        $position[] = html::a($this->createLink('execution', 'task', "executionID=$executionID"), $executions[$executionID]);
        $position[] = $this->lang->execution->importBug;

        /* Get users, products and executions.*/
        $members  = $this->loadModel('user')->getTeamMemberPairs($executionID, 'execution', 'nodeleted');
        $users    = $this->loadModel('user')->getPairs('noclosed|nodeleted');

        $this->loadModel('tree');
        $showAllModule    = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
        $moduleOptionMenu = $this->tree->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');

        $products = $this->dao->select('t1.product, t2.name')->from(TABLE_PROJECTPRODUCT)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')
            ->on('t1.product = t2.id')
            ->where('t1.project')->eq($executionID)
            ->fetchPairs('product');

        if(!empty($products))
        {
            unset($executions);
            $executions = $this->dao->select('t1.project, t2.name')->from(TABLE_PROJECTPRODUCT)->alias('t1')
                ->leftJoin(TABLE_EXECUTION)->alias('t2')
                ->on('t1.project = t2.id')
                ->where('t1.product')->in(array_keys($products))
                ->andWhere('t2.type')->in('sprint,stage,kanban')
                ->fetchPairs('project');

            $projects = $this->loadModel('product')->getProjectPairsByProductIDList(array_keys($products));
        }
        else
        {
            $executionName = $executions[$executionID];
            unset($executions);
            $executions[$executionID] = $executionName;

            $projects[$project->id] = $project->name;
        }

        /* Get bugs.*/
        $bugs = array();
        if($browseType != "bysearch")
        {
            $bugs = $this->bug->getActiveAndPostponedBugs(array_keys($products), $executionID, $pager);
        }
        else
        {
            if($queryID)
            {
                $query = $this->loadModel('search')->getQuery($queryID);
                if($query)
                {
                    $this->session->set('importBugQuery', $query->sql);
                    $this->session->set('importBugForm', $query->form);
                }
                else
                {
                    $this->session->set('importBugQuery', ' 1 = 1');
                }
            }
            else
            {
                if($this->session->importBugQuery == false) $this->session->set('importBugQuery', ' 1 = 1');
            }
            $bugQuery = str_replace("`product` = 'all'", "`product`" . helper::dbIN(array_keys($products)), $this->session->importBugQuery); // Search all execution.
            $bugs     = $this->execution->getSearchBugs($products, $executionID, $bugQuery, $pager, 'id_desc');
        }

        /* Build the search form. */
        $this->config->bug->search['actionURL'] = $this->createLink('execution', 'importBug', "executionID=$executionID&browseType=bySearch&param=myQueryID");
        $this->config->bug->search['queryID']   = $queryID;
        if(!empty($products))
        {
            $this->config->bug->search['params']['product']['values'] = array(''=>'') + $products + array('all'=>$this->lang->execution->aboveAllProduct);
        }
        else
        {
            $this->config->bug->search['params']['product']['values'] = array(''=>'');
        }
        $this->config->bug->search['params']['execution']['values'] = array(''=>'') + $executions + array('all'=>$this->lang->execution->aboveAllExecution);
        $this->config->bug->search['params']['plan']['values']      = $this->loadModel('productplan')->getPairs(array_keys($products));
        $this->config->bug->search['module'] = 'importBug';
        $this->config->bug->search['params']['confirmed']['values'] = array('' => '') + $this->lang->bug->confirmedList;

        $bugModules = array();
        foreach($products as $productID => $productName)
        {
            $productModules = $this->tree->getOptionMenu($productID, 'bug', 0, 'all');
            foreach($productModules as $moduleID => $moduleName)
            {
                if(empty($moduleID))
                {
                    $bugModules[$moduleID] = $moduleName;
                    continue;
                }
                $bugModules[$moduleID] = $productName . $moduleName;
            }
        }
        $this->config->bug->search['params']['module']['values'] = $bugModules;

        $this->config->bug->search['params']['project']['values'] = array('' => '') + $projects;

        $this->config->bug->search['params']['openedBuild']['values'] = $this->loadModel('build')->getBuildPairs($productID, 'all', 'withbranch|releasetag');

        unset($this->config->bug->search['fields']['resolvedBy']);
        unset($this->config->bug->search['fields']['closedBy']);
        unset($this->config->bug->search['fields']['status']);
        unset($this->config->bug->search['fields']['toTask']);
        unset($this->config->bug->search['fields']['toStory']);
        unset($this->config->bug->search['fields']['severity']);
        unset($this->config->bug->search['fields']['resolution']);
        unset($this->config->bug->search['fields']['resolvedBuild']);
        unset($this->config->bug->search['fields']['resolvedDate']);
        unset($this->config->bug->search['fields']['closedDate']);
        unset($this->config->bug->search['fields']['branch']);
        if(empty($execution->multiple) and empty($execution->hasProduct)) unset($this->config->bug->search['fields']['plan']);
        if(empty($project->hasProduct))
        {
            unset($this->config->bug->search['fields']['product']);
            if($project->model !== 'scrum') unset($this->config->bug->search['fields']['plan']);
        }
        unset($this->config->bug->search['params']['resolvedBy']);
        unset($this->config->bug->search['params']['closedBy']);
        unset($this->config->bug->search['params']['status']);
        unset($this->config->bug->search['params']['toTask']);
        unset($this->config->bug->search['params']['toStory']);
        unset($this->config->bug->search['params']['severity']);
        unset($this->config->bug->search['params']['resolution']);
        unset($this->config->bug->search['params']['resolvedBuild']);
        unset($this->config->bug->search['params']['resolvedDate']);
        unset($this->config->bug->search['params']['closedDate']);
        unset($this->config->bug->search['params']['branch']);
        $this->loadModel('search')->setSearchParams($this->config->bug->search);
        unset($this->lang->task->typeList['affair']);
        /* Assign. */
        $this->view->title            = $title;
        $this->view->position         = $position;
        $this->view->pager            = $pager;
        $this->view->bugs             = $bugs;
        $this->view->recTotal         = $pager->recTotal;
        $this->view->recPerPage       = $pager->recPerPage;
        $this->view->browseType       = $browseType;
        $this->view->param            = $param;
        $this->view->members          = $members;
        $this->view->users            = $users;
        $this->view->execution        = $execution;
        $this->view->executionID      = $executionID;
        $this->view->moduleOptionMenu = $moduleOptionMenu;
        $this->view->requiredFields   = explode(',', $this->config->task->create->requiredFields);
        $this->display();
    }
}