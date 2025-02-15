<?php
helper::importControl('programplan');
class myProgramplan extends programplan
{
    /**
     * Edit a project plan.
     *
     * @param  int    $planID
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function edit($planID = 0, $projectID = 0)
    {
        $project = $this->loadModel('project')->getByID($projectID);
        $plan    = $this->loadModel('execution')->getByID($planID);
        if($_POST and $project->model == 'ipd' and !$plan->parallel)
        {
            $this->programplan->checkIpdStageDate($plan);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        }

        return parent::edit($planID, $projectID);
    }
}
