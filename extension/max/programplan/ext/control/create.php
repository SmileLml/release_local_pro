<?php
helper::importControl('programplan');
class myProgramplan extends programplan
{
    public function create($programID = 0, $productID = 0, $planID = 0, $executionType = 'stage')
    {
        $this->view->documentList = $this->programplan->getDocumentList();
        parent::create($programID, $productID, $planID, $executionType);
    }
}
