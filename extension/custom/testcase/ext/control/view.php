<?php

helper::importControl('testcase');
class mytestcase extends testcase
{

    /**
     * View a test case.
     *
     * @param int $caseID
     * @param int $version
     * @param string $from
     * @access public
     * @return void
     */
    public function view($caseID, $version = 0, $from = 'testcase', $taskID = 0)
    {
        $this->session->set('bugList', $this->app->getURI(true), $this->app->tab);

        $caseID = (int)$caseID;
        $case   = $this->testcase->getById($caseID, $version);

        if(!$case)
        {
            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => '404 Not found'));
            return print(js::error($this->lang->notFound) . js::locate($this->createLink('qa', 'index')));
        }

        $case = $this->loadModel('story')->checkNeedConfirm($case);

        if($from == 'testtask')
        {
            $run = $this->loadModel('testtask')->getRunByCase($taskID, $caseID);
            $case->assignedTo    = $run->assignedTo;
            $case->lastRunner    = $run->lastRunner;
            $case->lastRunDate   = $run->lastRunDate;
            $case->lastRunResult = $run->lastRunResult;
            $case->caseStatus    = $case->status;
            $case->status        = $run->status;

            $results = $this->testtask->getResults($run->id);
            $result  = array_shift($results);
            if($result)
            {
                $case->xml      = $result->xml;
                $case->duration = $result->duration;
            }
        }

        $isLibCase = ($case->lib and empty($case->product));
        if($isLibCase)
        {
            $libraries = $this->loadModel('caselib')->getLibraries();
            $this->app->tab == 'project' ? $this->loadModel('project')->setMenu($this->session->project) : $this->caselib->setLibMenu($libraries, $case->lib);

            $this->view->title      = "CASE #$case->id $case->title - " . $libraries[$case->lib];
            $this->view->position[] = html::a($this->createLink('caselib', 'browse', "libID=$case->lib"), $libraries[$case->lib]);

            $this->view->libName               = $libraries[$case->lib];
            $this->view->canBeChangedByProject = true;
        }
        else
        {
            $productID = $case->product;
            $product   = $this->product->getByID($productID);
            $branches  = $product->type == 'normal' ? array() : $this->loadModel('branch')->getPairs($productID);

            if($this->app->tab == 'project') $this->loadModel('project')->setMenu($this->session->project);
            if($this->app->tab == 'execution') $this->loadModel('execution')->setMenu($this->session->execution);
            if($this->app->tab == 'qa') $this->testcase->setMenu($this->products, $productID, $case->branch);

            $this->view->title                 = "CASE #$case->id $case->title - " . $product->name;
            $this->view->product               = $product;
            $this->view->branches              = $branches;
            $this->view->productName           = $product->name;
            $this->view->branchName            = $product->type == 'normal' ? '' : zget($branches, $case->branch, '');
            $this->view->canBeChangedByProject = common::canModify('projectproduct', $product);
        }


        if(isset($case->execution) && $case->execution)
        {
            $execution = $this->loadModel('execution')->getByID($case->execution);
            $this->view->canBeChangedByProject = common::canModify('execution', $execution);
        }

        if(isset($case->project) && $case->project)
        {
            $project = $this->loadModel('project')->getByID($case->project);
            $this->view->canBeChangedByProject = common::canModify('project', $project);
        }

        $caseFails = $this->dao->select('COUNT(*) AS count')->from(TABLE_TESTRESULT)
            ->where('caseResult')->eq('fail')
            ->andwhere('`case`')->eq($caseID)
            ->beginIF($from == 'testtask')->andwhere('`run`')->eq($taskID)->fi()
            ->fetch('count');
        $case->caseFails = $caseFails;
        $this->executeHooks($caseID);
        if($this->config->edition == 'ipd') $case = $this->loadModel('story')->getAffectObject('', 'case', $case);

        $this->view->position[] = $this->lang->testcase->common;
        $this->view->position[] = $this->lang->testcase->view;

        $this->view->case       = $case;
        $this->view->from       = $from;
        $this->view->taskID     = $taskID;
        $this->view->version    = $version ? $version : $case->version;
        $this->view->modulePath = $this->tree->getParents($case->module);
        $this->view->caseModule = empty($case->module) ? '' : $this->tree->getById($case->module);
        $this->view->users      = $this->user->getPairs('noletter');
        $this->view->actions    = $this->loadModel('action')->getList('case', $caseID);
        $this->view->preAndNext = !isOnlybody() ? $this->loadModel('common')->getPreAndNextObject('testcase', $caseID) : '';
        $this->view->runID      = $from == 'testcase' ? 0 : $run->id;
        $this->view->isLibCase  = $isLibCase;
        $this->view->caseFails  = $caseFails;

        if(defined('RUN_MODE') and RUN_MODE == 'api' and !empty($this->app->version)) return $this->send(array('status' => 'success', 'case' => $case));
        $this->display();
    }
}