<?php
class myProject extends project
{
    public function create($model = 'scrum', $programID = 0, $copyProjectID = 0, $extra = '')
    {
        $this->loadModel('roadmap');
        $this->view->charters = array(0 => '') + $this->loadModel('charter')->getPairs();

        $copyedCharter = 0;
        if($copyProjectID) $copyedCharter = $this->dao->select('charter')->from(TABLE_PROJECT)->where('id')->eq($copyProjectID)->fetch('charter');
        $this->view->copyedCharter = $copyedCharter;

        parent::create($model, $programID, $copyProjectID, $extra);
    }
}
