<?php
public function grantPriv()
{
    parent::grantPriv();
    if(dao::isError()) return false;
    $this->loadModel('upgrade')->importBuildinModules();
    $this->loadModel('upgrade')->importLiteModules();
    $this->loadModel('upgrade')->addSubStatus();
}
