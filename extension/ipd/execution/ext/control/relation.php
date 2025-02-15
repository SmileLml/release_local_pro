<?php
/**
 * The control file of execution module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     execution
 * @version     $Id$
 * @link        http://www.zentao.net
 */
helper::importControl('execution');
class myexecution extends execution
{
    /**
     * Show relation of execution.
     *
     * @param  int    $executionID
     * @access public
     * @return void
     */
    public function relation($executionID = 0)
    {
        $execution   = $this->commonAction($executionID);
        $executionID = $execution->id;
        $tasks     = $this->loadModel('task')->getExecutionTaskPairs($executionID);
        $relations = $this->execution->getRelationsOfTasks($executionID);

        $this->execution->setMenu($executionID);

        /* The header and position. */
        $this->view->title      = $this->lang->execution->common . $this->lang->colon . $this->lang->execution->gantt->relationOfTasks;
        $this->view->position[] = $this->lang->execution->gantt->relationOfTasks;

        $this->view->executionID = $executionID;
        $this->view->tasks       = $tasks;
        $this->view->relations   = $relations;
        $this->view->execution   = $execution;
        $this->display();
    }
}
