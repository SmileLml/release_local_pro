<?php
helper::importControl('project');
class myproject extends project
{
    /**
     * create project.
     *
     * @param  string $model
     * @param  int    $programID
     * @param  int    $copyProjectID
     * @param  string $extra
     * @access public
     * @return void
     */
    public function create($model = 'scrum', $programID = 0, $copyProjectID = 0, $extra = '')
    {
        $this->loadModel('program');
        $this->view->programListSet = $this->program->getParentPairs();
        return parent::create($model, $programID, $copyProjectID, $extra);
    }
}
