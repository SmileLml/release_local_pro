<?php
helper::importControl('task');
class myTask extends task
{
    public function confirmDemandUnlink($objectID = '', $objectType = '', $extra = '')
    {
        echo $this->fetch('story', 'confirmDemandUnlink', "objectID=$objectID&objectType=$objectType&extra=$extra");
    }
}
