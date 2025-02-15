<?php
/**
 * The control file of risk module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yuchun Li <liyuchun@cnezsoft.com>
 * @package     risk
 * @version     $Id: control.php 5107 2020-09-04 09:06:12Z lyc $
 * @link        http://www.zentao.net
 */
class risk extends control
{
    public function commonAction($projectID, $from = 'project')
    {
        if($from == 'project' || $from == 'execution') $this->loadModel($from)->setMenu($projectID);
        if($from == 'execution')
        {
            $this->executions = $this->loadModel('execution')->getPairs(0, 'all', 'nocode');
            if(!$this->executions and $this->app->getViewType() != 'mhtml') $this->locate($this->createLink('execution', 'create'));
            $execution = $this->loadModel('execution')->getByID($projectID);
        }
    }

    /**
     * Browse risks.
     *
     * @param  int    $projectID
     * @param  string $browseType
     * @param  string $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($projectID = 0, $from = 'project', $browseType = 'all', $param = '', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->commonAction($projectID, $from);
        $uri = $this->app->getURI(true);
        $this->session->set('riskList', $uri, $this->app->tab);

        /* Build the search form. */
        $queryID   = ($browseType == 'bysearch') ? (int)$param : 0;
        $actionURL = $this->createLink('risk', 'browse', "projectID=$projectID&from=$from&browseType=bysearch&queryID=myQueryID");
        $this->risk->buildSearchForm($queryID, $actionURL);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $risks = $this->risk->getList($projectID, $browseType, $param, $orderBy, $pager);

