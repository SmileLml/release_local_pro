<?php
helper::importControl('task');
class myTask extends task
{
    public function confirmDemandRetract($objectID = '', $objectType = '', $extra = '')
    {
        echo $this->fetch('story', 'confirmDemandRetract', "objectID=$objectID&objectType=$objectType&extra=$extra");
    }
}
