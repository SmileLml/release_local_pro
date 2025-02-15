<?php
class flowWorkflow extends workflowModel
{
    /**
     * Get Apps.
     *
     * @param  string $exclude
     * @param  bool   $splitProject
     * @access public
     * @return array
     */
    public function getApps($exclude = 'admin', $splitProject = true)
    {
        $apps = array('');
        $menu = commonModel::getMainNavList($this->app->rawModule);
        foreach($menu as $menuItem)
        {
            if(empty($menuItem->title)) continue;
            if($exclude && strpos(",$exclude,", ",$menuItem->code,") !== false) continue;
            if($menuItem->code == 'project' and $splitProject)
            {
                if(isset($this->lang->scrum->menu)) $apps['scrum'] = $this->lang->project->common . '/' . $this->lang->workflow->scrum;
                if(isset($this->lang->waterfall->menu)) $apps['waterfall'] = $this->lang->project->common . '/' . $this->lang->workflow->waterfall;

                if($this->config->vision == 'lite')
                {
                    $apps['project'] = $this->lang->project->common;
                    unset($apps['scrum'], $apps['waterfall'], $apps['kanban']);
                }
            }
            else
            {
                $apps[$menuItem->code] = trim(strip_tags($menuItem->title));
            }
        }
        return $apps;
    }

    /**
     * Get menus of an app.
     *
     * @param  string $app
     * @param  string $exclude
     * @access public
     * @return array
     */
    public function getAppMenus($app, $exclude = '')
    {
        $menus = array('');
        if(empty($app)) return $menus;
        if($app == 'kanban') return $menus;

        if($this->config->vision == 'lite' and $app == 'project') $app = 'kanban';

        $customPrimaryFlow = $this->dao->select('id')->from(TABLE_WORKFLOW)
            ->where('module')->eq($app)
            ->andWhere('type')->eq('flow')
            ->andWhere('status')->eq('normal')
            ->andWhere('buildin')->eq('0')
            ->andWhere('navigator')->eq('primary')
            ->andWhere('vision')->eq($this->config->vision)
            ->fetch();

        $this->app->loadLang($app);

        if(empty($customPrimaryFlow) && isset($this->lang->$app->menuOrder) && (is_array($this->lang->$app->menuOrder) or is_object($this->lang->$app->menuOrder)))
        {
            ksort($this->lang->$app->menuOrder);
            foreach($this->lang->$app->menuOrder as $module)
            {
                if($exclude && strpos(",{$exclude},", ",{$module},") !== false) continue;

                if(isset($this->lang->$app->menu->$module))
                {
                    $menuItem = $this->lang->$app->menu->$module;

                    if(is_string($menuItem)) $label = substr($menuItem, 0, strpos($menuItem, '|'));
                    if(is_array($menuItem))
                    {
                        if(!isset($menuItem['link'])) continue;
                        $link = $menuItem['link'];
                        $label = substr($link, 0, strpos($link, '|'));
                    }
                    if($module == 'bysearch')
                    {
                        $this->app->loadLang('search');
                        $label = $this->lang->search->common;
                    }
                    if(empty($label)) continue;
                    if(strpos($label, '@') !== false) continue;

                    $menus[$module] = $label;
                }
            }
        }
        else
        {
            $flows = $this->dao->select('id,app,position,module,name')->from(TABLE_WORKFLOW)
                ->where('app')->eq($app)
                ->andWhere('buildin')->eq(0)
                ->andWhere('status')->eq('normal')
                ->andWhere('type')->eq('flow')
                ->orderBy('id')
                ->fetchAll('id');
            $currentFlowName = $this->dao->select('id,app,position,module,name')->from(TABLE_WORKFLOW)->where('module')->eq($app)->fetch('name');

            $orders[$app] = 5;
            $positions = array();
            $flowPairs = array();
            $unsorts   = array();
            foreach($flows as $flow)
            {
                $flowPairs[$flow->module] = $flow->name;

                $position  = $flow->position;
                $direction = strpos($position, 'after') === 0 ? 'after' : 'before';
                $position  = substr($position, strlen($direction));

                if(isset($orders[$position]))
                {
                    if($direction == 'after')  $orders[$flow->module] = $orders[$position] + '0.1';
                    if($direction == 'before') $orders[$flow->module] = $orders[$position] - '0.1';
                    $result  = $this->reorderMenu($unsorts, $orders);
                    $orders  = $result['orders'];
                    $unsorts = $result['unsorts'];
                }
                else
                {
                    $unsorts[$position][$flow->module] = $direction;
                }
            }

            asort($orders);
            $menus = array();
            foreach($orders as $flowModule => $order)
            {
                if($exclude && strpos(",{$exclude},", ",{$flowModule},") !== false) continue;
                $menus[$flowModule] = $flowModule == $app ? $currentFlowName : $flowPairs[$flowModule];
            }
        }

        return $menus;
    }