        /* Process the sql, get the conditon partion, save it to session. */
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'risk');

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->browse;
        $this->view->position[] = $this->lang->risk->browse;
        $this->view->risks      = $risks;
        $this->view->browseType = $browseType;
        $this->view->param      = $param;
        $this->view->orderBy    = $orderBy;
        $this->view->projectID  = $projectID;
        $this->view->pager      = $pager;
        $this->view->from       = $from;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->approvers  = $this->loadModel('assetlib')->getApproveUsers('risk');
        $this->view->libs       = $this->assetlib->getPairs('risk');

        $this->display();
    }

    /**
     * Create a risk.
     *
     * @param  int  $projectID
     * @access public
     * @return void
     */
    public function create($projectID = 0, $from = 'project')
    {
        $this->commonAction($projectID, $from);

        if($from == 'execution')
        {
            $execution = $this->loadModel('execution')->getByID($projectID);
            $projectID = $execution->project;
            $this->view->executionID = $execution->id;
        }

        if($_POST)
        {
            $riskID = $this->risk->create($projectID);

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            if(!$riskID)
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                return $this->send($response);
            }

            $this->loadModel('action')->create('risk', $riskID, 'Opened');
            $response['locate'] = $this->session->riskList;
            $response['id']     = $riskID;
            return $this->send($response);
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->create;
        $this->view->position[] = $this->lang->risk->create;
        $this->view->executions = array(0 => '') + $this->loadModel('execution')->getPairs($projectID, 'all', 'leaf');
        $this->view->projectID  = $projectID;
        $this->view->users      = $this->loadModel('project')->getTeamMemberPairs($projectID);
        $this->view->issues     = $this->loadModel('issue')->getProjectIssuePairs($projectID);

        $this->display();
    }

    /**
     * Edit a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function edit($riskID, $from = 'project')
    {
        $risk = $this->risk->getById($riskID);
        if($from == 'project') $this->commonAction($risk->project, $from);
        if($from == 'execution') $this->commonAction($risk->execution, $from);

        if($_POST)
        {
            $changes = $this->risk->update($riskID);

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            if(dao::isError())
            {
                if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => dao::getError()));
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                return $this->send($response);
            }

            $this->loadModel('action');
            if(!empty($changes))
            {
                $actionID = $this->action->create('risk', $riskID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }

            $response['locate'] = inlink('view', "riskID=$riskID&from=$from");
            return $this->send($response);
        }

        $this->view->title       = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->edit;
        $this->view->projectList = $this->loadModel('project')->getPairsByModel('all', 0, 'multiple');
        $this->view->risk        = $risk;
        $this->view->users       = $this->loadModel('project')->getTeamMemberPairs($risk->project);
        $this->view->issues      = $this->loadModel('issue')->getProjectIssuePairs($risk->project, true, $risk->issues);
        $this->view->executions  = array(0 => '') + $this->loadModel('execution')->getPairs($risk->project, 'all', 'leaf');

        $this->display();
    }

    /**
     * View a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function view($riskID, $from = 'project')
    {
        $riskID = (int)$riskID;
        $risk   = $this->risk->getById($riskID);
        if(empty($risk))
        {
            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => '404 Not found'));
            die(js::error($this->lang->notFound) . js::locate($this->createLink('project', 'browse')));
        }
        if($from == 'project') $this->commonAction($risk->project, $from);
        if($from == 'execution') $this->commonAction($risk->execution, $from);

        $linkedIssues = array();
        if($risk->issues) $linkedIssues = $this->loadModel('issue')->getByList($risk->issues);

        $this->view->title        = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->view;
        $this->view->risk         = $risk;
        $this->view->from         = $from;
        $this->view->actions      = $this->loadModel('action')->getList('risk', $riskID);
        $this->view->users        = $this->loadModel('user')->getPairs('noletter');
        $this->view->approvers    = $this->loadModel('assetlib')->getApproveUsers('risk');
        $this->view->libs         = $this->assetlib->getPairs('risk');
        $this->view->execution    = $this->loadModel('execution')->getById($risk->execution);
        $this->view->preAndNext   = $this->loadModel('common')->getPreAndNextObject('risk', $riskID);
        $this->view->linkedIssues = $linkedIssues;

        $this->display();
    }

    /**
     * Batch create risks.
     *
     * @param  int  $projectID
     * @access public
     * @return void
     */
    public function batchCreate($projectID = 0, $from = 'project')
    {
        $this->commonAction($projectID, $from);

        if($from == 'execution')
        {
            $execution = $this->loadModel('execution')->getByID($projectID);
            $projectID = $execution->project;
            $this->view->executionID = $execution->id;
        }

        if($_POST)
        {
            $this->risk->batchCreate($projectID);

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                return $this->send($response);
            }

            $response['locate'] = $this->session->riskList;
            return $this->send($response);
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->batchCreate;
        $this->view->position[] = $this->lang->risk->batchCreate;
        $this->view->executions = array(0 => '') + $this->loadModel('execution')->getPairs($projectID, 'all', 'leaf');
        $this->view->users      = $this->loadModel('project')->getTeamMemberPairs($projectID);

        $this->display();
    }

    /**
     * Import from library.
     *
     * @param  int    $objectID
     * @param  int    $libID
     * @param  string $orderBy
     * @param  string $browseType
     * @param  int    $queryID
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function importFromLib($objectID, $from = 'project', $libID = 0, $orderBy = 'id_desc', $browseType = 'all', $queryID = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->commonAction($objectID, $from);
        $browseType = strtolower($browseType);
        $queryID    = (int)$queryID;

        $projectID = $objectID;

        if($from == 'execution')
        {
            $execution = $this->loadModel('execution')->getByID($objectID);
            $projectID = $execution->project;
        }

        $executionID = isset($execution) ? $execution->id : 0;

        if($_POST)
        {
            $this->risk->importFromLib($projectID, $executionID);
            die(js::reload('parent'));
        }

        $libraries = $this->loadModel('assetlib')->getPairs('risk');
        if(empty($libraries))
        {
            echo js::alert($this->lang->assetlib->noLibrary);
            die(js::locate($this->session->riskList));
        }
        if(empty($libID) or !isset($libraries[$libID])) $libID = key($libraries);

        /* Build the search form. */
        $actionURL = $this->createLink('risk', 'importFromLib', "projectID=$objectID&from=$from&libID=$libID&orderBy=$orderBy&browseType=bysearch&queryID=myQueryID");
        $this->config->risk->search['module'] = 'importRisk';
        $this->config->risk->search['fields']['lib'] = $this->lang->assetlib->lib;
        $this->config->risk->search['params']['lib'] = array('operator' => '=', 'control' => 'select', 'values' => array('' => '', $libID => $libraries[$libID], 'all' => $this->lang->risk->allLib));
        $needUnsetFields = array('status','identifiedDate','resolution','plannedClosedDate','actualClosedDate','resolvedBy','activateBy','assignedTo','cancelBy','hangupBy','trackedBy');
        foreach($needUnsetFields as $fieldName) unset($this->config->risk->search['fields'][$fieldName]);
        $this->risk->buildSearchForm($queryID, $actionURL);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $risks = $this->risk->getNotImported($libraries, $libID, $projectID, $orderBy, $browseType, $queryID);
        $pager = pager::init(count($risks), $recPerPage, $pageID);
        $risks = array_chunk($risks, $pager->recPerPage);

        $this->view->title = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->importFromLib;

        $this->view->libraries  = $libraries;
        $this->view->libID      = $libID;
        $this->view->projectID  = $objectID;
        $this->view->risks      = empty($risks) ? $risks : $risks[$pageID - 1];
        $this->view->users      = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->pager      = $pager;
        $this->view->from       = $from;
        $this->view->orderBy    = $orderBy;
        $this->view->browseType = $browseType;
        $this->view->queryID    = $queryID;

        $this->display();
    }

    /**
     * Delete a risk.
     *
     * @param  int    $riskID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($riskID, $from = 'project',$confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->risk->confirmDelete, $this->createLink('risk', 'delete', "risk=$riskID&from=$from&confirm=yes"), ''));
        }
        else
        {
            $projectID = $this->dao->select('project')->from(TABLE_RISK)->where('id')->eq($riskID)->fetch('project');
            $this->risk->delete(TABLE_RISK, $riskID);

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));
            die(js::locate($this->session->riskList, 'parent'));
        }
    }

    /**
     * Track a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function track($riskID)
    {
        $risk = $this->risk->getById($riskID);
        $this->loadModel('project')->setMenu($risk->project);

        if($_POST)
        {
            $changes = array();
            if($this->post->isChange) $changes = $this->risk->track($riskID);

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                return $this->send($response);
            }

            $this->loadModel('action');
            if(!empty($changes) or $_POST['comment'])
            {
                $actionID = $this->action->create('risk', $riskID, 'Tracked', $_POST['comment']);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return $this->send(array('locate' => 'parent', 'message' => $this->lang->saveSuccess, 'result' => 'success'));
            return $this->send(array('locate' => inlink('browse', "projectID=$risk->project")));
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->track;
        $this->view->position[] = $this->lang->risk->track;

        $this->view->risk  = $risk;
        $this->view->users = $this->loadModel('user')->getPairs('noclosed');
        $this->display();
    }

    /**
     * Update assign of risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function assignTo($riskID)
    {
        $risk = $this->risk->getById($riskID);

        if($_POST)
        {
            $changes = $this->risk->assign($riskID);
            if(dao::isError()) die(js::error(dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('risk', $riskID, 'Assigned', $this->post->comment, $this->post->assignedTo);
                $this->action->logHistory($actionID, $changes);
            }

            die(js::locate($this->session->riskList, 'parent.parent'));
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->assignedTo;
        $this->view->position[] = $this->lang->risk->assignedTo;

        $this->view->risk  = $risk;
        $this->view->users = $this->loadModel('project')->getTeamMemberPairs($risk->project);
        $this->display();
    }


    /**
     * Cancel a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function cancel($riskID)
    {
        $risk = $this->risk->getById($riskID);

        if($_POST)
        {
            $changes = $this->risk->cancel($riskID);
            if(dao::isError()) die(js::error(dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('risk', $riskID, 'Canceled', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::locate($this->createLink('risk', 'browse', "projectID=$risk->project"), 'parent'));
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->cancel;
        $this->view->position[] = $this->lang->risk->cancel;

        $this->view->users = $this->loadModel('project')->getTeamMemberPairs($risk->project);
        $this->view->risk  = $risk;
        $this->display();
    }

    /**
     * Close a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function close($riskID)
    {
        $risk = $this->risk->getById($riskID);

        if($_POST)
        {
            $changes = $this->risk->close($riskID);
            if(dao::isError()) die(js::error(dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('risk', $riskID, 'Closed', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::locate($this->createLink('risk', 'browse', "projectID=$risk->project"), 'parent'));
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->close;
        $this->view->position[] = $this->lang->risk->close;

        $this->view->users = $this->loadModel('project')->getTeamMemberPairs($risk->project);
        $this->view->risk  = $risk;
        $this->display();
    }

    /**
     * Hangup a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function hangup($riskID)
    {
        $risk = $this->risk->getById($riskID);

        if($_POST)
        {
            $changes = $this->risk->hangup($riskID);
            if(dao::isError()) die(js::error(dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('risk', $riskID, 'Hangup', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::closeModal('parent.parent', 'this'));
            die(js::locate($this->createLink('risk', 'browse', "projectID=$risk->project"), 'parent'));
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->hangup;
        $this->view->position[] = $this->lang->risk->hangup;

        $this->view->users = $this->loadModel('project')->getTeamMemberPairs($risk->project);
        $this->view->risk  = $risk;
        $this->display();
    }

    /**
     * Activate a risk.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function activate($riskID)
    {
        $risk = $this->risk->getById($riskID);

        if($_POST)
        {
            $changes = $this->risk->activate($riskID);
            if(dao::isError()) die(js::error(dao::getError()));

            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('risk', $riskID, 'Activated', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) die(js::reload('parent.parent'));;
            die(js::locate($this->createLink('risk', 'browse', "projectID=$risk->project"), 'parent'));
        }

        $this->view->title      = $this->lang->risk->common . $this->lang->colon . $this->lang->risk->activate;
        $this->view->position[] = $this->lang->risk->activate;

        $this->view->users = $this->loadModel('project')->getTeamMemberPairs($risk->project);
        $this->view->risk  = $risk;
        $this->display();
    }

    /**
     * Import risk to risk lib.
     *
     * @param  int    $riskID
     * @access public
     * @return void
     */
    public function importToLib($riskID)
    {
        $this->risk->importToLib($riskID);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success', 'message' => $this->lang->importSuccess, 'locate' => 'reload'));
    }

    /**
     * Batch import to lib.
     *
     * @access public
     * @return void
     */
    public function batchImportToLib()
    {
        $riskIDList = $this->post->riskIDList;
        $this->risk->importToLib($riskIDList);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success', 'message' => $this->lang->importSuccess, 'locate' => 'reload'));
    }

    /**
     * AJAX: return risks of a user in html select.
     *
     * @param  int    $userID
     * @param  string $id
     * @param  string $status
     * @access public
     * @return void
     */
    public function ajaxGetUserRisks($userID = '', $id = '', $status = 'all')
    {
        if($userID == '') $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;

        $risks = $this->risk->getUserRiskPairs($account, 0, $status);

        if($id) die(html::select("risks[$id]", $risks, '', 'class="form-control"'));
        die(html::select('risk', $risks, '', 'class=form-control'));
    }

    /**
     * Ajax get project risks.
     *
     * @param  int    $projectID
     * @param  string $append
     * @access public
     * @return void
     */
    public function ajaxGetProjectRisks($projectID, $append = '')
    {
        $risks = $this->risk->getProjectRiskPairs($projectID, $append);

        $selectHtml = html::select('risk', $risks, '', "class='form-control chosen'");
        return print($selectHtml);
    }

    /**
     * Export risk.
     *
     * @param  string $objectID
     * @param  string $browseType
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($objectID, $browseType, $orderBy)
    {
        if($_POST)
        {
            $this->loadModel('file');
            $riskLang = $this->lang->risk;

            /* Create field lists. */
            $sort   = common::appendOrder($orderBy);
            $fields = explode(',', $this->config->risk->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($riskLang->$fieldName) ? $riskLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            /* Get risks. */
            $risks = $this->dao->select('*')->from(TABLE_RISK)->where($this->session->riskQueryCondition)
                ->beginIF($this->post->exportType == 'selected')->andWhere('id')->in($this->cookie->checkedItem)->fi()
                ->orderBy($sort)->fetchAll('id');

            /* Get executions. */
            $executions = $this->dao->select('id, name')->from(TABLE_PROJECT)->where('id')->in(helper::arrayColumn($risks, 'execution'))
                ->fetchAll('id');

            /* Get users. */
            $users = $this->loadModel('user')->getPairs('noletter');

            $data = array();
            foreach($risks as $risk)
            {
                $tmp = new stdClass();
                $tmp->id                = $risk->id;
                $tmp->source            = zget($this->lang->risk->sourceList, $risk->source, '');
                $tmp->name              = $risk->name;
                $tmp->execution         = isset($executions[$risk->execution]) ? $executions[$risk->execution]->name . "(#{$risk->execution})" : '';
                $tmp->category          = zget($this->lang->risk->categoryList, $risk->category, '');
                $tmp->strategy          = zget($this->lang->risk->strategyList, $risk->strategy, '');
                $tmp->status            = zget($this->lang->risk->statusList, $risk->status, '');
                $tmp->impact            = $risk->impact;
                $tmp->probability       = $risk->probability;
                $tmp->rate              = $risk->rate;
                $tmp->pri               = zget($this->lang->risk->priList, $risk->pri, '');
                $tmp->identifiedDate    = $risk->identifiedDate;
                $tmp->plannedClosedDate = $risk->plannedClosedDate;
                $tmp->actualClosedDate  = $risk->actualClosedDate;
                $tmp->assignedTo        = isset($users[$risk->assignedTo]) ? $users[$risk->assignedTo] . "(#{$risk->assignedTo})" : '';

                if($this->post->fileType == 'csv')
                {
                    $risk->prevention = htmlspecialchars_decode($risk->prevention);
                    $risk->prevention = str_replace("<br />", "\n", $risk->prevention);
                    $tmp->prevention = str_replace('"', '""', $risk->prevention);

                    $risk->remedy = htmlspecialchars_decode($risk->remedy);
                    $risk->remedy = str_replace("<br />", "\n", $risk->remedy);
                    $tmp->remedy = str_replace('"', '""', $risk->remedy);

                    $risk->resolution = htmlspecialchars_decode($risk->resolution);
                    $risk->resolution = str_replace("<br />", "\n", $risk->resolution);
                    $tmp->resolution = str_replace('"', '""', $risk->resolution);
                }
                else
                {
                    $tmp->prevention        = $risk->prevention;
                    $tmp->remedy            = $risk->remedy;
                    $tmp->resolution        = $risk->resolution;
                }

                $data[] = $tmp;
            }

            $this->post->set('fields', $fields);
            $this->post->set('rows', $data);
            $this->post->set('kind', 'risk');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $object = $this->dao->select('id, name')->from(TABLE_PROJECT)->where('id')->eq($objectID)->fetch();
        $fileName = zget($object, 'name',  '') . $this->lang->dash . zget($this->lang->risk->featureBar['browse'], $browseType, '') . $this->lang->risk->common;

        $this->view->fileName = $fileName;
        $this->display();
    }
}
