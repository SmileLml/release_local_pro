<?php
class effort extends control
{
    public function ajaxGetEffortsByMonth($userID = '', $year = '', $month = '')
    {
        $account = '';
        if($userID)
        {
            $user    = $this->loadModel('user')->getById($userID, 'id');
            $account = $user->account;
        }

        die($this->effort->ajaxGetEffortsByMonth($account, $year, $month));
    }
}
