<?php
class flowCommon extends commonModel
{
    /**
     * Load custom lang.
     *
     * @access public
     * @return void
     */
    public function loadCustomLang()
    {
        if(defined('IN_UPGRADE') and IN_UPGRADE) return;

        $rawModule = $this->app->rawModule;
        $rawMethod = $this->app->rawMethod;

        $flowModule = $rawModule;
        if($rawModule == 'execution' && $rawMethod == 'task') $flowModule = 'task';
        if(($rawModule == 'project' || $rawModule == 'execution') && $rawMethod == 'build') $flowModule = 'build';
        if($rawModule == 'projectrelease' && $rawMethod == 'browse') $flowModule = 'release';
        if($rawModule == 'project' && $rawMethod == 'execution') $flowModule = 'execution';
        if($rawModule == 'execution' && $rawMethod == 'bug') $flowModule = 'bug';
        if($rawModule == 'assetlib' && $rawMethod == 'caselib') $flowModule = 'caselib';

        $flow = $this->dao->select('id,module,`table`,approval')->from(TABLE_WORKFLOW)->where('module')->eq($flowModule)->fetch();
        if($flow)
        {
            $table      = $this->processFlowTable($flow->table);
            $flowFields = $this->dao->select('id,module,field,name')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($rawModule)->andWhere('buildin')->eq(0)->fetchAll();
            if($flowFields)
            {
                if(!isset($this->lang->{$rawModule})) $this->lang->{$rawModule} = new stdclass();
                if($_POST and $table != $rawModule and !isset($this->lang->{$table})) $this->lang->{$table} = new stdclass();
                if(($rawModule == 'execution' or $rawModule == 'program') and !isset($this->lang->project)) $this->lang->project = new stdclass();
                if(($rawModule == 'caselib') and !isset($this->lang->testsuite)) $this->lang->testsuite = new stdclass();

                foreach($flowFields as $field)
                {
                    $this->lang->{$rawModule}->{$field->field} = $field->name;
                    if($_POST and $table != $rawModule) $this->lang->{$table}->{$field->field} = $field->name;
                    if($rawModule == 'execution' or $rawModule == 'program') $this->lang->project->{$field->field} = $field->name;
                    if($rawModule == 'caselib') $this->lang->testsuite->{$field->field} = $field->name;
                }
            }

            $flowLabels = $this->dao->select('id,module,label')->from(TABLE_WORKFLOWLABEL)
                ->where('module')->eq($flowModule)
                ->andWhere('code')->eq('review')
                ->andWhere('buildin')->eq(0)
                ->andWhere('role')->eq('approval')
                ->fetchAll();

            if($flowLabels && $flow->approval == 'enabled')
            {
                foreach($flowLabels as $label)
                {
                    if($rawModule == 'project' && $label->module == 'execution')
                    {
                        $this->lang->execution->featureBar['all']['review'] = $label->label;
                    }
                    if($rawModule == 'execution' && $label->module == 'bug')
                    {
                        $this->lang->execution->featureBar['bug']['review'] = $label->label;
                    }
                    else if($label->module == 'execution' || $label->module == 'product')
                    {
                        $this->lang->{$rawModule}->featureBar['all']['review'] = $label->label;
                    }
                    else if($label->module == 'task' || $label->module == 'build' || $label->module == 'caselib')
                    {
                        $this->lang->{$rawModule}->featureBar[$flowModule]['review'] = $label->label;
                    }
                    else
                    {
                        $this->lang->{$rawModule}->featureBar['browse']['review'] = $label->label;
                    }
                }
            }

            /* Append children flow fields. */
            $childrenFlows = $this->dao->select('id,module,`table`')->from(TABLE_WORKFLOW)->where('parent')->eq($rawModule)->andWhere('type')->eq('table')->fetchAll('module');
            if($childrenFlows)
            {
                $childrenFlowFields = $this->dao->select('id,module,field,name')->from(TABLE_WORKFLOWFIELD)->where('module')->in(array_keys($childrenFlows))->andWhere('buildin')->eq(0)->fetchGroup('module', 'id');
                foreach($childrenFlowFields as $childModule => $flowFields)
                {
                    $childFlow = $childrenFlows[$childModule];
                    $table     = $this->processFlowTable($childFlow->table);

                    if(!isset($this->lang->{$childModule})) $this->lang->{$childModule} = new stdclass();
                    if($_POST and $table != $childModule and !isset($this->lang->{$table})) $this->lang->{$table} = new stdclass();
                    foreach($flowFields as $field)
                    {
                        $this->lang->{$childModule}->{$field->field} = $field->name;
                        if($_POST and $table != $childModule) $this->lang->{$table}->{$field->field} = $field->name;
                    }
                }
            }
        }
    }

