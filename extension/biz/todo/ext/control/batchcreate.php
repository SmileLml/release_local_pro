<?php
helper::importControl('todo');
class mytodo extends todo
{
    public function batchCreate($date = 'today', $userID = '')
    {
        if(!empty($_POST))
        {
            $this->todo->batchCreate();
            if(dao::isError()) die(js::error(dao::getError()));

            if(isonlybody())die(js::reload('parent.parent'));

            /* Locate the browser. */
            $date = str_replace('-', '', $this->post->date);
            if($date == '')
            {
                $date = 'future';
            }
            else if($date == date('Ymd'))
            {
                $date= 'today';
            }
            die(js::locate($this->createLink('my', 'todo', "type=$date"), 'parent'));
        }
        parent::batchCreate($date, $userID);
    }
}
