<?php
class zentaomaxBuild extends buildModel
{
    /**
     * Get execution build pairs with stage build of the same project.
     *
     * @param  int|array  $products
     * @param  string|int $branch
     * @param  string     $params   noempty|notrunk|noterminate|withbranch, can be a set of them
     * @param  string|int $objectID
     * @param  string     $objectType
     * @param  int|array  $buildIdList
     * @param  bool       $replace
     * @access public
     * @return array
     */
    public function getBuildPairs($products, $branch = 'all', $params = 'noterminate,nodone', $objectID = 0, $objectType = 'execution', $buildIdList = '', $replace = true)
    {
        $builds = parent::getBuildPairs($products, $branch, $params, $objectID, $objectType, $buildIdList, $replace);

        /* Get other stage builds in same project. */
        $otherStageBuilds = array();
        $execution        = $this->dao->select('id,project,type')->from(TABLE_EXECUTION)->where('id')->eq($objectID)->fetch();
        if($execution and $execution->type == 'stage')
        {
            $otherStages = $this->dao->select('id')->from(TABLE_EXECUTION)
                ->where('project')->eq($execution->project)
                ->andWhere('deleted')->eq(0)
                ->andWhere('id')->ne((int)$objectID)
                ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->sprints)->fi()
                ->fetchPairs('id');

            $executionBuilds = $this->dao->select('t1.id, t1.name, t1.execution, t2.status as executionStatus, t3.id as releaseID, t3.status as releaseStatus, t4.name as branchName')->from(TABLE_BUILD)->alias('t1')
                ->leftJoin(TABLE_EXECUTION)->alias('t2')->on('FIND_IN_SET(t2.id, t1.execution)')
                ->leftJoin(TABLE_RELEASE)->alias('t3')->on('t1.id = t3.build')
                ->leftJoin(TABLE_BRANCH)->alias('t4')->on('t1.branch = t4.id')
                ->where('t2.id')->in($otherStages)
                ->beginIF($products)->andWhere('t1.product')->in($products)->fi()
                ->beginIF($branch)->andWhere('t1.branch')->in("0,$branch")->fi()
                ->andWhere('t1.deleted')->eq(0)
                ->orderBy('t1.date desc, t1.id desc')->fetchAll('id');

            /* Set builds and filter terminate releases. */
            foreach($executionBuilds as $buildID => $build)
            {
                if(empty($build->releaseID) and (strpos($params, 'nodone') !== false) and ($build->executionStatus === 'done')) continue;
                if((strpos($params, 'noterminate') !== false) and ($build->releaseStatus === 'terminate')) continue;
                if(isset($builds[$buildID])) continue;
                $otherStageBuilds[$buildID] = $build->name;
            }
            if(empty($otherStageBuilds)) return $builds;

            /* if the build has been released, replace build name with release name. */
            $releases = $this->dao->select('t1.build, t1.name')->from(TABLE_RELEASE)->alias('t1')
                ->leftJoin(TABLE_BUILD)->alias('t2')->on('FIND_IN_SET(t2.id, t1.build)')
                ->where('t2.id')->in(array_keys($otherStageBuilds))
                ->beginIF($branch)->andWhere('t1.branch')->in("0,$branch")->fi()
                ->andWhere('t1.deleted')->eq(0)
                ->fetchPairs();
            foreach($releases as $buildIdList => $releaseName)
            {
                foreach(explode(',', trim($buildIdList, ',')) as $buildID)
                {
                    if(empty($buildID)) continue;
                    if(isset($builds[$buildID])) continue;
                    $otherStageBuilds[$buildID] = $releaseName;
                }
            }
        }

        return $builds + $otherStageBuilds;
    }
}
