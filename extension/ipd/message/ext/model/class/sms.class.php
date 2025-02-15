<?php
class smsMessage extends messageModel
{
    public function send($objectType, $objectID, $actionType, $actionID, $actor = '', $extra = '')
    {
        parent::send($objectType, $objectID, $actionType, $actionID, $actor, $extra);

        $messageSetting = $this->config->message->setting;
        if(is_string($messageSetting)) $messageSetting = json_decode($messageSetting, true);
        if(isset($messageSetting['sms']))
        {
            $actions = $messageSetting['sms']['setting'];
            if(isset($actions[$objectType]) and in_array($actionType, $actions[$objectType]))
            {
                $this->loadModel('sms')->send($objectType, $objectID, $actionType);
            }
        }

    }
}
