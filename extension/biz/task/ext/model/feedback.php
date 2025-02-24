<?php
public function create($executionID = 0, $bugID = 0)
{
    return $this->loadExtension('qeelin')->create($executionID, $bugID);
}
