<?php

public function checkReleaseByBuild($projectID, $productID, $buildID)
{
    return $this->dao->select('id')->from(TABLE_RELEASE)
    ->where('deleted')->eq(0)
    ->andWhere("FIND_IN_SET($projectID, project)")
    ->andWhere('product')->eq($productID)
    ->andWhere("FIND_IN_SET($buildID, build)")
    ->count();
}

/**
 * Get builds in pairs.
 *
 * @param int|array  $products
 * @param string|int $branch
 * @param string     $params   noempty|notrunk|noterminate|withbranch|hasproject|noDeleted|singled|noreleased|releasedtag, can be a set of them
 * @param string|int $objectID
 * @param string     $objectType
 * @param int|array  $buildIdList
 * @param bool       $replace
 * @access public
 * @return array
 */
public function getBuildPairs($products, $branch = 'all', $params = 'noterminate, nodone', $objectID = 0, $objectType = 'execution', $buildIdList = '', $replace = true)
{
    $branch         = trim($branch, ',');
    $sysBuilds      = array();
    $selectedBuilds = array();
    if(strpos($params, 'noempty') === false) $sysBuilds = array('' => '');
    if(strpos($params, 'notrunk') === false) $sysBuilds = $sysBuilds + array('trunk' => $this->lang->trunk);
    $productIdList = is_array($products) ? array_keys($products) : $products;
    if($buildIdList)
    {
        $buildIdList = str_replace('trunk', '0', $buildIdList);
        $selectedBuilds = $this->dao->select('id, name')->from(TABLE_BUILD)
            ->where('id')->in($buildIdList)
            ->beginIF($products and $products != 'all')->andWhere('product')->in($productIdList)->fi()
            ->beginIF($objectType === 'execution' and $objectID)->andWhere('execution')->eq($objectID)->fi()
            ->beginIF($objectType === 'project' and $objectID)->andWhere('project')->eq($objectID)->fi()
            ->beginIF(strpos($params, 'hasdeleted') === false)->andWhere('deleted')->eq(0)->fi()
            ->fetchPairs();
    }
    $branchPairs = $this->dao->select('id,name')->from(TABLE_BRANCH)->fetchPairs();

    $shadows   = $this->dao->select('shadow')->from(TABLE_RELEASE)->where('product')->in($productIdList)->fetchPairs('shadow', 'shadow');
    $branchs   = strpos($params, 'separate') === false ? "0,$branch" : $branch;

    $allBuilds = $this->dao->select('t1.id, t1.name, t1.branch, t1.execution, t1.date, t1.deleted, t2.status as objectStatus, t3.id as releaseID, t3.status as releaseStatus, t4.type as productType')->from(TABLE_BUILD)->alias('t1')
        ->beginIF($objectType === 'execution')->leftJoin(TABLE_EXECUTION)->alias('t2')->on('t1.execution = t2.id')->fi()
        ->beginIF($objectType === 'project')->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')->fi()
        ->leftJoin(TABLE_RELEASE)->alias('t3')->on("FIND_IN_SET(t1.id,t3.build)")
        ->leftJoin(TABLE_PRODUCT)->alias('t4')->on('t1.product = t4.id')
        ->where('1=1')
        ->beginIf(!empty($shadows))->andWhere('t1.id')->notIN($shadows)->fi()
        ->beginIF(strpos($params, 'hasdeleted') === false)->andWhere('t1.deleted')->eq(0)->fi()
        ->beginIF(strpos($params, 'hasproject') !== false)->andWhere('t1.project')->ne(0)->fi()
        ->beginIF(strpos($params, 'singled') !== false)->andWhere('t1.execution')->ne(0)->fi()
        ->beginIF(strpos($params, 'projectclosefilter') !== false)->andWhere('t2.status')->ne('closed')->fi()
        ->beginIF($products and $products != 'all')->andWhere('t1.product')->in($productIdList)->fi()
        ->beginIF($objectType === 'execution' and $objectID)->andWhere('t1.execution')->eq($objectID)->fi()
        ->beginIF($objectType === 'project' and $objectID)->andWhere('t1.project')->eq($objectID)->fi()
        ->orderBy('t1.date desc, t1.id desc')->fetchAll('id');


    $deletedExecutions = $this->dao->select('id, deleted')->from(TABLE_EXECUTION)->where('type')->eq('sprint')->andWhere('deleted')->eq('1')->fetchPairs();

    /* Set builds and filter done executions and terminate releases. */
    $builds      = array();
    $buildIdList = array();
    $this->app->loadLang('branch');
    foreach($allBuilds as $id => $build)
    {
        if($build->branch === '') $build->branch = 0;
        if(empty($build->releaseID) and (strpos($params, 'nodone') !== false) and ($build->objectStatus === 'done')) continue;
        if((strpos($params, 'noterminate') !== false) and ($build->releaseStatus === 'terminate')) continue;
        if((strpos($params, 'withexecution') !== false) and $build->execution and isset($executions[$build->execution])) continue;
        if($branch !== 'all' and strpos(",{$build->branch},", ",{$branch},") === false) continue;

        if($build->deleted == 1) $build->name .= ' (' . $this->lang->build->deleted . ')';

        if(!empty($build->branch))
        {
            $branchName = '';
            foreach(explode(',', $build->branch) as $buildBranch)
            {
                if(empty($buildBranch))
                {
                    $branchName .= $this->lang->branch->main;
                }
                else
                {
                    $branchName .= isset($branchPairs[$buildBranch]) ? $branchPairs[$buildBranch] : '';
                }
                $branchName .= ',';
            }

            $branchName = trim($branchName, ',');
        }
        else
        {
            $branchName = $this->lang->branch->main;
        }

        $buildName = $build->name;
        if(strpos($params, 'withbranch') !== false and $build->productType != 'normal') $buildName = $branchName . '/' . $buildName;

        $buildIdList[$id] = $id;
        $builds[$build->date][$id] = $buildName;
    }

    if(empty($builds) and empty($shadows)) return $sysBuilds + $selectedBuilds;

    /* if the build has been released and replace is true, replace build name with release name. */
    if($replace)
    {
        $releases = $this->dao->select('t1.id,t1.shadow,t1.product,t1.branch,t1.build,t1.name,t1.date,t3.name as branchName,t4.type as productType')->from(TABLE_RELEASE)->alias('t1')
            ->leftJoin(TABLE_BUILD)->alias('t2')->on('FIND_IN_SET(t2.id, t1.build)')
            ->leftJoin(TABLE_BRANCH)->alias('t3')->on('FIND_IN_SET(t3.id, t1.branch)')
            ->leftJoin(TABLE_PRODUCT)->alias('t4')->on('t1.product=t4.id')
            ->where('t2.id')->in($buildIdList)
            ->andWhere('t1.product')->in($productIdList)
            ->andWhere('t1.deleted')->eq(0)
            ->andWhere('t1.shadow')->ne(0)
            ->fetchAll('id');
        if($shadows)
        {
            /* Append releases of only shadow and not link build. */
            $releases += $this->dao->select('t1.id,t1.shadow,t1.product,t1.branch,t1.build,t1.name,t1.date,t2.name as branchName,t3.type as productType')->from(TABLE_RELEASE)->alias('t1')
                ->leftJoin(TABLE_BRANCH)->alias('t2')->on('FIND_IN_SET(t2.id, t1.branch)')
                ->leftJoin(TABLE_PRODUCT)->alias('t3')->on('t1.product=t3.id')
                ->where('t1.shadow')->in($shadows)
                ->andWhere('t1.build')->eq(0)
                ->andWhere('t1.deleted')->eq(0)
                ->fetchAll('id');
        }
        foreach($releases as $release)
        {
            if($branch !== 'all')
            {
                $inBranch = false;
                foreach(explode(',', trim($release->branch, ',')) as $branchID)
                {
                    if($branchID === '') continue;
                    if(strpos(",{$branchs},", ",{$branchID},") !== false) $inBranch = true;
                }
                if(!$inBranch) continue;
            }

            $releaseName = $release->name;
            $branchName  = $release->branchName ? $release->branchName : $this->lang->branch->main;
            if($release->productType != 'normal') $releaseName = (strpos($params, 'withbranch') !== false ? $branchName . '/' : '') . $releaseName;
            if(strpos($params, 'releasetag') !== false) $releaseName = $releaseName . " [{$this->lang->build->released}]";
            $builds[$release->date][$release->shadow] = $releaseName;
            foreach(explode(',', trim($release->build, ',')) as $buildID)
            {
                if(!isset($allBuilds[$buildID])) continue;
                $build = $allBuilds[$buildID];
                if(strpos($params, 'noreleased') !== false) unset($builds[$build->date][$buildID]);
            }
        }
    }

    krsort($builds);
    $buildPairs = array();
    foreach($builds as $date => $childBuilds) $buildPairs += $childBuilds;

    return $sysBuilds + $buildPairs + $selectedBuilds;
}