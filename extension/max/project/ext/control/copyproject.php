<?php
helper::importControl('project');
class myproject extends project
{
    /**
     * Copy project.
     *
     * @param  string $model
     * @param  int    $programID
     * @param  int    $copyProjectID
     * @param  string $extra
     * @access public
     * @return void
     */
    public function copyProject($model = 'scrum', $programID = 0, $copyProjectID = 0, $extra = '')
    {
        $this->app->methodName = 'create';
        if($_POST)
        {
            $this->project->checkCreate();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'locate' => $this->createLink('project', 'copyConfirm', "copyprojectID=$copyProjectID&products=" . join(',', $this->post->products))));
        }

        $extra = str_replace(array(',', ' '), array('&', ''), $extra);
        parse_str($extra, $output);
        $copyProject = $this->dao->select('*')->from(TABLE_PROJECT)->where('id')->eq($copyProjectID)->fetch();
        $programs    = $this->loadModel('program')->getParentPairs();

        $extra .= ',from=global';
        $this->view->copyType           = isset($output['copyType']) ? $output['copyType'] : '';
        $this->view->PM                 = $copyProject->PM;
        $this->view->acl                = $copyProject->acl;
        $this->view->auth               = $copyProject->auth;
        $this->view->programListSet     = $programs;
        $this->view->copyProjectsLatest = array_slice($this->project->getPairsByModel($model), 0, 10, true);
        $this->view->project            = $copyProject;
        $this->view->division           = $copyProject->division;

        if($this->config->edition == 'ipd')
        {
            $this->loadModel('roadmap');
            $this->view->charters = array(0 => '') + $this->loadModel('charter')->getPairs();

            $copyedCharter = 0;
            if($copyProjectID) $copyedCharter = $this->dao->select('charter')->from(TABLE_PROJECT)->where('id')->eq($copyProjectID)->fetch('charter');    
            $this->view->copyedCharter = $copyedCharter;
        }

        return parent::create($model, $programID, $copyProjectID, $extra);
    }
}
