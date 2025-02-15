<?php

helper::importControl('doc');

class mydoc extends doc
{
    public function tableContents($type = 'custom', $objectID = 0, $libID = 0, $moduleID = 0, $browseType = 'all', $orderBy = 'status,id_desc', $param = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        if(in_array($type, array('project', 'execution')) && $objectID)
        {
            $modelClass    = class_exists("extcommonModel") ? "extcommonModel" :  "commonModel";
            $currentObject = $type == 'project' ? $this->loadModel('project')->getByID($objectID) : $this->loadModel('execution')->getByID($objectID);
            $canBeModify   = $modelClass::canModify($type, $currentObject);

        }

        $this->view->canBeModify   = $canBeModify ?? true;
        $this->view->currentObject = $currentObject ?? null;

        parent::tableContents($type, $objectID, $libID, $moduleID, $browseType, $orderBy, $param, $recTotal, $recPerPage, $pageID);
    }
}