    /**
     * Process flow table.
     *
     * @param  string    $table
     * @access public
     * @return string
     */
    public function processFlowTable($table)
    {
        if(isset($this->config->db->prefix))
        {
            $table = strtolower(str_replace(array($this->config->db->prefix, '`'), '', $table));
        }
        elseif(strpos($this->table, '_') !== false)
        {
            $table = strtolower(substr($table, strpos($table, '_') + 1));
            $table = str_replace('`', '', $table);
        }

        return $table;
    }

    /**
     * Merge lang from flow.
     *
     * @access public
     * @return void
     */
    public function mergeLangFromFlow()
    {
        if(defined('IN_UPGRADE')) return;
        if(!$this->config->db->name) return;

        $this->app->setOpenApp();

        $rawModule = $this->app->rawModule;
        $rawMethod = $this->app->rawMethod;
        $tab       = $this->app->tab;

        $primaryFlows   = array();
        $secondaryFlows = array();
        $flows          = $this->dao->select('*')->from(TABLE_WORKFLOW)->where('buildin')->eq('0')->andWhere('vision')->eq($this->config->vision)->andWhere('status')->eq('normal')->andWhere('type')->eq('flow')->orderBy('navigator_asc')->fetchAll('id');
        foreach($flows as $flow)
        {
            if($flow->navigator == 'primary') $primaryFlows[$flow->module] = $flow;

            $flowApp = $flow->app;
            if($flowApp == 'scrum' or $flowApp == 'waterfall') $flowApp = 'project';
            if($flow->navigator == 'secondary' and ($flowApp == $tab or $tab == 'workflow')) $secondaryFlows[$flow->module] = $flow;
        }

        $this->sortFlows($primaryFlows, 'primary');
        $isLogon = $this->loadModel('user')->isLogon();

        /* If main nav has workflow, load menuOrders from lang table. */
        //if(!empty($primaryFlows)) $this->app->LoadLang('mainNav');

        foreach($primaryFlows as $flow)
        {
            if(!isset($this->lang->{$flow->module})) $this->lang->{$flow->module} = new stdclass();
            $this->lang->{$flow->module}->common  = $flow->name;
            $this->lang->mainNav->{$flow->module} = "{$this->lang->navIcons['workflow']} {$flow->name}|{$flow->module}|browse|";
            if($tab == $flow->module)
            {
                if(!isset($this->lang->{$flow->module}))       $this->lang->{$flow->module} = new stdclass();
                if(!isset($this->lang->{$flow->module}->menu)) $this->lang->{$flow->module}->menu = new stdclass();
                $this->lang->{$flow->module}->menu->{$flow->module} = array('link' => "{$flow->name}|{$flow->module}|browse|");
                $this->lang->{$flow->module}->menuOrder[5]          = $flow->module;
            }
        }

        $this->sortFlows($secondaryFlows, 'secondary');
        foreach($secondaryFlows as $flow)
        {
            $flowApp = ($this->config->vision == 'lite' and $flow->app == 'project') ? 'kanban' : $flow->app;
            if(!isset($this->lang->{$flow->module})) $this->lang->{$flow->module} = new stdclass();

            $this->lang->{$flow->module}->common = $flow->name;
            if(!isset($this->lang->{$flowApp})) $this->lang->{$flowApp} = new stdclass();
            if(!isset($this->lang->{$flowApp}->menu)) $this->lang->{$flowApp}->menu = new stdclass();
            $this->lang->{$flowApp}->menu->{$flow->module} = array('link' => "{$flow->name}|{$flow->module}|browse|");
        }

        if($this->app->isFlow)
        {
            if($tab == 'product' or $tab == 'qa')
            {
                $products  = $this->loadModel('product')->getPairs('nocode');
                $productID = $this->product->saveState(0, $products);
                $branch    = (int)$this->cookie->preBranch;
                if($tab == 'product') $this->product->setMenu($productID, $branch, 0);
                if($tab == 'qa') $this->loadModel('qa')->setMenu($products, $productID, $branch);
            }
            elseif($tab == 'project')
            {
                $projects  = $this->loadModel('project')->getPairsByProgram();
                $projectID = $this->project->saveState($this->session->project, $projects);
                $this->project->setMenu($projectID);
            }
            elseif($tab == 'execution')
            {
                $this->loadModel('execution')->setMenu($this->session->execution);
            }
        }
    }

