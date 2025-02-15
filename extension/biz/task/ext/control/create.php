<?php
helper::importControl('task');
class myTask extends task
{
    public function create($executionID = 0, $storyID = 0, $moduleID = 0, $taskID = 0, $todoID = 0, $extras = '', $bugID = 0)
    {
        $extras = str_replace(array(',', ' '), array('&', ''), $extras);
        parse_str($extras, $params);
        foreach($params as $varName => $varValue) $$varName = $varValue;

        if(!empty($feedbackID))
        {
            $feedback = $this->loadModel('feedback')->getById($feedbackID);
            $actions  = $this->loadModel('action')->getList('feedback', $feedbackID);
            $this->feedback->setMenu($feedback->product);
            if(!$feedback) die(js::error($this->lang->notFound) . js::locate('back', 'parent'));
            foreach($actions as $action)
            {
                if($action->action == 'reviewed' and $action->comment)
                {
                    $feedback->desc .= $feedback->desc ? '<br/>' . $this->lang->feedback->reviewOpinion . '：' . $action->comment : $this->lang->feedback->reviewOpinion . '：' . $action->comment;
                }
            }

            $this->view->users = $this->loadModel('user')->getPairs('devfirst|noclosed|nodeleted');

            if(!empty($_POST))
            {
                $response['result']  = 'success';
                $response['message'] = $this->lang->saveSuccess;
                setcookie('lastTaskModule', (int)$this->post->module, $this->config->cookieLife, $this->config->webRoot, '', false, false);

                if($this->post->execution) $executionID = (int)$this->post->execution;
                if(!$executionID)
                {
                    $response['result']  = 'fail';
                    $response['message'] = array('execution' => array(0 => $this->lang->task->noExecution));
                    return $this->send($response);
                }

                $tasksID = $this->task->create($executionID);
                if(dao::isError())
                {
                    $response['result']  = 'fail';
                    $response['message'] = dao::getError();
                    return $this->send($response);
                }

                $response['locate'] = $this->createLink('feedback', 'adminView', "feedbackID=$feedbackID");
                if(isonlybody()) $response['locate'] = 'parent';
                if(count($tasksID) == 1)
                {
                    $taskID = reset($tasksID);
                    if($taskID['status'] == 'exists')
                    {
                        $response['message'] = sprintf($this->lang->duplicate, $this->lang->task->common);
                        return $this->send($response);
                    }
                }

                $this->loadModel('action');
                foreach($tasksID as $taskID)
                {
                    /* if status is exists then this task has exists not new create. */
                    if($taskID['status'] == 'exists') continue;

                    $taskID   = $taskID['id'];
                    $actionID = $this->action->create('task', $taskID, 'FromFeedback', '', $feedbackID);
                    $this->loadModel('mail')->sendmail($taskID, $actionID);
                }

                $message = $this->executeHooks($taskID);
                if($message) $this->lang->saveSuccess = $message;

                /* Return task id when call the API. */
                if($this->viewType == 'json' or (defined('RUN_MODE') && RUN_MODE == 'api')) return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'id' => $taskID));

                return $this->send($response);
            }

            /* Init vars. */
            $task = new stdClass();
            $task->module     = $moduleID;
            $task->assignedTo = '';
            $task->name       = $feedback->title;
            $task->story      = $storyID;
            $task->type       = '';
            $task->pri        = '3';
            $task->estimate   = '';
            $task->desc       = $feedback->desc;
            $task->estStarted = '';
            $task->deadline   = '';
            $task->mailto     = '';
            $task->color      = '';

            $showAllModule    = isset($this->config->execution->task->allModule) ? $this->config->execution->task->allModule : '';
            $moduleOptionMenu = $this->tree->getTaskOptionMenu($executionID, 0, 0, $showAllModule ? 'allModule' : '');
            $task->module     = $task->module ? $task->module : (int)$this->cookie->lastTaskModule;
            if(!isset($moduleOptionMenu[$task->module])) $task->module = 0;

