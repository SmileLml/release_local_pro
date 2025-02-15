<?php
public function getList($objectType, $objectID)
{
    return $this->loadExtension('feedback')->getList($objectType, $objectID);
}

public function getRelatedFields($objectType, $objectID, $actionType = '', $extra = '')
{
    return $this->loadExtension('feedback')->getRelatedFields($objectType, $objectID, $actionType, $extra);
}

public function printAction($action, $desc = '')
{
    return $this->loadExtension('feedback')->printAction($action, $desc);
}
