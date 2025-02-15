<?php
/**
 * Add task estimate.
 *
 * @param  object   $data
 * @access public
 * @return int
 */
public function addTaskEstimate($data)
{
    return $this->loadExtension('effort')->addTaskEstimate($data);
}
