<?php
public function send($objectType, $objectID, $actionType, $actionID, $actor = '', $extra = '')
{
    return $this->loadExtension('sms')->send($objectType, $objectID, $actionType, $actionID, $actor, $extra);
}
