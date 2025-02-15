<?php
class flowGroup extends groupModel
{
    /**
     * Load custom lang.
     *
     * @access public
     * @return void
     */
    public function loadCustomLang()
    {
        $flows       = $this->loadModel('workflow', 'flow')->getList('browse', 'flow', 'normal');
        $flowActions = $this->loadModel('workflowaction', 'flow')->getGroupList('enable');
        $flowLabels  = $this->loadModel('workflowlabel', 'flow')->getGroupList();
        $exportFlows = $this->dao->select('DISTINCT `module`')->from(TABLE_WORKFLOWFIELD)->where('canExport')->eq('1')->fetchPairs();
        $searchFlows = $this->dao->select('DISTINCT `module`')->from(TABLE_WORKFLOWFIELD)->where('canSearch')->eq('1')->fetchPairs();
        foreach($flows as $flow)
        {
            $actions = zget($flowActions, $flow->module, array());

            if(!$flow->buildin)
            {
                if(!isset($this->lang->{$flow->module})) $this->lang->{$flow->module} = new stdclass();
                $this->lang->{$flow->module}->common = $flow->name;


                array_push($this->lang->moduleOrder, $flow->module);

                $this->lang->resource->{$flow->module} = new stdclass();

                $key = 0;
                foreach($actions as $action)
                {
                    if($action->action == 'browse' and !empty($flowLabels))
                    {
                        $this->lang->{$flow->module}->{$action->action}           = $action->name;
                        $this->lang->resource->{$flow->module}->{$action->action} = $action->action;
                        $this->lang->{$flow->module}->methodOrder[$key]           = $action->action;
                        $key++;

                        $labels = zget($flowLabels, $flow->module, array());
                        foreach($labels as $label) $this->lang->{$flow->module}->menus[$label->id] = $label->label;
                    }
                    else
                    {
                        if($action->action == 'export' && !isset($exportFlows[$flow->module])) continue;

                        $this->lang->{$flow->module}->{$action->action}           = $action->name;
                        $this->lang->resource->{$flow->module}->{$action->action} = $action->action;
                        $this->lang->{$flow->module}->methodOrder[$key]           = $action->action;
                    }
                    $key++;
                }

                if(!isset($searchFlows[$flow->module])) continue;

                $this->lang->{$flow->module}->search            = $this->lang->workflowlabel->search;
                $this->lang->resource->{$flow->module}->search  = 'search';
                $this->lang->{$flow->module}->methodOrder[$key] = 'search';
            }
            else
            {
                foreach($actions as $action)
                {
                    if($action->buildin) continue;

                    if(!isset($this->lang->{$action->module})) continue;
                    if(!isset($this->lang->resource->{$action->module})) continue;

                    $this->lang->{$action->module}->{$action->action}           = $action->name;
                    $this->lang->resource->{$action->module}->{$action->action} = $action->action;
                    $this->lang->{$action->module}->methodOrder[]               = $action->action;
                }
            }
        }
    }
}
