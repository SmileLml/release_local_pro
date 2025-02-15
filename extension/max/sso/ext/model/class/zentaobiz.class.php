<?php
class zentaobizSso extends ssoModel
{
    public function bind()
    {
        if($this->post->bindType == 'add' and $this->loadModel('user')->checkBizUserLimit('user'))
        {
            dao::$errors['password1'][] = $this->lang->user->noticeUserLimit;
            return false;
        }
        return parent::bind();
    }

    public function createUser()
    {
        if($this->loadModel('user')->checkBizUserLimit('user')) return array('status' => 'fail', 'data' => $this->lang->user->noticeUserLimit);
        return parent::createUser();
    }
}
