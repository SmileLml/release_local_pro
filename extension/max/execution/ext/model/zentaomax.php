<?php
/**
 * Get taskes by search.
 *
 * @param  string $condition
 * @param  object $pager
 * @param  string $orderBy
 * @access public
 * @return array
 */
public function getSearchTasks($condition, $pager, $orderBy)
{
    return $this->loadExtension('zentaomax')->getSearchTasks($condition, $pager, $orderBy);
}
