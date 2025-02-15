<?php
public function buildOperateViewMenu($task)
{
    if(!empty($task->confirmeObject))
    {
        $method = $task->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
        return $this->buildMenu('task', $method, "objectID=$task->id&object=task&extra={$task->confirmeObject['id']}", $task, 'view', 'search', '', 'iframe', true);
    }
    return parent::buildOperateViewMenu($task);
}
