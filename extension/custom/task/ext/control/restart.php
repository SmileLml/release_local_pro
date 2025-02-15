<?php

helper::importControl('task');

class mytask extends task
{
    public function restart($taskID, $extra = '')
    {
        $this->view->hoursConsumed = $this->loadModel('effort')->getAccountStatistics();
        parent::restart($taskID, $extra);
    }
}