    public function sortFlows($flows, $navigator = 'primary')
    {
        $tab       = $this->app->tab;
        $menuOrder = array();
        $waterfallOrder = array();
        if($navigator == 'primary')
        {
            $menuOrder = $this->lang->mainNav->menuOrder;
        }
        elseif($navigator == 'secondary')
        {
            if($tab == 'project')
            {
                $menuOrder = $this->lang->scrum->menuOrder;
                if(isset($this->lang->waterfall->menuOrder)) $waterfallOrder = $this->lang->waterfall->menuOrder;
                if($this->config->vision == 'lite') $menuOrder = $this->lang->kanbanProject->menuOrder;
            }
            elseif(isset($this->lang->{$tab}->menuOrder))
            {
                $menuOrder = $this->lang->{$tab}->menuOrder;
            }
        }
        if(empty($menuOrder) and empty($waterfallOrder)) return true;

        $waterfallFlows = array();
        foreach($flows as $module => $flow)
        {
            if($flow->app != 'waterfall') $menuOrder[] = $flow->module;
            if($flow->app == 'waterfall')
            {
                $waterfallOrder[] = $flow->module;
                $waterfallFlows[$module] = $flow;
                unset($flows[$module]);
            }
        }
        $waterfallOrderFlip = array_flip($waterfallOrder);
        $menuOrderFlip = array_flip($menuOrder);

        foreach($flows as $flow)
        {
            if($flow->app != 'waterfall') $menuOrderFlip = $this->computeMenuOrder($flow, $menuOrderFlip, $flows);
            if($flow->app == 'waterfall') $waterfallOrderFlip = $this->computeMenuOrder($flow, $waterfallOrderFlip, $waterfallFlows);
        }

        $menuOrder = $this->flipMenuOrder($menuOrderFlip);
        $waterfallOrder = $this->flipMenuOrder($waterfallOrderFlip);
        if($navigator == 'primary')
        {
            $this->lang->mainNav->menuOrder = $menuOrder;
        }
        elseif($navigator == 'secondary')
        {
            if($tab == 'project')
            {
                $this->lang->scrum->menuOrder = $menuOrder;
                if(isset($this->lang->waterfall->menuOrder)) $this->lang->waterfall->menuOrder = $waterfallOrder;
                if($this->config->vision == 'lite') $this->lang->kanbanProject->menuOrder = $menuOrder;
            }
            elseif(isset($this->lang->{$tab}->menuOrder))
            {
                $this->lang->{$tab}->menuOrder = $menuOrder;
            }
        }

        return true;
    }

    public function computeMenuOrder($flow, $menuOrderFlip, $flows)
    {
        static $computedModules = array();
        if(isset($computedModules[$flow->module])) return $menuOrderFlip;

        if(strpos($flow->position, 'before') === 0 or strpos($flow->position, 'after') === 0)
        {
            $computedModules[$flow->module] = true;

            $mode   = strpos($flow->position, 'before') === 0 ? 'before' : 'after';
            $module = substr($flow->position, strlen($mode));
            if(isset($flows[$module]) and $module != $flow->module and !isset($computedModules[$module])) $menuOrderFlip = $this->computeMenuOrder($flows[$module], $menuOrderFlip, $flows);

            if(isset($menuOrderFlip[$module]))
            {
                $order = $menuOrderFlip[$module];
                $step  = (is_numeric($order) and strpos($order, '.') !== false) ? '0.01' : '0.1';
                $order = $mode == 'before' ? (string)($order - $step) : (string)($order + $step);
                $menuOrderFlip[$flow->module] = $order;
            }
        }

        return $menuOrderFlip;
    }

    /**
     * Flip menuOrder
     *
     * @param  array    $orderFlip
     * @access public
     * @return array
     */
    public function flipMenuOrder($orderFlip)
    {
        asort($orderFlip);
        $orders = array();
        $index  = 0;
        foreach($orderFlip as $moduleName => $order)
        {
            $index += 5;
            $orders[$index] = $moduleName;
        }
        return $orders;
    }
}
