<?php
helper::importControl('attend');
class myAttend extends attend
{
    public function review($attendID, $reviewStatus = '')
    {
        $this->view->reviewStatus = $reviewStatus;
        if(empty($reviewStatus))
        {
            $deptList = $this->loadModel('dept')->getPairs('', 'dept');
            $deptList['0'] = '/';

            $this->view->title    = $this->lang->attend->review;
            $this->view->attend   = $this->attend->getByID($attendID);
            $this->view->users    = $this->loadModel('user')->getPairs('noletter');
            $this->view->user     = $this->user->getById($this->view->attend->account);
            $this->view->deptList = $deptList;
            $this->display();
        }
        else
        {
            return parent::review($attendID, $reviewStatus);
        }
    }
}
