<?php
/**
 * The control file of marketresearch module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Hu Fangzhou <hufangzhou@easycorp.ltd>
 * @package     marketresearch
 * @link        https://www.zentao.net
 */
class marketresearch extends control
{
    /**
     * Construct.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        global $lang;
        $this->loadModel('execution');
        $this->loadModel('market');
        $this->loadModel('task');

        $lang->execution->common = $this->lang->execution->stage;
        $lang->projectCommon     = $this->lang->marketresearch->common;
        $lang->executionCommon   = $this->lang->execution->stage;
    }

    /**
     * Create marketresearch.
     *
     * @access public
     * @return void
     */
    public function create($marketID = 0)
    {
        $this->loadModel('market')->setMenu($marketID);
        $this->app->loadLang('project');
        if($_POST)
        {
            $marketresearchID = $this->marketresearch->create();

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            if($marketresearchID) $this->loadModel('action')->create('marketresearch', $marketresearchID, 'created');

            $locateLink = $this->session->marketresearchList ? $this->session->marketresearchList : $this->inlink('all', "marketID=$marketID");
            if($marketID && $marketID != $_POST['market']) $locateLink = $this->inlink('browse', "marketID=$_POST[market]");

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = $locateLink;

            $this->send($response);
        }

        $this->view->title        = $this->lang->marketresearch->create;
        $this->view->users        = $this->loadModel('user')->getPairs('noletter|noclosed');
        $this->view->marketID     = $marketID;
        $this->view->marketList   = array('' => '') + $this->loadModel('market')->getPairs();
        $this->display();
    }

    /**
     * Edit a market research.
     *
     * $param  int    $researchID
     * @access public
     * @return void
     */
    public function edit($researchID = 0)
    {
        $oldResearch = $this->marketresearch->getById($researchID);
        $this->loadModel('market')->setMenu($oldResearch->market);
        $this->app->loadLang('project');
        $this->app->loadConfig('execution');
        if($_POST)
        {
            $changes = $this->marketresearch->update($oldResearch);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            if($changes)
            {
                $actionID = $this->loadModel('action')->create('marketresearch', $researchID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }

            $locateLink = $this->session->marketresearchList ? $this->session->marketresearchList : $this->inlink('stage', "researchID=$researchID");

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = $locateLink;

            $this->send($response);
        }

        $this->view->title        = $this->lang->marketresearch->create;
        $this->view->users        = $this->loadModel('user')->getPairs('noletter|noclosed');
        $this->view->researchID   = $researchID;
        $this->view->marketList   = array('' => '') + $this->market->getPairs();
        $this->view->research     = $oldResearch;
        $this->display();
    }

    /**
     * View a research.
     *
     * @param  int    $researchID
     * @access public
     * @return void
     */
    public function view($researchID = 0)
    {
        $this->app->loadLang('execution');
        $this->app->loadLang('marketreport');
        $this->session->set('teamList', $this->app->getURI(true), 'marketresearch');

        $research = $this->loadModel('project')->getById($researchID);

        $this->loadModel('market')->setMenu($research->market);

        if(empty($research))
        {
            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'code' => 404, 'message' => '404 Not found'));
            return print(js::error($this->lang->notFound) . js::locate($this->createLink('research', 'browse')));
        }

        /* Check exist extend fields. */
        $isExtended = false;
        if($this->config->edition != 'open')
        {
            $extend = $this->loadModel('workflowaction')->getByModuleAndAction('marketresearch', 'view');
            if(!empty($extend) and $extend->extensionType == 'extend') $isExtended = true;
        }

        $this->executeHooks($researchID);

        $this->view->title       = $this->lang->overview;
        $this->view->position    = $this->lang->overview;
        $this->view->researchID  = $researchID;
        $this->view->research    = $research;
        $this->view->reports     = $this->loadModel('marketreport')->getPairsByResearch($researchID);
        $this->view->actions     = $this->loadModel('action')->getList('marketresearch', $researchID);
        $this->view->users       = $this->loadModel('user')->getPairs('noletter');
        $this->view->teamMembers = $this->project->getTeamMembers($researchID);
        $this->view->workhour    = $this->project->getWorkhour($researchID);
        $this->view->dynamics    = $this->action->getDynamic('all', 'all', 'date_desc', 30, 'all', $researchID);
        $this->view->isExtended  = $isExtended;

