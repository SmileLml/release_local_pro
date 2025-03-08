<?php

class myproject extends project
{
    /**
     * Export project.
     *
     * @param  string $status
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($status, $orderBy)
    {
        if($_POST)
        {
            $projectLang   = $this->lang->project;
            $projectConfig = $this->config->project;

            /* Create field lists. */
            $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $projectConfig->list->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = zget($projectLang, $fieldName);
                unset($fields[$key]);
            }
            if(!isset($this->config->setCode) or empty($this->config->setCode)) unset($fields['code']);

            if(isset($fields['hasProduct'])) $fields['hasProduct'] = $projectLang->type;

            $involved = $this->cookie->involved ? $this->cookie->involved : 0;
            $projects = $this->project->getInfoList($status, $orderBy, '', $involved);
            $users    = $this->loadModel('user')->getPairs('noletter');

            $this->loadModel('product');
            foreach($projects as $i => $project)
            {
                $hasProduct = $project->hasProduct;

                $project->PM          = zget($users, $project->PM);
                $project->status      = $this->processStatus('project', $project);
                $project->model       = zget($projectLang->modelList, $project->model);
                $project->budget      = $project->budget != 0 ? $project->budget . zget($projectLang->unitList, $project->budgetUnit) : $this->lang->project->future;
                $project->realBeganAB = $project->realBegan;
                $project->realEndAB   = $project->realEnd;
                $project->parent      = $project->parentName;
                $project->hasProduct  = zget($projectLang->projectTypeList, $project->hasProduct);

                $linkedProducts = $this->product->getProducts($project->id, 'all', '', false);
                $project->linkedProducts = implode('，', $linkedProducts);

                if(!$hasProduct) $project->linkedProducts = '';
                if($this->post->exportType == 'selected')
                {
                    $checkedItem = $this->post->checkedItem;
                    if(strpos(",$checkedItem,", ",{$project->id},") === false) unset($projects[$i]);
                }
            }
            if($this->config->edition != 'open') list($fields, $projects) = $this->loadModel('workflowfield')->appendDataFromFlow($fields, $projects);
            $this->post->set('fields', $fields);
            $this->post->set('rows', $projects);
            $this->post->set('kind', 'project');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $this->display();
    }
}