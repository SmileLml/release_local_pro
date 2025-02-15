<?php

/**
 * Export module data.
 *
 * @param  string $model
 * @access public
 * @return void
 */
public function export($model = '')
{
    parent::export($model);
    $rows = isset($_POST['rows']) ? $_POST['rows'] : [];
    if($model == 'bug') foreach($rows as $id => $row) if(isset($row->plan) && !$row->plan) $rows[$id]->plan = '';
    $this->post->set('rows', $rows);
}