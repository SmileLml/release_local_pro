<?php
public function delete($moduleID, $null = null)
{
    return $this->loadExtension('zentaobiz')->delete($moduleID);
}

public function getFeedbackTreeMenu($userFunc)
{
    return $this->loadExtension('zentaobiz')->getFeedbackTreeMenu($userFunc);
}

public function getTicketTreeMenu($userFunc)
{
    return $this->loadExtension('zentaobiz')->getTicketTreeMenu($userFunc);
}

public function getGroupTree($dimensionID = 0, $type = 'chart', $orderBy = 'id_desc', $pager = null)
{
    return $this->loadExtension('zentaobiz')->getGroupTree($dimensionID, $type, $orderBy, $pager);
}

public function getGroupStructure($dimensionID = 0, $groupID = 0, $type = 'chart')
{
    return $this->loadExtension('zentaobiz')->getGroupStructure($dimensionID, $groupID, $type);
}

public function getGroupPairs($dimensionID = 0, $parentGroup = 0, $grade = 2, $type = 'chart')
{
    return $this->loadExtension('zentaobiz')->getGroupPairs($dimensionID, $parentGroup, $grade, $type);
}

public function getPracticeTreeMenu($userFunc = '', $type = 'browse')
{
    return $this->loadExtension('zentaobiz')->getPracticeTreeMenu($userFunc, $type);
}
