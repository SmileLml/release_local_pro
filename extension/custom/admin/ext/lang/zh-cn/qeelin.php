<?php
if($config->vision != 'lite')
{
    $lang->admin->menuList->feature['subMenu']['project'] = array('link' => "{$lang->projectCommon}|custom|project|");

    $lang->admin->menuList->feature['menuOrder']['5'] = 'my';
    $lang->admin->menuList->feature['menuOrder']['10'] = 'project';
    $lang->admin->menuList->feature['menuOrder']['15'] = 'product';
    $lang->admin->menuList->feature['menuOrder']['20'] = 'execution';
    $lang->admin->menuList->feature['menuOrder']['25'] = 'qa';
    $lang->admin->menuList->feature['menuOrder']['30'] = 'kanban';
    $lang->admin->menuList->feature['menuOrder']['35'] = 'doc';
    $lang->admin->menuList->feature['menuOrder']['50'] = 'user';

    $lang->admin->menuList->feature['subMenu']['execution']         = array('link' => "{$lang->execution->common}|custom|required|module=execution", 'exclude' => 'required,set', 'alias' => 'execution,limittaskdate,limitworkhour');
    $lang->admin->menuList->feature['tabMenu']['execution']['task'] = array('link' => "{$lang->task->common}|custom|required|module=task", 'links' => array('custom|set|module=task&field=priList', 'custom|limittaskdate|', 'custom|limitworkhour|'), 'alias' => 'limittaskdate,limitworkhour','exclude' => 'custom-required');
}