<?php
if(helper::hasFeature('devops'))
{
    $lang->devops->menu->code   = array('link' => "{$lang->repo->common}|repo|browse|repoID=%s", 'alias' => 'diff,view,revision,log,blame,showsynccommit');
    $lang->devops->menu->review = array('link' => '问题|repo|review|repoID=%s', 'subModule' => 'bug');
    $lang->devops->menuOrder[16] = 'review';

    $lang->execution->menu->devops['subMenu']->review = '问题|repo|review|repoID=0&browseType=all&executionID=%s';
    $lang->execution->menu->devops['menuOrder'][10] = 'review';
}