    /**
     * Resort Menu
     *
     * @param  array    $unsorts
     * @param  array    $orders
     * @access public
     * @return array
     */
    public function reorderMenu($unsorts, $orders)
    {
        foreach($unsorts as $position => $flowModules)
        {
            if(isset($orders[$position]))
            {
                foreach($flowModules as $flowModule => $direction)
                {
                    $order = $orders[$position];
                    $step  = (is_numeric($order) and strpos($order, '.') === false) ? '0.1' : '0.01';
                    if($direction == 'after')  $orders[$flowModule] = $orders[$position] + $step;
                    if($direction == 'before') $orders[$flowModule] = $orders[$position] - $step;
                }
                unset($unsorts[$position]);

                $result  = $this->reorderMenu($unsorts, $orders);
                $orders  = $result['orders'];
                $unsorts = $result['unsorts'];
            }
        }

        return array('orders' => $orders, 'unsorts' => $unsorts);
    }

    /**
     * Get build in modules.
     * This function is used to check if the code of an user defined module is exist.
     *
     * @param  string $root
     * @access public
     * @return array
     */
    public function getBuildinModules($root = '', $rootType = '')
    {
        if(!$root) $root = $this->app->getModuleRoot();

        $modules = array();
        $handle  = opendir($root);
        if($handle)
        {
            while(($dir = readdir($handle)) !== false)
            {
                if($dir == '.' || $dir == '..') continue;
                $dirPath = $root . DIRECTORY_SEPARATOR . $dir;
                if(is_dir($dirPath))
                {
                    $dir = strtolower($dir);
                    $modules[$dir] = $dir;
                }
            }
            closedir($handle);
        }
        $modules['parent'] = 'parent';
        $modules['sub']    = 'sub';
        return $modules;
    }

    /**
     * Get all used apps of flow.
     *
     * @access public
     * @return array
     */
    public function getFlowApps()
    {
        return $this->dao->select('app')->from(TABLE_WORKFLOW)->where('app')->ne('')->orderBy('id')->fetchPairs();
    }

