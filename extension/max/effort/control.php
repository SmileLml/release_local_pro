<?php
/**
 * The control file of effort module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     effort
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class effort extends control
{
    /**
     * Construct function, load model of task, bug, my.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('todo');
    }

    /**
     * Batch create efforts.
     *
     * @param  string|date $date
     * @param  int         $userID
     * @access public
     * @return void
     */
    public function batchCreate($date = 'today', $userID = '')
    {
        if($date == 'today') $date   = date(DT_DATE1, time());
        if($userID == '')    $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;
        if(!empty($_POST))
        {
            $this->effort->batchCreate();
            if(dao::isError()) die(js::error(dao::getError()));
            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::locate($this->createLink('my', 'effort'), 'parent'));
        }

        $actions = $this->effort->getActions($date, $account);

        /* Fix bug #18282. */
        $efforts = $this->effort->getList($date, $date, $account);
        foreach($actions as $key => $action)
        {
            if(!isset($action->objectType) or !isset($action->objectID)) continue;
            foreach($efforts as $effort)
            {
                if($effort->objectType == $action->objectType and $effort->objectID == $action->objectID) unset($actions[$key]);
            }
        }

        $typeList = array();
        if(isset($actions['typeList'])) $typeList += $actions['typeList'];

        $executionTask = array();
        $executionBug  = array();
        if(isset($actions['executionTask'])) $executionTask += $actions['executionTask'];
        if(isset($actions['executionBug'])) $executionBug += $actions['executionBug'];

        $status = $this->config->CRExecution ? 'all' : 'noclosed';

        $appendExecutions = empty($actions['executions']) ? array() : $actions['executions'];

        $maxCount      = 50;
        $joinExecution = $this->effort->getJoinExecution($status, $maxCount);

        $recentlyExecution = array();
        if($maxCount - count($joinExecution) > 0) $recentlyExecution = $this->effort->getRecentlyExecutions($status, $maxCount - count($joinExecution), array_keys($joinExecution));

        unset($actions['typeList']);
        unset($actions['executionTask']);
        unset($actions['executionBug']);
        unset($actions['executions']);

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->effort->create;
        $this->view->position[] = $this->lang->effort->create;

        $this->view->date          = !is_numeric($date) ? $date : substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        $this->view->actions       = $actions;
        $this->view->typeList      = array('' => '') + $typeList;
        $this->view->executions    = array('' => '') + $joinExecution + $recentlyExecution + $appendExecutions;
        $this->view->executionTask = $executionTask;
        $this->view->executionBug  = $executionBug;
        $this->display();
    }

    /**
     * create a effort for a object.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @param  string $from
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function createForObject($objectType, $objectID, $from = '', $orderBy = '')
    {
        if(!empty($_POST))
        {
            if($objectType == 'task') $oldTask = $this->loadModel('task')->getById($objectID);
            $this->effort->batchCreate();
            if(dao::isError()) return print(js::error(dao::getError()));
            if($this->app->viewType == 'mhtml')
            {
                return print(js::locate($this->createLink($objectType, 'view', "{$objectType}ID=$objectID"), 'parent'));
            }
            if(defined('RUN_MODE') && RUN_MODE == 'api')
            {
                return $this->send(array('status' => 'success', 'data' => $objectID));
            }

            /* Remind whether to update status of the bug, if task which from that bug has been finished. */
            if($objectType == 'task')
            {
                $task = $this->task->getById($objectID);
                if($this->task->needUpdateBugStatus($task))
                {
                    if($oldTask->status != 'done' && $task->status == 'done')
                    {
                        $confirmURL = $this->createLink('bug', 'view', "id=$task->fromBug");
                        unset($_GET['onlybody']);
                        $cancelURL  = $this->createLink('task', 'view', "taskID=$taskID");
                        return print(js::confirm(sprintf($this->lang->task->remindBug, $task->fromBug), $confirmURL, $cancelURL, 'parent', 'parent.parent'));
                    }
                }
            }

            if(isonlybody())
            {
                if($objectType == 'task')
                {
                    $this->loadModel('kanban');
                    $task      = $this->task->getById($objectID);
                    $execution = $this->loadModel('execution')->getByID($task->execution);
                    if($task->parent > 0) $this->task->updateParentStatus($task->id);
                    if($task->story) $this->loadModel('story')->setStage($task->story);
                    if($task->status != $oldTask->status) $this->kanban->updateLane($task->execution, 'task', $task->id);
                    $this->loadModel('common')->syncPPEStatus($objectID);
                    if($oldTask->feedback) $this->loadModel('feedback')->updateStatus('task', $oldTask->feedback, $task->status, $oldTask->status);

                    $execLaneType = $this->session->execLaneType ? $this->session->execLaneType : 'all';
                    $execGroupBy  = $this->session->execGroupBy ? $this->session->execGroupBy : 'default';
                    if(($this->app->tab == 'execution' or ($this->config->vision == 'lite' and $this->app->tab == 'project')) and $execution->type == 'kanban')
                    {
                        $rdSearchValue = $this->session->rdSearchValue ? $this->session->rdSearchValue : '';
                        $kanbanData    = $this->kanban->getRDKanban($task->execution, $execLaneType, 'id_desc', 0, $execGroupBy, $rdSearchValue);
                        $kanbanData    = json_encode($kanbanData);

                        return print(js::reload('parent') . js::execute("parent.parent.updateKanban($kanbanData)"));
                    }
                    if($from == 'taskkanban')
                    {
                        $taskSearchValue = $this->session->taskSearchValue ? $this->session->taskSearchValue : '';
                        $kanbanData      = $this->kanban->getExecutionKanban($task->execution, $execLaneType, $execGroupBy, $taskSearchValue);
                        $kanbanType      = $execLaneType == 'all' ? 'task' : key($kanbanData);
                        $kanbanData      = $kanbanData[$kanbanType];
                        $kanbanData      = json_encode($kanbanData);

                        return print(js::reload('parent') . js::execute("parent.parent.updateKanban(\"task\", $kanbanData)"));
                    }
                    return print(js::reload('parent') . js::execute("if(typeof(parent.parent.ajaxRefresh) == 'function') parent.parent.ajaxRefresh()"));
                }

                return print(js::closeModal('parent.parent', 'this'));
            }
            return print(js::reload('parent'));
        }

        $this->app->loadLang('task');

        $date = date(DT_DATE1);
        $task = $objectType == 'task' ? $this->loadModel('task')->getById($objectID) : '';
        if(!empty($task) and !empty($task->team) and $task->mode == 'linear')
        {
            if(empty($orderBy))
            {
                $orderBy = 'id_desc';
            }
            else
            {
                /* The id sort with order or date style. */
                $orderBy .= preg_replace('/(order_|date_)/', ',id_', $orderBy);
            }
        }
        if(empty($orderBy)) $orderBy = 'id_desc';
        if($objectType == 'task')
        {
            /* Set the fold state of the current task. */
            $referer = strtolower($_SERVER['HTTP_REFERER']);
            if(strpos($referer, 'createforobject') and $this->cookie->taskEffortFold !== false)
            {
                $taskEffortFold = $this->cookie->taskEffortFold;
            }
            else
            {
                $taskEffortFold = 0;
                $currentAccount = $this->app->user->account;
                if($task->assignedTo == $currentAccount) $taskEffortFold = 1;
                if(!empty($task->team))
                {
                    $teamMember = helper::arrayColumn($task->team, 'account');
                    if(in_array($currentAccount, $teamMember)) $taskEffortFold = 1;
                }
            }

            $this->view->taskEffortFold = $taskEffortFold;
        }

        $efforts = $this->effort->getByObject($objectType, $objectID, $orderBy);
        if(isset($efforts['typeList'])) $this->view->typeList = $efforts['typeList'];
        unset($efforts['typeList']);

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->effort->create;
        $this->view->position[] = $this->lang->effort->create;

        $this->view->modalTitle = $this->effort->getModalTitle($objectType, $objectID);
        $this->view->task       = $task;
        $this->view->date       = $date;
        $this->view->from       = $from;
        $this->view->orderBy    = $orderBy;
        $this->view->efforts    = $efforts;
        $this->view->objectType = $objectType;
        $this->view->objectID   = $objectID;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|noempty');
        $this->display();
    }

    /**
     * Edit a effort.
     *
     * @param  int    $effortID
     * @access public
     * @return void
     */
    public function edit($effortID)
    {
        if(!empty($_POST))
        {
            $changes = $this->effort->update($effortID);
            if(dao::isError()) die(js::error(dao::getError()));
            if($changes)
            {
                $actionID = $this->loadModel('action')->create('effort', $effortID, 'edited');
                $this->action->logHistory($actionID, $changes);
            }

            if(dao::isError()) die(js::error(dao::getError()));
            if(isonlybody())   die(js::reload('parent.parent'));

            $url = $this->session->effortList ? $this->session->effortList : inlink('view', "effortID=$effortID");
            die(js::locate($url, 'parent'));
        }

        /* Judge a private effort or not, If private, die. */
        $effort       = $this->effort->getById($effortID);
        $effort->date = (int)$effort->date == 0 ? $effort->date : substr($effort->date, 0, 4) . '-' . substr($effort->date, 4, 2) . '-' . substr($effort->date, 6, 2);
        $executions   = $this->loadModel('execution')->getPairs(0, 'all', 'noclosed|leaf');

        /* Get the id of the latest date effort. */
        $recentDateID = 0;
        if($effort->objectType === 'task')
        {
            $recentDateID = $this->dao->select('*')->from(TABLE_EFFORT)->where('objectType')->eq('task')->andWhere('objectID')->eq($effort->objectID)->andWhere('deleted')->eq(0)->orderBy('`date` desc,`id` desc')->limit(1)->fetch('id');
            $executions   = $this->execution->getPairs($effort->project, 'all', 'noclosed|leaf');
            $this->view->task = $this->loadModel('task')->getById($effort->objectID);
        }

        if($effort->objectType == 'doc')
        {
            $doc        = $this->dao->findById($effort->objectID)->from(TABLE_DOC)->fetch();
            $executions = $this->execution->getPairs($doc->project, 'all', 'noclosed|leaf');
        }

        /* Add project name. */
        if($effort->project)
        {
            $project = $this->loadModel('project')->getById($effort->project);
            foreach($executions as $id => $name)
            {
                $executions[$id] = $project->name . $name;
                if(empty($project->multiple)) $executions[$id] = $project->name . "({$this->lang->project->disableExecution})";
            }

            $this->view->project = $project;
        }

        $objectName = zget($this->lang->effort->objectTypeList, $effort->objectType);
        if($effort->objectType == 'story') $objectName = $this->dao->findById($effort->objectID)->from(TABLE_STORY)->fetch('title');
        if($effort->objectType == 'case')  $objectName = $this->dao->findById($effort->objectID)->from(TABLE_CASE)->fetch('title');
        if($effort->objectType == 'task')  $objectName = $this->dao->findById($effort->objectID)->from(TABLE_TASK)->fetch('name');
        if($effort->objectType == 'bug')   $objectName = $this->dao->findById($effort->objectID)->from(TABLE_BUG)->fetch('title');
        if($effort->objectType == 'doc')   $objectName = $this->dao->findById($effort->objectID)->from(TABLE_DOC)->fetch('title');

        if($effort->execution)
        {
            $execution = $this->execution->getByID($effort->execution);
            if(!empty($execution->status) and $execution->status == 'closed') $executions += array($execution->id => $execution->name);
        }

        /* Remove duplicate case. */
        unset($this->lang->effort->objectTypeList['testcase']);

        $this->view->title         = $this->lang->my->common . $this->lang->colon . $this->lang->effort->edit;
        $this->view->position[]    = $this->lang->effort->edit;
        $this->view->products      = $this->loadModel('product')->getPairs();
        $this->view->executions    = $executions;
        $this->view->objectName    = $objectName;
        $this->view->effort        = $effort;
        $this->view->recentDateID  = $recentDateID;
        $this->display();
    }

    /**
     * Batch edit a effort.
     *
     * @param  int    $effortID
     * @access public
     * @return void
     */
    public function batchEdit($from = 'browse', $userID = '')
    {
        if($userID == '') $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;
        if(!empty($_POST) and $from == 'batchEdit')
        {
            $this->effort->batchUpdate();
            if(dao::isError()) die(js::error(dao::getError()));

            $effortType = isset($_SESSION['effortType']) ? $_SESSION['effortType'] : 'today';

            $url = $this->session->effortList ? $this->session->effortList : $this->createLink('my', 'effort', "type=$effortType");
            die(js::locate($url, 'parent'));
        }

        if(empty($_POST['effortIDList'])) $this->post->set('effortIDList', array());
        /* Judge a private effort or not, If private, die. */
        $efforts = $this->effort->getByAccount($_POST['effortIDList'], $account);
        if(isset($efforts['typeList']))
        {
            $typeList = $efforts['typeList'];
            unset($efforts['typeList']);
            $typeList['custom']   = '';
            $this->view->typeList = $typeList;
        }

        $this->view->title          = $this->lang->my->common . $this->lang->colon . $this->lang->effort->batchEdit;
        $this->view->position[]     = $this->lang->effort->batchEdit;
        $this->view->products       = $this->loadModel('product')->getPairs();
        $this->view->shadowProducts = $this->loadModel('product')->getPairs('', 0, '', 1);
        $this->view->executions     = $this->loadModel('execution')->getPairs(0, 'all', 'leaf');
        $this->view->efforts        = $efforts;
        $this->display();
    }

    /**
     * View a effort.
     *
     * @param  int    $effortID
     * @param  string $from     my|company
     * @access public
     * @return void
     */
    public function view($effortID, $from = 'company')
    {
        $effort = $this->effort->getById($effortID);
        if(!$effort) die(js::error($this->lang->notFound) . js::locate('back'));

        $this->view->title      = $this->lang->effort->view;
        $this->view->position[] = $this->lang->effort->view;
        $this->view->effort     = $effort;
        $this->view->work       = $this->effort->getWork($effort->objectType, $effort->objectID);
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions    = $this->loadModel('action')->getList('effort', $effortID);
        $this->view->from       = $from;
        $this->view->user       = $this->user->getById($effort->account);

        $this->display();
    }

    /**
     * Delete a effort.
     *
     * @param  int    $effortID
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function delete($effortID, $confirm = 'no')
    {
        $this->app->loadLang('task');
        $effort = $this->effort->getByID($effortID);
        if($effort->objectType == 'task') $task = $this->dao->select('*')->from(TABLE_TASK)->where('id')->eq($effort->objectID)->fetch();
        if($confirm == 'no' and $effort->objectType == 'task' and $task->consumed - $effort->consumed != 0)
        {
            die(js::confirm($this->lang->effort->confirmDelete, $this->createLink('effort', 'delete', "effortID=$effortID&confirm=yes")));
        }
        elseif($confirm == 'no' and $effort->objectType == 'task' and $task->consumed - $effort->consumed == 0)
        {
            die(js::confirm($this->lang->task->confirmDeleteLastEstimate, $this->createLink('effort', 'delete', "effortID=$effortID&confirm=yes")));
        }
        elseif($confirm == 'no' and $effort->objectType != 'task')
        {
            die(js::confirm($this->lang->effort->confirmDelete, $this->createLink('effort', 'delete', "effortID=$effortID&confirm=yes")));
        }
        else
        {
            $this->effort->delete(TABLE_EFFORT, $effortID);
            if($effort->objectType == 'task')
            {
                $this->effort->changeTaskConsumed($effort, 'delete');
            }
            else
            {
                if($effort->objectType != 'custom')
                {
                    $this->effort->recordAction($effort->objectType, $effort->objectID, 'deleteEstimate', '', $effort->consumed);
                }
                else
                {
                    $this->effort->recordAction('effort', $effort->id, 'deleteEstimate', '', $effort->consumed);
                }
            }

            if($effort->objectType == 'task' and $task->consumed - $effort->consumed == 0) return print(js::reload('parent.parent'));
            return print(js::reload('parent'));
        }
    }

    /**
     * Get data to export
     *
     * @param  int    $userID
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($userID, $orderBy = 'id_desc')
    {
        if($_POST)
        {
            $effortLang   = $this->lang->effort;
            $effortConfig = $this->config->effort;

            /* Create field lists. */
            $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $effortConfig->list->defaultFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($effortLang->$fieldName) ? $effortLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            /* Get efforts. */
            $efforts = $this->dao->select('t1.*,t2.dept')->from(TABLE_EFFORT)->alias('t1')
                ->leftJoin(TABLE_USER)->alias('t2')->on('t1.account=t2.account')
                ->where($this->session->effortReportCondition)
                ->andWhere('t1.deleted')->eq(0)
                ->beginIF($this->post->exportType == 'selected')->andWhere('t1.id')->in($this->cookie->checkedItem)->fi()
                ->orderBy("$orderBy, account_desc")->fetchAll('id');

            /* Get users, bugs, tasks and times. */
            $users      = $this->loadModel('user')->getPairs('noletter');
            $products   = $this->loadModel('product')->getPairs('', 0, '', 'all');
            $executions = $this->loadModel('execution')->getPairs(0, 'all', 'multiple');
            $projects   = $this->loadModel('project')->getPairsByProgram();
            $depts      = $this->loadModel('dept')->getOptionMenu();

            $objectTypes = array();
            foreach($efforts as $effort)
            {
                if(isset($fields['dept'])) $effort->dept = zget($depts, $effort->dept, '');
                if(isset($fields['execution'])) $effort->execution = zget($executions, $effort->execution, '');
                if(isset($fields['project'])) $effort->project = zget($projects, $effort->project, '');
                if(isset($fields['product']))
                {
                    $effortProducts  = explode(',', trim($effort->product, ','));
                    $effort->product = '';
                    foreach($effortProducts as $productID) $effort->product .= zget($products, $productID, '') . ' ';
                }

                if(empty($effort->objectType)) continue;
                if($effort->objectType == 'custom') continue;
                if(!isset($objectTypes[$effort->objectType])) $objectTypes[$effort->objectType]['table'] = $this->config->objectTables[$effort->objectType];
                $objectTypes[$effort->objectType]['id'][] = $effort->objectID;
            }

            $objectTitles = array();
            foreach($objectTypes as $type => $objectType) $objectTitles[$type] = $this->dao->select('*')->from($objectType['table'])->where('id')->in($objectType['id'])->fetchAll('id');

            if(isset($objectTitles['todo']))
            {
                $linkTodoObjects = array();
                foreach($objectTitles['todo'] as $todoid => $todo)
                {
                    if($todo->type == 'bug' or $todo->type == 'task')$linkTodoObjects[$todo->type][] = $todo->idvalue;
                }

                $todoTitles = array();
                foreach($linkTodoObjects as $type => $linkObjectIDs) $todoTitles[$type] = $this->dao->select('*')->from('`' . $this->config->db->prefix . $type . '`')->where('id')->in($linkObjectIDs)->fetchAll('id');
            }

            foreach($efforts as $effort)
            {
                /* fill some field with useful value. */
                if(isset($users[$effort->account])) $effort->account = $users[$effort->account];
                $effort->work = htmlspecialchars_decode($effort->work);

                if($effort->objectType != 'custom')
                {
                    if(strpos(',story,bug,case,doc,productplan,', ',' . $effort->objectType . ',') !==false)
                    {
                        $objectTitle = isset($objectTitles[$effort->objectType][$effort->objectID]) ? $objectTitles[$effort->objectType][$effort->objectID]->title : '';
                    }
                    elseif(strpos(',release,task,build,testtask', ',' . $effort->objectType . ',') !==false)
                    {
                        $objectTitle = isset($objectTitles[$effort->objectType][$effort->objectID]) ? $objectTitles[$effort->objectType][$effort->objectID]->name : '';
                    }
                    elseif($effort->objectType == 'todo')
                    {
                        $objectTitle = ' ';
                        if(!empty($objectTitles[$effort->objectType][$effort->objectID]))
                        {
                            $todo        = $objectTitles[$effort->objectType][$effort->objectID];
                            $objectTitle = $todo->name;
                            if($todo->type != 'custom')
                            {
                                if($todo->type == 'bug') $objectTitle = isset($todoTitles['bug'][$todo->idvalue]) ? $todoTitles['bug'][$todo->idvalue]->title : $objectTitle;
                                if($todo->type == 'task') $objectTitle = isset($todoTitles['task'][$todo->idvalue]) ? $todoTitles['task'][$todo->idvalue]->name : $objectTitle;
                            }
                        }
                    }
                    if(isset($effortLang->objectTypeList[$effort->objectType])) $effort->objectType = $effortLang->objectTypeList[$effort->objectType] . " : #{$effort->objectID} " . $objectTitle;
                }
                else
                {
                    $effort->objectType = $effortLang->objectTypeList[$effort->objectType];
                }
            }

            $width['account']    = 11;
            $width['date']       = 11;
            $width['consumed']   = 15;
            $width['left']       = 15;
            $width['work']       = 40;
            $width['objectType'] = 40;

            if($this->config->edition != 'open') list($fields, $efforts) = $this->loadModel('workflowfield')->appendDataFromFlow($fields, $efforts);

            $this->post->set('fields', $fields);
            $this->post->set('rows', $efforts);
            $this->post->set('kind', $this->lang->effort->common);
            $this->post->set('width', $width);
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        /* Unset product field when under a independent project. */
        if($this->session->effortReportCondition and strpos($this->session->effortReportCondition, 'execution') !== false)
        {
            preg_match("/execution  = '([0-9]+)'/i", $this->session->effortReportCondition, $matches);
            $executionID = isset($matches[1]) ? (int)$matches[1] : 0;
            if($executionID)
            {
                $execution = $this->loadModel('execution')->getByID($executionID);
                $project   = $this->loadModel('project')->getByID($execution->project);

                if($project)
                {
                    if(!$project->hasProduct)
                    {
                        $this->config->effort->list->exportFields  = str_replace(',product,', ',', $this->config->effort->list->exportFields);
                        $this->config->effort->list->defaultFields = str_replace(',product,', ',', $this->config->effort->list->defaultFields);
                    }

                    if(!$project->multiple)
                    {
                        $this->config->effort->list->exportFields  = str_replace(',execution,', ',', $this->config->effort->list->exportFields);
                        $this->config->effort->list->defaultFields = str_replace(',execution,', ',', $this->config->effort->list->defaultFields);
                    }
                }
            }
        }

        $this->view->fileName        = $this->app->user->realname . ' - ' . $this->lang->effort->common;
        $this->view->allExportFields = $this->config->effort->list->exportFields;
        $this->view->selectedFields  = $this->config->effort->list->defaultFields;
        $this->view->customExport    = true;
        $this->display();
    }

    /**
     * Remind not record.
     *
     * @access public
     * @return void
     */
    public function remindNotRecord()
    {
        $users = $this->loadModel('user')->getPairs('nodeleted|noclosed|noempty|noletter');

        $timestamp = strtotime('yesterday');
        $yesterday = date('Y-m-d', $timestamp);
        $efforts   = $this->dao->select('distinct account')->from(TABLE_EFFORT)->where('date')->eq($yesterday)->andWhere('deleted')->eq(0)->fetchPairs('account', 'account');

        $this->loadModel('sso');
        if($this->config->sso->turnon)
        {
            $leaveUsers = $this->effort->getRanzhiLeaveUsers();
            if(!empty($leaveUsers))
            {
                $linkedZentaoUsers = $this->dao->select('*')->from(TABLE_USER)->where('ranzhi')->in($leaveUsers)->fetchPairs('account', 'account');
                foreach($linkedZentaoUsers as $account) unset($users[$account]);
            }
        }

        $noRecordUsers = array_diff(array_keys($users), array_keys($efforts));

        $this->loadModel('mail');
        $subject = $this->lang->effort->remindSubject;
        $domain  = zget($this->config->mail, 'domain', common::getSysURL());
        $link    = $domain . $this->createLink('effort', 'batchCreate', 'date=' . date('Ymd', $timestamp));
        $content = sprintf($this->lang->effort->remindContent, $link);

        foreach($noRecordUsers as $toList)
        {
            echo "Send to $toList\n";
            $this->mail->send($toList, $subject, $content);
            if($this->mail->isError()) error_log(join("\n", $this->mail->getError()));
        }
    }
}
