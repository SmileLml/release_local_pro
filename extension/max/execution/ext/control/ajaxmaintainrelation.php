<?php
class execution extends control
{
    /**
     * Maintain task relation from the gantt by ajax.
     *
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function ajaxMaintainRelation($executionID)
    {
        if(!empty($_POST))
        {
            /* Add history maintain relation. */
            $relations = $this->execution->getRelationsOfTasks($executionID);
            if(!empty($relations))
            {
                foreach($relations as $relation)
                {
                    $_POST['id'][$relation->id]        = $relation->id;
                    $_POST['pretask'][$relation->id]   = $relation->pretask;
                    $_POST['condition'][$relation->id] = $relation->condition;
                    $_POST['task'][$relation->id]      = $relation->task;
                    $_POST['action'][$relation->id]    = $relation->action;
                }
            }
            $this->execution->editRelationOfTasks($executionID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => $this->lang->execution->gantt->warning->noCreateLink));
            return $this->send(array('result' => 'success'));
        }
    }
}