    /**
     * Get flow list.
     *
     * @param  string $mode     browse | bysearch
     * @param  string $type     flow | type
     * @param  string $status   wait | normal | pause
     * @param  string $parent
     * @param  string $app      crm | oa | proj | doc | cash | team | hr | psi | flow | ameba
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getList($mode = 'browse', $type = 'flow', $status = 'normal', $parent = '', $app = '', $orderBy = 'id_desc', $pager = null)
    {
        if($this->session->workflowQuery == false) $this->session->set('workflowQuery', ' 1 = 1');
        $workflowQuery = $this->loadModel('search')->replaceDynamic($this->session->workflowQuery);

        $flows = $this->dao->select('*')->from(TABLE_WORKFLOW)
            ->where('vision')->eq($this->config->vision)
            ->beginIF($type)->andWhere('type')->eq($type)->fi()
            ->beginIF($type == 'table' && $parent)->andWhere('parent')->eq($parent)->fi()
            ->beginIF($type == 'flow' && $app)->andWhere('app')->eq($app)->fi()
            ->beginIF($type == 'flow' && $status && $status != 'unused')->andWhere('status')->eq($status)->fi()
            ->beginIF($type == 'flow' && $status == 'unused')->andWhere('status')->in('wait,pause')->fi()
            ->beginIF($mode == 'bysearch')->andWhere($workflowQuery)->fi()
            ->beginIF($this->config->systemMode == 'light')->andWhere('module')->notin('program')->fi()
            ->beginIF($this->config->visions == ',lite,')->andWhere('module')->notin('feedback')->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        foreach($flows as $flow)
        {
            if($this->config->vision == 'rnd' and $flow->module == 'story')  $flow->name = $this->lang->story->common;
            if($this->config->vision == 'lite' and $flow->module == 'story') $flow->name = $this->lang->story->common;
        }

        return $flows;
    }

    /**
     * Sort module menu.
     *
     * @param  string    $app
     * @param  string    $module
     * @param  string    $position
     * @param  string    $positionModule
     * @param  array     $buildInModules
     * @access public
     * @return bool
     */
    public function sortModuleMenu($app, $module, $position, $positionModule, $buildInModules = array())
    {
        if($app != $module)
        {
            $this->app->loadLang($app);
            if(!isset($this->lang->{$app}->menu)) return true;

            $menus = $this->lang->{$app}->menu;
        }
        else
        {
            $menus = $this->lang->mainNav;
            $app   = 'mainNav';
        }

        $this->loadModel('custom');
        if(empty($buildInModules)) $buildInModules = $this->getBuildinModules();

        if(!isset($this->lang->{$app}->menuOrder)) $this->lang->{$app}->menuOrder = array();
        foreach($menus as $moduleName => $moduleMenu)
        {
            if($app == 'mainNav' && $moduleName == 'menuOrder') continue;

            if(!in_array($moduleName, $this->lang->{$app}->menuOrder)) $this->lang->{$app}->menuOrder[] = $moduleName;
        }

        ksort($this->lang->{$app}->menuOrder);

        $moduleKey = array_search($module, $this->lang->{$app}->menuOrder);
        if($moduleKey) unset($this->lang->{$app}->menuOrder[$moduleKey]);

        $i = 5;
        foreach($this->lang->{$app}->menuOrder as $moduleMenu)
        {
            if($moduleMenu == $positionModule)
            {
                if($position == 'before')
                {
                    $system = isset($buildInModules[$module]);
                    $this->custom->setItem("all.{$app}.menuOrder.{$i}.{$system}", $module);

                    $i += 5;
                    $system = isset($buildInModules[$moduleMenu]);
                    $this->custom->setItem("all.{$app}.menuOrder.{$i}.{$system}", $moduleMenu);
                }
                elseif($position == 'after')
                {
                    $system = isset($buildInModules[$moduleMenu]);
                    $this->custom->setItem("all.{$app}.menuOrder.{$i}.{$system}", $moduleMenu);

                    $i += 5;
                    $system = isset($buildInModules[$module]);
                    $this->custom->setItem("all.{$app}.menuOrder.{$i}.{$system}", $module);
                }
            }
            else
            {
                $system = isset($buildInModules[$moduleMenu]);
                $this->custom->setItem("all.{$app}.menuOrder.{$i}.{$system}", $moduleMenu);
            }

            $i += 5;
        }
        return !dao::isError();
    }

    /**
     * Disable approval of a flow.
     *
     * @param  string $module
     * @access public
     * @return bool
     */
    public function disableApproval($module)
    {
        $this->app->loadConfig('workflowaction');
        $flow = $this->getByModule($module);
        if($flow->approval == 'enabled')
        {
            $approval = $this->dao->select('*')->from($flow->table)
                ->where('deleted')->eq('0')
                ->andWhere('reviewStatus')->eq('doing')
                ->beginIF($module == 'caselib')->andWhere('type')->eq('library')->fi()
                ->beginIF($module == 'testsuite')->andWhere('type')->in('public,private')->fi()
                ->beginIF($module == 'execution')->andWhere('type')->eq('sprint')->fi()
                ->beginIF($module == 'project')->andWhere('type')->eq('project')->fi()
                ->beginIF($module == 'program')->andWhere('type')->eq('program')->fi()
                ->fetchAll();

            if($approval) return array('result' => 'fail', 'message' => $this->lang->workflowapproval->tips->processesInProgress);

            $this->dao->update(TABLE_WORKFLOW)->set('approval')->eq('disabled')->where('module')->eq($module)->exec();
            $this->dao->update(TABLE_WORKFLOWACTION)->set('status')->eq('disable')->where('module')->eq($module)->andWhere('action')->in($this->config->workflowaction->approval->actions)->exec();
        }
        return array('result' => 'success');
    }

