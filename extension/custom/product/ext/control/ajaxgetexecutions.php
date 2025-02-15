<?php

class myproduct extends product
{
    /**
     * AJAX: get executions of a product in html select.
     *
     * @param  int    $productID
     * @param  int    $projectID
     * @param  int    $branch
     * @param  string $number
     * @param  int    $executionID
     * @param  string $from showImport
     * @param  string mode
     * @access public
     * @return void
     */
    public function ajaxGetExecutions($productID, $projectID = 0, $branch = 0, $number = '', $executionID = 0, $from = '', $mode = '')
    {
        if($this->app->tab == 'execution' and $this->session->execution)
        {
            $execution = $this->loadModel('execution')->getByID($this->session->execution);
            if($execution->type == 'kanban') $projectID = $execution->project;
        }

        if($projectID) $project = $this->loadModel('project')->getById($projectID);
        $mode .= ($from == 'bugToTask' or empty($this->config->CRExecution)) ? 'noclosed' : '';
        $mode .= !$projectID ? ',multiple' : '';
        $mode .= isset($this->config->CRProject) && empty($this->config->CRProject) ? 'projectclosefilter' : '';
        $executions = $from == 'showImport' ? $this->product->getAllExecutionPairsByProduct($productID, $branch, $projectID) : $this->product->getExecutionPairsByProduct($productID, $branch, 'id_desc', $projectID, $mode);
        if($this->app->getViewType() == 'json') return print(json_encode($executions));

        if($number === '')
        {
            $event = $from == 'bugToTask' ? '' : " onchange='loadExecutionRelated(this.value)'";
            $datamultiple = !empty($project) ? "data-multiple={$project->multiple}" : '';
            return print(html::select('execution', array('' => '') + $executions, $executionID, "class='form-control' $datamultiple $event"));
        }
        else
        {
            $executions     = empty($executions) ? array('' => '') : $executions;
            $executionsName = $from == 'showImport' ? "execution[$number]" : "executions[$number]";
            $misc           = $from == 'showImport' ? "class='form-control' onchange='loadImportExecutionRelated(this.value, $number)'" : "class='form-control' onchange='loadExecutionBuilds($productID, this.value, $number)'";
            return print(html::select($executionsName, $executions, '', $misc));
        }
    }

}