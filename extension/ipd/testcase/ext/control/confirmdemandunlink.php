<?php
helper::importControl('testcase');
class myTestcase extends testcase
{
    public function confirmDemandUnlink($objectID = '', $objectType = '', $extra = '')
    {
        echo $this->fetch('story', 'confirmDemandUnlink', "objectID=$objectID&objectType=$objectType&extra=$extra");
    }
}