    /**
     * Delete a flow by id.
     *
     * @param  int    $id
     * @param  object $null
     * @access public
     * @return bool
     */
    public function delete($id, $null = null)
    {
        $flow = $this->dao->select('*')->from(TABLE_WORKFLOW)->where('id')->eq($id)->fetch();
        if(!$flow) return false;
        if($flow->buildin) return false;

        /*  Delete sub tables first. */
        $result = $this->deleteSubTables($flow->module);
        if(!$result) return false;

        /* Drop table first. */
        try
        {
            $table = $this->dbh->tableExits($flow->table);
            if($table) $this->dbh->exec("DROP TABLE `$flow->table`");
        }
        catch(PDOException $exception)
        {
            dao::$errors = $exception->getMessage();
            return false;
        }

        $this->dao->delete()->from(TABLE_WORKFLOWACTION)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWFIELD)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWLABEL)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWSQL)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWVERSION)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWRELATION)->where('prev')->eq($flow->module)->orWhere('next')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOW)->where('id')->eq($id)->exec();
        $this->dao->delete()->from(TABLE_GROUPPRIV)->where('module')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_APPROVALOBJECT)->where('objectType')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_APPROVAL)->where('objectType')->eq($flow->module)->exec();
        $this->dao->delete()->from(TABLE_ACTION)->where('objectType')->eq($flow->module)->exec();

        return !dao::isError();
    }

    /**
     * Save field.
     *
     * @param  objetc $flow
     * @param  object $field
     * @param  array  $oldFields
     * @access public
     * @return bool | array
     */
    public function saveField($flow, $field, $oldFields)
    {
        $this->loadModel('workflowfield', 'flow');

        $field->field     = str_replace(' ', '', $field->field);
        $field->canExport = isset($field->canExport) ? $field->canExport : '0';
        $field->canSearch = isset($field->canSearch) ? $field->canSearch : '0';
        $field->isValue   = isset($field->isValue)   ? $field->isValue   : '0';

        if(is_object($field->options)) $field->options = (array)$field->options;

        if(is_array($field->options))
        {
            $duplicatedNames = array_diff_assoc($field->options, array_unique($field->options));
            if($duplicatedNames) return array('result' => 'fail', 'message' => array($field->field => array('options' => sprintf($this->lang->workflowfield->error->duplicatedName, implode(',', array_unique($duplicatedNames))))));

            $field->options = helper::jsonEncode($field->options);
        }

        switch($field->control)
        {
        case 'decimal':
            $field->type = 'decimal';
            list($field->integerDigits, $field->decimalDigits) = explode(',', $field->length);
            break;
        case 'textarea':
        case 'richtext':
        case 'checkbox':
        case 'multi-select':
            $field->type    = 'text';
            $field->default = '';
            break;
        case 'integer':
            $field->length = 0;
            break;
        case 'date':
        case 'datetime':
            $field->type   = $field->control;
            $field->length = 0;
        case 'formula':
            if($this->config->db->driver == 'dm' and $field->type == 'decimal')
            {
                list($integerDigits, $decimalDigits) = explode(',', $field->length);
                $integerDigits += 2;
                $field->length = "$integerDigits,$decimalDigits";
            }
            break;
        }

        $result = $this->workflowfield->processFieldLength($field);
        if(zget($result, 'result') == 'fail')
        {
            $result['field'] = $field->field;
            return $result;
        }

        $field = $result;

        /* If this options's value of field is user, set value of type and value of length. */
        if($field->options == 'user' && $field->type != 'text')
        {
            $field->type   = 'varchar';
            $field->length = 30;
        }

        $skip = 'id,integerDigits,decimalDigits,canSearch,canExport,show,width,position,readonly,mobileShow,summary,defaultValue,layoutRules,order,optionValue,optionText,sql,sqlVars,optionsData';

        if(!empty($field->id) && isset($oldFields[$field->id]))
        {
            $oldField = $oldFields[$field->id];

            $this->dao->update(TABLE_WORKFLOWFIELD)->data($field, $skip)->where('module')->eq($flow->module)->andWhere('id')->eq($field->id)->exec();

            if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

            if(!isset($field->default)) $field->default = $oldField->default;

            $result = $this->workflowfield->processTable($flow->table, $oldField, $field);
            if(is_array($result)) return $result;

            if($oldField->field != $field->field) $this->workflowfield->updateRelated($flow, $oldField, $field->field);
        }
        else
        {
            $this->dao->insert(TABLE_WORKFLOWFIELD)->data($field, $skip)->autoCheck()->exec();

            if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

            if($field->length) $field->type .= "($field->length)";

            $sql = "ALTER TABLE `$flow->table` ADD `$field->field` $field->type NOT NULL;";

            try
            {
                $this->dbh->query($sql);
            }
            catch(PDOException $exception)
            {
                $this->dao->delete()->from(TABLE_WORKFLOWFIELD)->where('module')->eq($module)->andWhere('field')->eq($field->field)->exec();

                return array('result' => 'fail', 'message' => $exception->getMessage() . ". The sql is : " . $sql);
            }
        }

        if($field->options == 'sql')
        {
            $result = $this->workflowfield->checkSqlAndVars($field->sql);
            if($result !== true) return array('result' => 'fail', 'message' => array('sql' => $result));

            $this->workflowfield->createSqlAndVars($flow->module, $field->field, $field->sql);
        }

        return true;
    }

    /**
     * Create a workflow.
     *
     * @access public
     * @return mixed
     */
    public function create()
    {
        if($this->post->navigator == 'primary') $_POST['app'] = $this->post->module;

        if($this->post->type == 'flow')
        {
            $license = $this->loadModel('common')->getLicense();
            if($license)
            {
                $license = $this->common->decrypt($license);
                $license = json_decode(helper::safe64Decode($license));
            }
            if(!empty($license->flowLimit))
            {
                $flowCount = $this->dao->select('COUNT(id) AS count')->from(TABLE_WORKFLOW)->where('type')->eq('flow')->fetch('count');
                if($flowCount >= $license->flowLimit)
                {
                    dao::$errors = sprintf($this->lang->workflow->error->flowLimit, $license->flowLimit);
                    return false;
                }
            }
        }

        $user   = $this->app->user->account;
        $now    = helper::now();
        $module = strtolower(str_replace(' ', '', $this->post->module));
        $flow   = fixer::input('post')
            ->add('table', $this->config->db->prefix . 'flow_' . $module)
            ->add('createdBy', $user)
            ->add('createdDate', $now)
            ->add('vision', $this->config->vision)
            ->setForce('module', $module)
            ->setIF($this->post->type == 'flow', 'status', 'wait')
            ->setIF($this->post->type == 'table', 'status', 'normal')
            ->remove('approvalFlow')
            ->get();

        if($this->post->type == 'flow' && $this->post->approval == 'enabled' && empty($_POST['approvalFlow'])) dao::$errors['approvalFlow'] = sprintf($this->lang->error->notempty, $this->lang->workflowapproval->approvalFlow);
        if(empty($flow->name)) dao::$errors['name'] = sprintf($this->lang->error->notempty, $this->lang->workflow->name);
        if(empty($flow->module))
        {
            dao::$errors['module'] = sprintf($this->lang->error->notempty, $this->lang->workflow->module);
        }
        else
        {
            if(isset($this->lang->{$flow->module})) dao::$errors['module'][] = sprintf($this->lang->workflow->error->conflict, $this->lang->workflow->module);

            /* Check if the module is a built-in module. */
            $buildInModules = $this->getBuildinModules();
            if(isset($buildInModules[$flow->module])) dao::$errors['module'][] = $this->lang->workflow->error->buildInModule;

            if(!validater::checkREG($flow->module, '|^[A-Za-z]+$|')) dao::$errors['module'][] = sprintf($this->lang->workflow->error->wrongCode, $this->lang->workflow->module);
        }
        if(isset($flow->app) && empty($flow->app)) dao::$errors['app'] = sprintf($this->lang->error->notempty, $this->lang->workflow->app);

        if(dao::isError()) return false;

        if(!empty($flow->app))
        {
            $this->sortModuleMenu($flow->app, $flow->module, $flow->position, $flow->positionModule, $buildInModules);
            $flow->position = !empty($flow->positionModule) ? $flow->position . $flow->positionModule : '';
        }

        $this->dao->insert(TABLE_WORKFLOW)->data($flow, $skip = 'positionModule')
            ->autoCheck()
            ->batchCheck($this->config->workflow->uniqueFields, 'unique')
            ->exec();

        $result = $this->dao->lastInsertId();
        if(dao::isError() and $this->app->getMethodName() == 'copy' and isset(dao::$errors['navigator']))
        {
            if($this->post->navigator == 'primary')   dao::$errors['positionModule'] = dao::$errors['navigator'];
            if($this->post->navigator == 'secondary') dao::$errors['app'] = dao::$errors['navigator'];
            if($this->post->navigator) unset(dao::$errors['navigator']);
        }

        return $result;
    }
}
