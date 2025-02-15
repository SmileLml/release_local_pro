<?php
class excelTicket extends ticketModel
{
    /**
     * Set export list value
     *
     * @access public
     * @return void
     */
    public function setListValue()
    {
        $modulesProductMap = $this->loadModel('feedback')->getModuleList('ticket');

        $modules    = array();
        $moduleList = array();
        /* Group by module for cascade. */
        foreach($modulesProductMap as $productID => $module)
        {
            if(empty($module)) continue;
            foreach($module as $moduleID => $moduleName)
            {
                $moduleList[$productID][$moduleID] = $moduleName . "(#$moduleID)";
                $modules[$moduleID] = $moduleName . "(#$moduleID)";
            }
        }

        $this->post->set('moduleList', ($this->post->fileType == 'xlsx' and $moduleList) ? $moduleList : $modules);
    }
}
