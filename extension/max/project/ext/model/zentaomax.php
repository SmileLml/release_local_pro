<?php
/**
 * Save copy project.
 *
 * @param  int    $copyProjectID
 * @param  string $model
 * @param  object $executions
 * @access public
 * @return string
 */
public function saveCopyProject($copyProjectID, $model = 'scrum', $executions = array())
{
    return $this->loadExtension('zentaomax')->saveCopyProject($copyProjectID, $model, $executions);
}

/**
 * Check create.
 *
 * @access public
 * @return bool
 */
public function checkCreate()
{
    return $this->loadExtension('zentaomax')->checkCreate();
}

/**
 * check execution.
 *
 * @param array  $executions
 * @param int    $projectID
 * @param string $model
 * @access public
 * @return void
 */
public function checkExecution($executions, $projectID = 0, $model = 'scrum')
{
    return $this->loadExtension('zentaomax')->checkExecution($executions, $projectID, $model);
}

/**
 * Save process.
 *
 * @param int     $copyProjectID
 * @param int     $executionID
 * @param int     $lastExecutionID
 * @param string  $model
 * @access public
 * @return void
 */
public function saveProcess($copyProjectID, $projectID, $executionID, $lastExecutionID, $model)
{
    return $this->loadExtension('zentaomax')->saveProcess($copyProjectID, $projectID, $executionID, $lastExecutionID, $model);
}

/**
 * Save QA.
 *
 * @param int  $copyProjectID
 * @param int  $projectID
 * @param int  $executionID
 * @param int  $lastExecutionID
 * @access public
 * @return void
 */
public function saveQA($copyProjectID, $projectID, $executionID, $lastExecutionID)
{
    return $this->loadExtension('zentaomax')->saveQA($copyProjectID, $projectID, $executionID, $lastExecutionID);
}

/**
 * Save task.
 *
 * @param int  $copyProjectID
 * @param int  $projectID
 * @param int  $executionID
 * @param int  $lastExecutionID
 * @access public
 * @return void
 */
public function saveTask($copyProjectID, $projectID, $executionID, $lastExecutionID)
{
    return $this->loadExtension('zentaomax')->saveTask($copyProjectID, $projectID, $executionID, $lastExecutionID);
}

/**
 * Save execution doc lib.
 *
 * @param int  $executionID
 * @param int  $lastExecutionID
 * @access public
 * @return void
 */
public function saveExecutionDocLib($executionID, $lastExecutionID)
{
    return $this->loadExtension('zentaomax')->saveExecutionDocLib($executionID, $lastExecutionID);
}

/**
 * Save project doc lib.
 *
 * @param int  $copyProjectID
 * @param int  $projectID
 * @access public
 * @return void
 */
public function saveProjectDocLib($copyProjectID, $projectID)
{
    return $this->loadExtension('zentaomax')->saveProjectDocLib($copyProjectID, $projectID);
}

/**
 * Save team.
 *
 * @param int     $copyObjectID
 * @param int     $objectID
 * @param string  $type
 * @access public
 * @return void
 */
public function saveTeam($copyObjectID, $objectID, $type = 'project')
{
    return $this->loadExtension('zentaomax')->saveTask($copyObjectID, $objectID, $type);
}

/**
 * Save stakeholder.
 *
 * @param int  $copyProjectID
 * @param int  $projectID
 * @access public
 * @return void
 */
public function saveStakeholder($copyProjectID, $projectID)
{
    return $this->loadExtension('zentaomax')->saveStakeholder($copyProjectID, $projectID);
}

/**
 * Save group.
 *
 * @param int  $copyProjectID
 * @param int  $projectID
 * @access public
 * @return void
 */
public function saveGroup($copyProjectID, $projectID)
{
    return $this->loadExtension('zentaomax')->saveGroup($copyProjectID, $projectID);
}

/**
 * Save RD kanban.
 *
 * @param  int    $execution
 * @param  int    $lastExecutionID
 * @access public
 * @return void
 */
public function saveKanban($execuitonID, $lastExecutionID)
{
    return $this->loadExtension('zentaomax')->saveKanban($execuitonID, $lastExecutionID);
}

/**
 * Set menu of project module.
 *
 * @param  int    $objectID
 * @access public
 * @return void
 */
public function setMenu($objectID)
{
    return $this->loadExtension('zentaomax')->setMenu($objectID);
}
