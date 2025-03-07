<?php

class myeffort extends effort
{
    public function createForObject($objectType, $objectID, $from = '', $orderBy = '')
    {
        $this->view->hoursConsumedToday = $this->hoursConsumedToday = $this->effort->getAccountStatistics();
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
            $task->consumed = ceil($task->consumed * 100) / 100;
            $task->estimate = ceil($task->estimate * 100) / 100;
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
}