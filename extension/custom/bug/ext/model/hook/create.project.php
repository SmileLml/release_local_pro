<?php
if(!empty($this->post->project))
{
    $project = $this->loadModel('project')->getById($bug->project);
    if($project->status == 'closed' and empty($this->config->CRProject))
    {
        dao::$errors[] = $this->lang->bug->closedProject;
        return false;
    }
}