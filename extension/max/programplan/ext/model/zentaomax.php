<?php
public function setMenu($programID, $productID)
{
    $baselines = $this->loadModel('cm')->getPairsForGantt($programID, $productID);
    $subMenu   = array();
    if(!empty($baselines))
    {
        foreach($baselines as $id => $version)
        {
            $link = array();
            $link['module'] = 'programplan';
            $link['method'] = 'browse';
            $link['vars']   = "program=$programID&productID=$productID&type=gantt&orderBy=id_desc&baselineID=$id";

            $menu            = new stdclass();
            $menu->name      = $id;
            $menu->link      = $link;
            $menu->text      = $version;
            $menu->subModule = '';
            $menu->alias     = '';
            $menu->hidden    = false;

            $subMenu[$id] = $menu;
        }

        $this->lang->programplan->menu->gantt['subMenu'] = $subMenu;
        $this->lang->programplan->menu->gantt['class']   = 'dropdown dropdown-hover';
    }
}

public function getDocumentList()
{
    return $document = $this->dao->select("t1.id, CONCAT_WS(' / ', t2.name, t1.name) as name")->from(TABLE_ZOUTPUT)->alias('t1')
        ->leftJoin(TABLE_ACTIVITY)->alias('t2')->on('t1.activity = t2.id')
        ->where('t1.deleted')->eq(0)
        ->fetchPairs('id', 'name');
}

public function getPlans($programID = 0, $productID = 0, $orderBy = 'id_asc')
{
    $plans        = $this->getStage($programID, $productID, 'all', $orderBy);
    $documentList = $this->getDocumentList();

    $parents = array();
    foreach($plans as $planID => $plan)
    {
        $document = '';
        if(!empty($plan->output))
        {
            $output = explode(',', $plan->output);
            foreach($output as $title) if(isset($documentList[$title])) $document .= $documentList[$title];
        }

        $plan->output = empty($plan->output) ? '' : $document;

        $plan->grade == 1 ? $parents[$planID] = $plan : $children[$plan->parent][] = $plan;
    }

    foreach($parents as $planID => $plan) $parents[$planID]->children = isset($children[$planID]) ? $children[$planID] : array();

    return $parents;
}
