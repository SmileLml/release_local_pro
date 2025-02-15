<?php
helper::importControl('user');
class myuser extends user
{
    public function logout($referer = 0)
    {
        $this->app->loadModuleConfig('attend');
        /* Save sign out info. */
        $this->loadModel('attend')->signOut();
        return parent::logout($referer);
    }
}
