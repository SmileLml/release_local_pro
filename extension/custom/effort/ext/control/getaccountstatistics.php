<?php
helper::importControl('effort');
class myeffort extends effort
{
    public function getAccountStatistics($account = '', $date = 'today')
    {
        $consumed = $this->effort->getAccountStatistics($account, $date);
        if(helper::isAjaxRequest())
        {
            return print($consumed);
        }
        return $consumed;
    }
}
