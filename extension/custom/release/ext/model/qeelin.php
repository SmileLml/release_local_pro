<?php
/**
 * Get released builds from product.
 *
 * @param  int        $productID
 * @param  string|int $branch
 * @access public
 * @return void
 */
public function getReleasedBuilds($productID, $branch = 'all')
{
    $releases = $this->dao->select('branch,shadow,build')->from(TABLE_RELEASE)
        ->where('deleted')->eq(0)
        ->andWhere('product')->eq($productID)
        ->fetchAll();

    $buildIdList = array();
    foreach($releases as $release)
    {
        if($branch != 'all' and $branch !== '')
        {
            $inBranch = false;
            foreach(explode(',', trim($release->branch, ',')) as $branchID)
            {
                if($branchID === '') continue;

                if(strpos(",{$branch},", ",{$branchID},") !== false) $inBranch = true;
            }
            if(!$inBranch) continue;
        }

        $builds = explode(',', $release->build);
        $buildIdList   = array_merge($buildIdList, $builds);
        $buildIdList[] = (string)$release->shadow;
    }
    return $buildIdList;
}