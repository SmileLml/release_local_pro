<?php
helper::importControl('bug');
class myBug extends bug
{
    public function confirmDemandUnlink($objectID = '', $objectType = '', $extra = '')
    {
        echo $this->fetch('story', 'confirmDemandUnlink', "objectID=$objectID&objectType=$objectType&extra=$extra");
    }
}
