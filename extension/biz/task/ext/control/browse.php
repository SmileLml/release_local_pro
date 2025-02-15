<?php
class task extends control
{
    public function browse()
    {
        $this->locate($this->createLink('project', 'task'));
    }
}
