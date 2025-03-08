<?php
/**
 * Get project stats.
 *
 * @param  int    $programID
 * @param  string $browseType
 * @param  int    $queryID
 * @param  string $orderBy
 * @param  object $pager
 * @param  string $programTitle
 * @param  int    $involved
 * @param  bool   $queryAll
 * @access public
 * @return array
 */
public function getProjectStats($programID = 0, $browseType = 'undone', $queryID = 0, $orderBy = 'id_desc', $pager = null, $programTitle = 0, $involved = 0, $queryAll = false)
{
    if(defined('TUTORIAL')) return $this->loadModel('tutorial')->getProjectStats($browseType);

    $projects = $this->getProjectList($programID, $browseType, $queryID, $orderBy, $pager, $programTitle, $involved, $queryAll);
    if(empty($projects)) return array();

    $leftTasks = ($this->cookie->projectType and $this->cookie->projectType == 'bycard') ? $this->loadModel('project')->getProjectLeftTasks(array_keys($projects)) : array();

    /* Get the members of project teams. */
    $teamMembers = $this->dao->select('t1.root,t1.account')->from(TABLE_TEAM)->alias('t1')
        ->leftJoin(TABLE_USER)->alias('t2')->on('t1.account=t2.account')
        ->where('t1.root')->in(array_keys($projects))
        ->andWhere('t1.type')->eq('project')
        ->andWhere('t2.deleted')->eq(0)
        ->fetchGroup('root', 'account');
    $currentProjectsIsColse = true;
    /* Process projects. */
    foreach($projects as $projectID => $project)
    {
        if($project->end == '0000-00-00') $project->end = '';

        /* Judge whether the project is delayed. */
        if($project->status != 'done' and $project->status != 'closed' and $project->status != 'suspended')
        {
            $delay = helper::diffDate(helper::today(), $project->end);
            if($delay > 0) $project->delay = $delay;
        }

        $project->teamMembers = isset($teamMembers[$projectID]) ? array_keys($teamMembers[$projectID]) : array();
        $project->leftTasks   = isset($leftTasks[$projectID]) ? $leftTasks[$projectID]->tasks : '—';

        $stats[$projectID] = $project;
        $project->status != 'closed' && $currentProjectsIsColse = false;

        $project->collect = false;
        if(strpos(",{$project->favorites},", ",{$this->app->user->account},") !== false) $project->collect = true;
    }

    $this->session->set('currentProjectsIsColse', 0, 'project');
    isset($this->config->CRProject) && empty($this->config->CRProject) && $currentProjectsIsColse && $this->session->set('currentProjectsIsColse', 1, 'project');
    return $stats;
}

/**
 * Get project list data.
 *
 * @param  int       $programID
 * @param  string    $browseType
 * @param  string    $queryID
 * @param  string    $orderBy
 * @param  object    $pager
 * @param  int       $programTitle
 * @param  int       $involved
 * @param  bool      $queryAll
 * @access public
 * @return object
 */
public function getProjectList($programID = 0, $browseType = 'all', $queryID = 0, $orderBy = 'id_desc', $pager = null, $programTitle = 0, $involved = 0, $queryAll = false)
{
    $path = '';
    if($programID)
    {
        $program = $this->getByID($programID);
        $path    = $program->path;
    }

    if($queryID)
    {
        $query = $this->loadModel('search')->getQuery($queryID);
        if($query)
        {
            $this->session->set('projectQuery', $query->sql);
            $this->session->set('projectForm', $query->form);
        }
        else
        {
            $this->session->set('projectQuery', ' 1 = 1');
        }
    }
    else
    {
        if($browseType == 'bySearch' and $this->session->projectQuery == false) $this->session->set('projectQuery', ' 1 = 1');
    }

    $query = str_replace('`id`','t1.id', $this->session->projectQuery);
    $stmt  = $this->dao->select('DISTINCT t1.*')->from(TABLE_PROJECT)->alias('t1');
    if($this->cookie->involved || $involved) $stmt->leftJoin(TABLE_TEAM)->alias('t2')->on('t1.id=t2.root')->leftJoin(TABLE_STAKEHOLDER)->alias('t3')->on('t1.id=t3.objectID');
    $stmt->where('t1.deleted')->eq('0')
        ->andWhere('t1.vision')->eq($this->config->vision)
        ->beginIF($browseType == 'bysearch' and $query)->andWhere($query)->fi()
        ->andWhere('t1.type')->eq('project')
        ->beginIF(!in_array($browseType, array('all', 'undone', 'bysearch', 'review', 'unclosed', 'collect'), true))->andWhere('t1.status')->eq($browseType)->fi()
        ->beginIF($browseType == 'undone' or $browseType == 'unclosed')->andWhere('t1.status')->in('wait,doing')->fi()
        ->beginIF($browseType == 'review')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.reviewers)")
        ->andWhere('t1.reviewStatus')->eq('doing')
        ->fi()
        ->beginIF($browseType == 'collect')->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.favorites)")->fi()
        ->beginIF($path)->andWhere('t1.path')->like($path . '%')->fi()
        ->beginIF(!$queryAll and !$this->app->user->admin)->andWhere('t1.id')->in($this->app->user->view->projects)->fi()
        ->beginIF($this->config->systemMode == 'ALM')->andWhere('t1.model')->ne('ipd')->fi();
    if($this->cookie->involved || $involved)
    {
        $stmt->andWhere('t2.type')->eq('project')
            ->andWhere('t1.openedBy', true)->eq($this->app->user->account)
            ->orWhere('t1.PM')->eq($this->app->user->account)
            ->orWhere('t2.account')->eq($this->app->user->account)
            ->orWhere('(t3.user')->eq($this->app->user->account)
            ->andWhere('t3.deleted')->eq(0)
            ->markRight(1)
            ->orWhere("CONCAT(',', t1.whitelist, ',')")->like("%,{$this->app->user->account},%")
            ->markRight(1);
    }
    $projectList = $stmt->orderBy($orderBy)->page($pager, 't1.id')->fetchAll('id');

    /* Determine how to display the name of the program. */
    if($programTitle and in_array($this->config->systemMode, array('ALM', 'PLM')))
    {
        $programList = $this->getPairs();
        foreach($projectList as $id => $project)
        {
            $path = explode(',', $project->path);
            $path = array_filter($path);
            array_pop($path);
            $programID = $programTitle == 'base' ? current($path) : end($path);
            if(empty($path) || $programID == $id) continue;

            $programName = isset($programList[$programID]) ? $programList[$programID] : '';

            if($programName) $projectList[$id]->name = $programName . '/' . $projectList[$id]->name;
        }
    }
    return $projectList;
}