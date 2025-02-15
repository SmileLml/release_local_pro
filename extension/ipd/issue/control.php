<?php
/**
 * The control file of issue module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yong Lei <leiyong@easycorp.ltd>
 * @package     issue
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class issue extends control
{
    /**
     * Get issue list data.
     *
     * @param  int    $objectID projectID|executionID
     * @param  string $from project|execution
     * @param  string $browseType bySearch|open|assignTo|closed|suspended|canceled
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($objectID = 0, $from = 'project', $browseType = 'all', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->commonAction($objectID, $from);
        $uri = $this->app->getURI(true);
        $this->session->set('issueList', $uri, $this->app->tab);

        /* Load pager */
        $this->app->loadClass('pager', true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Build the search form. */
        $browseType = strtolower($browseType);
        $queryID    = ($browseType == 'bysearch') ? (int)$param : 0;
        $actionURL  = $this->createLink('issue', 'browse', "objectID=$objectID&from=$from&browseType=bysearch&queryID=myQueryID");
        $this->issue->buildSearchForm($actionURL, $queryID, $objectID);

        $issueList  = $this->issue->getList($objectID, $browseType, $queryID, $orderBy, $pager);

        /* Process the sql, get the conditon partion, save it to session. */
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'issue');

        $this->view->title      = $this->lang->issue->common . $this->lang->colon . $this->lang->issue->browse;
        $this->view->position[] = $this->lang->issue->browse;
        $this->view->pager      = $pager;
        $this->view->param      = $param;
        $this->view->orderBy    = $orderBy;
        $this->view->from       = $from;
        $this->view->browseType = $browseType;
        $this->view->projectID  = $objectID;
        $this->view->issueList  = $issueList;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|pofirst|nodeleted');
        $this->view->approvers  = $this->loadModel('assetlib')->getApproveUsers('issue');
        $this->view->libs       = $this->assetlib->getPairs('issue');

        $this->display();
    }

    /**
     * Create an issue.
     *
     * @param  int    $objectID
     * @param  string $from  issue|stakeholder
     * @param  string $owner
     * @access public
     * @return void
     */
    public function create($objectID = 0, $from = 'issue', $owner = '')
    {
        $this->commonAction($objectID, $from);
        $this->loadModel('project');

        if($from == 'execution')
        {
            $execution = $this->loadModel('execution')->getByID($objectID);
            $projectID = $execution->project;
            $this->view->executionID = $objectID;
        }
        else
        {
            $projectID = $objectID;
        }

        if($_POST)
        {
            $issueID = $this->issue->create($projectID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->loadModel('action')->create('issue', $issueID, 'Opened');
            $link = $this->session->issueList;
            if($from == 'stakeholder')
            {
                $stakeholderID = $this->dao->select('id')->from(TABLE_STAKEHOLDER)
                    ->where('objectType')->eq('project')
                    ->andwhere('objectID')->eq($projectID)
                    ->andwhere('user')->eq($owner)
                    ->fetch('id');

                $link = $this->createLink('stakeholder', 'userIssue', "userID=$stakeholderID&from=$from");
            }
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $link, 'id' => $issueID));
        }

        $this->view->title      = $this->lang->issue->common . $this->lang->colon . $this->lang->issue->create;

        $this->view->owners      = $this->loadModel('stakeholder')->getStakeholders4Issue();
        $this->view->teamMembers = $this->project->getTeamMemberPairs($objectID);
        $this->view->projectID   = $projectID;
        $this->view->from        = $from;
        $this->view->owner       = $owner;
        $this->view->executions  = array(0 => '') + $this->loadModel('execution')->getPairs($projectID, 'all', 'leaf');
        $this->view->risks       = $this->loadModel('risk')->getProjectRiskPairs($projectID);


        $this->display();
    }

    /**
     * Batch create issues.
     *
     * @param  int    $objectID
     * @access public
     * @return void
     */
    public function batchCreate($objectID = 0, $from = 'project')
    {
        $this->loadModel('project');
        $this->commonAction($objectID, $from);

        if($from == 'execution')
        {
            $execution = $this->loadModel('execution')->getByID($objectID);
            $projectID = $execution->project;
            $this->view->executionID = $objectID;
        }
        else
        {
            $projectID = $objectID;
        }

        if($_POST)
        {
            $issues = $this->issue->batchCreate($projectID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            foreach($issues as $issue) $this->loadModel('action')->create('issue', $issue, 'Opened');
            $link = $this->session->issueList;
            die(js::locate($link, 'parent'));
        }

        $this->view->title      = $this->lang->issue->common . $this->lang->colon . $this->lang->issue->batchCreate;
        $this->view->position[] = $this->lang->issue->common;
        $this->view->position[] = $this->lang->issue->batchCreate;

        $this->view->projectID   = $projectID;
        $this->view->teamMembers = $this->project->getTeamMemberPairs($objectID);
        $this->view->executions  = array(0 => '') + $this->loadModel('execution')->getPairs($projectID, 'all', 'leaf');

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

        if($from == 'execution')
        {
            $execution = $this->loadModel('execution')->getByID($objectID);
            $projectID = $execution->project;
        }
        else
        {
            $projectID = $objectID;
        }

        $executionID = isset($execution) ? $execution->id : 0;

        if($_POST)
        {
            $this->issue->importFromLib($projectID, $executionID);
            die(js::reload('parent'));
        }

        $libraries = $this->loadModel('assetlib')->getPairs('issue');
        if(empty($libraries))
        {
            echo js::alert($this->lang->issue->noIssueLib);
            die(js::locate($this->session->issueList));
        }
        if(empty($libID) or !isset($libraries[$libID])) $libID = key($libraries);

        /* Build the search form. */
        $actionURL = $this->createLink('issue', 'importFromLib', "projectID=$objectID&from=$from&libID=$libID&orderBy=$orderBy&browseType=bysearch&queryID=myQueryID");
        $this->config->issue->search['module'] = 'importIssue';
        $this->config->issue->search['fields']['lib'] = $this->lang->assetlib->lib;
        $this->config->issue->search['params']['lib'] = array('operator' => '=', 'control' => 'select', 'values' => array('' => '', $libID => $libraries[$libID], 'all' => $this->lang->issue->allLib));
        $needUnsetFields = array('status','closedBy','closedDate','assignedTo','assignedDate');
        foreach($needUnsetFields as $fieldName) unset($this->config->issue->search['fields'][$fieldName]);
        $this->issue->buildSearchForm($actionURL, $queryID);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $issues = $this->issue->getNotImported($libraries, $libID, $projectID, $orderBy, $browseType, $queryID);
        $pager  = pager::init(count($issues), $recPerPage, $pageID);
        $issues = array_chunk($issues, $pager->recPerPage);

        $this->view->title = $this->lang->issue->common . $this->lang->colon . $this->lang->issue->importFromLib;

        $this->view->libraries  = $libraries;
        $this->view->libID      = $libID;
        $this->view->projectID  = $objectID;
        $this->view->issues     = empty($issues) ? $issues : $issues[$pageID - 1];
        $this->view->users      = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->pager      = $pager;
        $this->view->from       = $from;
        $this->view->orderBy    = $orderBy;
        $this->view->browseType = $browseType;
        $this->view->queryID    = $queryID;

        $this->display();
    }

    /**
     * Delete an issue.
     *
     * @param  int    $issueID
     * @param  string $from
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function delete($issueID = 0, $from='project', $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->issue->confirmDelete, inLink('delete', "issueID=$issueID&from=$from&confirm=yes")));
        }
        else
        {
            $this->issue->delete(TABLE_ISSUE, $issueID);
            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));
            die(js::locate($this->session->issueList, 'parent'));
        }
    }

    /**
     * Edit an issue.
     *
     * @param  int    $issueID
     * @access public
     * @return void
     */
    public function edit($issueID, $from = 'project')
    {
        $this->loadModel('project');
        $issue = $this->issue->getByID($issueID);
        if($from == 'project') $this->commonAction($issue->project, $from);
        if($from == 'execution') $this->commonAction($issue->execution, $from);

        $objectID = $from == 'execution' ? $issue->execution : $issue->project;

        if($_POST)
        {
            if(empty($issue))
            {
                if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => '404 Not found'));
                die(js::error($this->lang->notFound));
            }

            $changes = $this->issue->update($issueID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $actionID = $this->loadModel('action')->create('issue', $issueID, 'Edited');
            $this->action->logHistory($actionID, $changes);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inLink('view', "issueID=$issueID&from=$from")));
        }

        $this->view->title       = $this->lang->issue->common . $this->lang->colon . $this->lang->issue->edit;
        $this->view->position[]  = $this->lang->issue->common;
        $this->view->position[]  = $this->lang->issue->edit;
        $this->view->executions  = array(0 => '') + $this->loadModel('execution')->getPairs($issue->project, 'all', 'leaf');
        $this->view->projectList = $this->project->getPairsByModel('all', 0, 'multiple');
        $this->view->teamMembers = $this->project->getTeamMemberPairs($objectID);
        $this->view->risks       = $this->loadModel('risk')->getProjectRiskPairs($issue->project, $issue->risk);
        $this->view->issue       = $issue;

        $this->display();
    }

    /**
     * Assign an issue.
     *
     * @param  int    $issueID
     * @param  string $from
     * @access public
     * @return void
     */
    public function assignTo($issueID, $from = 'project')
    {
        if($_POST)
        {
            $changes = $this->issue->assignTo($issueID);

            if(dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->loadModel('action')->create('issue', $issueID, 'Assigned', $this->post->comment, $this->post->assignedTo);

            $this->action->logHistory($actionID, $changes);
            die(js::locate($this->session->issueList, 'parent.parent'));
        }

        $issue    = $this->issue->getByID($issueID);
        $objectID = $issue->project;

        if($from == 'execution') $objectID = $issue->execution;

        $this->view->issue = $issue;
        $this->loadModel('project');
        $this->view->users = $this->project->getTeamMemberPairs($objectID);

        $this->display();
    }

    /**
     * Close an issue.
     *
     * @param  int    $issueID
     * @access public
     * @return void
     */
    public function close($issueID)
    {
        if($_POST)
        {
            $changes = $this->issue->close($issueID);

            if(dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->loadModel('action')->create('issue', $issueID, 'Closed', $this->post->comment);

            $this->action->logHistory($actionID, $changes);
            die(js::reload('parent.parent'));
        }

        $this->view->issue   = $this->issue->getByID($issueID);
        $this->view->actions = $this->loadModel('action')->getList('issue', $issueID);
        $this->view->users   = $this->loadModel('user')->getPairs('noclosed|nodeleted');
        $this->display();
    }

    /**
     * Confirm the issue.
     *
     * @param  int    $issueID
     * @access public
     * @return void
     */
    public function confirm($issueID)
    {
        if($_POST)
        {
            $changes = $this->issue->confirm($issueID);

            if(dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->loadModel('action')->create('issue', $issueID, 'issueConfirmed');

            $this->action->logHistory($actionID, $changes);
            die(js::closeModal('parent.parent', 'this'));
        }

        $this->view->issue = $this->issue->getByID($issueID);
        $this->display();
    }

    /**
     * Cancel an issue.
     *
     * @param  int    $issueID
     * @access public
     * @return void
     */
    public function cancel($issueID)
    {
        if($_POST)
        {
            $changes = $this->issue->cancel($issueID);

            if(dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->loadModel('action')->create('issue', $issueID, 'Canceled');

            $this->action->logHistory($actionID, $changes);
            die(js::reload('parent.parent'));
        }

        $this->view->issue = $this->issue->getByID($issueID);

        $this->display();
    }

    /**
     * Activate an issue.
     *
     * @param  int    $issueID
     * @param  string $from
     * @access public
     * @return void
     */
    public function activate($issueID, $from = 'project')
    {
        if($_POST)
        {
            $changes = $this->issue->activate($issueID);

            if(dao::isError()) die(js::error(dao::getError()));
            $actionID = $this->loadModel('action')->create('issue', $issueID, 'Activated');

            $this->action->logHistory($actionID, $changes);
            die(js::reload('parent.parent'));
        }

        $issue    = $this->issue->getByID($issueID);
        $objectID = $issue->project;

        if($from == 'execution') $objectID = $issue->execution;

        $this->view->issue = $issue;
        $this->loadModel('project');
        $this->view->users = $this->project->getTeamMemberPairs($objectID);

        $this->display();
    }

    /**
     * Resolve an issue.
     *
     * @param  int    $issueID
     * @param  string $from
     * @access public
     * @return void
     */
    public function resolve($issueID, $from = 'project')
    {
        $issue = $this->issue->getByID($issueID);

        if($_POST)
        {
            $data = fixer::input('post')->stripTags('steps', $this->config->allowedTags)->get();
            $resolution = $data->resolution;
            unset($_POST['resolution'], $_POST['resolvedBy'], $_POST['resolvedDate']);

            $objectID = '';
            if($resolution == 'totask')
            {
                $objectID = $this->issue->createTask($issueID);
                if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

                $objectLink = html::a($this->createLink('task', 'view', "id=$objectID"), $this->post->name, '_blank');
                $comment    = sprintf($this->lang->issue->logComments[$resolution], $objectLink);

                $this->loadModel('action')->create('task', $objectID, 'Opened', '');
                $this->loadModel('action')->create('issue', $issueID, 'Resolved', $comment);
            }

            if($resolution == 'tostory')
            {
                unset($_POST['project']);
                $objectID   = $this->issue->createStory($issueID);
                if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
                $objectLink = html::a($this->createLink('story', 'view', "id=$objectID"), $this->post->title, '_blank');
                $comment    = sprintf($this->lang->issue->logComments[$resolution], $objectLink);

                $this->loadModel('action')->create('story', $objectID, 'Opened', '');
                $this->loadModel('action')->create('issue', $issueID, 'Resolved', $comment);
            }

            if($resolution == 'tobug')
            {
                $objectID   = $this->issue->createBug($issueID);
                if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
                $objectLink = html::a($this->createLink('bug', 'view', "id=$objectID"), $this->post->title, '_blank');
                $comment    = sprintf($this->lang->issue->logComments[$resolution], $objectLink);

                $this->loadModel('action')->create('bug', $objectID, 'Opened', '');
                $this->loadModel('action')->create('issue', $issueID, 'Resolved', $comment);
            }

            if($resolution == 'torisk')
            {
                $objectID   = $this->issue->createRisk($issueID);
                if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
                $objectLink = html::a($this->createLink('risk', 'view', "id=$objectID"), $this->post->name, '_blank');
                $comment    = sprintf($this->lang->issue->logComments[$resolution], $objectLink);

                $this->loadModel('action')->create('risk', $objectID, 'Opened', '');
                $this->loadModel('action')->create('issue', $issueID, 'Resolved', $comment);
            }

            $this->issue->resolve($issueID, $data);
            if($resolution == 'resolved') $this->loadModel('action')->create('issue', $issueID, 'Resolved', $this->post->resolutionComment);
            $this->dao->update(TABLE_ISSUE)->set('objectID')->eq($objectID)->where('id')->eq($issueID)->exec();

            if(isonlybody()) return $this->send(array('locate' => 'parent', 'message' => $this->lang->saveSuccess, 'result' => 'success'));
            die(js::locate(inLink('browse', "projectID=$issue->project"), 'parent'));
        }

        $this->view->title = $this->lang->issue->resolve;
        $this->view->issue = $issue;

        $objectID = $issue->project;
        if($from == 'execution') $objectID = $issue->execution;

        $this->loadModel('project');
        $this->view->users = $this->project->getTeamMemberPairs($objectID);

        $this->display();
    }

    /**
     * Import issue to issue lib.
     *
     * @param  int    $issueID
     * @access public
     * @return void
     */
    public function importToLib($issueID)
    {
        $this->issue->importToLib($issueID);
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
        $issueIDList = $this->post->issueIDList;
        $this->issue->importToLib($issueIDList);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success', 'message' => $this->lang->importSuccess, 'locate' => 'reload'));
    }

    /**
     * Get different types of resolution forms.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function ajaxGetResolveForm($projectID)
    {
        $data  = fixer::input('post')->get();
        $issue = $this->issue->getByID($data->issueID);
        $users = $this->loadModel('user')->getPairs('noclosed|nodeleted');

        $task = new stdClass();
        $task->module     = 0;
        $task->assignedTo = '';
        $task->name       = $issue->title;
        $task->type       = '';
        $task->estimate   = '';
        $task->desc       = $issue->desc;
        $task->estStarted = '';
        $task->deadline   = '';

        $this->view->resolution = $data->mode;
        $this->view->issue      = $issue;
        $this->view->users      = $users;
        $this->view->task       = $task;

        if(in_array($data->mode, array('tostory', 'tobug', 'totask')))
        {
            $this->loadModel('task');
            $this->loadModel('tree');
            $this->loadModel('project');

            $moduleOptionMenu = array('' => '/');
            if($data->mode == 'totask') $moduleOptionMenu = $this->tree->getOptionMenu($projectID, 'task');

            $this->view->moduleOptionMenu = $moduleOptionMenu;
            $this->view->showAllModule    = 'allModule';
            $this->view->projectID        = $projectID;
            $this->view->moduleID         = 0;
            $this->view->branch           = 0;
        }

        if(in_array($data->mode, array('tostory', 'tobug')))
        {
            $products   = $this->loadModel('product')->getProductPairsByProject($projectID);
            $productID  = $this->session->product;
            $productID  = isset($products[$productID]) ? $productID : key($products);
            $branches   = $this->loadModel('branch')->getPairs($productID, 'noempty');
            $projects   = $this->product->getProjectPairsByProduct($productID);
            $executions = $this->product->getExecutionPairsByProduct($productID, 0, 'id_desc', $projectID);

            $module = $data->mode == 'tostory' ? 'story' : 'bug';
            $moduleOptionMenu = $this->tree->getOptionMenu($productID, $module);

            $this->view->moduleOptionMenu = $moduleOptionMenu;
            $this->view->branches         = $branches;
            $this->view->products         = $products;
            $this->view->productID        = $productID;
            $this->view->projects         = $projects;
            $this->view->executions       = $executions;
        }

        switch($data->mode)
        {
            case 'totask':
                $this->loadModel('story');

                $executions  = $this->loadModel('execution')->getPairs($projectID);
                $executionID = key($executions);

                $this->view->members    = $this->loadModel('user')->getTeamMemberPairs($executionID, 'executionID', 'nodeleted');
                $this->view->stories    = $this->story->getExecutionStoryPairs($executionID, 0, 0);
                $this->view->showFields = $this->config->task->custom->createFields;
                $this->view->executions = $executions;

                $this->display('issue', 'taskform');
                break;
            case 'tobug':
                $this->loadModel('bug');
                $this->view->builds     = $this->loadModel('build')->getBuildPairs($productID, 'all', 'noempty,noterminate,nodone,noreleased');
                $this->view->buildID    = 0;
                $this->view->showFields = $this->config->bug->custom->createFields;

                $this->display('issue', 'bugform');
                break;
            case 'tostory':
                $this->loadModel('story');
                $this->view->plans      = $this->loadModel('productplan')->getPairsForStory($productID, key($branches), true);
                $this->view->showFields = $this->config->story->custom->createFields;
                $this->display('issue', 'storyform');
                break;
            case 'torisk':
                $this->app->loadLang('risk');
                $this->display('issue', 'riskform');
                break;
            case 'resolved':
                $this->display('issue', 'resolveform');
                break;
        }
    }

    /**
     * AJAX: return issues of a user in html select.
     *
     * @param  int    $userID
     * @param  string $id
     * @param  string $status
     * @access public
     * @return void
     */
    public function ajaxGetUserIssues($userID = '', $id = '', $status = 'all')
    {
        if($userID == '') $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;

        $issues = $this->issue->getUserIssuePairs($account, 0, $status);

        if($id) die(html::select("issues[$id]", $issues, '', 'class="form-control"'));
        die(html::select('issue', $issues, '', 'class=form-control'));
    }

    /**
     *  View an issue.
     *
     * @param  int    $issueID
     * @access public
     * @return void
     */
    public function view($issueID, $from = 'project')
    {
        /* Set actions and get issue by id. */
        $issueID = (int)$issueID;
        $issue   = $this->issue->getByID($issueID);
        if(!$issue) die(js::error($this->lang->notFound) . js::locate($this->createLink('project', 'browse')));
        if($from == 'project') $this->commonAction($issue->project, $from);
        if($from == 'execution') $this->commonAction($issue->execution, $from);

        $this->session->project = $issue->project;

        $this->view->title      = $this->lang->issue->common . $this->lang->colon . $issue->title;
        $this->view->actions    = $this->loadModel('action')->getList('issue', $issueID);
        $this->view->position[] = $this->lang->issue->common;
        $this->view->position[] = $this->lang->issue->basicInfo;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|pofirst|nodeleted');
        $this->view->issue      = $issue;
        $this->view->linkedRisk = $issue->risk ? $this->loadModel('risk')->getById($issue->risk) : '';
        $this->view->from       = $from;
        $this->view->execution  = $this->loadModel('execution')->getById($issue->execution);
        $this->view->approvers  = $this->loadModel('assetlib')->getApproveUsers('issue');
        $this->view->libs       = $this->assetlib->getPairs('issue');
        $this->view->preAndNext = $this->loadModel('common')->getPreAndNextObject('issue', $issueID);

        $this->display();
    }

    /**
     * Common actions of issue module.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
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
     * Export issue.
     *
     * @param  int    $objectID
     * @param  string $from
     * @param  string $browseType
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($objectID, $from = 'project', $browseType = 'all', $orderBy = 'id_desc')
    {
        if($_POST)
        {
            $this->loadModel('file');
            $issueLang   = $this->lang->issue;
            $issueConfig = $this->config->issue;

            /* Create field lists. */
            $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $issueConfig->list->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($issueLang->$fieldName) ? $issueLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            /* Get issues. */
            $issues = $this->dao->select('*')->from(TABLE_ISSUE)->where($this->session->issueQueryCondition)
                ->beginIF($this->post->exportType == 'selected')->andWhere('id')->in($this->cookie->checkedItem)->fi()
                ->orderBy($orderBy)->fetchAll('id');

            /* Get users, executions. */
            $users = $this->loadModel('user')->getPairs('noletter');
            if($from == 'project')
            {
                $executions = $this->loadModel('execution')->getPairs($objectID, 'all', 'all');
            }
            elseif($from == 'execution')
            {
                $execution = $this->loadModel('execution')->getById($objectID);
                $executions[$objectID] = $execution->name;
            }

            /* Get related objects title or names. */
            $relatedFiles = $this->dao->select('id, objectID, pathname, title')->from(TABLE_FILE)->where('objectType')->eq('issue')->andWhere('objectID')->in(array_keys($issues))->andWhere('extra')->ne('editor')->fetchGroup('objectID');

            foreach($issues as $issue)
            {
                if($this->post->fileType == 'csv')
                {
                    $issue->desc = str_replace("<br />", "\n", $issue->desc);
                    $issue->desc = str_replace('"', '""', $issue->desc);
                    $issue->desc = str_replace('&nbsp;', ' ', $issue->desc);

                    $issue->resolutionComment = str_replace("<br />", "\n", $issue->resolutionComment);
                    $issue->resolutionComment = str_replace('"', '""', $issue->resolutionComment);
                    $issue->resolutionComment = str_replace('&nbsp;', ' ', $issue->resolutionComment);
                }

                /* fill some field with useful value. */
                $issue->execution = !isset($executions[$issue->execution]) ? '' : $executions[$issue->execution] . "(#$issue->execution)";

                if(isset($issueLang->priList[$issue->pri]))               $issue->pri        = $issueLang->priList[$issue->pri];
                if(isset($issueLang->typeList[$issue->type]))             $issue->type       = $issueLang->typeList[$issue->type];
                if(isset($issueLang->severityList[$issue->severity]))     $issue->severity   = $issueLang->severityList[$issue->severity];
                if(isset($issueLang->statusList[$issue->status]))         $issue->status     = $issueLang->statusList[$issue->status];
                if(isset($issueLang->resolutionList[$issue->resolution])) $issue->resolution = $issueLang->resolutionList[$issue->resolution];

                if(!empty($users[$issue->owner]))        $issue->owner       = zget($users, $issue->owner);
                if(!empty($users[$issue->assignedTo]))   $issue->assignedTo  = zget($users, $issue->assignedTo) . "(#$issue->assignedTo)";
                if(!empty($users[$issue->createdBy]))    $issue->createdBy   = zget($users, $issue->createdBy);
                if(!empty($users[$issue->resolvedBy]))   $issue->resolvedBy  = zget($users, $issue->resolvedBy);
                if(!empty($users[$issue->activateBy]))   $issue->activateBy  = zget($users, $issue->activateBy);
                if(!empty($users[$issue->closedBy]))     $issue->closedBy    = zget($users, $issue->closedBy);

                if(helper::isZeroDate($issue->deadline))     $issue->deadline     = '';
                if(helper::isZeroDate($issue->resolvedDate)) $issue->resolvedDate = '';
                if(helper::isZeroDate($issue->activateDate)) $issue->activateDate = '';
                if(helper::isZeroDate($issue->closedDate))   $issue->closedDate   = '';

                $issue->title = htmlspecialchars_decode($issue->title, ENT_QUOTES);
                $issue->resolutionComment = htmlspecialchars_decode($issue->resolutionComment, ENT_QUOTES);

                /* Set related files. */
                $issue->files = '';
                if(isset($relatedFiles[$issue->id]))
                {
                    foreach($relatedFiles[$issue->id] as $file)
                    {
                        $fileURL = common::getSysURL() . helper::createLink('file', 'download', "fileID={$file->id}");
                        $issue->files .= html::a($fileURL, $file->title, '_blank') . '<br />';
                    }
                }

                unset($issue->deleted);
            }

            $fieldWidths = array();
            $fieldWidths['type']        = '14';
            $fieldWidths['createdDate'] = '20';

            $this->post->set('fields', $fields);
            $this->post->set('rows', $issues);
            $this->post->set('width', $fieldWidths);
            $this->post->set('kind', 'issue');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $fileName    = $this->lang->issue->common;
        $projectName = $this->dao->findById($objectID)->from(TABLE_PROJECT)->fetch('name');
        $browseType  = zget($this->lang->issue->featureBar['browse'], $browseType, '');
        $fileName    = $projectName . $this->lang->dash . $browseType . $fileName;

        $this->view->title    = $this->lang->issue->export;
        $this->view->fileName = $fileName;
        $this->display();
    }

    /**
     * Ajax get project issues.
     *
     * @param  int    $objectType
     * @param  int    $objectID
     * @param  string $append
     * @access public
     * @return void
     */
    public function ajaxGetProjectIssues($projectID, $append = '')
    {
        $issues = $this->issue->getProjectIssuePairs($projectID, true, $append);

        $selectHtml = html::select('issues[]', $issues, '', "class='form-control chosen' multiple");
        return print($selectHtml);
    }
}
