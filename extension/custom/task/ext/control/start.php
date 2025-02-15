<?php

helper::importControl('task');

class mytask extends task
{
    public function start($taskID, $extra = '')
    {
        $this->view->hoursConsumed = $this->loadModel('effort')->getAccountStatistics();
        parent::start($taskID, $extra);
    }
}