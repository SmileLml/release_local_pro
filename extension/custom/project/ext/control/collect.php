<?php

class myproject extends project
{
    public function collect($projectID)
    {
        $browseLink = $this->session->projectList ? $this->session->projectList : inlink('browse');

        if(!$this->app->user->admin and strpos(",{$this->app->user->view->projects},", ",{$projectID},") === false and $projectID != 0)
        {
            return print(js::error($this->lang->project->accessDenied) . js::locate($browseLink));
        }

        $project = $this->project->getByID($projectID);

        if(strpos(",{$project->favorites},", ",{$this->app->user->account},") !== false)
        {
            $project->favorites = str_replace(",{$this->app->user->account},", ',', $project->favorites);
        }
        else
        {
            $project->favorites = rtrim($project->favorites, ',') . ",{$this->app->user->account},";
        }
        $this->dao->update(TABLE_PROJECT)->set('favorites')->eq($project->favorites)->where('id')->eq($project->id)->exec();
        return print(js::locate($browseLink));
    }
}