            $projectID     = isset($projectID) ? $projectID : 0;
            $executions    = $this->execution->getPairs($projectID);
            $moduleIdList  = $task->module ? $this->tree->getAllChildID($moduleID) : array();
            $stories       = $this->story->getExecutionStoryPairs($executionID, 0, 'all', $moduleIdList, '', 'active');

            $executionID = $this->execution->saveState($executionID, $executions);
            $execution   = $this->execution->getById($executionID);

            $lifetimeList  = array();
            $attributeList = array();
            $executionList = $this->execution->getByIdList(array_keys($executions));
            foreach($executionList as $id => $object)
            {
                $lifetimeList[$id]  = $object->lifetime;
                $attributeList[$id] = $object->attribute;
            }

            $testStoryIdList = $this->loadModel('story')->getTestStories(array_keys($stories), $execution->id);
            /* Stories that can be used to create test tasks. */
            $testStories     = array();
            foreach($stories as $storyID => $storyTitle)
            {
                if(empty($storyID) or isset($testStoryIdList[$storyID])) continue;
                $testStories[$storyID] = $storyTitle;
            }

            if($execution->type == 'kanban')
            {
                $this->loadModel('kanban');

                $regionPairs = $this->kanban->getRegionPairs($execution->id, 0, 'execution');
                $regionID    = !empty($output['regionID']) ? $output['regionID'] : key($regionPairs);
                $lanePairs   = $this->kanban->getLanePairsByRegion($regionID, 'task');
                $laneID      = isset($output['laneID']) ? $output['laneID'] : key($lanePairs);

                $this->view->regionID    = $regionID;
                $this->view->laneID      = $laneID;
                $this->view->regionPairs = $regionPairs;
                $this->view->lanePairs   = $lanePairs;
            }

            /* Set Menu. */
            $this->lang->feedback->menu->browse['subModule'] = 'task';

            foreach(explode(',', $this->config->task->customCreateFields) as $field) $customFields[$field] = $this->lang->task->$field;

            /* Get block id of assinge to me. */
            $blockID = 0;
            if(isonlybody())
            {
                $blockID = $this->dao->select('id')->from(TABLE_BLOCK)
                    ->where('block')->eq('assingtome')
                    ->andWhere('module')->eq('my')
                    ->andWhere('account')->eq($this->app->user->account)
                    ->orderBy('order_desc')
                    ->fetch('id');
            }

            $this->view->customFields = $customFields;
            $this->view->showFields   = $this->config->task->custom->createFields;

            $this->view->title            = $execution->name . $this->lang->colon . $this->lang->task->create;
            $this->view->users            = $this->loadModel('user')->getPairs('devfirst|noclosed|nodeleted');
            $this->view->executions       = array('' => '') + $executions;
            $this->view->moduleID         = $task->module;
            $this->view->execution        = $execution;
            $this->view->executionID      = $executionID;
            $this->view->feedbackID       = $feedbackID;
            $this->view->sourceFiles      = $feedback->files;
            $this->view->taskID           = $taskID;
            $this->view->blockID          = $blockID;
            $this->view->stories          = $stories;
            $this->view->gobackLink       = (isset($output['from']) and $output['from'] == 'global') ? $this->createLink('execution', 'task', "executionID=$executionID") : '';
            $this->view->taskTitle        = $feedback->title;
            $this->view->lifetimeList     = $lifetimeList;
            $this->view->attributeList    = $attributeList;
            $this->view->task             = $task;
            $this->view->members          = $this->user->getTeamMemberPairs($executionID, 'execution', 'nodeleted');
            $this->view->steps            = htmlspecialchars($feedback->desc);
            $this->view->showAllModule    = $showAllModule;
            $this->view->moduleOptionMenu = $moduleOptionMenu;
            $this->view->testStories      = $testStories;
            $this->view->testStoryIdList  = $testStoryIdList;
            $this->view->features         = $this->execution->getExecutionFeatures($execution);

            $this->display();
        }
        else
        {
            return parent::create($executionID, $storyID, $moduleID, $taskID, $todoID, $extras, $bugID);
        }
    }
}
