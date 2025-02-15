<?php
helper::importControl('install');
class myInstall extends install
{
    public function step5()
    {
        if($_POST and !extension_loaded('ionCube Loader')) return print(js::alert($this->lang->install->uninstallLoader));

        return parent::step5();
    }
}
