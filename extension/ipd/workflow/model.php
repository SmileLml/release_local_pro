<?php
/**
 * The model file of workflow flow of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflow
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowModel extends model
{
    /**
     * Get Apps
     *
     * @param  bool    $flowEntry
     * @access public
     * @return array
     */
    public function getApps($flowEntry = true)
    {
        $apps = array('');
        foreach($this->config->workflow->apps as $app)
        {
            if(!isset($this->lang->apps->$app)) continue;

            $apps[$app] = $this->lang->apps->$app;
        }

        if($flowEntry)
        {
            $entries = $this->loadModel('entry')->getFlowEntries();
            foreach($entries as $code => $name) $apps[$code] = $name;
        }

        return $apps;
    }

    /**
     * Get menus of an app.
     *
     * @param  string $app
     * @param  string $exclude  The exclude menus, separated by comma.
     * @access public
     * @return array
     */
    public function getAppMenus($app, $exclude = '')
    {
        $menus = array('');
        if(!$app) return $menus;

        if(isset($this->lang->apps->$app))
        {
            $this->app->loadLang('common', $app);

            if(isset($this->lang->$app->menuOrder))
            {
                $menuOrder = $this->lang->$app->menuOrder;
                if(is_array($menuOrder) or is_object($menuOrder))
                {
                    ksort($menuOrder);
                    foreach($menuOrder as $module)
                    {
                        if($exclude && strpos(",{$exclude},", ",{$module},") !== false) continue;

                        if(isset($this->lang->menu->$app->$module))
                        {
                            $menuItem = $this->lang->menu->$app->$module;
                            $menus[$module] = substr($menuItem, 0, (strpos($menuItem, '|')));
                        }
                    }
                }
            }
        }

        $flows = $this->getPairs('', 'flow', $app);
        foreach($flows as $module => $name) if($module != $exclude) $menus[$module] = $name;

        return $menus;
    }

    /**
     * Get build in modules.
     * This function is used to check if the code of an user defined module is exist.
     *
     * @param  string $root
     * @param  string $rootType
     * @access public
     * @return array
     */
    public function getBuildinModules($root = '', $rootType = 'app')
    {
        if(!$root) $root = $this->app->getBasePath() . 'app' . DIRECTORY_SEPARATOR;

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
                    if($rootType == 'app')
                    {
                        $appModules = $this->getBuildinModules($dirPath, 'module');
                        $modules    = array_merge($modules, $appModules);
                    }
                    else
                    {
                        $modules[$dir] = $dir;
                    }
                }
            }
            closedir($handle);
        }
        /* Set parent and sub as module name to make sure the user defined module can not use them as name. */
        $modules['parent'] = 'parent';
        $modules['sub']    = 'sub';
        return $modules;
    }

    /**
     * Get a flow by id.
     *
     * @param  int    $id
     * @access public
     * @return object
     */
    public function getByID($id)
    {
        $flow = $this->dao->select('*')->from(TABLE_WORKFLOW)->where('id')->eq($id)->fetch();
        if($flow)
        {
            $flow->actions        = $this->loadModel('workflowaction', 'flow')->getList($flow->module);
            $flow->positionModule = str_replace(array('before','after'), '', $flow->position);
            $flow->position       = strpos($flow->position, 'before') !== false ? 'before' : (strpos($flow->position, 'after') !== false ? 'after' : '');
        }

        return $flow;
    }

    /**
     * Get a flow by module.
     *
     * @param  string $module
     * @param  int    $actions
     * @access public
     * @return object
     */
    public function getByModule($module, $actions = false)
    {
        $flow = $this->dao->select('*')->from(TABLE_WORKFLOW)->where('module')->eq($module)->fetch();
        if($flow && $actions) $flow->actions = $this->loadModel('workflowaction', 'flow')->getList($flow->module);
        return $flow;
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

        return $this->dao->select('*')->from(TABLE_WORKFLOW)
            ->where(1)
            ->beginIF($type)->andWhere('type')->eq($type)->fi()
            ->beginIF($type == 'table' && $parent)->andWhere('parent')->eq($parent)->fi()
            ->beginIF($type == 'flow' && $app)->andWhere('app')->eq($app)->fi()
            ->beginIF($type == 'flow' && $status && $status != 'unused')->andWhere('status')->eq($status)->fi()
            ->beginIF($type == 'flow' && $status == 'unused')->andWhere('status')->in('wait,pause')->fi()
            ->beginIF($mode == 'bysearch')->andWhere($workflowQuery)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /**
     * Get flow pairs.
     *
     * @param  string $parent
     * @param  string $type
     * @param  string $app
     * @access public
     * @return array
     */
    public function getPairs($parent = 'all', $type = '', $app = '', $status = '')
    {
        return $this->dao->select('module, name')->from(TABLE_WORKFLOW)
            ->where(1)
            ->beginIF($parent != 'all')->andWhere('parent')->eq($parent)->fi()
            ->beginIF($type)->andWhere('type')->eq($type)->fi()
            ->beginIF($app)->andWhere('app')->eq($app)->fi()
            ->beginIF($status)->andWhere('status')->eq($status)->fi()
            ->fetchPairs();
    }

    /**
     * Get relation pairs.
     *
     * @param  string $prev
     * @param  array  $nextFlows
     * @access public
     * @return array
     */
    public function getRelationPairs($prev, $nextFlows = array())
    {
        return $this->dao->select('module, name')->from(TABLE_WORKFLOW)
            ->where(1)
            ->andWhere('type')->eq('flow')
            ->andWhere('buildin', true)->eq('0')
            ->orWhere('`module`')->in($nextFlows)
            ->markRight(1)
            ->andWhere('`module`')->ne($prev)
            ->fetchPairs();
    }

    /**
     * Create a flow.
     *
     * @access public
     * @return bool | int
     */
    public function create()
    {
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

        return $this->dao->lastInsertId();
    }

    /**
     * Create table and default fields.
     *
     * @param  object $flow
     * @param  string $type     default | approval
     * @param  string $mode     create | edit
     * @access public
     * @return bool
     */
    public function createFields($flow, $type = 'default', $mode = 'create')
    {
        $this->loadModel('action');
        $this->loadModel('workflowfield', 'flow');

        $fieldLang   = $this->lang->workflowfield->$type;
        $fieldConfig = $this->config->workflowfield->$type;

        $field = new stdclass();
        $field->module      = $flow->module;
        $field->createdBy   = $this->app->user->account;
        $field->createdDate = helper::now();
        $field->role        = $type;

        $sql = $mode == 'create' ? "CREATE TABLE IF NOT EXISTS `{$flow->table}` ( " : '';

        if(empty($fieldLang->fields)) return true;

        foreach($fieldLang->fields as $code => $name)
        {
            $field->field    = $code;
            $field->name     = ($flow->type == 'table' && $code == 'parent') ? $this->lang->workflowfield->tableParent : $name;
            $field->type     = zget($fieldConfig->fieldTypes, $code, 'varchar');
            $field->length   = zget($fieldConfig->fieldLength, $code, 255);
            $field->control  = zget($fieldConfig->controls, $code, 'input');
            $field->options  = zget($fieldConfig->options, $code, '[]');
            $field->default  = zget($fieldConfig->values, $code, '');
            $field->rules    = zget($fieldConfig->rules, $code, '');
            $field->readonly = zget($fieldConfig->readonly, $code, '1');
            $field->buildin  = zget($fieldConfig->buildin, $code, '0');
            if(is_array($field->options)) $field->options = helper::jsonEncode($field->options);

            $this->dao->insert(TABLE_WORKFLOWFIELD)->data($field)->autoCheck()->exec();

            $fieldID = $this->dao->lastInsertID();
            $this->action->create('workflowfield', $fieldID, 'created');

            $param = $fieldConfig->fields[$code];
            $sql .= $mode == 'create' ? "`$code` $param, " : "ALTER TABLE `{$flow->table}` ADD `$code` $param;";
        }

        $this->dao->update(TABLE_WORKFLOWFIELD)->set('`order` = `id`')->where('module')->eq($flow->module)->exec();
        if(dao::isError()) return false;

        if($mode == 'create') $sql .= $fieldConfig->indexes . ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

        try
        {
            $this->dbh->query($sql);
        }
        catch(PDOException $exception)
        {
            dao::$errors[] = $this->lang->workflow->error->createTableFail;
        }

        return !dao::isError();
    }

    /**
     * Create default actions.
     *
     * @param  object $flow
     * @param  string $type     default | approval
     * @access public
     * @return bool
     */
    public function createActions($flow, $type = 'default')
    {
        if(!$flow->type == 'table') return true;

        $this->loadModel('action');
        $this->loadModel('workflowaction', 'flow');

        $actionLang   = $this->lang->workflowaction->$type;
        $actionConfig = $this->config->workflowaction->$type;

        if($flow->buildin)
        {
            foreach(array('conditions', 'hooks', 'linkages', 'verifications') as $item)
            {
                if(empty($actionConfig->$item)) continue;
                $actionConfigItem = $actionConfig->$item;

                foreach($actionConfigItem as $code => $itemConfigs)
                {
                    foreach($itemConfigs as $configIndex => $itemConfig)
                    {
                        if(empty($itemConfig['fields'])) continue;
                        foreach($itemConfig['fields'] as $fieldIndex => $field)
                        {
                            if($field['field'] != 'createdBy' || empty($this->config->workflow->buildin->createdBy[$flow->module])) continue;
                            $actionConfigItem[$code][$configIndex]['fields'][$fieldIndex]['field'] = $this->config->workflow->buildin->createdBy[$flow->module];
                        }
                    }
                }
                $actionConfig->$item = $actionConfigItem;
            }
        }

        $action = new stdclass();
        $action->module      = $flow->module;
        $action->conditions  = '[]';
        $action->hooks       = '[]';
        $action->linkages    = '';
        $action->createdBy   = $this->app->user->account;
        $action->createdDate = helper::now();
        $action->order       = 0;
        $action->role        = $type;
        foreach($actionLang->actions as $code => $name)
        {
            $action->action     = $code;
            $action->name       = $name;
            $action->method     = zget($actionConfig->methods, $code, 'operate');
            $action->type       = zget($actionConfig->types, $code, 'single');
            $action->batchMode  = zget($actionConfig->batchModes, $code, 'same');
            $action->open       = zget($actionConfig->opens, $code, 'normal');
            $action->position   = zget($actionConfig->positions, $code, 'browseandview');
            $action->show       = zget($actionConfig->shows, $code, 'direct');
            $action->status     = zget($actionConfig->statuses, $code, 'enable');
            $action->buildin    = zget($actionConfig->buildin, $code, '0');
            $action->conditions = helper::jsonEncode(zget($actionConfig->conditions, $code, array()));
            $action->linkages   = helper::jsonEncode(zget($actionConfig->linkages, $code, array()));

            if(!empty($this->config->vision)) $action->vision = $this->config->vision;

            $this->dao->insert(TABLE_WORKFLOWACTION)->data($action)->autoCheck()->exec();

            $actionID = $this->dao->lastInsertID();
            $this->action->create('workflowaction', $actionID, 'created');

            $action->order++;
        }

        return !dao::isError();
    }

    /**
     * Create default labels.
     *
     * @param  object $flow
     * @param  string $type     default | approval
     * @access public
     * @return bool
     */
    public function createLabels($flow, $type = 'default')
    {
        if(!$flow->type == 'table') return true;

        $this->loadModel('action');
        $this->loadModel('workflowlabel', 'flow');

        $labelLang   = $this->lang->workflowlabel->$type;
        $labelConfig = $this->config->workflowlabel->$type;

        $label = new stdclass();
        $label->module      = $flow->module;
        $label->createdBy   = $this->app->user->account;
        $label->createdDate = helper::now();
        $label->role        = $type;
        $label->order       = $this->dao->select('IFNULL(MAX(`order`), 0) + 1 as `order`')->from(TABLE_WORKFLOWLABEL)->where('module')->eq($flow->module)->fetch()->order;

        foreach($labelLang->labels as $code => $name)
        {
            $label->code   = $code;
            $label->label  = $name;
            $label->params = helper::jsonEncode(zget($labelConfig->params, $code, ''));

            $this->dao->insert(TABLE_WORKFLOWLABEL)->data($label)->autoCheck()->exec();

            $labelID = $this->dao->lastInsertID();

            if($type == 'default') $this->dao->update(TABLE_WORKFLOWLABEL)->set('code')->eq('browse' . $labelID)->where('id')->eq($labelID)->exec();

            $labelID = $this->dao->lastInsertID();
            $this->action->create('workflowlabel', $labelID, 'created');

            $label->order++;
        }

        return !dao::isError();
    }

    /**
     * Create default layouts.
     *
     * @param  object $flow
     * @param  string $type     default | approval
     * @access public
     * @return bool
     */
    public function createLayouts($flow, $type = 'default')
    {
        if(!$flow->type == 'table') return true;

        $this->loadModel('workflowlayout', 'flow');

        $layoutConfig = $this->config->workflowlayout->$type;
        $notEmptyRule = $this->loadModel('workflowrule', 'flow')->getByTypeAndRule('system', 'notempty');

        $layout = new stdclass();
        $layout->module = $flow->module;

        foreach($layoutConfig->layouts as $action => $fields)
        {
            $layout->action = $action;

            $order = 1;
            foreach($fields as $field => $options)
            {
                $layout->field        = $field;
                $layout->order        = $order++;
                $layout->defaultValue = zget($options, 'default', '');
                $layout->layoutRules  = zget($options, 'require', '', zget($notEmptyRule, 'id', ''));

                $this->dao->insert(TABLE_WORKFLOWLAYOUT)->data($layout)->exec();
            }
        }
        return !dao::isError();
    }

    /**
     * Copy a flow to a new one.
     *
     * @param  string $source
     * @access public
     * @return bool
     */
    public function copy($source)
    {
        $flowID = $this->create();
        if(dao::isError()) return false;

        $flow   = $this->getByID($flowID);
        $source = $this->getByModule($source);

        /* Copy flowchart,css,js. */
        $data = new stdclass();
        $data->flowchart = $source->flowchart;
        $data->css       = $source->css;
        $data->js        = $source->js;
        $this->dao->update(TABLE_WORKFLOW)->data($data)->autoCheck()->where('module')->eq($flow->module)->exec();

        $this->copyTable($flow->table, $source->table);
        $this->copyFields($flow->module, $source->module);
        $this->copyActions($flow, $source);
        $this->copyLabels($flow->module, $source->module);
        $this->copyLayouts($flow->module, $source->module);
        $this->copySubTables($flow->module, $source->module);

        if(dao::isError())
        {
            $errors = dao::getError();

            $this->delete($flowID);

            dao::$errors = $errors;
        }

        return $flowID;
    }

    /**
     * Copy table of a flow to a new one.
     *
     * @param  string $table
     * @param  string $sourceTable
     * @access public
     * @return bool
     */
    public function copyTable($table, $sourceTable)
    {
        try
        {
            $this->dbh->query("CREATE TABLE $table LIKE $sourceTable");
        }
        catch(PDOException $exception)
        {
            dao::$errors[] = $exception->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Copy fields of a flow to a new one.
     *
     * @param  string $module
     * @param  string $source
     * @access public
     * @return bool
     */
    public function copyFields($module, $source)
    {
        $fields = $this->dao->select('*')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($source)->orderBy('`order`')->fetchAll();
        foreach($fields as $field)
        {
            unset($field->id);
            unset($field->editedBy);
            unset($field->editedDate);

            $field->module      = $module;
            $field->createdBy   = $this->app->user->account;
            $field->createdDate = helper::now();

            $this->dao->insert(TABLE_WORKFLOWFIELD)->data($field)->exec();
        }

        return !dao::isError();
    }

    /**
     * Copy actions of a flow to a new one.
     *
     * @param  object $flow
     * @param  object $source
     * @access public
     * @return bool
     */
    public function copyActions($flow, $source)
    {
        $actions = $this->dao->select('*')->from(TABLE_WORKFLOWACTION)
            ->where('module')->eq($source->module)
            ->beginIF($this->config->vision)->andWhere('vision')->eq($this->config->vision)->fi()
            ->orderBy('id')
            ->fetchAll();

        foreach($actions as $action)
        {
            unset($action->id);
            unset($action->editedBy);
            unset($action->editedDate);

            $conditions = str_replace($source->table, $flow->table, $action->conditions);

            $verifications = str_replace($source->table, $flow->table, $action->verifications);

            $hooks = str_replace("\"table\":\"{$source->module}\"", "\"table\":\"{$flow->module}\"", $action->hooks);
            $hooks = str_replace("`$source->table`", "`$flow->table`", $hooks);

            $action->module        = $flow->module;
            $action->conditions    = $conditions;
            $action->verifications = $verifications;
            $action->hooks         = $hooks;
            $action->createdBy     = $this->app->user->account;
            $action->createdDate   = helper::now();
            if(!empty($this->config->vision)) $action->vision = $this->config->vision;

            $this->dao->insert(TABLE_WORKFLOWACTION)->data($action)->exec();
        }

        return !dao::isError();
    }

    /**
     * Copy labels of a flow to a new one.
     *
     * @param  string $module
     * @param  string $source
     * @access public
     * @return bool
     */
    public function copyLabels($module, $source)
    {
        $labels = $this->dao->select('*')->from(TABLE_WORKFLOWLABEL)->where('module')->eq($source)->orderBy('`order`')->fetchAll();
        foreach($labels as $label)
        {
            unset($label->id);
            unset($label->editedBy);
            unset($label->editedDate);

            $label->module      = $module;
            $label->createdBy   = $this->app->user->account;
            $label->createdDate = helper::now();

            $this->dao->insert(TABLE_WORKFLOWLABEL)->data($label)->exec();

            $labelID = $this->dao->lastInsertID();

            $this->dao->update(TABLE_WORKFLOWLABEL)->set('code')->eq('browse' . $labelID)->where('id')->eq($labelID);
        }

        $this->dao->update(TABLE_WORKFLOWLABEL)->set("`code` = CONCAT('browse', `id`)")->where('module')->eq($module);

        return !dao::isError();
    }

    /**
     * Copy layouts of a flow to a new one.
     *
     * @param  string $module
     * @param  string $source
     * @access public
     * @return bool
     */
    public function copyLayouts($module, $source)
    {
        $layouts = $this->dao->select('*')->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($source)->orderBy('`order`')->fetchAll();
        foreach($layouts as $layout)
        {
            unset($layout->id);

            $layout->module = $module;
            if(strpos($layout->field, 'sub_') !== false)
            {
                $sourceSubTable = str_replace('sub_', '', $layout->field);
                $newSubTable    = $module . $sourceSubTable;
                $layout->field  = str_replace($sourceSubTable, $newSubTable, $layout->field);
            }

            $this->dao->insert(TABLE_WORKFLOWLAYOUT)->data($layout)->exec();
        }

        return !dao::isError();
    }

    /**
     * Copy relations of a flow to a new one.
     *
     * @param  string $module
     * @param  string $source
     * @access public
     * @return bool
     */
    public function copyRelations($module, $source)
    {
        $relations = $this->dao->select('*')->from(TABLE_WORKFLOWRELATION)->where('prev')->eq($source)->orderBy('id')->fetchAll();
        foreach($relations as $relation)
        {
            unset($relation->id);

            $relation->prev        = $module;
            $relation->createdBy   = $this->app->user->account;
            $relation->createdDate = helper::now();

            $this->dao->insert(TABLE_WORKFLOWRELATION)->data($relation)->exec();
        }

        return !dao::isError();
    }

    /**
     * Copy sub tables of a flow to a new one.
     *
     * @param  string $module
     * @param  string $source
     * @access public
     * @return bool
     */
    public function copySubTables($module, $source)
    {
        $subTables = $this->dao->select('*')->from(TABLE_WORKFLOW)
            ->where('parent')->eq($source)
            ->andWhere('type')->eq('table')
            ->fetchAll();

        foreach($subTables as $subTable)
        {
            unset($subTable->id);
            unset($subTable->editedBy);
            unset($subTable->editedDate);

            $sourceModule = $subTable->module;
            $sourceTable  = $subTable->table;

            $newModule = $module . $sourceModule;
            $newTable  = str_replace($sourceModule, $newModule, $sourceTable);

            $subTable->parent      = $module;
            $subTable->module      = $newModule;
            $subTable->table       = $newTable;
            $subTable->createdBy   = $this->app->user->account;
            $subTable->createdDate = helper::now();

            $this->dao->insert(TABLE_WORKFLOW)->data($subTable)->exec();

            $this->copyTable($newTable, $sourceTable);      // Copy table.
            $this->copyFields($newModule, $sourceModule);   // Copy fields.
            $this->copyLayouts($newModule, $sourceModule);  // Copy layouts.

            /* Update hooks of actions. */
            $actions = $this->dao->select('*')->from(TABLE_WORKFLOWACTION)->where('module')->eq($module)->fetchAll();
            foreach($actions as $action)
            {
                $hooks = str_replace("\"table\":\"{$sourceModule}\"", "\"table\":\"{$module}\"", $action->hooks);
                $hooks = str_replace("`$sourceTable`", "`$newTable`", $hooks);

                $this->dao->update(TABLE_WORKFLOWACTION)->set('hooks')->eq($hooks)->where('id')->eq($action->id)->exec();
            }
        }

        return !dao::isError();
    }

    /**
     * Update a flow.
     *
     * @param  int    $id
     * @access public
     * @return array
     */
    public function update($id)
    {
        $oldFlow = $this->getByID($id);
        if($oldFlow->type == 'table')
        {
            $this->lang->workflow->edit   = $this->lang->workflowtable->edit;
            $this->lang->workflow->module = $this->lang->workflowtable->module;
            $this->lang->workflow->name   = $this->lang->workflowtable->name;
        }

        $user = $this->app->user->account;
        $now  = helper::now();
        $flow = fixer::input('post')
            ->add('editedBy', $user)
            ->add('editedDate', $now)
            ->get();

        if(empty($flow->name)) dao::$errors['name'] = sprintf($this->lang->error->notempty, $this->lang->workflow->name);
        if(!$oldFlow->buildin && $oldFlow->type == 'flow' && $oldFlow->status != 'wait')
        {
            if(isset($flow->app) && empty($flow->app) && strpos(",{$this->config->workflow->require->edit},", ',app,') !== false) dao::$errors['app'] = sprintf($this->lang->error->notempty, $this->lang->workflow->app);
        }
        if(dao::isError()) return false;

        if(!empty($flow->app))
        {
            $this->sortModuleMenu($flow->app, $flow->module, $flow->position, $flow->positionModule);
            $flow->position = !empty($flow->positionModule) ? $flow->position . $flow->positionModule : '';
        }

        $this->dao->update(TABLE_WORKFLOW)->data($flow, $skip = 'positionModule')->where('id')->eq($id)->autoCheck()->exec();

        if(dao::isError()) return false;

        return commonModel::createChanges($oldFlow, $flow);
    }

    /**
     * Update actions.
     *
     * @param  object $flow
     * @param  string $type
     * @access public
     * @return bool
     */
    public function updateActions($flow, $type = 'default')
    {
        if($flow->type == 'table') return true;

        $this->loadModel('workflowaction', 'flow');
        $actionConfig = $this->config->workflowaction->$type;

        $actions = $this->dao->select('*')->from(TABLE_WORKFLOWACTION)->where('module')->eq($flow->module)->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()->fetchAll('action');
        foreach($actions as $action) $this->workflowaction->decode($action);

        foreach($actionConfig->updateActions as $code => $items)
        {
            if(!isset($actions[$code])) continue;

            $oldAction = $actions[$code];
            if($flow->buildin && $oldAction->buildin) continue;

            $action = new stdclass();
            foreach($items as $item)
            {
                if(empty($actionConfig->$item)) continue;
                $actionConfigItem = $actionConfig->$item;

                if($item == 'hooks')
                {
                    foreach($actionConfigItem[$code] as $key => $hook)
                    {
                        $actionConfigItem[$code][$key]['table'] = $flow->module;
                        $actionConfigItem[$code][$key]['sql' ]  = sprintf($actionConfigItem[$code][$key]['sql'], $flow->table);
                    }
                }

                if(in_array($item, array('conditions', 'hooks', 'linkages', 'verifications')))
                {
                    $newActionItem = $oldActionItem = $oldAction->$item;

                    foreach($actionConfigItem[$code] as $key => $itemValue)
                    {
                        $exist = false;
                        if(!empty($oldActionItem) && is_array($oldActionItem))
                        {
                            foreach($oldActionItem as $oldItemValue)
                            {
                                if($flow->buildin && !empty($itemValue['fields']))
                                {
                                    foreach($itemValue['fields'] as $fieldIndex => $field)
                                    {
                                        if($field['field'] != 'createdBy' || empty($this->config->workflow->buildin->createdBy[$flow->module])) continue;
                                        $itemValue['fields'][$fieldIndex]['field'] = $this->config->workflow->buildin->createdBy[$flow->module];
                                    }
                                }

                                if(helper::jsonEncode($oldItemValue) == helper::jsonEncode($itemValue))
                                {
                                    $exist = true;
                                    break;
                                }
                            }
                        }

                        if(!$exist) $newActionItem[] = $itemValue;
                    }

                    $action->$item = helper::jsonEncode($newActionItem);
                }
                else
                {
                    $action->$item = zget($actionConfigItem, $code, '');
                }
            }

            $this->dao->update(TABLE_WORKFLOWACTION)->data($action)->autoCheck()->where('module')->eq($flow->module)->andWhere('action')->eq($code)->exec();
        }

        return !dao::isError();
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
            $table = $this->dbh->query("SHOW TABLES LIKE '$flow->table'")->fetch();
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
     * Delete sub tables of a flow.
     *
     * @param  string $module
     * @access public
     * @return bool
     */
    public function deleteSubTables($module)
    {
        $subTables = $this->dao->select('`table`, module')->from(TABLE_WORKFLOW)->where('type')->eq('table')->andWhere('parent')->eq($module)->andWhere('buildin')->eq(0)->fetchPairs();

        /* Drop tables first. */
        try
        {
            foreach($subTables as $table => $module) $this->dbh->exec("DROP TABLE `$table`");
        }
        catch(PDOException $exception)
        {
            dao::$errors = $exception->getMessage();
            return false;
        }

        $this->dao->delete()->from(TABLE_WORKFLOWFIELD)->where('module')->in($subTables)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOW)->where('module')->in($subTables)->exec();

        return !dao::isError();
    }

    /**
     * Set js of a flow or an action.
     *
     * @param  int    $id
     * @param  string $type     flow | action
     * @access public
     * @return bool
     */
    public function setJS($id, $type = 'flow')
    {
        $data = new stdclass();
        $data->js         = $this->post->js;
        $data->editedBy   = $this->app->user->account;
        $data->editedDate = helper::now();

        $table = $type == 'flow' ? TABLE_WORKFLOW : TABLE_WORKFLOWACTION;

        $this->dao->update($table)->data($data, $skip = 'uid')->autoCheck()->where('id')->eq($id)->exec();

        return !dao::isError();
    }

    /**
     * Set css of a flow or an action.
     *
     * @param  int    $id
     * @param  string $type     flow | action
     * @access public
     * @return bool
     */
    public function setCSS($id, $type = 'flow')
    {
        $data = new stdclass();
        $data->css        = $this->post->css;
        $data->editedBy   = $this->app->user->account;
        $data->editedDate = helper::now();

        $table = $type == 'flow' ? TABLE_WORKFLOW : TABLE_WORKFLOWACTION;

        $this->dao->update($table)->data($data, $skip = 'uid')->autoCheck()->where('id')->eq($id)->exec();

        return !dao::isError();
    }

    /**
     * Set title field and content fields for full text search.
     *
     * @param  int    $id
     * @access public
     * @return bool
     */
    public function setFulltextSearch($id)
    {
        $flow = fixer::input('post')->setDefault('contentField', '')->get();

        if($this->post->contentField) $flow->contentField = trim(implode(',', $this->post->contentField), ',');

        $this->dao->update(TABLE_WORKFLOW)->data($flow)->where('id')->eq($id)->exec();

        return !dao::isError();
    }

    /**
     * Release a flow.
     *
     * @param  int    $id
     * @access public
     * @return bool | array
     */
    public function release($id)
    {
        $oldFlow = $this->getByID($id);
        $flow    = fixer::input('post')
            ->add('status', 'normal')
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', helper::now())
            ->remove('name,code,createApp')
            ->get();

        $entryID = 0;
        if($this->post->createApp)
        {
            $maxOrder = $this->dao->select('`order`')->from(TABLE_ENTRY)->orderBy('order_desc')->limit(1)->fetch('order');

            $entry = new stdclass();
            $entry->name    = $this->post->name;
            $entry->code    = $this->post->code;
            $entry->login   = 'route2flow';
            $entry->key     = md5(microtime());
            $entry->open    = 'iframe';
            $entry->ip      = '*';
            $entry->control = 'none';
            $entry->visible = 1;
            $entry->order   = $maxOrder + 10;
            $entry->flow    = '1';

            $entries = $this->loadModel('entry')->getPairs('', true);
            if(isset($entries[$entry->code]))
            {
                dao::$errors['code'] = sprintf($this->lang->entry->error->conflict, $entry->code);
                return false;
            }

            $this->dao->insert(TABLE_ENTRY)->data($entry)
                ->autoCheck()
                ->batchCheck('code, name', 'notempty')
                ->checkIF($entry->code, 'code', 'unique')
                ->checkIF($entry->code, 'code', 'code')
                ->checkIF($entry->code, 'code', 'notInt')
                ->exec();

            if(dao::isError()) return false;

            $entryID = $this->dao->lastInsertID();

            $flow->positionModule = 'index';
            $flow->app            = $this->post->code;
        }

        if(isset($flow->app) && empty($flow->app)) dao::$errors['app'] = sprintf($this->lang->error->notempty, $this->lang->workflow->app);
        if(dao::isError()) return false;

        if(!empty($flow->app))
        {
            $this->sortModuleMenu($flow->app, $oldFlow->module, $flow->position, $flow->positionModule);
            $flow->position = !empty($flow->positionModule) ? $flow->position . $flow->positionModule : '';
        }

        $this->dao->update(TABLE_WORKFLOW)->data($flow, $skip = 'positionModule')->where('id')->eq($id)->exec();

        if(dao::isError() && $entryID)
        {
            $errors = dao::getError();
            $this->dao->delete()->from(TABLE_ENTRY)->where('id')->eq($entryID)->exec();
            dao::$errors = $errors;

            return false;
        }

        return commonModel::createChanges($oldFlow, $flow);
    }

    /**
     * Get a version by module and version.
     *
     * @param  string $module
     * @param  string $version
     * @access public
     * @return object
     */
    public function getVersion($module, $version)
    {
        return $this->dao->select('*')->from(TABLE_WORKFLOWVERSION)->where('module')->eq($module)->andWhere('version')->eq($version)->fetch();
    }

    /**
     * Get version pairs.
     *
     * @param  object $flow
     * @access public
     * @return void
     */
    public function getVersionPairs($flow)
    {
        return $this->dao->select('version')->from(TABLE_WORKFLOWVERSION)
            ->where('module')->eq($flow->module)
            ->andWhere('version')->gt($flow->version)
            ->fetchPairs();
    }

    /**
     * Compare a flow with new version.
     *
     * @param  string $oldModule
     * @param  string $newVersion
     * @access public
     * @return array
     */
    public function compare($oldModule, $newVersion)
    {
        $newFlow = $this->getVersion($oldModule, $newVersion);
        if(!$newFlow) return array();

        $oldFields  = $this->dao->select('*')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($oldModule)->fetchAll('field');
        $oldActions = $this->dao->select('*')->from(TABLE_WORKFLOWACTION)->where('module')->eq($oldModule)->fetchAll('action');
        $oldLayouts = $this->dao->select('*')->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($oldModule)->fetchAll();
        $oldSqls    = $this->dao->select('*')->from(TABLE_WORKFLOWSQL)->where('module')->eq($oldModule)->fetchAll();
        $oldLabels  = $this->dao->select('*')->from(TABLE_WORKFLOWLABEL)->where('module')->eq($oldModule)->fetchAll('params');

        $newFields  = json_decode($newFlow->fields);
        $newActions = json_decode($newFlow->actions);
        $newLayouts = json_decode($newFlow->layouts);
        $newSqls    = json_decode($newFlow->sqls);
        $newLabels  = json_decode($newFlow->labels);

        $sqls = array();
        $sqls = array_merge($sqls, $this->getUpgradeSqls($oldFields,  $newFields,  $oldModule, 'workflowfield'));
        $sqls = array_merge($sqls, $this->getUpgradeSqls($oldActions, $newActions, $oldModule, 'workflowaction'));
        $sqls = array_merge($sqls, $this->getUpgradeSqls($oldLayouts, $newLayouts, $oldModule, 'workflowlayout'));
        $sqls = array_merge($sqls, $this->getUpgradeSqls($oldSqls,    $newSqls,    $oldModule, 'workflowsql'));
        $sqls = array_merge($sqls, $this->getUpgradeSqls($oldLabels,  $newLabels,  $oldModule, 'workflowlabel'));

        /* Compare child tables. */
        $childPairs = $this->getPairs($parent = $oldModule, $type = 'table');
        foreach($childPairs as $childModule => $name)
        {
            $childSqls = $this->compare($childModule, $newVersion);
            $sqls = array_merge($sqls, $childSqls);
        }

        return $sqls;
    }

    /**
     * Get upgrade sqls.
     *
     * @param  object $oldObjects
     * @param  object $newObjects
     * @param  string $module
     * @param  string $type
     * @return array
     */
    public function getUpgradeSqls($oldObjects, $newObjects, $module, $type)
    {
        $flow = $this->getByModule($module);
        if(!$flow) return array();

        if(!$type) return array();

        if($type == 'workflowlayout' or $type == 'workflowsql')
        {
            foreach($oldObjects as $key => $object)
            {
                $oldObjects[$object->action . $object->field] = $object;
                unset($oldObjects[$key]);
            }
        }

        $sqls  = array();
        $dsqls = array();
        $user  = $this->app->user->account;
        $now   = helper::now();
        $table = $this->config->objectTables[$type];
        foreach($newObjects as $object)
        {
            $key  = $object->id;
            $skip = '';
            if($type == 'workflowfield')
            {
                $key  = $object->field;
                $skip = 'field';
            }
            elseif($type == 'workflowaction')
            {
                $key  = $object->action;
                $skip = 'action';
            }
            elseif($type == 'workflowlabel')
            {
                $key  = $object->params;
                $skip = 'label';
            }
            elseif($type == 'workflowlayout' or $type == 'workflowsql')
            {
                $key  = $object->action . $object->field;
                $skip = 'action,field';
            }
            if($type != 'workflowsql') $skip .= ',order';

            if(isset($oldObjects[$key]))
            {
                $oldObject = $oldObjects[$key];
                if($oldObject != $object)
                {
                    $sqlFields = array();
                    foreach($object as $key => $value)
                    {
                        if(strpos(",id,module,$skip,createdBy,createdDate,editedBy,editedDate,", ",$key,") !== false) continue;

                        if($oldObject->$key != $object->$key)
                        {
                            $sqlFields[] = " `$key` = '$value' ";
                        }
                    }
                    if($sqlFields)
                    {
                        $sqlFields[] = " `editedBy` = '$user' ";
                        $sqlFields[] = " `editedDate` = '$now' ";

                        $sqls[] = "UPDATE $table SET " . implode(',', $sqlFields) . " WHERE `id` = $oldObject->id;";

                        if($type == 'workflowfield')
                        {
                            $sql = "ALTER TABLE `{$flow->table}` CHANGE `$object->field` `$object->field` $object->type";
                            if($object->length)  $sql .= "($object->length) ";
                            $sql .= ' NOT NULL ';
                            if($object->default) $sql .= " DEFAULT '$object->default'";
                            $sql .= ';';

                            $dsqls[] = $sql;
                        }
                    }

                }
            }
            else
            {
                $sqlFields = array();
                $sqlValues = array();

                foreach($object as $key => $value)
                {
                    if($key == 'id') continue;
                    if($key == 'createdBy')   $value = $user;
                    if($key == 'createdDate') $value = $now;
                    if($key == 'editedBy')    $value = '';
                    if($key == 'editedDate')  $value = '0000-00-00 00:00:00';

                    $sqlFields[] = "`$key`";
                    $sqlValues[] = "'$value'";

                }
                $sqlFields = implode(',', $sqlFields);
                $sqlValues = implode(',', $sqlValues);

                $sqls[] = "INSERT INTO $table ($sqlFields) VALUES ($sqlValues);";
                if($type == 'workflowfield')
                {
                    $sql = "ALTER TABLE `{$flow->table}` ADD `$object->field` $object->type";
                    if($object->length)  $sql .= "($object->length) ";
                    $sql .= ' NOT NULL ';
                    if($object->default) $sql .= " DEFAULT '$object->default'";
                    $sql .= ';';

                    $dsqls[] = $sql;
                }
            }
        }

        return array_merge($sqls, $dsqls);
    }

    /**
     * Backup a flow.
     *
     * @param  string $module
     * @access public
     * @return bool
     */
    public function backup($module)
    {
        $flow = $this->getByModule($module);
        if(!$flow) return false;

        $fields  = $this->dao->select('*')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($module)->fetchAll();
        $actions = $this->dao->select('*')->from(TABLE_WORKFLOWACTION)->where('module')->eq($module)->fetchAll();
        $layouts = $this->dao->select('*')->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($module)->fetchAll();
        $sqls    = $this->dao->select('*')->from(TABLE_WORKFLOWSQL)->where('module')->eq($module)->fetchAll();
        $labels  = $this->dao->select('*')->from(TABLE_WORKFLOWLABEL)->where('module')->eq($module)->fetchAll();
        $table   = $this->dbh->query("SHOW CREATE TABLE `{$flow->table}`")->fetch();
        $datas   = $this->dao->select('*')->from("`{$flow->table}`")->fetchAll();

        $backup = new stdclass();
        $backup->module  = $module;
        $backup->version = $flow->version;
        $backup->fields  = helper::jsonEncode($fields);
        $backup->actions = helper::jsonEncode($actions);
        $backup->layouts = helper::jsonEncode($layouts);
        $backup->sqls    = helper::jsonEncode($sqls);
        $backup->labels  = helper::jsonEncode($labels);
        $backup->table   = str_replace("\n", '', $table->{'Create Table'});
        $backup->datas   = helper::jsonEncode($datas);

        $this->dao->replace(TABLE_WORKFLOWVERSION)->data($backup)->exec();

        if(dao::isError()) return array('result' => 'fail', 'errors' => dao::getError());

        /* Backup child tables. */
        $childPairs = $this->getPairs($parent = $module, $type = 'table');
        foreach($childPairs as $childModule => $name)
        {
            $this->backup($childModule);
        }

        if(dao::isError()) return array('result' => 'fail', 'errors' => dao::getError());

        return true;
    }

    /**
     * Upgrade a flow.
     *
     * @param  string $module
     * @param  string $toVersion
     * @access public
     * @return array
     */
    public function upgrade($module, $toVersion)
    {
        $errors = array();
        $sqls   = $this->compare($module, $toVersion);
        try
        {
            foreach($sqls as $sql) $this->dbh->exec($sql);
        }
        catch(PDOException $exception)
        {
            $errors[] = $exception->getMessage();
        }
        if($errors) return array('result' => 'fail', 'errors' => $errors);

        /* Upgrade module's version. */
        $this->dao->update(TABLE_WORKFLOW)->set('version')->eq($toVersion)->where('module')->eq($module)->exec();
        /* Upgrade child tables' version. */
        $this->dao->update(TABLE_WORKFLOW)->set('version')->eq($toVersion)->where('parent')->eq($module)->andWhere('type')->eq('table')->exec();

        return array('result' => 'success');
    }

    /**
     * Install a new flow.
     *
     * @param  string $module
     * @param  string $toVersion
     * @access public
     * @return array
     */
    public function install($module, $toVersion, $parentModule)
    {
        $flow = $this->getByModule($module);
        if(!$flow) return array('result' => 'fail', 'errors' => $this->lang->workflow->upgrade->installFail);

        $version    = $this->getVersion($module, $toVersion);
        $childPairs = $this->getPairs($parent = $module, $type = 'table');
        if($version)
        {
            $fields  = json_decode($version->fields);
            $actions = json_decode($version->actions);
            $layouts = json_decode($version->layouts);
            $sqls    = json_decode($version->sqls);
            $labels  = json_decode($version->labels);
            $table   = $version->table;
        }
        else
        {
            $fields  = $this->dao->select('*')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($module)->fetchAll();
            $actions = $this->dao->select('*')->from(TABLE_WORKFLOWACTION)->where('module')->eq($module)->fetchAll();
            $layouts = $this->dao->select('*')->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($module)->fetchAll();
            $sqls    = $this->dao->select('*')->from(TABLE_WORKFLOWSQL)->where('module')->eq($module)->fetchAll();
            $labels  = $this->dao->select('*')->from(TABLE_WORKFLOWLABEL)->where('module')->eq($module)->fetchAll();
            $table   = $this->dbh->query("SHOW CREATE TABLE `$flow->table`")->fetch();
            $table   = str_replace("\n", '', $table->{'Create Table'});
        }

        $user      = $this->app->user->account;
        $now       = helper::now();
        $newModule = $module . (int)((float)$toVersion * 10);
        $newTable  = $this->config->db->prefix . 'flow_' . $newModule;
        $tableSql  = "DROP TABLE IF EXISTS `{$newTable}`;";
        $tableSql .= str_replace($flow->table, $newTable, $table);

        if($parentModule) $flow->parent = $parentModule;

        $flow->module      = $newModule;
        $flow->table       = $newTable;
        $flow->name       .= $toVersion;
        $flow->version     = $toVersion;
        $flow->createdBy   = $user;
        $flow->createdDate = $now;

        $this->dao->insert(TABLE_WORKFLOW)->data($flow, $skip = 'id, editedBy, editedDate')->autoCheck()->exec();

        foreach($fields as $field)
        {
            $field->module      = $newModule;
            $field->createdBy   = $user;
            $field->createdDate = $now;

            $this->dao->insert(TABLE_WORKFLOWFIELD)->data($field, $skip = 'id, editedBy, editedDate')->autoCheck()->exec();
        }

        foreach($actions as $action)
        {
            $action->module      = $newModule;
            $action->createdBy   = $user;
            $action->createdDate = $now;

            $this->dao->insert(TABLE_WORKFLOWACTION)->data($action, $skip = 'id, editedBy, editedDate')->autoCheck()->exec();
        }

        foreach($layouts as $layout)
        {
            $layout->module = $newModule;
            foreach($childPairs as $childModule => $name)
            {
                if($layout->field == $childModule) $layout->field .= (int)((float)$toVersion * 10);
            }

            $this->dao->insert(TABLE_WORKFLOWLAYOUT)->data($layout, $skip = 'id')->autoCheck()->exec();
        }

        foreach($sqls as $sql)
        {
            $sql->module      = $newModule;
            $sql->createdBy   = $user;
            $sql->createdDate = $now;

            $this->dao->insert(TABLE_WORKFLOWSQL)->data($sql, $skip = 'id, editedBy, editedDate')->autoCheck()->exec();
        }

        foreach($labels as $label)
        {
            $label->module      = $newModule;
            $label->createdBy   = $user;
            $label->createdDate = $now;

            $this->dao->insert(TABLE_WORKFLOWLABEL)->data($label, $skip = 'id, editedBy, editedDate')->autoCheck()->exec();
        }

        $errors = array();
        if(dao::isError()) $errors = dao::getError();

        try
        {
            $this->dbh->query($tableSql);
        }
        catch(PDOException $exception)
        {
            $errors[] = $exception->getMessage();
        }

        if($errors) return array('result' => 'fail', 'errors' => $errors);

        /* Install new instance of child tables. */
        foreach($childPairs as $childModule => $name)
        {
            $result = $this->install($childModule, $toVersion, $newModule);
            if($result['result'] == 'fail')
            {
                $errors += $result['errors'];
            }
        }

        if($errors) return array('result' => 'fail', 'errors' => $errors);

        /* Upgrade old module's version. */
        $this->dao->update(TABLE_WORKFLOW)->set('version')->eq($toVersion)->where('module')->eq($module)->exec();
        /* Upgrade child tables' version. */
        $this->dao->update(TABLE_WORKFLOW)->set('version')->eq($toVersion)->where('parent')->eq($module)->andWhere('type')->eq('table')->exec();

        return array('result' => 'success');
    }

    /**
     * Save layout of an action in quick mode.
     *
     * @param  string $module
     * @param  string $action
     * @access public
     * @return array
     */
    public function saveLayout($module, $action)
    {
        $fields = json_decode($this->post->fields);

        /* Check default value of all fields. */
        $errors = array();
        $this->loadModel('workflowfield', 'flow');
        foreach($fields as $field)
        {
            if(!empty($field->defaultValue))
            {
                /* Compatible fast and advanced. */
                $field->default = $field->defaultValue;
                $result = $this->workflowfield->checkDefaultValue($field);
                unset($field->default);

                if(is_array($result) && zget($result, 'result') == 'fail') $errors[$field->field] = $result['message'];
            }
        }

        if(!empty($errors)) return array('result' => 'fail', 'message' => $errors);

        /* Remove layout fields except the sub-table fields. */
        $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)
            ->where('module')->eq($module)
            ->andWhere('action')->eq($action)
            ->beginIF($this->config->vision)->andWhere('vision')->eq($this->config->vision)->fi()
            ->andWhere('field')->notLike('sub\_%')
            ->exec();

        $flow      = $this->getByModule($module);
        $oldFields = $this->dao->select('*')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($module)->fetchAll('id');

        $layout = new stdclass();
        $layout->module = $module;
        $layout->action = $action;

        $order = 1;
        foreach($fields as $field)
        {
            if($field->field != 'actions' && $field->field != 'file')
            {
                $result = $this->saveField($flow, $field, $oldFields);

                if(zget($result, 'result') == 'fail') return $result;
            }

            $position = $field->position;
            if($action == 'view' && !$position) $position = isset($this->config->workflowfield->default->fields[$field->field]) ? 'basic' : 'info';

            $layout->field      = $field->field;
            $layout->width      = $field->width;
            $layout->position   = $position;
            $layout->readonly   = $field->readonly;
            $layout->mobileShow = $field->mobileShow;
            $layout->summary    = $field->summary;
            if(($field->control == 'multi-select' || $field->control == 'checkbox') && is_array($field->defaultValue))
            {
                $field->defaultValue = array_filter($field->defaultValue);
                $field->defaultValue = implode(",", $field->defaultValue);
            }
            $layout->defaultValue = $field->defaultValue;
            $layout->layoutRules  = isset($field->layoutRules) ? $field->layoutRules : '';
            $layout->order        = $order;
            if(!empty($this->config->vision)) $layout->vision = $this->config->vision;
            $this->dao->insert(TABLE_WORKFLOWLAYOUT)->data($layout)->exec();

            if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

            $order++;
        }

        return array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload');
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
     * Check flow chart data, unset relation if it has no action.
     *
     * @param  string    $flowchart
     * @access public
     * @return array
     */
    public function processFLowchart($flowchart)
    {
        $chartItems = json_decode($flowchart);

        $flows = array();
        foreach($chartItems as $chartItem)
        {
            if($chartItem->type == 'relation') continue;
            if($chartItem->type == 'process') $chartItem->codeReadonly = true;

            $flows[$chartItem->id] = $chartItem->id;
        }

        foreach($chartItems as $key => $chartItem)
        {
            if($chartItem->type != 'relation') continue;

            if(!isset($flows[$chartItem->from]) or !isset($flows[$chartItem->to])) unset($chartItems[$key]);
        }

        return $chartItems;
    }

    /**
     * Save flowchart.
     *
     * @param  string    $module
     * @param  string    $chartItems
     * @access public
     * @return bool
     */
    public function saveFlowchart($module, $chartItems)
    {
        $this->dao->update(TABLE_WORKFLOW)->set('flowchart')->eq(json_encode($chartItems))->where('module')->eq($module)->exec();

        return !dao::isError();
    }

    /**
     * Check field and layout before release.
     *
     * @param  string $module
     * @access public
     * @return array
     */
    public function checkFieldAndLayout($module)
    {
        $this->app->loadModuleConfig('workflowfield', 'flow');
        $this->app->loadLang('workflowfield', 'flow');
        $this->app->loadLang('workflowaction', 'flow');
        $this->app->loadLang('workflowlayout', 'flow');

        $errors       = array();
        $approval     = $this->dao->select('approval')->from(TABLE_WORKFLOW)->where('module')->eq($module)->fetch('approval');
        $actions      = $this->dao->select('action, name')->from(TABLE_WORKFLOWACTION)->where('module')->eq($module)->andWhere('status')->eq('enable')->andWhere('open')->ne('none')->andWhere('`virtual`')->eq(0)->fetchPairs();
        $fields       = $this->dao->select('id')->from(TABLE_WORKFLOWFIELD)->where('module')->eq($module)->andWhere('field')->notin(array_keys($this->config->workflowfield->default->fields))->fetchPairs();
        $layoutFields = $this->dao->select('DISTINCT action')->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($module)->andWhere('action')->in(array_keys($actions))->andWhere('field')->notin('actions,file')->fetchPairs();

        if(empty($fields)) $errors[] = $this->lang->workflowfield->error->emptyCustomField;

        foreach($actions as $action => $name)
        {
            if(isset($layoutFields[$action]) || (!empty($this->config->openedApproval) && $approval == 'enabled' && $action == 'approvalsubmit'))
            {
                unset($actions[$action]);
                continue;
            }
        }

        if(!empty($actions)) $errors[] = sprintf($this->lang->workflowlayout->error->emptyLayout, implode(', ', $actions));

        return $errors;
    }

    /**
     * Check if user can click the action of a flow.
     *
     * @param  object $flow
     * @param  string $action
     * @access public
     * @return bool
     */
    public function isClickable($flow, $action)
    {
        $action    = strtolower($action);
        $clickable = commonModel::hasPriv('workflow', $action);
        if(!$clickable) return false;

        switch($action)
        {
            case 'copy':
            case 'delete':
            case 'flowchart':  return $clickable && !$flow->buildin;
            case 'upgrade':    return $clickable && !$flow->buildin && !empty($flow->newVersion);
            case 'release':    return $clickable && !$flow->buildin && $flow->status == 'wait';
            case 'deactivate': return $clickable && !$flow->buildin && $flow->status == 'normal';
            case 'activate':   return $clickable && !$flow->buildin && $flow->status == 'pause';
        }

        return true;
    }

    /**
     * Sort module menu.
     *
     * @param  string $app
     * @param  string $module
     * @param  string $position
     * @param  string $positionModule
     * @access public
     * @return void
     */
    public function sortModuleMenu($app, $module, $position, $positionModule, $buildInModules = array())
    {
        $this->app->loadLang('common', $app);
        if(!isset($this->lang->menu->{$app})) return true;

        $this->loadModel('setting');
        if(empty($buildInModules)) $buildInModules = $this->getBuildinModules();

        if(!isset($this->lang->{$app})) $this->lang->{$app} = new stdclass();
        if(!isset($this->lang->{$app}->menuOrder)) $this->lang->{$app}->menuOrder = array();
        foreach($this->lang->menu->{$app} as $moduleName => $moduleMenu)
        {
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
                    $this->setting->setItem("all.{$app}.common.menuOrder.{$i}.{$system}", $module, 'lang');

                    $i += 5;
                    $system = isset($buildInModules[$moduleMenu]);
                    $this->setting->setItem("all.{$app}.common.menuOrder.{$i}.{$system}", $moduleMenu, 'lang');
                }
                elseif($position == 'after')
                {
                    $system = isset($buildInModules[$moduleMenu]);
                    $this->setting->setItem("all.{$app}.common.menuOrder.{$i}.{$system}", $moduleMenu, 'lang');

                    $i += 5;
                    $system = isset($buildInModules[$module]);
                    $this->setting->setItem("all.{$app}.common.menuOrder.{$i}.{$system}", $module, 'lang');
                }
            }
            else
            {
                $system = isset($buildInModules[$moduleMenu]);
                $this->setting->setItem("all.{$app}.common.menuOrder.{$i}.{$system}", $moduleMenu, 'lang');
            }

            $i += 5;
        }
        return !dao::isError();
    }

    /**
     * On-off approval.
     *
     * @param  string $module
     * @access public
     * @return void
     */
    public function setApproval($module)
    {
        if($this->post->approval == 'enabled')  return $this->enableApproval($module);
        if($this->post->approval == 'disabled') return $this->disableApproval($module);
        return array('result' => 'fail', 'message' => sprintf($this->lang->error->notempty, $this->lang->workflowapproval->approval));
    }

    /**
     * Enable approval of a flow.
     *
     * @param  string $module 
     * @access public
     * @return bool
     */
    public function enableApproval($module)
    {
        $approvalFlow = $this->post->approvalFlow;
        if(empty($approvalFlow)) return array('result' => 'fail', 'message' => array('approvalFlow' => sprintf($this->lang->error->notempty, $this->lang->workflowapproval->approvalFlow))); 

        if(in_array($module, $this->config->workflow->buildin->noApproval)) return array('result' => 'fail', 'message' => $this->lang->workflowapproval->disableApproval);

        $exists = $this->checkApproval($module);
        $flow   = $this->getByModule($module);

        if(!empty($exists['fields']) || !empty($exists['actions']))
        {
            if($this->post->cover)
            {
                $this->cover($flow, $exists);
            }
            else
            {
                $message = $this->createMessage($exists);
                return array('result' => 'fail', 'coverMessage' => $message);
            }
        }

        $this->createApprovalRelation($flow);
        $this->createApprovalObject($approvalFlow, $module);

        $this->dao->update(TABLE_WORKFLOW)->set('approval')->eq('enabled')->where('module')->eq($module)->exec();
        $this->dao->update(TABLE_WORKFLOWACTION)->set('status')->eq('enable')->where('module')->eq($module)->andWhere('action')->in($this->config->workflowaction->approval->actions)->exec();

        if(dao::isError()) return array('result' => 'fail', 'message' => dao::getError());

        return array('result' => 'success');
    }

    /**
     * Create approval relation.
     *
     * @param  object $flow
     * @access public
     * @return bool
     */
    public function createApprovalRelation($flow)
    {
        $this->createFields($flow, 'approval', 'edit');
        $this->createActions($flow, 'approval');
        $this->createLabels($flow, 'approval');
        $this->createLayouts($flow, 'approval');
        $this->updateActions($flow, 'approval');

        return !dao::isError();
    }

    /**
     * Create approval object.
     *
     * @param  int    $approvalFlow
     * @param  string $module
     * @access public
     * @return bool 
     */
    public function createApprovalObject($approvalFlow, $module)
    {
        $data = new stdclass();
        $data->flow       = $approvalFlow;
        $data->objectType = $module;

        $this->dao->delete()->from(TABLE_APPROVALFLOWOBJECT)->where('objectType')->eq($module)->exec();
        $this->dao->insert(TABLE_APPROVALFLOWOBJECT)->data($data)->exec();

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
        $flow = $this->getByModule($module);
        if($flow->approval == 'enabled')
        {
            $approval = $this->dao->select('*')->from($flow->table)->where('deleted')->eq('0')->andWhere('reviewStatus')->eq('doing')->fetchAll();
            if($approval) return array('result' => 'fail', 'message' => $this->lang->workflowapproval->tips->processesInProgress);

            $this->dao->update(TABLE_WORKFLOW)->set('approval')->eq('disabled')->where('module')->eq($module)->exec();
            $this->dao->update(TABLE_WORKFLOWACTION)->set('status')->eq('disable')->where('module')->eq($module)->andWhere('action')->in($this->config->workflowaction->approval->actions)->exec();
        }
        return array('result' => 'success');
    }

    /**
     * Check fields, actions and labels before open an approval.
     *
     * @param  string module
     * @access public
     * @return array 
     */
    public function checkApproval($module)
    {
        $this->loadModel('workflowfield', 'flow');
        $this->loadModel('workflowaction', 'flow');
        $this->loadModel('workflowlayout', 'flow');
        $this->loadModel('workflowlabel', 'flow');
        $existFields  = array();
        $existActions = array();

        $fields = $this->dao->select('name, field, role')->from(TABLE_WORKFLOWFIELD)
            ->where('module')->eq($module)
            ->andWhere('field')->in(array_keys($this->config->workflowfield->approval->fields))
            ->fetchAll();

        foreach($fields as $field)
        {
            if($field->role == 'approval')
            {
                unset($this->lang->workflowfield->approval->fields[$field->field]);
            }
            else
            {
                $existFields[$field->field] = $field;
            }
        }

        $actions = $this->dao->select('name, action, role')->from(TABLE_WORKFLOWACTION)
            ->where('module')->eq($module)
            ->andWhere('action')->in(array_keys($this->lang->workflowaction->approval->actions))
            ->fetchAll();

        foreach($actions as $action)
        {
            if($action->role == 'approval')
            {
                unset($this->lang->workflowaction->approval->actions[$action->action]);
                unset($this->config->workflowlayout->approval->layouts[$action->action]);
            }
            else
            {
                $existActions[$action->action] = $action;
            }
        }

        $label = $this->dao->select('id')->from(TABLE_WORKFLOWLABEL)
            ->where('module')->eq($module)
            ->andWhere('role')->eq('approval')
            ->andWhere('code')->eq('review')
            ->fetch();
        if($label) unset($this->lang->workflowlabel->approval->labels['review']);

        return array('fields' => $existFields, 'actions' => $existActions);
    }

    /**
     * Create message.
     *
     * @param  array $exists
     * @access public
     * @return string 
     */
    public function createMessage($exists)
    {
        $message = '';
        if(!empty($exists['fields']))
        {
            $message .= '<p>' . $this->lang->workflowapproval->conflictField;
            $message .= '<strong>';
            $fields   = array();
            foreach($exists['fields'] as $field) $fields[] = $field->name;
            $message .= implode($this->lang->workflow->delimiter, $fields);
            $message .= '</strong></p>';
        }
        if(!empty($exists['actions']))
        {
            $message .= '<p>' . $this->lang->workflowapproval->conflictAction;
            $message .= '<strong>';
            $actions  = array();
            foreach($exists['actions'] as $action) $actions[] = $action->name;
            $message .= implode($this->lang->workflow->delimiter, $actions);
            $message .= '</strong></p>';
        }
        return $message;
    }

    /**
     * Cover custom field and action when open the approval.
     *
     * @param  object $flow 
     * @param  array  $exists 
     * @access public
     * @return void
     */
    public function cover($flow, $exists)
    {
        $module  = $flow->module;
        $fields  = array_keys($exists['fields']);
        $actions = array_keys($exists['actions']);

        $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($module)->andWhere('field')->in($fields)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)->where('module')->eq($module)->andWhere('action')->in($actions)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWFIELD)->where('module')->eq($module)->andWhere('field')->in($fields)->exec();
        $this->dao->delete()->from(TABLE_WORKFLOWACTION)->where('module')->eq($module)->andWhere('action')->in($actions)->exec();

        $sql = "ALTER TABLE `{$flow->table}` ";
        foreach($fields as $field)
        {
            $sql .= "DROP `$field`,";
        }
        $sql = rtrim($sql, ',') . ';';

        try
        {
            $this->dbh->query($sql);
            return true;
        }
        catch(Exception $exception)
        {
            dao::$errors = $exception->getMessage();
            return false;
        }
    }
}
