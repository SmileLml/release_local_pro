<?php
helper::importControl('programplan');
class myProgramplan extends programplan
{
    /**
     * Create a project plan.
     *
     * @param  int    $projectID
     * @param  int    $productID
     * @param  int    $planID
     * @param  string $executionType
     * @access public
     * @return void
     */
    public function create($projectID = 0, $productID = 0, $planID = 0, $executionType = 'stage')
    {
        $this->view->canParallel = $this->programplan->canParallel($projectID);
        if(isset($_POST['parallel']) and !$_POST['parallel'])
        {
            $begin = $_POST['begin'];
            $end   = $_POST['end'];

            $preDate  = '';
            $nextDate = '';
            foreach($begin as $index => $value)
            {
                $preDate  = isset($end[$index - 1]) ? $end[$index - 1] : '';
                $nextDate = isset($begin[$index + 1]) ? $begin[$index + 1] : '';

                if($preDate == '') continue;

                if($value < $preDate) dao::$errors[$index]['begin'] = $this->lang->programplan->error->outOfDate;
                if($nextDate and $end[$index] > $nextDate) dao::$errors[$index]['end'] = $this->lang->programplan->error->lessOfDate;
            }
            if(dao::isError()) return $this->send(array('result' => 'fail', 'callback' => array('name' => 'addRowErrors', 'params' => array(dao::getError()))));
        }
        return parent::create($projectID, $productID, $planID, $executionType);
    }
}
