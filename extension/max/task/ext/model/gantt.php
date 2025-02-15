<?php
public function start($taskID, $extra = '')
{
    return $this->loadExtension('gantt')->start($taskID, $extra);
}

public function finish($taskID, $extra = '')
{
    return $this->loadExtension('gantt')->finish($taskID, $extra);
}

public function checkDepend($taskID, $action = 'begin')
{
    return $this->loadExtension('gantt')->checkDepend($taskID, $action);
}
