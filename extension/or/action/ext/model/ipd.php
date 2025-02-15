<?php
/**
 * Undelete a record.
 *
 * @param  int      $actionID
 * @access public
 * @return void
 */
public function undelete($actionID)
{
    $action = $this->getById($actionID);
    if($action->action != 'deleted') return;

    if($action->objectType == 'requirement')
    {
        $demand = new stdclass();
        $story  = $this->loadModel('story')->getByID($action->objectID);
        if($story->demand)
        {
            $demand = $this->loadModel('demand')->getByID($story->demand);

            if($story->vision == 'or') $status = 'distributed';
            if(strpos($story->vision, 'rnd') !== false) $status = 'launched';

            if(isset($status)) $this->dao->update(TABLE_DEMAND)->set('status')->eq($status)->where('id')->eq($demand->id)->exec();
        }
    }

    return parent::undelete($actionID);
}
