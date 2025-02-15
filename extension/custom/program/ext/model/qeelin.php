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
    }

    $this->session->set('currentProjectsIsColse', 0, 'project');
    isset($this->config->CRProject) && empty($this->config->CRProject) && $currentProjectsIsColse && $this->session->set('currentProjectsIsColse', 1, 'project');
    return $stats;
}