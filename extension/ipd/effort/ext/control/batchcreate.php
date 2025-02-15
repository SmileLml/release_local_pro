<?php
helper::importControl('effort');
class myeffort extends effort
{
    public function batchCreate($date = 'today', $userID = '')
    {
        if(!empty($_POST))
        {
            $this->effort->batchCreate();
            if(dao::isError()) die(js::error(dao::getError()));

            if(isonlybody()) die(js::closeModal('parent.parent', '', "function(){if(typeof(parent.parent.refreshCalendar) == 'function'){parent.parent.refreshCalendar()}else{parent.parent.location.reload(true)}}"));
            die(js::locate($this->createLink('my', 'effort'), 'parent'));
        }
        parent::batchCreate($date, $userID);
    }
}
