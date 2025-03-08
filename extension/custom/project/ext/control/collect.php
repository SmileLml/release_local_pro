<?php

class myproject extends project
{
    public function collect($projectID)
    {
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
        return print(js::reload('parent'));
    }
}