<?php
helper::importControl('user');
class myuser extends user
{
    public function batchCreate($deptID = 0)
    {
        $properties = array();
        if(function_exists('ioncube_license_properties')) $properties = ioncube_license_properties();
        if($this->config->edition != 'open' and isset($properties['user']))
        {
            $userCount = $this->dao->select("COUNT('*') as count")->from(TABLE_USER)->where('deleted')->eq(0)->fetch('count');
            $maxUser   = $properties['user']['value'] <= $userCount;
            if($maxUser)
            {
                echo js::alert($this->lang->user->noticeUserLimit);
                echo js::locate('back');
                return;
            }

            $this->view->userAddWarning = $this->user->getAddUserWarning();

            if($_POST)
            {
                foreach($this->post->account as $i => $account)
                {
                    if(empty($account)) continue;
                    if(join(',', $_POST['visions'][$i]) == 'ditto') $_POST['visions'][$i] = $_POST['visions'][($i - 1)];

                    if(!$maxUser)
                    {
                        $userCount ++;
                        if($properties['user']['value'] <= $userCount) $maxUser = true;
                    }
                    else
                    {
                        $_POST['account'][$i] = '';
                    }
                }
            }
        }

        $this->view->properties = $properties;
        return parent::batchCreate($deptID);
    }
}
