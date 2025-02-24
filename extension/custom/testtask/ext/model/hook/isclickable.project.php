<?php
global $config;
if(isset($config->CRProject) && empty($config->CRProject) && isset($testtask->canAction) && !$testtask->canAction)
{
    $action = strtolower($action);
    if(in_array($action, array('cases', 'linkcase', 'browse', 'edit', 'delete', 'copy'))) return false;
}