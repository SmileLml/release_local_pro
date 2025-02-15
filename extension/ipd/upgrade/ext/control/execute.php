<?php
helper::importControl('upgrade');
class myupgrade extends upgrade
{
    public function execute($fromVersion = '')
    {
        parent::execute($fromVersion);
    }
}
