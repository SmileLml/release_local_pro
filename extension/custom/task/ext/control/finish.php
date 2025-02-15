<?php

helper::importControl('task');

class mytask extends task
{
    public function finish($taskID, $extra = '')
    {
        $this->view->hoursConsumed = $this->loadModel('effort')->getAccountStatistics();
        parent::finish($taskID, $extra);
    }
}