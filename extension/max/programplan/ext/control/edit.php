<?php
helper::importControl('programplan');
class myProgramplan extends programplan
{
    public function edit($planID = 0, $projectID = 0)
    {
        $this->view->documentList = $this->programplan->getDocumentList();
        parent::edit($planID, $projectID);
    }
}