        $this->display();
    }

    /**
     * All market researches.
     *
     * @param  int    $marketID
     * @param  string $browseType  all|doing|closed
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function all($marketID = 0, $browseType = 'all', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->loadModel('datatable');
        $this->loadModel('market');

        $browseType = strtolower($browseType);
        $market     = $this->market->getByID($marketID);

        $this->market->setMenu($marketID);

        if($this->app->rawMethod == 'browse')
        {
            $marketIndex = array_search('market', $this->config->marketresearch->datatable->defaultField);
            unset($this->config->marketresearch->datatable->defaultField[$marketIndex]);
            unset($this->config->marketresearch->datatable->fieldList['market']);
        }

        $this->session->set('marketresearchList', $this->app->getURI(true));
        $queryID = ($browseType == 'bysearch') ? (int)$param : 0;

        /* Refresh stats fields of projects. */
        $this->loadModel('program')->refreshStats();

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $involved = $this->cookie->involvedResearch ? $this->cookie->involvedResearch : 0;

        $this->view->title      = $marketID ? $market->name . '-' . $this->lang->marketresearch->browse : $this->lang->marketresearch->browse;
        $this->view->researches = $this->marketresearch->getList($marketID, $browseType, $orderBy, $involved, $pager);
        $this->view->marketID   = $marketID;
        $this->view->markets    = $this->market->getPairs();
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->browseType = $browseType;
        $this->view->orderBy    = $orderBy;
        $this->view->recTotal   = $recTotal;
        $this->view->recPerPage = $recPerPage;
        $this->view->pageID     = $pageID;
        $this->view->pager      = $pager;
        $this->display();
    }

    /**
     * Browse market's researches.
     *
     * @param  int    $marketID
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($marketID = 0, $browseType = 'all', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        echo $this->fetch('marketresearch', 'all', "marketID=$marketID&browseType=$browseType&param=$param&orderBy=$orderBy&recTotal=$recTotal&recPerPage=$recPerPage&pageID=$pageID");
    }

    /**
     * Start research.
     *
     * @param  int    $researchID
     * @access public
     * @return void
     */
    public function start($researchID)
    {
        $this->loadModel('action');
        $this->loadModel('project');
        $research = $this->project->getByID($researchID);

        if(!empty($_POST))
        {
            $changes = $this->project->start($researchID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->comment != '' or !empty($changes))
            {
                $actionID = $this->action->create('marketresearch', $researchID, 'Started', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            $this->executeHooks($researchID);
            return print(js::reload('parent.parent'));
        }

        $this->view->title      = $this->lang->marketresearch->start;
        $this->view->position[] = $this->lang->marketresearch->start;
        $this->view->research   = $research;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions    = $this->action->getList('marketresearch', $researchID);
        $this->display();
    }

    /**
     * Close a research.
     *
     * @param  int    $researchID
     * @access public
     * @return void
     */
    public function close($researchID)
    {
        $this->loadModel('action');

        if(!empty($_POST))
        {
            $changes = $this->marketresearch->close($researchID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->comment != '' or !empty($changes))
            {
                $actionID = $this->action->create('marketresearch', $researchID, 'Closed', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }
            $this->executeHooks($researchID);
            return print(js::reload('parent.parent'));
        }

        $this->view->title      = $this->lang->marketresearch->close;
        $this->view->position[] = $this->lang->marketresearch->close;
        $this->view->research   = $this->loadModel('project')->getByID($researchID);
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions    = $this->action->getList('marketresearch', $researchID);

        $this->display();
    }

    /**
     * Activate stage.
     *
     * @param  int    $stageID
     * @access public
     * @return void
     */
    public function activateStage($stageID)
    {
        $this->loadStageLang();
        $stage = $this->loadModel('execution')->getById($stageID);
        if(!empty($_POST))
        {
            $this->marketresearch->activateStage($stageID);
            if(dao::isError()) return print(js::error(dao::getError()));

            $this->loadModel('programplan')->computeProgress($stageID, 'activate');

            $this->executeHooks($stageID);
            return print(js::reload('parent.parent'));
        }

        $newBegin = date('Y-m-d');
        $dateDiff = helper::diffDate($newBegin, $stage->begin);
        $newEnd   = date('Y-m-d', strtotime($stage->end) + $dateDiff * 24 * 3600);

        $this->view->title      = $this->lang->marketresearch->activateStage;
        $this->view->position[] = html::a($this->createLink('marketresearch', 'stage', "stageID=$stageID"), $stage->name);
        $this->view->position[] = $this->lang->marketresearch->activateStage;
        $this->view->stage      = $stage;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions    = $this->loadModel('action')->getList('researchstage', $stageID);
        $this->view->newBegin   = $newBegin;
        $this->view->newEnd     = $newEnd;
        $this->display();
    }

    /**
     * Load stage lang.
     *
     * @access public
     * @return void
     */
    public function loadStageLang()
    {
        $this->lang->researchstage = new stdclass();
        $this->lang->researchstage->status     = $this->lang->marketresearch->status;
        $this->lang->researchstage->realEnd    = $this->lang->marketresearch->realEnd;
        $this->lang->researchstage->closedBy   = $this->lang->marketresearch->closedBy;
        $this->lang->researchstage->closedDate = $this->lang->marketresearch->closedDate;
    }

    /**
     * Close stage.
     *
     * @param  int    $stageID
     * @access public
     * @return void
     */
    public function closeStage($stageID)
    {
        $this->loadStageLang();
        $stage = $this->loadModel('execution')->getById($stageID);

        if(!empty($_POST))
        {
            $this->marketresearch->closeStage($stageID);
            if(dao::isError()) return print(js::error(dao::getError()));

            $this->executeHooks($stageID);
            return print(js::reload('parent.parent'));
        }

        $this->view->title      = $this->lang->marketresearch->closeStage;
        $this->view->stage      = $stage;
        $this->view->position[] = $this->lang->marketresearch->closeStage;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions    = $this->loadModel('action')->getList('researchstage', $stageID);
        $this->display();
    }

    /**
     * Delete research
     *
     * @param  int    $researchID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($researchID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            $research = $this->loadModel('project')->getByID($researchID);
            return print(js::confirm(sprintf($this->lang->marketresearch->confirmDelete, $research->name), $this->createLink('marketresearch', 'delete', "researchID=$researchID&confirm=yes"), ''));
        }
        $this->dao->update(TABLE_MARKETRESEARCH)->set('deleted')->eq('1')->where('id')->eq($researchID)->exec();
        $this->loadModel('action')->create('marketresearch', $researchID, 'deleted', '', ACTIONMODEL::CAN_UNDELETED);
        if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));
        $locateLink = $this->session->marketresearchList ? $this->session->marketresearchList : $this->inlink('browse', "marketID={$research->market}");
        return print(js::locate($locateLink, 'parent'));
    }

    /**
     * Activate research.
     *
     * @param  int    $researchID
     * @access public
     * @return void
     */
    public function activate($researchID)
    {
        $this->loadModel('action');
        $this->app->loadLang('execution');
        $research = $this->loadModel('project')->getByID($researchID);

        if(!empty($_POST))
        {
            if($_POST['readjustTime'] && $_POST['begin'] != '' && $_POST['end'] != '' && $_POST['begin'] > $_POST['end'])
            {
                return print(js::alert(sprintf($this->lang->marketresearch->cannotGe, $this->lang->marketresearch->begin, $_POST[begin], $this->lang->marketresearch->end, $_POST[end])));
            }
            $changes = $this->marketresearch->activate($researchID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->comment != '' or !empty($changes))
            {
                $actionID = $this->action->create('marketresearch', $researchID, 'Activated', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }
            $this->executeHooks($researchID);
            return print(js::reload('parent.parent'));
        }

        $newBegin = date('Y-m-d');
        $dateDiff = helper::diffDate($newBegin, $research->begin);
        $newEnd   = date('Y-m-d', strtotime($research->end) + $dateDiff * 24 * 3600);

        $this->view->title      = $this->lang->marketresearch->activate;
        $this->view->position[] = $this->lang->marketresearch->activate;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions    = $this->action->getList('marketresearch', $researchID);
        $this->view->newBegin   = $newBegin;
        $this->view->newEnd     = $newEnd;
        $this->view->research   = $research;

        $this->display();
    }

    /*
     * Stage list.
     *
     * @param int    $researchID
     * @param string $browseType
     * @param int    $param
     * @param string $orderBy
     * @param int    $recTotal
     * @param int    $recPerPage
     * @param int    $pageID
     * @access public
     * @return void
     */
    public function stage($researchID = 0, $browseType = 'unclosed', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 100, $pageID = 1)
    {
        $this->loadModel('task');
        $this->loadModel('execution');
        $this->app->loadLang('programplan');
        $this->session->set('researchBrowseType', $browseType);

        $browseType = strtolower($browseType);

        /* Refresh stats fields of projects. */
        $this->loadModel('program')->refreshStats();

        /* Get research info. */
        $research = $this->loadModel('project')->getByID($researchID);

        /* Set menu. */
        $this->market->setMenu($research->market);

        /* Set queryID. */
        $queryID = ($browseType == 'bysearch') ? (int)$param : 0;

        /* Build the search form. */
        $actionURL = $this->createLink('marketresearch', 'stage', "researchID=$researchID&browseType=bySearch&param=myQueryID");
        $this->marketresearch->buildTaskSearchForm($researchID, $queryID, $actionURL);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml' || $this->app->getViewType() == 'xhtml') $recPerPage = 10;
        $pager = new pager($recTotal, $recPerPage, $pageID);

        /* Build stage list structure. */
        $stageTasks    = array();
        $researchTasks = $this->marketresearch->getTasks($researchID, $browseType, $queryID, $orderBy);
        foreach($researchTasks as $task) $stageTasks[$task->execution][] = $task;
        $stageStats = $this->marketresearch->getStatData($researchID, $stageTasks, 'order_asc,status_asc', $pager);

        /* Set session. */
        $this->app->session->set('marketstageList', $this->app->getURI(true) . "#app={$this->app->tab}", 'market');

        /* Assign. */
        $this->view->title      = $this->lang->marketresearch->create;
        $this->view->researchID = $researchID;
        $this->view->research   = $research;
        $this->view->stageStats = $stageStats;
        $this->view->taskTotal  = $this->session->researchTaskTotal;
        $this->view->pager      = $pager;
        $this->view->recTotal   = $pager->recTotal;
        $this->view->recPerPage = $pager->recPerPage;
        $this->view->orderBy    = $orderBy;
        $this->view->browseType = $browseType;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|all');
        $this->view->param      = $param;
        $this->display();
    }

    /**
     * Setting stage.
     *
     * @param  int    $researchID
     * @param  int    $stageID
     * @param  string $executionType
     * @access public
     * @return void
     */
    public function createStage($researchID = 0, $stageID = 0, $executionType = 'stage')
    {
        /* Load module and lang. */
        $this->app->loadLang('project');
        $this->app->loadLang('stage');
        $this->loadModel('programplan');
        $this->loadModel('execution');
        $this->loadStageLang();

        $project     = $this->loadModel('project')->getById($researchID);
        $plans       = $this->programplan->getStage($stageID ? $stageID : $researchID, 0, 'parent', 'order_asc,status_asc');
        $programPlan = $this->project->getById($stageID, 'stage');

        $this->loadModel('market')->setMenu($project->market);

        if($_POST)
        {
            $this->programplan->create($researchID, 0, $stageID);
            if(dao::isError())
            {
                $errors = dao::getError();
                if(isset($errors['message']))  return $this->send(array('result' => 'fail', 'message' => $errors));
                if(!isset($errors['message'])) return $this->send(array('result' => 'fail', 'callback' => array('name' => 'addRowErrors', 'params' => array($errors))));
            }

            $locate = $this->createLink('marketresearch', 'stage', "researchID=$researchID");
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $locate));
        }

        /* Set visible and required fields. */
        $visibleFields  = array();
        $requiredFields = array();
        foreach(explode(',', $this->config->marketresearch->customCreateFields) as $field) $customFields[$field] = $this->lang->programplan->$field;
        $showFields = $this->config->marketresearch->custom->createFields;
        foreach(explode(',', $showFields) as $field)
        {
            if($field) $visibleFields[$field] = '';
        }

        foreach(explode(',', $this->config->programplan->create->requiredFields) as $field)
        {
            if($field)
            {
                $requiredFields[$field] = '';
                if(strpos(",{$this->config->programplan->customCreateFields},", ",{$field},") !== false) $visibleFields[$field] = '';
            }
        }

        /* Assign. */
        $this->view->title              = $this->lang->programplan->create . $this->lang->colon . $project->name;
        $this->view->project            = $project;
        $this->view->plans              = $plans;
        $this->view->stageID            = $stageID;
        $this->view->type               = 'lists';
        $this->view->executionType      = $executionType;
        $this->view->PMUsers            = $this->loadModel('user')->getPairs('noclosed|nodeleted|pmfirst',  $project->PM);
        $this->view->custom             = 'custom';
        $this->view->customFields       = $customFields;
        $this->view->showFields         = $showFields;
        $this->view->visibleFields      = $visibleFields;
        $this->view->requiredFields     = $requiredFields;
        $this->view->colspan            = count($visibleFields) + 3;
        $this->view->programPlan        = $programPlan;
        $this->view->enableOptionalAttr = (empty($programPlan) or (!empty($programPlan) and $programPlan->attribute == 'mix'));

        $this->display();
    }

    /**
     * Batch create stage.
     *
     * @param int     $researchID
     * @param int     $stageID
     * @access public
     * @return void
     */
    public function batchStage($researchID = 0, $stageID = 0)
    {
        echo $this->fetch('marketresearch', 'createStage', "researchID=$researchID&stageID=$stageID&executionType=stage");
    }

    /**
     * Delete stage.
     *
     * @param  int    $stageID
     * @access public
     * @return void
     */
    public function deleteStage($stageID = 0, $confirm = 'no')
    {
        $this->loadStageLang();
        $this->loadModel('execution');
        $this->loadModel('project');
        $execution = $this->execution->getByID($stageID);
        if($confirm == 'no')
        {
            /* Get the number of unfinished tasks and unresolved bugs. */
            $unfinishedTasks = $this->dao->select('COUNT(id) AS count')->from(TABLE_TASK)
                ->where('execution')->eq($stageID)
                ->andWhere('deleted')->eq(0)
                ->andWhere('status')->in('wait,doing,pause')
                ->fetch();

            /* Set prompt information. */
            $tips = '';
            if($unfinishedTasks->count) $tips  = sprintf($this->lang->execution->unfinishedTask, $unfinishedTasks->count);
            if($tips)                   $tips  = $this->lang->execution->unfinishedExecution . $tips;

            if($tips) $tips = str_replace($this->lang->executionCommon, $this->lang->project->stage, $tips);
            $this->lang->execution->confirmDelete = str_replace($this->lang->executionCommon, $this->lang->project->stage, $this->lang->execution->confirmDelete);

            return print(js::confirm($tips . sprintf($this->lang->marketresearch->stageConfirmDelete, $execution->name), $this->createLink('marketresearch', 'deletestage', "stageID=$stageID&confirm=yes")));
        }
        else
        {
            /* Delete execution. */
            $this->dao->update(TABLE_EXECUTION)->set('deleted')->eq(1)->where('id')->eq($stageID)->exec();
            $this->loadModel('action')->create('researchstage', $stageID, 'deleted', '', ACTIONMODEL::CAN_UNDELETED);
            $this->loadModel('user')->updateUserView($stageID, 'sprint');
            $this->loadModel('common')->syncPPEStatus($executionID);

            $project = $this->project->getById($execution->project);
            $this->loadModel('programplan')->computeProgress($stageID);

            $message = $this->executeHooks($stageID);
            if($message) $this->lang->saveSuccess = $message;

            if($this->viewType == 'json') return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess));
            return print(js::reload('parent'));
        }
    }

    /**
     * Edit stage.
     *
     * @param int $stageID
     * @access public
     * @return void
     */
    public function editStage($stageID = 0, $projectID = 0)
    {
        echo $this->fetch('programplan', 'edit', "stageID=$stageID&projectID=$projectID");
    }

    /**
     * Start stage.
     *
     * @param  int    $stageID
     * @access public
     * @return void
     */
    public function startStage($stageID = 0)
    {
        echo $this->fetch('execution', 'start', "stageID=$stageID");
    }

    /**
     * Create research task.
     *
     * @param  int    $researchID
     * @param  int    $stageID
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function createTask($researchID = 0, $stageID = 0, $taskID = 0)
    {
        $marketID = $this->market->getIdByResearch($researchID);
        $this->market->setMenu($marketID);

        $task = new stdClass();
        $task->assignedTo = '';
        $task->name       = '';
        $task->type       = '';
        $task->pri        = '3';
        $task->estimate   = '';
        $task->desc       = '';
        $task->estStarted = '';
        $task->deadline   = '';
        $task->mailto     = '';
        $task->color      = '';
        if($taskID > 0) $task = $this->task->getByID($taskID);

        if(!empty($_POST))
        {
            $response['result'] = 'success';
            $_POST['project']   = $researchID;

            /* Create task here. */
            $tasksID = $this->task->create($_POST['execution'], 0);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                return $this->send($response);
            }

            /* Create actions. */
            $this->loadModel('action');
            foreach($tasksID as $taskID)
            {
                /* if status is exists then this task has exists not new create. */
                if($taskID['status'] == 'exists') continue;

                $taskID = $taskID['id'];
                $this->action->create('task', $taskID, 'Opened', '');
            }

            if($this->post->after == 'continueAdding')
            {
                $response['message'] = $this->lang->task->successSaved . $this->lang->marketresearch->task->afterChoices['continueAdding'];
                $response['locate']  = $this->createLink('marketresearch', 'createTask', "researchID=$researchID&stageID=$stageID");
                return $this->send($response);
            }
            elseif($this->post->after == 'toTaskList')
            {
                $response['message'] = $this->lang->saveSuccess;
                $response['locate']  = $this->createLink('marketresearch', 'stage', "researchID=$researchID");
                return $this->send($response);
            }
        }

        $this->view->title   = $this->lang->marketresearch->createTask;
        $this->view->stageID = $stageID;
        $this->view->taskID  = $taskID;
        $this->view->task    = $task;
        $this->view->members = $this->loadModel('user')->getTeamMemberPairs($researchID, 'project', 'nodeleted');
        $this->view->stages  = $this->execution->getByProject($researchID, 'all', 0, true);
        $this->view->users   = $this->loadModel('user')->getPairs('noclosed|nodeleted');

        $this->display();
    }

    /**
     * Batch create task.
     *
     * @param int    $executionID
     * @param int    $taskID
     * @param string $extra
     *
     * @access public
     * @return void
     */
    public function batchCreateTask($executionID = 0, $taskID = 0, $extra = '')
    {
        $execution = $this->loadModel('execution')->getById($executionID);
        $marketID  = $this->market->getIdByResearch($execution->project);
        $taskLink  = $this->createLink('marketresearch', 'stage', "executionID=$execution->project");

        $this->market->setMenu($marketID);

        /* When common task are child tasks, query whether common task are consumed. */
        $taskConsumed = 0;
        if($taskID) $taskConsumed = $this->dao->select('consumed')->from(TABLE_TASK)->where('id')->eq($taskID)->andWhere('parent')->eq(0)->fetch('consumed');

        if(!empty($_POST))
        {
            $mails = $this->task->batchCreate($executionID, $extra);
            if(dao::isError()) return print(js::error(dao::getError()));

            $taskIDList = array();
            foreach($mails as $mail) $taskIDList[] = $mail->taskID;

            /* Return task id list when call the API. */
            if($this->viewType == 'json' or (defined('RUN_MODE') && RUN_MODE == 'api')) return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'idList' => $taskIDList));

            /* If link from no head then reload. */
            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($taskLink, 'parent'));
        }

        /* Set Custom*/
        foreach(explode(',', $this->config->task->customBatchCreateFields) as $field)
        {
            if($execution->type == 'stage' and strpos('estStarted,deadline', $field) !== false) continue;
            $customFields[$field] = $this->lang->task->$field;
        }

        $showFields = $this->config->marketresearch->task->batchCreateFields;
        $title      = $execution->name . $this->lang->colon . $this->lang->task->batchCreate;
        $members    = $this->loadModel('user')->getTeamMemberPairs($executionID, 'execution', 'nodeleted');

        if($taskID) $this->view->parentTitle = $this->dao->select('name')->from(TABLE_TASK)->where('id')->eq($taskID)->fetch('name');
        if($taskID) $this->view->parentPri   = $this->dao->select('pri')->from(TABLE_TASK)->where('id')->eq($taskID)->fetch('pri');

        $this->view->title        = $title;
        $this->view->execution    = $execution;
        $this->view->customFields = $customFields;
        $this->view->showFields   = $showFields;
        $this->view->parent       = $taskID;
        $this->view->members      = $members;
        $this->view->taskConsumed = $taskConsumed;
        $this->display();
    }

    /**
     * Edit a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function editTask($taskID)
    {
        $this->loadModel('task');
        $this->loadModel('execution');
        $task     = $this->task->getById($taskID);
        $stage    = $this->execution->getById($task->execution);
        $marketID = $this->market->getIdByResearch($stage->project);

        $this->market->setMenu($marketID);

        if(!empty($_POST))
        {
            $_POST['team'] = $task->team;
            $this->loadModel('action');

            $changes = $this->task->update($taskID);

            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->comment != '' or !empty($changes))
            {
                $action   = !empty($changes) ? 'Edited' : 'Commented';
                $actionID = $this->action->create('task', $taskID, $action, $this->post->comment);
                if(!empty($changes)) $this->action->logHistory($actionID, $changes);
            }

            if(defined('RUN_MODE') && RUN_MODE == 'api')
            {
                return $this->send(array('status' => 'success', 'data' => $taskID));
            }
            else
            {
                return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $this->createLink('marketresearch', 'stage', "researchID={$task->project}")));
            }
        }

        $tasks = $this->task->getParentTaskPairs($task->execution, $task->parent);
        if(isset($tasks[$taskID])) unset($tasks[$taskID]);

        $this->view->task    = $task;
        $this->view->tasks   = $tasks;
        $this->view->title   = $this->lang->task->edit . 'TASK' . $this->lang->colon . $this->view->task->name;
        $this->view->stageID = $task->execution;
        $this->view->members = $this->loadModel('user')->getTeamMemberPairs($task->project, 'project', 'nodeleted');
        $this->view->stages  = $this->execution->getByProject($task->project, 'all', 0, true);
        $this->view->users   = $this->loadModel('user')->getPairs('noclosed|nodeleted');

        $this->display();
    }

    public function viewTask($taskID)
    {
        $taskID = (int)$taskID;
        $task   = $this->task->getById($taskID, true);
        $this->loadModel('execution');
        $execution = $this->execution->getById($task->execution);
        $marketID  = $this->market->getIdByResearch($execution->project);

        $this->market->setMenu($marketID);
        $this->session->set('executionList', $this->app->getURI(true), 'execution');

        /* Update action. */
        if($task->assignedTo == $this->app->user->account) $this->loadModel('action')->read('task', $taskID);

        $title = "TASK#$task->id $task->name / $execution->name";

        $this->view->title        = $title;
        $this->view->execution    = $execution;
        $this->view->task         = $task;
        $this->view->actions      = $this->loadModel('action')->getList('task', $taskID);
        $this->view->users        = $this->loadModel('user')->getPairs('noletter');
        $this->display();
    }

    /**
     * Start a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function startTask($taskID)
    {
        echo $this->fetch('task', 'start', "taskID=$taskID");
    }

    /**
     * Finish a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function finishtask($taskID)
    {
        echo $this->fetch('task', 'finish', "taskID=$taskID");
    }

    /**
     * Delete a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function deleteTask($executionID, $taskID, $confirm = 'no', $from = 'market')
    {
        if($confirm == 'no') return print(js::confirm($this->lang->task->confirmDelete, inlink('deleteTask', "executionID=$executionID&taskID=$taskID&confirm=yes&from=$from")));
        echo $this->fetch('task', 'delete', "executionID=$executionID&taskID=$taskID&confirm=$confirm&from=$from");
    }

    /**
     * Close a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function closetask($taskID)
    {
        echo $this->fetch('task', 'close', "taskID=$taskID");
    }

    /**
     * Cancel a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function cancelTask($taskID)
    {
        echo $this->fetch('task', 'cancel', "taskID=$taskID");
    }

    /**
     * Activate a task.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function activatetask($taskID)
    {
        echo $this->fetch('task', 'activate', "taskID=$taskID");
    }

    /**
     * Record consumed and estimate.
     *
     * @param  int    $taskID
     * @param  string $from
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function recordTaskEstimate($taskID, $from = '', $orderBy = '')
    {
        echo $this->fetch('task', 'recordEstimate', "taskID=$taskID&from=$from&orderBy=$orderBy");
    }

    /**
     * Update assign of task.
     *
     * @param  int    $executionID
     * @param  int    $taskID
     * @param  string $kanbanGroup
     * @param  string $from
     * @access public
     * @return void
     */
    public function taskAssignTo($executionID, $taskID)
    {
        echo $this->fetch('task', 'assignTo', "executionID=$executionID&taskID=$taskID");
    }

    /**
     * Browse team of a research.
     *
     * @param  int    $researchID
     * @access public
     * @return void
     */
    public function team($researchID = 0)
    {
        $this->loadModel('market');
        $this->loadModel('project');
        $this->app->loadLang('execution');

        $research = $this->marketresearch->getById($researchID);

        $this->market->setMenu($research->market);

        $deptID = $this->app->user->admin ? 0 : $this->app->user->dept;

        $this->view->title       = $research->name . $this->lang->colon . $this->lang->project->team;
        $this->view->researchID  = $researchID;
        $this->view->teamMembers = $this->project->getTeamMembers($researchID);
        $this->view->deptUsers   = $this->loadModel('dept')->getDeptUserPairs($deptID, 'id');

        $this->display();
    }

    /**
     * Manage market research members.
     *
     * @param  int    $researchID
     * @param  int    $dept
     * @param  int    $copyResearchID
     * @access public
     * @return void
     */
    public function manageMembers($researchID, $dept = '', $copyResearchID = 0)
    {
        /* Load model. */
        $this->loadModel('user');
        $this->loadModel('dept');
        $this->loadModel('project');
        $this->app->loadLang('execution');
        $this->app->loadConfig('execution');

        $research = $this->marketresearch->getById($researchID);
        $this->market->setMenu($research->market);

        if(!empty($_POST))
        {
            $this->project->manageMembers($researchID);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action')->create('team', $researchID, 'ManagedTeam');

            return $this->send(array('message' => $this->lang->saveSuccess, 'result' => 'success', 'locate' => $this->createLink('marketresearch', 'team', "researchID=$researchID")));
        }

        $users        = $this->user->getPairs('noclosed|nodeleted|devfirst');
        $roles        = $this->user->getUserRoles(array_keys($users));
        $deptUsers    = $dept === '' ? array() : $this->dept->getDeptUserPairs($dept);
        $userInfoList = $this->user->getUserDisplayInfos(array_keys($users), $dept);

        $currentMembers = $this->project->getTeamMembers($researchID);
        $members2Import = $this->project->getMembers2Import($copyResearchID, array_keys($currentMembers));

        $this->view->title          = $this->lang->project->manageMembers . $this->lang->colon . $research->name;
        $this->view->research       = $research;
        $this->view->users          = $users;
        $this->view->deptUsers      = $deptUsers;
        $this->view->userInfoList   = $userInfoList;
        $this->view->roles          = $roles;
        $this->view->dept           = $dept;
        $this->view->depts          = array('' => '') + $this->dept->getOptionMenu();
        $this->view->currentMembers = $currentMembers;
        $this->view->members2Import = $members2Import;
        $this->view->teams2Import   = array('' => '') + $this->loadModel('personnel')->getCopiedObjects($researchID, 'project', true);
        $this->view->copyResearchID = $copyResearchID;
        $this->display();
    }

    /**
     * Remove member from research.
     *
     * @param  int    $researchID
     * @param  int    $userID
     * @param  string $confirm     yse|no
     * @access public
     * @return void
     */
    public function unlinkMember($researchID, $userID, $confirm = 'no')
    {
        echo $this->fetch('project', 'unlinkMember', "researchID=$researchID&userID=$userID&confirm=$confirm");
    }

    /**
     * Browse research reports.
     *
     * @param  int    $researchID
     * @access public
     * @return void
     */
    public function reports($researchID = 0)
    {
        if($researchID) $research = $this->marketresearch->getByID($researchID);
        $marketID     = $researchID ? $research->market : 0;
        $reportsCount = $this->loadModel('marketreport')->countReports($researchID);

        $this->lang->market->homeMenu->report['subModule'] = 'marketresearch';
        $this->lang->market->menu->report['subModule']     = 'marketresearch';

        if($reportsCount)
        {
            echo $this->fetch('marketreport', 'browse', "marketID=$marketID&browseType=published&orderBy=id_desc&recTotal=0&recPerPage=20&pageID=1");
        }
        else
        {
            echo $this->fetch('marketreport', 'create', "marketID=$marketID");
        }
    }
}
