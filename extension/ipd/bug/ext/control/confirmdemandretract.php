<?php
helper::importControl('bug');
class myBug extends bug
{
    public function confirmDemandRetract($objectID = '', $objectType = '', $extra = '')
    {
        echo $this->fetch('story', 'confirmDemandRetract', "objectID=$objectID&objectType=$objectType&extra=$extra");
    }
}
