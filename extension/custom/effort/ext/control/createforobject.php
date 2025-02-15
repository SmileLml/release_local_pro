<?php

class myeffort extends effort
{
    public function createForObject($objectType, $objectID, $from = '', $orderBy = '')
    {
        $this->view->hoursConsumedToday = $this->hoursConsumedToday = $this->effort->getAccountStatistics();
        parent::createForObject($objectType, $objectID, $from, $orderBy);
    }
}