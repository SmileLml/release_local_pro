<?php
/**
 * The control file of deploy of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     deploy
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class deploy extends control
{
    public function __construct($moduleName = '', $methodName = '')
    {
        parent::__construct($moduleName, $methodName);
    }

    /**
     * Browse deployments.
     *
     * @param  int    $product
     * @param  string $date
     * @access public
     * @return void
     */
    public function browse($product = 0, $date = '')
    {
        $this->session->set('deployList', $this->app->getURI(true));

        if(empty($date)) $date = date('Y-m');
        if(is_numeric($date)) $date = date('Y-m', strtotime($date . '01'));
        $plans = $this->deploy->getList($product, $date);
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'deploy', false);

        $this->app->loadClass('date');
        $today      = date('Y-m-d');
        $tomorrow   = date::tomorrow();
        $after2Days = date('Y-m-d', time() + 2 * 24 * 3600);
        $thisWeek   = date::getThisWeek();

        $begin = date('Y-m', strtotime($date)) . '-01 00:00:00';
        $end   = date('Y-m-d 23:59:59', strtotime("$begin +1 month -1 day"));
        $thisMonth  = array('begin' => $begin, 'end' => $end);

        $processedPlans = array();
        $processedPlans['done']      = array();
        $processedPlans['today']     = array();
        $processedPlans['tomorrow']  = array();
        $processedPlans['thisweek']  = array();
        $processedPlans['thismonth'] = array();
        foreach($plans as $plan)
        {
            if($plan->status == 'done')
            {
                $processedPlans['done'][$plan->id] = $plan;
            }
            else
            {
                if($plan->begin >= $today and $plan->begin < $tomorrow)
                {
                    $processedPlans['today'][$plan->id] = $plan;
                }
                elseif($plan->begin >= $tomorrow and $plan->begin < $after2Days)
                {
                    $processedPlans['tomorrow'][$plan->id] = $plan;
                }
                elseif($plan->begin >= $thisWeek['begin'] and $plan->begin < $thisWeek['end'])
                {
                    $processedPlans['thisweek'][$plan->id] = $plan;
                }
                elseif($plan->begin >= $thisMonth['begin'] and $plan->begin < $thisMonth['end'])
                {
                    $processedPlans['thismonth'][$plan->id] = $plan;
                }
            }
        }

        $this->view->title = $this->lang->deploy->browse;
        $this->view->position[] = html::a(inlink('browse', "product=$product"), $this->lang->deploy->common);


        $this->view->dateList  = $this->deploy->getHasDeployDate($product);
        $this->view->date      = $date;
        $this->view->product   = $product;
        $this->view->plans     = $processedPlans;
        $this->view->users     = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $this->display();
    }

    /**
     * Create deployment.
     *
     * @param  int    $product
     * @access public
     * @return void
     */
    public function create($product = 0)
    {
        if($_POST)
        {
            $deployID = $this->deploy->create();
            if(dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->loadModel('action')->create('deploy', $deployID, 'Created');
            $this->deploy->sendmail($deployID, $actionID);

            $isonlybody = isonlybody();
            $target     = $isonlybody ? 'parent.parent' : 'parent';
            if($isonlybody or helper::isAjaxRequest()) die(js::reload($target));
            die(js::locate($this->createLink('deploy', 'browse', "product=$product"), $target));
        }

        $this->view->title = $this->lang->deploy->create;
        $this->view->position[] = html::a(inlink('browse', "product=$product"), $this->lang->deploy->common);
        $this->view->position[] = $this->lang->deploy->create;

        $this->view->users     = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $this->view->product   = $product;
        $this->view->products  = array(0 => '') + $this->loadModel('product')->getPairs();
        $this->view->releases  = $product ? array(0 => '') + $this->loadModel('release')->getPairsByProduct($product) : array(0 => '');

        $this->display();
    }

    /**
     * Edit the deployment.
     *
     * @param  int   $deployID
     * @access public
     * @return void
     */
    public function edit($deployID, $comment = false)
    {
        if($_POST)
        {
            if(!$comment)
            {
                $changes = $this->deploy->update($deployID);
                if(dao::isError()) die(js::error(dao::getError()));
            }

            if(!empty($changes) or $this->post->comment)
            {
                $action   = !empty($changes) ? 'Edited' : 'Commented';
                $actionID = $this->loadModel('action')->create('deploy', $deployID, $action, $this->post->comment);
                $this->action->logHistory($actionID, $changes);
                $this->deploy->sendmail($deployID, $actionID);
            }

            $isonlybody = isonlybody();
            $target     = $isonlybody ? 'parent.parent' : 'parent';
            if($isonlybody) die(js::closeModal($target, 'this'));
            die(js::locate($this->createLink('deploy', 'browse', "product=$product"), $target));
        }

        $deploy = $this->deploy->getById($deployID);
        $productIdList = array();
        foreach($deploy->products as $deployProduct) $productIdList[$deployProduct->product] = $deployProduct->product;

        $releaseGroup = $this->dao->select('*')->from(TABLE_RELEASE)->where('product')->in($productIdList)->andWhere('deleted')->eq(0)->fetchGroup('product', 'id');
        foreach($releaseGroup as $product => $releases)
        {
            $releaseGroup[$product][0] = '';
            foreach($releases as $id => $release) $releaseGroup[$product][$id] = $release->name;
        }

        $this->view->title = $this->lang->deploy->edit;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->lang->deploy->edit;

        $this->view->users        = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed', $deploy->owner . ',' . $deploy->members);
        $this->view->deploy       = $deploy;
        $this->view->products     = array(0 => '') + $this->loadModel('product')->getPairs();
        $this->view->releaseGroup = $releaseGroup;

        $this->display();
    }

    /**
     * Delete the deployment.
     *
     * @param  int    $delployID
     * @param  string $confirm   yes|no
     * @access public
     * @return void
     */
    public function delete($deployID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->deploy->confirmDelete, $this->createLink('deploy', 'delete', "deployID=$deployID&confirm=yes")));
        }
        else
        {
            $this->deploy->delete(TABLE_DEPLOY, $deployID);

            $locateLink = $this->session->deployList ? $this->session->deployList : inlink('browse');
            die(js::locate($locateLink, 'parent'));
        }
    }

    /**
     * Activate the deployment.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     */
    public function activate($deployID)
    {
        if($_POST)
        {
            $changes = $this->deploy->update($deployID);
            if(dao::isError()) die(js::error(dao::getError()));

            $this->deploy->changeStatus($deployID, 'activate');
            if(dao::isError()) die(js::error(dao::getError()));

            $actionID = $this->loadModel('action')->create('deploy', $deployID, "activated", $this->post->comment);
            if($changes) $this->action->logHistory($actionID, $changes);
            $this->deploy->sendmail($deployID, $actionID);

            $isonlybody = isonlybody();
            $target     = $isonlybody ? 'parent.parent' : 'parent';
            if($isonlybody) die(js::closeModal($target, 'this'));
            die(js::locate($this->createLink('deploy', 'view', "deployID=$deployID"), $target));
        }

        $this->view->title = $this->lang->deploy->activate;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->lang->deploy->activate;

        $this->view->deploy = $this->deploy->getById($deployID);
        $this->display();
    }

    /**
     * Finish a deployment.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     *
     */
    public function finish($deployID)
    {
        $deploy = $this->deploy->getById($deployID);
        $scope  = $this->deploy->getScope($deployID);

        $serviceIdList = array();
        foreach($scope as $item) $serviceIdList[$item->service] = $item->service;
        $services = $this->loadModel('service')->getByIdList($serviceIdList);

        if($_POST)
        {
            if(empty($_POST['result'])) die(js::alert($this->lang->deploy->resultNotEmpty));
            $this->deploy->changeStatus($deployID, 'finish');
            if(dao::isError()) die(js::error(dao::getError()));

            if($this->post->result == 'success')
            {
                $this->loadModel('service')->updateVersion();
                if($this->post->updateHost)
                {
                    foreach($scope as $item)
                    {
                        if(!isset($_POST['updateHost'][$item->service])) continue;
                        if(!isset($services[$item->service])) continue;

                        $service = $services[$item->service];
                        $hosts   = trim($service->hosts, ',');
                        if(!empty($item->add))    $hosts = $this->deploy->updateHostAdd($hosts, $item->add);
                        if(!empty($item->remove)) $hosts = $this->deploy->updateHostRemove($hosts, $item->remove);

                        $this->dao->update(TABLE_SERVICE)->set('hosts')->eq($hosts)->where('id')->eq($item->service)->exec();
                    }
                }
            }

            $actionID = $this->loadModel('action')->create('deploy', $deployID, "finished", $this->post->comment);
            $this->deploy->sendmail($deployID, $actionID);

            $isonlybody = isonlybody();
            $target     = $isonlybody ? 'parent.parent' : 'parent';
            if($isonlybody) die(js::closeModal($target, 'this'));
            die(js::locate($this->createLink('deploy', 'view', "deployID=$deployID"), $target));
        }

        $this->view->title = $this->lang->deploy->finish;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->lang->deploy->finish;

        $this->view->deploy     = $deploy;
        $this->view->scope      = $scope;
        $this->view->optionMenu = $this->loadModel('service')->getOptionMenu('all');
        $this->view->services   = $services;
        $this->display();
    }

    /**
     * The scope of the deployment.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     */
    public function scope($deployID)
    {
        $deploy = $this->deploy->getById($deployID);
        $scope  = $this->deploy->getScope($deployID);

        $this->view->title = $this->lang->deploy->manageScope;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->lang->deploy->manageScope;

        $this->view->deploy     = $deploy;
        $this->view->scope      = $scope;
        $this->view->hosts      = $this->loadModel('host')->getPairs();
        $this->view->optionMenu = $this->loadModel('service')->getOptionMenu('all');
        $this->display();
    }

    /**
     * Manage scope.
     *
     * @param  int    $deploy
     * @access public
     * @return void
     */
    public function manageScope($deployID)
    {
        if($_POST)
        {
            $this->deploy->manageScope($deployID);
            die(js::locate($this->createLink('deploy', 'scope', "deployID=$deployID"), 'parent'));
        }

        $deploy = $this->deploy->getById($deployID);
        $scope  = $this->deploy->getScope($deployID);

        $services = array();
        foreach($scope as $item) $services[$item->service] = $item->service;

        $this->view->title = $this->lang->deploy->scope;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->lang->deploy->scope;

        $this->view->deploy     = $deploy;
        $this->view->scope      = $scope;
        $this->view->hosts      = array('' => '') + $this->loadModel('host')->getPairs();
        $this->view->services   = $this->loadModel('service')->getByIdList($services);
        $this->view->optionMenu = $this->service->getOptionMenu('all');
        $this->display();
    }

    /**
     * View the deployment.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     */
    public function view($deployID)
    {
        $deployID = (int)$deployID;
        $deploy   = $this->deploy->getById($deployID);
        if(!$deploy) die(js::error($this->lang->notFound) . js::locate($this->createLink('deploy', 'browse')));

        $this->session->set('stepList', $this->app->getURI(true));

        $productIdList = $releaseIdList = array();
        foreach($deploy->products as $deployProduct)
        {
            $productIdList[$deployProduct->product] = $deployProduct->product;
            $releaseIdList[$deployProduct->release] = $deployProduct->release;
        }

        $this->view->title = $this->lang->deploy->view;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->view->title;

        $this->view->deploy     = $deploy;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $this->view->products   = $this->loadModel('product')->getByIdList($productIdList);
        $this->view->releases   = $this->dao->select('*')->from(TABLE_RELEASE)->where('id')->in($releaseIdList)->fetchAll('id');
        $this->view->actions    = $this->loadModel('action')->getList('deploy', $deployID);
        $this->display();
    }

    /**
     * The cases of the deployment.
     *
     * @param int $deployID
     * @access public
     * @return void
     */
    public function cases($deployID)
    {
        $this->loadModel('testcase');
        $deploy = $this->deploy->getById($deployID);

        $this->session->set('caseList', $this->app->getURI(true), 'qa');

        $this->view->title = $this->lang->deploy->cases;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->view->title;

        $this->view->deploy = $deploy;
        $this->view->users  = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $this->view->cases  = $deploy->cases ? $this->testcase->getByList($deploy->cases) : array();
        $this->display();
    }

    /**
     * Link cases.
     *
     * @param  int    $deployID
     * @param  string $type
     * @param  int    $param
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function linkCases($deployID, $type = 'all', $param = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        if(!empty($_POST))
        {
            $this->deploy->linkCases($deployID);
            die(js::locate(inlink('cases', "deploy=$deployID"), 'parent'));
        }

        /* Save session. */
        $this->session->set('caseList', $this->app->getURI(true));

        /* Get task and product id. */
        $deploy  = $this->deploy->getById($deployID);
        $productIdList = array();
        foreach($deploy->products as $deployProduct) $productIdList[$deployProduct->product] = $deployProduct->product;

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Build the search form. */
        $this->loadModel('testcase');
        $this->app->loadLang('testtask');
        unset($this->config->testcase->search['fields']['product']);
        unset($this->config->testcase->search['params']['product']);
        unset($this->config->testcase->search['fields']['branch']);
        unset($this->config->testcase->search['params']['branch']);
        unset($this->config->testcase->search['fields']['module']);
        unset($this->config->testcase->search['params']['module']);
        $this->config->testcase->search['actionURL'] = inlink('linkCases', "deployID=$deployID");
        $this->loadModel('search')->setSearchParams($this->config->testcase->search);

        $this->view->title      = $deploy->name . $this->lang->colon . $this->lang->deploy->linkCases;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->view->title;

        /* Get cases. */
        $cases = $this->deploy->getLinkableCases($deploy, $productIdList, $type, $param, $pager);

        $suiteList = array();
        $this->loadModel('testsuite');
        foreach($productIdList as $productID) $suiteList += $this->testsuite->getSuites($productID);

        $this->view->users     = $this->loadModel('user')->getPairs('noletter');
        $this->view->cases     = $cases;
        $this->view->deploy    = $deploy;
        $this->view->pager     = $pager;
        $this->view->type      = $type;
        $this->view->param     = $param;
        $this->view->suiteList = $suiteList;

        $this->display();
    }

    /**
     * Unlink cases.
     *
     * @param  int    $deployID
     * @param  int    $caseID
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function unlinkCase($deployID, $caseID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            $this->app->loadLang('testtask');
            die(js::confirm($this->lang->testtask->confirmUnlinkCase, $this->createLink('deploy', 'unlinkCase', "deploy=$deployID&caseID=$caseID&confirm=yes")));
        }
        else
        {
            $deploy = $this->deploy->getById($deployID);
            $cases  = trim(str_replace(",$caseID,", ',', ",{$deploy->cases},"), ',');
            $this->dao->update(TABLE_DEPLOY)->set('cases')->eq($cases)->where('id')->eq((int)$deployID)->exec();
            die(js::reload('parent'));
        }
    }

    /**
     * Batch unlink cases.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     */
    public function batchUnlinkCases($deployID)
    {
        if(isset($_POST['idList']))
        {
            $deploy = $this->deploy->getById($deployID);
            $cases  = "," . trim($deploy->cases, ',') . ",";

            $deletedList = fixer::input('post')->get('idList');
            foreach($deletedList as $caseID) $cases = str_replace(",$caseID,", ',', $cases);
            $this->dao->update(TABLE_DEPLOY)->set('cases')->eq(trim($cases, ','))->where('id')->eq((int)$deployID)->exec();
        }

        die(js::reload('parent'));
    }

    /**
     * The steps of the deployment.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     */
    public function steps($deployID)
    {
        $this->session->set('stepList', $this->app->getURI(true));

        $deploy = $this->deploy->getById($deployID);
        $stepGroups  = $this->deploy->getStepStageGroup($deployID);
        if($deploy->cases)
        {
            $this->app->loadLang('testtask');
            $stepGroups['cases'] = $this->loadModel('testcase')->getByList($deploy->cases);
            $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'testcase');

            $this->view->results = $this->dao->select('*')->from(TABLE_TESTRESULT)->where('`case`')->in($deploy->cases)->andWhere('deploy')->eq($deployID)->orderBy('date')->fetchAll('case');
        }

        $this->view->title = $this->lang->deploy->steps;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->view->title;

        $this->view->deploy     = $deploy;
        $this->view->stepGroups = $stepGroups;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|noclosed|nodeleted');
        $this->display();
    }

    /**
     * Manage step.
     *
     * @param  int    $deployID
     * @param  string $stage
     * @access public
     * @return void
     */
    public function manageStep($deployID, $stage = 'all')
    {
        if($_POST)
        {
            $this->deploy->manageStep($deployID, $stage);
            die(js::locate($this->createLink('deploy', 'steps', "deployID=$deployID"), 'parent'));
        }

        unset($this->lang->deploy->stageList['testing']);

        $this->view->title = $this->lang->deploy->manageStep;
        $this->view->position[] = html::a($this->session->deployList, $this->lang->deploy->common);
        $this->view->position[] = $this->view->title;

        $this->view->deploy     = $this->deploy->getById($deployID);
        $this->view->stepGroups = $this->deploy->getStepStageGroup($deployID, $stage);
        $this->view->users      = $this->deploy->getMembes($deployID);

        $this->display();
    }

    /**
     * Finish step.
     *
     * @param  int    $deployID
     * @access public
     * @return void
     */
    public function finishStep($stepID)
    {
        $step = $this->deploy->getStepById($stepID);

        if($_POST)
        {
            $changes = $this->deploy->finishStep($stepID);
            if(dao::isError()) die(js::error(dao::getError()));

            if($changes or $this->post->comment)
            {
                $actionID = $this->loadModel('action')->create('deploystep', $stepID, "finished", $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::locate(inlink('steps', "deployID={$step->deploy}"), 'parent'));
        }

        $this->view->step  = $step;
        $this->view->users = $this->deploy->getMembes($step->deploy);
        $this->display();
    }

    /**
     * Update assign of step.
     *
     * @param  int    $stepID
     * @access public
     * @return void
     */
    public function assignTo($stepID)
    {
        $step = $this->deploy->getStepById($stepID);

        if($_POST)
        {
            $changes = $this->deploy->assignTo($stepID);
            if(dao::isError()) die(js::error(dao::getError()));

            if($changes or $this->post->comment)
            {
                $actionID = $this->loadModel('action')->create('deploystep', $stepID, "Assigned", $this->post->comment, $this->post->assignedTo);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::locate(inlink('steps', "deployID={$step->deploy}"), 'parent'));
        }

        $this->view->step  = $step;
        $this->view->users = $this->deploy->getMembes($step->deploy);
        $this->display();
    }

    /**
     * View the step.
     *
     * @param  int    $stepID
     * @access public
     * @return void
     */
    public function viewStep($stepID)
    {
        $stepID = (int)$stepID;
        $step   = $this->loadModel('deploy')->getStepById($stepID);
        if(empty($step)) die(js::error($this->lang->notFound) . js::locate($this->createLink('deploy', 'browse')));

        $this->view->title   = $step->title;
        $this->view->step    = $step;
        $this->view->users   = $this->loadModel('user')->getPairs('noletter|noclosed|nodeleted');
        $this->view->actions = $this->loadModel('action')->getList('deploystep', $stepID);
        $this->display();
    }

    /**
     * Edit the step.
     *
     * @param  int    $stepID
     * @access public
     * @return void
     */
    public function editStep($stepID)
    {
        $step = $this->deploy->getStepById($stepID);

        if($_POST)
        {
            $changes = $this->deploy->updateStep($stepID);
            if(dao::isError()) die(js::error(dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('deploystep', $stepID, 'edited');
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::locate(inlink('steps', "deployID={$step->deploy}"), 'parent'));
        }

        unset($this->lang->deploy->stageList['testing']);

        $this->view->step  = $step;
        $this->view->users = $this->deploy->getMembes($step->deploy);
        $this->display();
    }

    /**
     * Delete the step.
     *
     * @param  int    $stepID
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function deleteStep($stepID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->deploy->confirmDeleteStep, $this->createLink('deploy', 'deleteStep', "stepID=$stepID&confirm=yes")));
        }
        else
        {
            $this->deploy->delete(TABLE_DEPLOYSTEP, $stepID);
            die(js::reload('parent'));
        }
    }
}
