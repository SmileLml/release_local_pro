<?php
class mytesttask extends testtask
{
    /**
     * autorun.
     *
     * @param  int    $taskID
     * @access public
     * @return void
     */
    public function copy($productID, $projectID = 0, $executionID = 0, $testtaskID = 0, $copyNumber = 1)
    {
        /* Get test task, and set menu. */
        $testtaskID = (int)$testtaskID;
        $task       = $this->testtask->getById($testtaskID, false, true);
        if(!$task) die(js::error($this->lang->notFound) . js::locate($this->createLink('qa', 'index')));

        if($_POST)
        {
            $taskIDs = $this->testtask->copy($task, $copyNumber);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            foreach($taskIDs as $taskID)
            {
                $this->loadModel('action')->create('testtask', $taskID, 'opened');
                $this->executeHooks($taskID);
            }
            if($this->app->tab == 'project') return print(js::locate($this->createLink('project', 'testtask', "projectID={$projectID}"), 'parent'));
            if($this->app->tab == 'execution') return print(js::locate($this->createLink('execution', 'testtask', "executionID={$executionID}"), 'parent'));
            if($this->app->tab == 'qa') return print(js::locate($this->createLink('testtask', 'browse', "productID={$productID}"), 'parent'));
        }

         /* Set menu. */
        if($this->app->tab == 'project')
        {
            $this->loadModel('project')->setMenu($projectID);
            $project = $this->project->getByID($projectID);
            $this->view->title      = $project->name . $this->lang->colon . $this->lang->project->common;
            $this->view->position[] = html::a($this->createLink('project', 'testtask', "projectID=$projectID"), $project->name);
        }
        elseif($this->app->tab == 'execution')
        {
            $this->loadModel('execution')->setMenu($executionID);
            $executions = $this->execution->getPairs(0, 'all', "nocode,executions");
            $this->view->title      = $executions[$executionID] . $this->lang->colon . $this->lang->testtask->common;
            $this->view->position[] = html::a($this->createLink('execution', 'testtask', "executionID=$executionID"), $executions[$executionID]);
        }
        elseif($this->app->tab == 'qa')
        {
            $this->loadModel('qa')->setMenu($this->products, $productID);
            $this->view->title      = $this->products[$productID] . $this->lang->colon . $this->lang->testtask->common;
            $this->view->position[] = html::a($this->createLink('testtask', 'browse', "productID=$productID"), $this->products[$productID]);
        }

        $this->view->position[] = $this->lang->testtask->common;
        $this->view->requiredFields = explode(',', $this->config->testtask->create->requiredFields);
        $this->view->task       = $task;
        $this->view->copyNumber = $copyNumber;
        $this->view->users       = $this->loadModel('user')->getPairs('noclosed|qdfirst|nodeleted');
        $this->display();
    }
}