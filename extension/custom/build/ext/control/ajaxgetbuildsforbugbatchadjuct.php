<?php

helper::importControl('build');

class mybuild extends build
{
    public function ajaxGetBuildsForBugBatchAdjuct($productID, $bugID, $branchID = 0, $projectID = 0, $build = '')
    {
        $builds = $this->build->getBuildPairs($productID, $branchID, 'noempty,noterminate,nodone,withbranch,noreleased', $projectID, 'project', $build);
        return print(html::select("build[$bugID][]", $builds, $build, 'size=4 class=form-control multiple'));
    }
}