<?php
class ganttTask extends taskModel
{
    public function start($taskID, $extra = '')
    {
        $message = $this->checkDepend($taskID, 'begin');
        if($message) die(js::alert($message));

        if($this->post->left == 0)
        {
            $task = $this->getById($taskID);
            $lastMember = array();
            if($task->mode == 'linear') $lastMember = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('task')->eq($taskID)->orderBy('order desc')->limit(1)->fetch();
            if(empty($lastMember) or $lastMember->account == $this->app->user->account)
            {
                $message = $this->checkDepend($taskID, 'end');
                if($message) die(js::alert($message));
            }
        }

        return parent::start($taskID, $extra);
    }

    public function finish($taskID, $extra = '')
    {
        $message = $this->checkDepend($taskID, 'end');
        if($message) die(js::alert($message));

        return parent::finish($taskID, $extra);
    }

    public function checkDepend($taskID, $action = 'begin')
    {
        $actions = $action;
        if($action == 'end') $actions = 'begin,end';

        $relations     = $this->dao->select('*')->from(TABLE_RELATIONOFTASKS)->where('task')->eq($taskID)->andWhere('action')->in($actions)->fetchAll('pretask');
        $relationTasks = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in(array_keys($relations))->fetchAll('id');
        $task          = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in($taskID)->fetch();

        $message = '';
        foreach($relations as $id => $relation)
        {
            $pretask = $relationTasks[$id];
            if($pretask->deleted) continue;
            if($action != $relation->action and $task->status != 'wait') continue;
            if($relation->condition == 'begin' and helper::isZeroDate($pretask->realStarted) and empty($pretask->finishedBy))
            {
                $noticeType = $action == 'begin' ? 'notSS' : 'notSF';
                $message .= sprintf($this->lang->task->gantt->notice->$noticeType, "$id::" . $pretask->name) . '\n';
            }
            elseif($relation->condition == 'end' and empty($pretask->finishedBy))
            {
                $noticeType = $action == 'begin' ? 'notFS' : 'notFF';
                $message .= sprintf($this->lang->task->gantt->notice->$noticeType, "$id::" . $pretask->name) . '\n';
            }
        }

        return $message;
    }

    public function addTaskEstimate($data)
    {
        if(file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'effort.class.php'))
        {
            return $this->loadExtension('effort')->addTaskEstimate($data);
        }

        return parent::addTaskEstimate($data);
    }
}
