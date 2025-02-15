<?php
/**
 * The control file of execution module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     execution
 * @version     $Id$
 * @link        http://www.zentao.net
 */
public function createRelationOfTasks($executionID)
{
    $this->loadExtension('gantt')->createRelationOfTasks($executionID);
}

public function editRelationOfTasks($executionID)
{
    $this->loadExtension('gantt')->editRelationOfTasks($executionID);
}

public function getRelationsOfTasks($executionID)
{
    return $this->loadExtension('gantt')->getRelationsOfTasks($executionID);
}

public function getDataForGantt($executionID, $type, $orderBy)
{
    return $this->loadExtension('gantt')->getDataForGantt($executionID, $type, $orderBy);
}

public function deleteRelation($id)
{
    $this->loadExtension('gantt')->deleteRelation($id);
}

public function parseOrderBy($orderBy)
{
    return $this->loadExtension('gantt')->parseOrderBy($orderBy);
}

public function buildKanbanOrderBy($field, $currentOrder, $currentDirect)
{
    return $this->loadExtension('gantt')->buildKanbanOrderBy($field, $currentOrder, $currentDirect);
}
