<?php
class zentaomaxExecution extends executionModel
{
    /**
     * Get taskes by search.
     *
     * @param  string $condition
     * @param  object $pager
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getSearchTasks($condition, $pager, $orderBy)
    {
        if(strpos($condition, '`assignedTo`') !== false)
        {
            preg_match("/`assignedTo`\s+(([^']*) ('([^']*)'))/", $condition, $matches);
            $condition = preg_replace('/`(\w+)`/', 't1.`$1`', $condition);
            $condition = str_replace("t1.$matches[0]", "(t1.$matches[0] or (t1.mode = 'multi' and t2.`account` $matches[1] and t1.status != 'closed' and t2.status != 'done') )", $condition);
        }

        $sql = $this->dao->select('t1.id')->from(TABLE_TASK)->alias('t1');
        if(strpos($condition, '`assignedTo`') !== false) $sql = $sql->leftJoin(TABLE_TASKTEAM)->alias('t2')->on("t2.task = t1.id and t2.account $matches[1]");

        $orderBy = array_map(function($value){return 't1.' . $value;}, explode(',', $orderBy));
        $orderBy = implode(',', $orderBy);

        $taskIdList = $sql->where($condition)
            ->andWhere('deleted')->eq(0)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        $tasks = $this->dao->select('t1.*, t2.id AS storyID, t2.title AS storyTitle, t2.product, t2.branch, t2.version AS latestStoryVersion, t2.status AS storyStatus, t3.realname AS assignedToRealName, t4.name AS designName, t4.version AS latestDesignVersion')
             ->from(TABLE_TASK)->alias('t1')
             ->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story = t2.id')
             ->leftJoin(TABLE_USER)->alias('t3')->on('t1.assignedTo = t3.account')
             ->leftJoin(TABLE_DESIGN)->alias('t4')->on('t1.design = t4.id')
             ->where('t1.deleted')->eq(0)
             ->andWhere('t1.id')->in(array_keys($taskIdList))
             ->orderBy($orderBy)
             ->fetchAll('id');

        if(empty($tasks)) return array();

        $taskTeam = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('task')->in(array_keys($tasks))->fetchGroup('task');
        if(!empty($taskTeam))
        {
            foreach($taskTeam as $taskID => $team) $tasks[$taskID]->team = $team;
        }

        $parents = array();
        foreach($tasks as $task)
        {
            if($task->parent > 0) $parents[$task->parent] = $task->parent;
        }
        $parents = $this->dao->select('*')->from(TABLE_TASK)->where('id')->in($parents)->fetchAll('id');

        foreach($tasks as $task)
        {
            if($task->parent > 0)
            {
                if(isset($tasks[$task->parent]))
                {
                    $tasks[$task->parent]->children[$task->id] = $task;
                    unset($tasks[$task->id]);
                }
                else
                {
                    $parent = $parents[$task->parent];
                    $task->parentName = $parent->name;
                }
            }
        }
        return $this->loadModel('task')->processTasks($tasks);
    }

    /**
     * Set menu.
     *
     * @param  int    $executionID
     * @param  int    $buildID
     * @param  string $extra
     * @access public
     * @return void
     */
    public function setMenu($executionID, $buildID = 0, $extra = '')
    {
        $execution = $this->getByID($executionID);
        if(!$execution) return;

        if($execution and $execution->type == 'kanban')
        {
            global $lang;
            $lang->executionCommon = $lang->execution->kanban;
            include $this->app->getModulePath('', 'execution') . 'lang/' . $this->app->getClientLang() . '.php';

            $this->lang->execution->menu           = new stdclass();
            $this->lang->execution->menu->kanban   = array('link' => "{$this->lang->kanban->common}|execution|kanban|executionID=%s", 'subModule' => 'task');
            $this->lang->execution->menu->CFD      = array('link' => "{$this->lang->execution->CFD}|execution|cfd|executionID=%s");
            $this->lang->execution->menu->build    = array('link' => "{$this->lang->build->common}|execution|build|executionID=%s");
            $this->lang->execution->menu->settings = array('link' => "{$this->lang->settings}|execution|view|executionID=%s", 'subModule' => 'personnel', 'alias' => 'edit,manageproducts,team,whitelist,addwhitelist,managemembers', 'class' => 'dropdown dropdown-hover');
            $this->lang->execution->dividerMenu    = '';

            $this->lang->execution->menu->settings['subMenu']            = new stdclass();
            $this->lang->execution->menu->settings['subMenu']->view      = array('link' => "{$this->lang->overview}|execution|view|executionID=%s", 'subModule' => 'view', 'alias' => 'edit,start,suspend,putoff,close');
            $this->lang->execution->menu->settings['subMenu']->products  = array('link' => "{$this->lang->productCommon}|execution|manageproducts|executionID=%s");
            $this->lang->execution->menu->settings['subMenu']->team      = array('link' => "{$this->lang->team->common}|execution|team|executionID=%s", 'alias' => 'managemembers');
            $this->lang->execution->menu->settings['subMenu']->whitelist = array('link' => "{$this->lang->whitelist}|execution|whitelist|executionID=%s", 'subModule' => 'personnel', 'alias' => 'addwhitelist');
        }

        if($execution->type == 'stage' or ($model == 'waterfallplus')) unset($this->lang->execution->menu->settings['subMenu']->products);

        if(!$this->app->user->admin and strpos(",{$this->app->user->view->sprints},", ",$executionID,") === false and !defined('TUTORIAL') and $executionID != 0) return print(js::error($this->lang->execution->accessDenied) . js::locate('back'));

        $executions = $this->getPairs(0, 'all', 'nocode');
        if(!$executionID and $this->session->execution) $executionID = $this->session->execution;
        if(!$executionID or !in_array($executionID, array_keys($executions))) $executionID = key($executions);
        $this->session->set('execution', $executionID, $this->app->tab);

        if($execution and $execution->type == 'stage')
        {
            global $lang;
            $this->app->loadLang('project');
            $lang->executionCommon = $lang->project->stage;
            include $this->app->getModulePath('', 'execution') . 'lang/' . $this->app->getClientLang() . '.php';
        }

        if(isset($execution->acl) and $execution->acl != 'private') unset($this->lang->execution->menu->settings['subMenu']->whitelist);

        /* Unset story, bug, build and testtask if type is ops. */
        if($execution and $execution->lifetime == 'ops')
        {
            unset($this->lang->execution->menu->story);
            unset($this->lang->execution->menu->qa);
            unset($this->lang->execution->menu->build);
            unset($this->lang->execution->menu->burn);
        }

        $stageFilter = array('request', 'design', 'review');
        if(isset($execution->attribute) and in_array($execution->attribute, $stageFilter))
        {
            if(in_array($execution->attribute, array('request', 'review')))
            {
                unset($this->lang->execution->menu->story);
                unset($this->lang->execution->menu->view['subMenu']->groupTask);
                unset($this->lang->execution->menu->view['subMenu']->tree);
                unset($this->lang->execution->menu->other);
            }

            unset($this->lang->execution->menu->devops);
            unset($this->lang->execution->menu->qa);
            unset($this->lang->execution->menu->build);
        }

        if($executions and (!isset($executions[$executionID]) or !$this->checkPriv($executionID))) $this->accessDenied();

        $moduleName = $this->app->getModuleName();
        $methodName = $this->app->getMethodName();

        if($this->cookie->executionMode == 'noclosed' and $execution and ($execution->status == 'done' or $execution->status == 'closed'))
        {
            setcookie('executionMode', 'all');
            $this->cookie->executionMode = 'all';
        }

        if(empty($execution->hasProduct)) unset($this->lang->execution->menu->settings['subMenu']->products);

        $this->lang->switcherMenu = $this->getSwitcher($executionID, $this->app->rawModule, $this->app->rawMethod);
        common::setMenuVars('execution', $executionID);

        $this->loadModel('project')->setNoMultipleMenu($executionID);

        if(isset($this->lang->execution->menu->storyGroup)) unset($this->lang->execution->menu->storyGroup);
        if(isset($this->lang->execution->menu->story['dropMenu']) and $methodName == 'storykanban')
        {
            unset($this->lang->execution->menu->story['dropMenu']);
            $this->lang->execution->menu->story['link'] = str_replace(array($this->lang->common->story, 'story'), array($this->lang->SRCommon, 'storykanban'), $this->lang->execution->menu->story['link']);
        }
    }
}
