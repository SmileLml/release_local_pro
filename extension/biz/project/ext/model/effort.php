<?php
public function getProjectsConsumed($projectIdList, $time = '')
{
    return $this->loadExtension('effort')->getProjectsConsumed($projectIdList, $time);
}
