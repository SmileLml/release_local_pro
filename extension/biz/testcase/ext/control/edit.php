<?php
include '../../control.php';
class mytestcase extends testcase
{
    /**
     * Edit a case.
     *
     * @param  int   $caseID
     * @access public
     * @return void
     */
    public function edit($caseID, $comment = false, $executionID = 0)
    {
        $this->loadModel('story');

        $case = $this->testcase->getById($caseID);
        if(!$case) return print(js::error($this->lang->notFound) . js::locate('back'));

        $testtasks = $this->loadModel('testtask')->getGroupByCases($caseID);
        $testtasks = empty($testtasks[$caseID]) ? array() : $testtasks[$caseID];

        if(!empty($_POST))
        {
            if(!empty($_FILES['scriptFile'])) unset($_FILES['scriptFile']);

            $changes = array();
            if($comment == false or $comment == 'false')
            {
                $changes = $this->testcase->update($caseID, $testtasks);
                if(dao::isError()) return print(js::error(dao::getError()));
            }
            if($this->post->comment != '' or !empty($changes))
            {
                $this->loadModel('action');
                $action = !empty($changes) ? 'Edited' : 'Commented';
                $actionID = $this->action->create('case', $caseID, $action, $this->post->comment);
                $this->action->logHistory($actionID, $changes);

                if($case->status != 'wait' and $this->post->status == 'wait') $this->action->create('case', $caseID, 'submitReview');
            }

            $this->executeHooks($caseID);
            // include_once('autotest.php');
            // $autotest = new autotest();
            // $testCase = $this->dao->select('*')->from(TABLE_CASE)->where('id')->eq($caseID)->fetch();
            // $fromcase = $testCase->fromCaseID;
            // if($testCase->fromCaseID==0){
            //     $fromcase = $caseID;
            // }
            // $ops = array('status'=>$testCase->auto);
            // $result = $autotest->Edittestcase($testcaseID=$fromcase,$optionalParams=$ops);
            if(defined('RUN_MODE') && RUN_MODE == 'api')
            {
                return $this->send(array('status' => 'success', 'data' => $caseID));
            }
            else
            {
                return print(js::locate($this->createLink('testcase', 'view', "caseID=$caseID"), 'parent'));
            }
        }

        if(empty($case->steps))
        {
            $step = new stdclass();
            $step->type   = 'step';
            $step->desc   = '';
            $step->expect = '';
            $case->steps[] = $step;
        }

        $isLibCase = ($case->lib and empty($case->product));
        if($isLibCase)
        {
            $productID = isset($this->session->product) ? $this->session->product : 0;
            $libraries = $this->loadModel('caselib')->getLibraries();
            $this->app->tab == 'project' ? $this->loadModel('project')->setMenu($this->session->project) : $this->caselib->setLibMenu($libraries, $case->lib);

            $title      = "CASE #$case->id $case->title - " . $libraries[$case->lib];
            $position[] = html::a($this->createLink('caselib', 'browse', "libID=$case->lib"), $libraries[$case->lib]);

            $this->view->libID     = $case->lib;
            $this->view->libName   = $libraries[$case->lib];
            $this->view->libraries = $libraries;
            $this->view->moduleOptionMenu = $this->tree->getOptionMenu($case->lib, $viewType = 'caselib', $startModuleID = 0);
        }
        else
        {
            $productID  = $case->product;
            $product    = $this->product->getById($productID);
            if(!isset($this->products[$productID])) $this->products[$productID] = $product->name;

            $title      = $this->products[$productID] . $this->lang->colon . $this->lang->testcase->edit;
            $position[] = html::a($this->createLink('testcase', 'browse', "productID=$productID"), $this->products[$productID]);

            /* Set menu. */
            if($this->app->tab == 'project' or $this->app->tab == 'execution')
            {
                $this->loadModel('execution');
                if($this->app->tab == 'project') $this->loadModel('project')->setMenu($case->project);
                if($this->app->tab == 'execution')
                {
                    if(!$executionID) $executionID = $case->execution;
                    $this->execution->setMenu($executionID);
                }
            }
            if($this->app->tab == 'qa') $this->testcase->setMenu($this->products, $productID, $case->branch);

            $moduleOptionMenu = $this->tree->getOptionMenu($productID, $viewType = 'case', $startModuleID = 0, $case->branch);
            if($case->lib and $case->fromCaseID)
            {
                $libName    = $this->loadModel('caselib')->getById($case->lib)->name;
                $libModules = $this->tree->getOptionMenu($case->lib, 'caselib');
                foreach($libModules as $moduleID => $moduleName)
                {
                    if($moduleID == 0) continue;
                    $moduleOptionMenu[$moduleID] = $libName . $moduleName;
                }
            }

            if(!isset($moduleOptionMenu[$case->module])) $moduleOptionMenu += $this->tree->getModulesName($case->module);

            /* Get product and branches. */
            if($this->app->tab == 'execution' or $this->app->tab == 'project')
            {
                $objectID = $this->app->tab == 'project' ? $case->project : $executionID;
            }

            /* Display status of branch. */
            $branches = $this->loadModel('branch')->getList($productID, isset($objectID) ? $objectID : 0, 'all');
            $branchTagOption = array();
            foreach($branches as $branchInfo)
            {
                $branchTagOption[$branchInfo->id] = $branchInfo->name . ($branchInfo->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : '');
            }
            if(!isset($branchTagOption[$case->branch]))
            {
                $caseBranch = $this->branch->getById($case->branch, $case->product, '');
                $branchTagOption[$case->branch] = $case->branch == BRANCH_MAIN ? $caseBranch : ($caseBranch->name . ($caseBranch->status == 'closed' ? ' (' . $this->lang->branch->statusList['closed'] . ')' : ''));
            }

            $moduleIdList = $case->module;
            if($case->module) $moduleIdList = $this->tree->getAllChildID($case->module);

            $storyStatus = $this->story->getStatusList('noclosed');
            if($this->app->tab == 'execution')
            {
                $stories = $this->story->getExecutionStoryPairs($case->execution, $productID, $case->branch, $moduleIdList);
            }
            else
            {
                $stories = $this->story->getProductStoryPairs($productID, $case->branch, $moduleIdList, $storyStatus,'id_desc', 0, 'full', 'story', false);
            }
            /* Logic of task 44139. */
            if(!in_array($this->app->tab, array('execution', 'project')) and empty($stories))
            {
                $stories = $this->story->getProductStoryPairs($case->product, $case->branch, 0, $storyStatus, 'id_desc', 0, 'full', 'story', false);
            }

            $this->view->productID        = $productID;
            $this->view->product          = $product;
            $this->view->products         = $this->products;
            $this->view->branchTagOption  = $branchTagOption;
            $this->view->productName      = $this->products[$productID];
            $this->view->moduleOptionMenu = $moduleOptionMenu;
            $this->view->stories          = array('' => '') + $stories;
        }

        $sceneOptionMenu = $this->testcase->getSceneMenu($productID, $case->module, $viewType = 'case', $startSceneID = 0,  0 );
        if(!isset($sceneOptionMenu[$case->scene])) $sceneOptionMenu += $this->testcase->getScenesName($case->scene);

        $forceNotReview = $this->testcase->forceNotReview();
        if($forceNotReview) unset($this->lang->testcase->statusList['wait']);

        $this->view->title           = $title;
        $this->view->currentModuleID = $case->module;
        $this->view->users           = $this->user->getPairs('noletter');
        $this->view->case            = $case;
        $this->view->actions         = $this->loadModel('action')->getList('case', $caseID);
        $this->view->isLibCase       = $isLibCase;
        $this->view->forceNotReview  = $forceNotReview;
        $this->view->testtasks       = $testtasks;
        $this->view->sceneOptionMenu = $sceneOptionMenu;
        $this->view->currentSceneID  = $case->scene;

        $this->display();
    }
}