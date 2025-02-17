<?php
helper::importControl('action');
class myaction extends action
{
    public function batchComment($objectType)
    {
        $idLists = array();

        if(strtolower($objectType) == 'task' && $this->post->taskIDS)
        {
            $this->loadModel('task');
            $idLists    = explode(',', string: $this->post->taskIDS);
            $taskLists  = $this->task->getByList($idLists);
            $executions = explode(',', $this->app->user->view->sprints);

            foreach($taskLists as $id => $task)
            {
                if(!common::canBeChanged('task', $task))
                {
                    return print(js::error(sprintf($this->lang->task->batchComment->taskNoCanChange, $id)));
                }
                if(!in_array($task->execution, $executions) or !commonModel::hasPriv('action', 'comment', $task))
                {
                    return print(js::error(sprintf($this->lang->task->batchComment->taskNoAccessDenied, $id)));
                }
            }
        }
        elseif(strtolower($objectType) == 'story' && $this->post->storyIDS)
        {
            $this->loadModel('story');
            $accessDenied = [];
            $idLists      = explode(',', string: $this->post->storyIDS);
            $executions   = explode(',', $this->app->user->view->sprints);
            $products     = explode(',', $this->app->user->view->products);
            foreach($idLists as $storyID)
            {
                $story = $this->story->getById($storyID);
                if(!array_intersect(array_keys($story->executions), $executions) and !in_array($story->product, $products) and empty($story->lib)) $accessDenied[] = $storyID;
            }
            if($accessDenied) return print(js::error(sprintf($this->lang->story->batchComment->storyNoAccessDenied, implode(',', $accessDenied))));
        }

        if(!$this->post->comment) return print(js::error($this->lang->action->commentNull));

        foreach($idLists as $id) $actionID = $this->action->create(strtolower($objectType), $id, 'Commented', $this->post->comment);

        die(js::reload('parent'));
    }
}