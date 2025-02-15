<?php
class opsRelease extends releaseModel
{
    public function getPairsByProduct($productID, $branch = 0)
    {
        return $this->dao->select('*')->from(TABLE_RELEASE)
            ->where('product')->eq((int)$productID)
            ->beginIF($branch)->andWhere('branch')->eq($branch)->fi()
            ->andWhere('deleted')->eq(0)
            ->orderBy('date DESC')
            ->fetchPairs('id', 'name');
    }
}
