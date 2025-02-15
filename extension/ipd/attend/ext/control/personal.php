<?php
helper::importControl('attend');
class myAttend extends attend
{
    /**
     * personal
     *
     * @param  string $date
     * @access public
     * @return void
     */
    public function personal($date = '')
    {
        if(isset($this->config->attend->noAttendUsers) and strpos(",{$this->config->attend->noAttendUsers},", ",{$this->app->user->account},") !== false)
        {
            return print(js::locate($this->createLink('attend', 'department')));
        }

        return parent::personal($date);
    }
}
