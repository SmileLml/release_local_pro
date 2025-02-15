<?php

/**
 * Get assigned by me objects.
 *
 * @param string $account
 * @param int    $limit
 * @param string $orderBy
 * @param int    $pager
 * @param int    $projectID
 * @param string $objectType
 * @access public
 * @return array
 */
public function getAssignedByMe($account, $limit = 0, $pager = null, $orderBy = "id_desc", $objectType = '')
{
    $module = $objectType == 'requirement' ? 'story' : $objectType;
    $this->loadModel($module);

    $objectIDList = $this->dao->select('objectID')->from(TABLE_ACTION)
        ->where('actor')->eq($account)
        ->andWhere('objectType')->eq($module)
        ->andWhere('action')->eq('assigned')
        ->fetchAll('objectID');
    if(empty($objectIDList)) return array();

    if($objectType == 'task')
    {
        $orderBy    = strpos($orderBy, 'pri_') !== false ? str_replace('pri_', 'priOrder_', $orderBy) : 't1.' . $orderBy;
        $objectList = $this->dao->select("t1.*, t3.id as project, t2.name as executionName, t2.multiple as executionMultiple, t3.name as projectName, t2.type as executionType, IF(t1.`pri` = 0, {$this->config->maxPriValue}, t1.`pri`) as priOrder")->from($this->config->objectTables[$module])->alias('t1')
            ->leftJoin(TABLE_EXECUTION)->alias('t2')->on("t1.execution = t2.id")
            ->leftJoin(TABLE_PROJECT)->alias('t3')->on("t2.project = t3.id")
            ->where('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq(0)
            ->andWhere('t1.id')->in(array_keys($objectIDList))
            ->orderBy($orderBy)
            ->page($pager, 't1.id')
            ->fetchAll('id');
    }
    elseif($objectType == 'requirement' or $objectType == 'story')
    {
        $orderBy    = (strpos($orderBy, 'priOrder') !== false or strpos($orderBy, 'severityOrder') !== false) ? $orderBy : "t1.$orderBy";
        $nameField  = $objectType == 'bug' ? 'productName' : 'productTitle';
        $select     = "t1.*, t2.name AS {$nameField}, t2.shadow AS shadow, " . (strpos($orderBy, 'severity') !== false ? "IF(t1.`severity` = 0, {$this->config->maxPriValue}, t1.`severity`) AS severityOrder" : "IF(t1.`pri` = 0, {$this->config->maxPriValue}, t1.`pri`) AS priOrder");
        $objectList = $this->dao->select($select)->from($this->config->objectTables[$module])->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on("t1.product = t2.id")
            ->where('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq(0)
            ->andWhere('t1.id')->in(array_keys($objectIDList))
            ->beginIF($objectType == 'requirement' or $objectType == 'story')->andWhere('t1.type')->eq($objectType)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }
    elseif($objectType == 'bug')
    {
        $orderBy    = (strpos($orderBy, 'priOrder') !== false or strpos($orderBy, 'severityOrder') !== false) ? $orderBy : "t1.$orderBy";
        $select     = "t1.*, t2.name AS productName, t2.shadow AS shadow, t3.name AS projectName," . (strpos($orderBy, 'severity') !== false ? "IF(t1.`severity` = 0, {$this->config->maxPriValue}, t1.`severity`) AS severityOrder" : "IF(t1.`pri` = 0, {$this->config->maxPriValue}, t1.`pri`) AS priOrder");
        $objectList = $this->dao->select($select)->from($this->config->objectTables[$module])->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t2')->on("t1.product = t2.id")
            ->leftJoin(TABLE_PROJECT)->alias('t3')->on("t1.project = t3.id")
            ->where('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq(0)
            ->andWhere('t1.id')->in(array_keys($objectIDList))
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }
    elseif($objectType == 'risk' or $objectType == 'issue' or $objectType == 'nc')
    {
        $objectList = $this->dao->select('t1.*')->from($this->config->objectTables[$module])->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')->on("t1.project = t2.id")
            ->where('t1.deleted')->eq(0)
            ->andWhere('t2.deleted')->eq(0)
            ->andWhere('t1.id')->in(array_keys($objectIDList))
            ->orderBy('t1.' . $orderBy)
            ->page($pager)
            ->fetchAll('id');
    }
    else
    {
        $objectList = $this->dao->select('*')->from($this->config->objectTables[$module])
            ->where('deleted')->eq(0)
            ->andWhere('id')->in(array_keys($objectIDList))
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    if($objectType == 'task')
    {
        if($objectList) return $this->loadModel('task')->processTasks($objectList);
        return $objectList;
    }

    if($objectType == 'requirement' or $objectType == 'story')
    {
        $planList = array();
        foreach($objectList as $story) $planList[$story->plan] = $story->plan;
        $planPairs = $this->dao->select('id,title')->from(TABLE_PRODUCTPLAN)->where('id')->in($planList)->fetchPairs('id');
        foreach($objectList as $story) $story->planTitle = zget($planPairs, $story->plan, '');
    }
    return $objectList;
}