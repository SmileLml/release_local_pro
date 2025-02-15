<?php
class myMessage extends message
{
    public function setting()
    {
        $approvalflows = $this->dao->select('module')->from(TABLE_WORKFLOW)->where('approval')->eq('enabled')->fetchAll();
        foreach($approvalflows as $flow)
        {
            $this->config->message->objectTypes[$flow->module][] = 'submit';
            $this->config->message->objectTypes[$flow->module][] = 'cancel';
            $this->config->message->objectTypes[$flow->module][] = 'review';

            foreach(array('message', 'mail', 'sms', 'xuanxuan', 'webhook') as $module)
            {
                $this->config->message->available[$module][$flow->module][] = 'submit';
                $this->config->message->available[$module][$flow->module][] = 'cancel';
                $this->config->message->available[$module][$flow->module][] = 'review';
            }
        }

        parent::setting();
    }
}
