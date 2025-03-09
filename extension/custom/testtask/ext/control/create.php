<?php
class mytesttask extends testtask
{
    /**
     * Create a test task.
     *
     * @param  int    $productID
     * @param  int    $executionID
     * @param  int    $build
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function create($productID, $executionID = 0, $build = 0, $projectID = 0)
    {
        if(!empty($_POST))
        {
            $taskID = $this->testtask->create($projectID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->loadModel('action')->create('testtask', $taskID, 'opened');

            $message = $this->executeHooks($taskID);
            if($message) $this->lang->saveSuccess = $message;

            if($this->viewType == 'json') return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'id' => $taskID));

            $task = $this->dao->findById($taskID)->from(TABLE_TESTTASK)->fetch();
            if($this->app->tab == 'project') $link = $this->createLink('project', 'testtask', "projectID=$task->project");
            if($this->app->tab == 'execution') $link = $this->createLink('execution', 'testtask', "executionID=$task->execution");
            if($this->app->tab == 'qa') $link = $this->createLink('testtask', 'browse', "productID=" . $this->post->product);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $link));
        }

        $this->loadModel('project');

        /* Set menu. */
        if($this->app->tab == 'project')
        {
            $this->project->setMenu($projectID);
        }
        elseif($this->app->tab == 'execution')
        {
            $this->loadModel('execution')->setMenu($executionID);
        }
        elseif($this->app->tab == 'qa')
        {
            $this->loadModel('qa')->setMenu($this->products, $productID);
        }

        /* Create testtask from testtask of test.*/
        $productID  = $productID ? $productID : key($this->products);
        $executions = empty($productID) ? array() : $this->loadModel('product')->getExecutionPairsByProduct($productID, '', 'id_desc', $projectID, 'stagefilter' . (isset($this->config->CRProject) && empty($this->config->CRProject) ? ',projectclosefilter' : ''));
        $builds     = empty($productID) ? array() : $this->loadModel('build')->getBuildPairs($productID, 'all', 'notrunk,withexecution' . (isset($this->config->CRProject) && empty($this->config->CRProject) ? ',projectclosefilter' : ''), $projectID, 'project', '', false);

        $execution = $this->loadModel('execution')->getByID($executionID);
        if(!empty($execution) and $execution->type == 'kanban') $this->lang->testtask->execution = str_replace($this->lang->execution->common, $this->lang->kanban->common, $this->lang->testtask->execution);

        /* Set menu. */
        $productID = $this->product->saveState($productID, $this->products);

        $project = $this->project->getByID($projectID);
        if($project && !$project->multiple) $this->view->noMultipleExecutionID = $this->loadModel('execution')->getNoMultipleID($project->id);

        $this->view->title      = $this->products[$productID] . $this->lang->colon . $this->lang->testtask->create;
        $this->view->position[] = html::a($this->createLink('testtask', 'browse', "productID=$productID"), $this->products[$productID]);
        $this->view->position[] = $this->lang->testtask->common;
        $this->view->position[] = $this->lang->testtask->create;

        $this->view->product     = $this->product->getByID($productID);
        $this->view->projectID   = $projectID;
        $this->view->executionID = $executionID;
        $this->view->executions  = $executions;
        $this->view->builds      = $builds;
        $this->view->build       = $build;
        $this->view->testreports = array('') + $this->loadModel('testreport')->getPairs($productID);
        $this->view->users       = $this->loadModel('user')->getPairs('noclosed|qdfirst|nodeleted');

        $this->display();
    }
}
