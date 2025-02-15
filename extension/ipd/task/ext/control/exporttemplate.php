<?php
helper::importControl('task');
class mytask extends task
{
    public function exportTemplate($executionID)
    {
        if($_POST)
        {
            $execution = $this->loadModel('execution')->getByID($executionID);
            $this->post->set('execution', $execution->name);
            $project   = $this->loadModel('project')->getByID($execution->project);
            $params    = "";

            if(!$execution->multiple)
            {
                $this->config->task->templateFields         = str_replace('execution,', 'project,', $this->config->task->templateFields);
                $this->config->task->create->requiredFields = str_replace('execution,', 'project,', $this->config->task->create->requiredFields);
                $this->post->set('project', $project->name);
            }
            else
            {
                $params = "executionID={$executionID}";
                $this->post->set('execution', $execution->name);
            }

            $this->fetch('transfer', 'exportTemplate', 'model=task&params=executionID='. $executionID);
        }

        $this->loadModel('transfer');
        $this->display();
    }
}
