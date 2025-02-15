<?php
helper::importControl('testcase');
class myTestcase extends testcase
{
    public function confirmDemandRetract($objectID = '', $objectType = '', $extra = '')
    {
        echo $this->fetch('story', 'confirmDemandRetract', "objectID=$objectID&objectType=$objectType&extra=$extra");
    }
}
