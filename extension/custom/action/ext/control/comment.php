<?php
helper::importControl('action');
class myaction extends action
{
    /**
     * Comment.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @access public
     * @return void
     */
    public function comment($objectType, $objectID)
    {
        if(!empty($_POST))
        {
            $isInZinPage = isInModal() || in_array($objectType, $this->config->action->newPageModule);

            if(strtolower($objectType) == 'task')
            {
                $task       = $this->loadModel('task')->getById($objectID);
                $executions = explode(',', $this->app->user->view->sprints);
                if(!in_array($task->execution, $executions))
                {
                    if($isInZinPage) return $this->send(array('result' => 'fail', 'message' => $this->lang->error->accessDenied));
                    return print(js::error($this->lang->error->accessDenied));
                }
            }
            elseif(strtolower($objectType) == 'story')
            {
                $story      = $this->loadModel('story')->getById($objectID);
                $executions = explode(',', $this->app->user->view->sprints);
                $products   = explode(',', $this->app->user->view->products);
                if(!array_intersect(array_keys($story->executions), $executions) and !in_array($story->product, $products) and empty($story->lib))
                {
                    if($isInZinPage) return $this->send(array('result' => 'fail', 'message' => $this->lang->error->accessDenied));
                    return print(js::error($this->lang->error->accessDenied));
                }
            }

            $comment = isset($this->post->actioncomment) ? $this->post->actioncomment : $this->post->comment;

            if($comment)
            {
                $actionID = $this->action->create($objectType, $objectID, 'Commented', $comment);
                if(empty($actionID))
                {
                    if($isInZinPage) return $this->send(array('result' => 'fail', 'message' => $this->lang->error->accessDenied));
                    return print(js::error($this->lang->error->accessDenied));
                }
                if(defined('RUN_MODE') && RUN_MODE == 'api')
                {
                    return $this->send(array('status' => 'success', 'data' => $actionID));
                }
            }

            if(isset($_FILES['files']))
            {
                $changes = array();
                if($objectType == 'task' || $objectType == 'bug')
                {
                    $oldObject = $this->loadModel($objectType)->getByID($objectID);
                    $this->loadModel('file')->saveUpload(strtolower($objectType), $objectID);
                    $newObject = $this->loadModel($objectType)->getByID($objectID);
                    $oldObject->files = join(',', array_keys($oldObject->files));
                    $newObject->files = join(',', array_keys($newObject->files));
                    $changes  = common::createChanges($oldObject, $newObject);
                }
                elseif($objectType == 'story')
                {
                    $oldObject   = $this->loadModel($objectType)->getByID($objectID);
                    $fileTitles  = $this->loadModel('file')->saveUpload(strtolower($oldObject->type), $objectID);
                    $data        = new stdclass();
                    $data->files = join(', ', array: array_keys($fileTitles)) . ', ' . trim(join(', ', array_keys($oldObject->files)), ',');
                    $this->dao->update(TABLE_STORYSPEC)->data($data)->where('story')->eq((int)$objectID)->andWhere('version')->eq($oldObject->version)->exec();
                    $newObject   = $this->loadModel($objectType)->getByID($objectID);
                    $oldObject->files = trim(join(', ', array_keys($oldObject->files)), ',');
                    $newObject->files = trim(join(', ', array_keys($newObject->files)), ',');
                    $changes = common::createChanges($oldObject, $newObject);
                }

                $actionID = $this->action->create(strtolower($objectType), $objectID, 'Edited');
                if(empty($actionID))
                {
                    if($isInZinPage) return $this->send(array('result' => 'fail', 'message' => $this->lang->error->accessDenied));
                    return print(js::error($this->lang->error->accessDenied));
                }
                else
                {
                    $this->action->logHistory($actionID, $changes);
                }
            }
            if($isInZinPage)
            {
                return $this->send(array('status' => 'success', 'closeModal' => true, 'callback' => array('name' => 'zui.HistoryPanel.update', 'params' => array('objectType' => $objectType, 'objectID' => (int)$objectID))));
            }
            echo js::reload('parent');
        }

        $this->view->title      = $this->lang->action->create;
        $this->view->objectType = $objectType;
        $this->view->objectID   = $objectID;
        $this->display();
    }
}