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
            $taskIDS    = explode(',', string: $this->post->taskIDS);
            $taskLists  = $this->task->getByList($taskIDS);
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
            $idLists = array_keys($taskLists);
        }

        if(!$this->post->comment) return print(js::error($this->lang->action->commentNull));

        foreach($idLists as $id) $actionID = $this->action->create(strtolower($objectType), $id, 'Commented', $this->post->comment);

        die(js::reload('parent'));
    }
}