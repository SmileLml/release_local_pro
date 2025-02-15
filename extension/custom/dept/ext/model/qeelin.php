<?php

/**
 * Get user pairs of a department.
 *
 * @param  int    $deptID
 * @param  string $key     id|account
 * @param  string $type    inside|outside
 * @param  string $params  all
 * @access public
 * @return array
 */
public function getDeptUserPairs($deptID = 0, $key = 'account', $type = 'inside', $params = '')
{
    $childDepts = $this->getAllChildID($deptID);
    $keyField   = $key == 'id' ? 'id' : 'account';
    $type       = $type == 'outside' ? 'outside' : 'inside';
    $realname   = 'realname';
    if(strpos($params, 'withdeleted') !== false) $realname = "CONCAT('【', IF(slack !='', slack, '-'), '】' ,realname, IF(deleted='1' , '(deleted)', '')) AS realname";

    return $this->dao->select("$keyField, $realname")->from(TABLE_USER)
        ->where('1=1')
        ->beginIF(strpos($params, 'withdeleted') === false)->andWhere('deleted')->eq(0)->fi()
        ->beginIF(strpos($params, 'all') === false)->andWhere('type')->eq($type)->fi()
        ->beginIF($childDepts)->andWhere('dept')->in($childDepts)->fi()
        ->beginIF($deptID === '0')->andWhere('dept')->eq($deptID)->fi()
        ->beginIF($this->config->vision)->andWhere("CONCAT(',', visions, ',')")->like("%,{$this->config->vision},%")->fi()
        ->orderBy('account')
        ->fetchPairs();
}