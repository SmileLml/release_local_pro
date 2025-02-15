<?php
helper::importControl('user');
class myuser extends user
{
    /**
     * User View.
     *
     * @param  int    $userID
     * @access public
     * @return void
     */
    public function view($userID)
    {
        if(common::hasPriv('user', 'todocalendar')) $this->locate(inlink('todocalendar', "userID=$userID"));
        parent::view($userID);
    }
}
