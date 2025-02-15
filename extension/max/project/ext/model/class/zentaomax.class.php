<?php
class zentaomaxProject extends projectModel
{
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
        $this->loadModel('doc');
        $this->loadModel('execution');
        if(empty($executions)) return false;

        $lastID       = $this->dao->select('id')->from(TABLE_EXECUTION)->orderBy('id_desc')->fetch('id');
        if(in_array($model, array('waterfall', 'waterfallplus', 'ipd')))
        {
            $parentAcl = '';
            $insertExecutions = array();
            foreach($executions->executionIDList as $productID => $executionIDs)
            {
                foreach($executionIDs as $executionID)
                {
                    $lastID ++;
                    $originExecutions = $this->execution->getByIdList($executionIDs);
                    $insertExecution = new stdClass();
                    $executionID     = (int)$executionID;
                    $insertExecution->executionID   = $executionID;
                    $insertExecution->name          = $executions->names[$productID][$executionID];
                    $insertExecution->status        = 'wait';
                    $insertExecution->parent        = $executions->parents[$productID][$executionID];
                    $insertExecution->PM            = $executions->PMs[$productID][$executionID];
                    $insertExecution->begin         = $executions->begins[$productID][$executionID];
                    $insertExecution->end           = $executions->ends[$productID][$executionID];
                    $insertExecution->product       = $productID;
                    $insertExecution->attribute     = $executions->attributes[$productID][$executionID];
                    $insertExecution->percent       = $executions->percents[$productID][$executionID];
                    $insertExecution->milestone     = $executions->milestone[$productID][$executionID];
                    $insertExecution->whitelist     = $originExecutions[$executionID]->whitelist;
                    $insertExecution->acl           = $executions->acl[$productID][$executionID];
                    $insertExecution->team          = $originExecutions[$executionID]->team;
                    $insertExecution->hasProduct    = $originExecutions[$executionID]->hasProduct;
                    $insertExecution->division      = $originExecutions[$executionID]->division;
                    $insertExecution->type          = $originExecutions[$executionID]->type;
                    $insertExecution->openedBy      = $this->app->user->account;
                    $insertExecution->openedDate    = helper::now();
                    $insertExecution->openedVersion = $this->config->version;
                    $insertExecution->order         = $lastID * 5;

                    $insertExecution->acl == 'same' ? $insertExecution->acl = $parentAcl : $parentAcl = $insertExecution->acl;

                    $insertExecutions[$executionID] = $insertExecution;
                }

                /* Check execution. */
                $checkExecution = $this->checkExecution($insertExecutions, $copyProjectID, $model);
                if(!$checkExecution) return false;
            }
        }
        else
        {
            $originExecutions = $this->loadModel('execution')->getByIdList($executions->executionIDList);
            foreach($executions->executionIDList as $executionID)
            {
                $executionID = (int)$executionID;
                $insertExecutions[$executionID] = new stdClass();
                $insertExecutions[$executionID]->executionID    = $executionID;
                $insertExecutions[$executionID]->type           = $originExecutions[$executionID]->type;
                $insertExecutions[$executionID]->name           = $executions->names[$executionID];
                $insertExecutions[$executionID]->status         = 'wait';
                $insertExecutions[$executionID]->parent         = $executions->parents[$executionID];
                $insertExecutions[$executionID]->PM             = $executions->PMs[$executionID];
                $insertExecutions[$executionID]->begin          = $executions->begins[$executionID];
                $insertExecutions[$executionID]->end            = $executions->ends[$executionID];
                if((isset($this->config->setCode) and $this->config->setCode)) $insertExecutions[$executionID]->code = $executions->codes[$executionID];
                $insertExecutions[$executionID]->lifetime       = $executions->lifetimes[$executionID];
                $insertExecutions[$executionID]->days           = $executions->dayses[$executionID] ? $executions->dayses[$executionID] : 0;
                $insertExecutions[$executionID]->team           = $originExecutions[$executionID]->team;
                $insertExecutions[$executionID]->acl            = $originExecutions[$executionID]->acl;
                $insertExecutions[$executionID]->whitelist      = $originExecutions[$executionID]->whitelist;
                $insertExecutions[$executionID]->hasProduct     = $originExecutions[$executionID]->hasProduct;
                $insertExecutions[$executionID]->division       = $originExecutions[$executionID]->division;
                $insertExecutions[$executionID]->openedBy       = $this->app->user->account;
                $insertExecutions[$executionID]->openedDate     = helper::now();
                $insertExecutions[$executionID]->openedVersion  = $this->config->version;
            }
            /* Check execution. */
            $checkExecution = $this->checkExecution($insertExecutions, $copyProjectID, $model);
            if(!$checkExecution) return false;
        }

        $executionMap        = array();
        $updateUVExecutionID = array();
        $isProcess           = false;
        $productID           = 0;

        $projectID = $this->config->edition == 'ipd' ? $this->loadExtension('zentaoipd')->create() : $this->create();
        $this->loadModel('action')->create('project', $projectID, 'opened');

        /* Query the products and plans associated with the project. */
        $products       = array();
        $plans          = array();
        $linkedProducts = $this->dao->select('product,plan')->from(TABLE_PROJECTPRODUCT)->where('project')->eq($projectID)->fetchPairs();
        foreach($linkedProducts as $productID => $planID)
        {
            if(empty($planID)) $planID = '0';
            $products[]        = $productID;
            $plans[$productID] = explode(',', $planID);
        }
        $_POST['products'] = $products;
        $_POST['plans']    = $plans;

        if(!$_POST['hasProduct']) $productID = $this->dao->select('product')->from(TABLE_PROJECTPRODUCT)->where('project')->eq($projectID)->fetch('product');

        $this->loadModel('review');
        foreach($insertExecutions as $insertExecution)
        {
            $executionID = $insertExecution->executionID;
            if($_POST['hasProduct']) $productID = isset($insertExecution->product) ? $insertExecution->product : $productID;

            $insertExecution->project = $projectID;
            unset($insertExecution->product);
            unset($insertExecution->executionID);

            $this->dao->insert(TABLE_EXECUTION)->data($insertExecution)->exec();
            $lastExecutionID = $this->dao->lastInsertId();

            $comment = $_POST['hasProduct'] ? join(',', $_POST['products']) : '';
            $this->action->create('execution', $lastExecutionID, 'opened', '', $comment);

            if(in_array($model, array('waterfall', 'waterfallplus')) and !empty($_POST['division']))
            {
                /* Add execution product */
                $this->dao->insert(TABLE_PROJECTPRODUCT)->set('project')->eq($lastExecutionID)->set('product')->eq($productID)->exec();
            }

            if(in_array($model, array('waterfall', 'waterfallplus', 'ipd')) and empty($_POST['division']) and !empty($_POST['products']))
            {
                /* Add execution product */
                $this->execution->updateProducts($lastExecutionID);
            }

            if(($model == 'scrum' or $model == 'agileplus') and !empty($_POST['products']))
            {
                unset($_POST['plans']);
                $this->execution->updateProducts($lastExecutionID);
            }

            if($insertExecution->parent == $copyProjectID)
            {
                $parentID = $projectID;
                $path     = ",{$projectID},{$lastExecutionID},";
                $grade    = 1;
            }
            else
            {
                /* If there is no parent, ignore the node. */
                if(!isset($executionMap[$productID][$insertExecution->parent])) continue;

                $parent   = $executionMap[$productID][$insertExecution->parent];
                $parentID = $parent['id'];
                $path     = $parent['path'] . $lastExecutionID . ',';
                $grade    = $parent['grade'] + 1;
            }
            $executionMap[$productID][$executionID] = array('id' => $lastExecutionID, 'path' => $path, 'grade' => $grade);

            $this->dao->update(TABLE_EXECUTION)
                ->set('path')->eq($path)
                ->set('parent')->eq($parentID)
                ->set('grade')->eq($grade)
                ->where('id')->eq($lastExecutionID)->exec();

            /* Add review point for ipd project. */
            if($this->config->edition == 'ipd' && $model == 'ipd' && $parentID == $projectID && $insertExecution->type == 'stage') $this->review->createDefaultPoint($projectID, $productID, $insertExecution->attribute);

            /* Add execution team. */
            $this->saveTeam($executionID, $lastExecutionID, 'execution');

            /* Add execution whitelis. */
            $this->saveWhitelist($insertExecution->whitelist, $lastExecutionID);

            /* Add process. */
            if($model == 'scrum' or $model == 'agileplus' or (in_array($model, array('waterfall', 'waterfallplus')) and !$isProcess))
            {
                $this->saveProcess($copyProjectID, $projectID, $executionID, $lastExecutionID, $model);
                $isProcess = true;
            }

            /* Add QA. */
            if($model != 'ipd') $this->saveQA($copyProjectID, $projectID, $executionID, $lastExecutionID);

            /* Add RD kanban. */
            $this->saveKanban($executionID, $lastExecutionID);

            /* Add task. */
            $this->saveTask($copyProjectID, $projectID, $executionID, $lastExecutionID);

            /* execution doc lib */
            $this->saveExecutionDocLib($executionID, $lastExecutionID);

            if($insertExecution->acl != 'open') $updateUVExecutionID[] = $lastExecutionID;
        }

        /* Add QA. */
        if($model != 'ipd') $this->saveQA($copyProjectID, $projectID, 0, 0);

        /* Update userview. */
        if(!empty($updateUVExecutionID)) $this->loadModel('user')->updateUserView($updateUVExecutionID, 'sprint');

        /* Project doc lib */
        $this->saveProjectDocLib($copyProjectID, $projectID);

        /* Teams */
        $this->saveTeam($copyProjectID, $projectID);

        /* Stakeholder */
        $this->saveStakeholder($copyProjectID, $projectID);

        /* Group */
        $this->saveGroup($copyProjectID, $projectID);

        return $projectID;
    }

    /**
     * Check create.
     *
     * @access public
     * @return bool
     */
    public function checkCreate()
    {
        $project = fixer::input('post')
            ->callFunc('name', 'trim')
            ->setDefault('status', 'wait')
            ->setIF($this->post->delta == 999, 'end', LONG_TIME)
            ->setIF($this->post->delta == 999, 'days', 0)
            ->setIF($this->post->acl   == 'open', 'whitelist', '')
            ->setIF(!isset($_POST['whitelist']), 'whitelist', '')
            ->setDefault('openedBy', $this->app->user->account)
            ->setDefault('openedDate', helper::now())
            ->setDefault('team', substr($this->post->name, 0, 30))
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', helper::now())
            ->cleanINT('parent')
            ->add('type', 'project')
            ->join('whitelist', ',')
            ->stripTags($this->config->project->editor->create['id'], $this->config->allowedTags)
            ->remove('products,branch,plans,delta,newProduct,productName,future,contactListMenu,teamMembers')
            ->get();

        $linkedProductsCount = 0;
        if($project->hasProduct && isset($_POST['products']))
        {
            foreach($_POST['products'] as $product)
            {
                if(!empty($product)) $linkedProductsCount++;
            }
        }

        $program = new stdClass();
        if($project->parent)
        {
            $program = $this->dao->select('*')->from(TABLE_PROGRAM)->where('id')->eq($project->parent)->fetch();

            /* Judge products not empty. */
            if($project->hasProduct and empty($linkedProductsCount) and !isset($_POST['newProduct']))
            {
                dao::$errors[] = $this->lang->project->productNotEmpty;
                return false;
            }
        }

        /* Judge workdays is legitimate. */
        $workdays = helper::diffDate($project->end, $project->begin) + 1;
        if(isset($project->days) and $project->days > $workdays)
        {
            dao::$errors['days'] = sprintf($this->lang->project->workdaysExceed, $workdays);
            return false;
        }

        if(!empty($project->budget))
        {
            if(!is_numeric($project->budget))
            {
                dao::$errors['budget'] = sprintf($this->lang->project->budgetNumber);
                return false;
            }
            else if(is_numeric($project->budget) and ($project->budget < 0))
            {
                dao::$errors['budget'] = sprintf($this->lang->project->budgetGe0);
                return false;
            }
            else
            {
                $project->budget = round((float)$this->post->budget, 2);
            }
        }

        /* When select create new product, product name cannot be empty and duplicate. */
        if(isset($_POST['newProduct']))
        {
            if(empty($_POST['productName']))
            {
                $this->app->loadLang('product');
                dao::$errors['productName'] = sprintf($this->lang->error->notempty, $this->lang->product->name);
                return false;
            }
            else
            {
                $programID        = isset($project->parent) ? $project->parent : 0;
                $existProductName = $this->dao->select('name')->from(TABLE_PRODUCT)->where('name')->eq($_POST['productName'])->andWhere('program')->eq($programID)->fetch('name');
                if(!empty($existProductName))
                {
                    dao::$errors['productName'] = $this->lang->project->existProductName;
                    return false;
                }
            }
        }

        $requiredFields = $this->config->project->create->requiredFields;
        if($this->post->delta == 999) $requiredFields = trim(str_replace(',end,', ',', ",{$requiredFields},"), ',');

        /* Redefines the language entries for the fields in the project table. */
        foreach(explode(',', $requiredFields) as $field)
        {
            if(isset($this->lang->project->$field)) $this->lang->project->$field = $this->lang->project->$field;
        }

        $this->lang->error->unique = $this->lang->error->repeat;
        $project = $this->loadModel('file')->processImgURL($project, $this->config->project->editor->create['id'], $this->post->uid);
        $this->dao->insert(TABLE_PROJECT)->data($project)
            ->autoCheck()
            ->batchcheck($requiredFields, 'notempty')
            ->checkIF(!empty($project->name), 'name', 'unique', "`type`='project' and `parent` = " . $this->dao->sqlobj->quote($project->parent) . " and `model` = " . $this->dao->sqlobj->quote($project->model) . " and `deleted` = '0'")
            ->checkIF(!empty($project->code), 'code', 'unique', "`type`='project' and `model` = " . $this->dao->sqlobj->quote($project->model) . " and `deleted` = '0'")
            ->checkIF($project->end != '', 'end', 'gt', $project->begin)
            ->checkFlow();

        return true;
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
        if(!empty($_POST)) $project = $_POST;

        $productID = 0;
        $parents   = array();
        foreach($executions as $index => $execution)
        {
            if($productID != $execution->product)
            {
                $productID = $execution->product;
                $parents   = array();
            }

            if(!isset($parents[$execution->parent]))
            {
                $parent = new stdclass();
                $parent->type         = $execution->parent == $projectID ? 'project' : 'execution';
                $parent->totalPercent = 0;
                $parent->attribute    = $parent->type == 'project' ? '' : $executions[$execution->parent]->attribute;
                $parent->begin        = $parent->type == 'project' ? $project['begin'] : $executions[$execution->parent]->begin;
                $parent->end          = $parent->type == 'project' ? $project['end'] : $executions[$execution->parent]->end;

                $parents[$execution->parent] = $parent;
            }

            $executionID   = $execution->executionID;
            $executionType = zget($this->lang->execution->typeList, $execution->type);
            $executionName = "『" . $executionType . $execution->name . "』";

            if(isset($execution->percent) and !empty($execution->percent) and !preg_match("/^[0-9]+(.[0-9]{1,3})?$/", $execution->percent))
            {
                dao::$errors["percent{$index}"][] = $this->lang->programplan->error->percentNumber;
                return false;
            }

            $parents[$execution->parent]->totalPercent += $execution->percent;
            $parent = $parents[$execution->parent];

            if($parent->totalPercent > 100)
            {
                dao::$errors['message'][] = $this->lang->programplan->error->percentOver;
                return false;
            }

            if($parent->type != 'project' and $parent->attribute != 'mix' and $parent->attribute != $execution->attribute)
            {
                dao::$errors["attributes{$index}"][] = sprintf($this->lang->programplan->error->sameType, $this->lang->stage->typeList[$parent->attribute]);
                return false;
            }

            if(helper::isZeroDate($execution->begin))
            {
                dao::$errors["begins{$index}"][] = $executionName . $this->lang->programplan->emptyBegin;
                return false;
            }
            if(!validater::checkDate($execution->begin))
            {
                dao::$errors["begins{$index}"][] = $executionName . $this->lang->programplan->checkBegin;
                return false;
            }

            if(helper::isZeroDate($execution->end))
            {
                dao::$errors["ends{$index}"][] = $executionName . $this->lang->programplan->emptyEnd;
                return false;
            }
            if(!validater::checkDate($execution->end))
            {
                dao::$errors["ends{$index}"][] = $executionName . $this->lang->programplan->checkEnd;
                return false;
            }

            if(!helper::isZeroDate($execution->end) and $execution->end < $execution->begin)
            {
                dao::$errors["ends{$index}"][] = $executionName . $this->lang->programplan->error->planFinishSmall;
                return false;
            }

            if(!helper::isZeroDate($execution->begin) and $execution->begin < $parent->begin)
            {
                if($parent->type == 'project')
                {
                    if($model == 'scrum' or $model == 'agileplus')
                    {
                        dao::$errors["begins{$index}"][] = $executionName . sprintf($this->lang->execution->errorBegin, $project['begin']);
                    }
                    else
                    {
                        dao::$errors["begins{$index}"][] = $executionName . sprintf($this->lang->programplan->errorBegin, $project['begin']);
                    }
                    return false;
                }
                dao::$errors["begins{$index}"][] = sprintf($this->lang->programplan->error->letterParent, $parent->begin);
                return false;
            }

            if(!helper::isZeroDate($execution->end) and $execution->end > $parent->end)
            {
                if($parent->type == 'project')
                {
                    if($model == 'scrum' or $model == 'agileplus')
                    {
                        dao::$errors["ends{$index}"][] = $executionName . sprintf($this->lang->execution->errorEnd, $project['end']);
                    }
                    else
                    {
                        dao::$errors["ends{$index}"][] = $executionName .  sprintf($this->lang->programplan->errorEnd, $project['end']);
                    }
                    return false;
                }
                dao::$errors["ends{$index}"][] = sprintf($this->lang->programplan->error->greaterParent, $parent->end);
                return false;
            }

            if(helper::isZeroDate($execution->begin)) $execution->begin = '';
            if(helper::isZeroDate($execution->end))   $execution->end   = '';
            if($model == 'warterfall')
            {
                foreach(explode(',', $this->config->programplan->create->requiredFields) as $field)
                {
                    $field = trim($field);
                    if($field and empty($execution->$field))
                    {
                        dao::$errors["{$field}s{$index}"][] = sprintf($this->lang->error->notempty, $this->lang->programplan->$field);
                        return false;
                    }
                }
            }
            else if($model == 'scrum' or $model == 'agileplus')
            {
                foreach(explode(',', $this->config->project->create->requiredFields) as $field)
                {
                    $field = trim($field);
                    if($field and isset($execution->$field) and empty($execution->$field))
                    {
                        dao::$errors["{$field}s{$index}"][] = sprintf($this->lang->error->notempty, $this->lang->execution->$field);
                        return false;
                    }
                }

                /* Judge workdays is legitimate. */
                $workdays = helper::diffDate($execution->end, $execution->begin) + 1;
                if(!empty($execution->days))
                {
                    if(!preg_match("/^[0-9]\d*$/", $execution->days))
                    {
                        dao::$errors["dayses{$index}"][] = $this->lang->project->copyProject->daysTips;
                        return false;
                    }
                    if($execution->days > $workdays)
                    {
                        dao::$errors["dayses{$index}"][] = sprintf($this->lang->project->workdaysExceed, $workdays);
                        return false;
                    }
                }

                if(isset($execution->code))
                {
                    if($this->checkCodeUnique($execution->code) !== true)
                    {
                        dao::$errors["codes{$index}"][] = $this->lang->project->copyProject->code;
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check code unique.
     *
     * @param  string $code
     * @access public
     * @return mix
     */
    public function checkCodeUnique($code)
    {
        $code = $this->dao->select('code')->from(TABLE_EXECUTION)
            ->where('type')->in('sprint,stage,kanban')
            ->andWhere('deleted')->eq('0')
            ->andWhere('code')->eq($code)
            ->fetch('code');
        return $code ? $code : true;
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
        $projectActivities = $this->dao->select('*')
            ->from(TABLE_PROGRAMACTIVITY)
            ->where('project')->eq($copyProjectID)
            ->andWhere('deleted')->eq('0')
            ->beginIF($model == 'scrum' or $model == 'agileplus')->andWhere('execution')->eq($executionID)->fi()
            ->fetchAll();

        if(!empty($projectActivities))
        {
            foreach($projectActivities as $projectActivity)
            {
                $insertProjectActivity = new stdClass();
                $insertProjectActivity->project     = $projectID;
                if($model == 'scrum' or $model == 'agileplus') $insertProjectActivity->execution = $lastExecutionID;
                $insertProjectActivity->process     = $projectActivity->process;
                $insertProjectActivity->activity    = $projectActivity->activity;
                $insertProjectActivity->name        = $projectActivity->name;
                $insertProjectActivity->content     = $projectActivity->content;
                $insertProjectActivity->reason      = $projectActivity->reason;
                $insertProjectActivity->result      = $projectActivity->result;
                $insertProjectActivity->linkedBy    = $this->app->user->account;
                $insertProjectActivity->createdBy   = $this->app->user->account;
                $insertProjectActivity->createdDate = helper::today();
                $this->dao->insert(TABLE_PROGRAMACTIVITY)->data($insertProjectActivity)->exec();
            }
        }

        $projectOutputs = $this->dao->select('*')
            ->from(TABLE_PROGRAMOUTPUT)
            ->where('project')->eq($copyProjectID)
            ->andWhere('deleted')->eq('0')
            ->beginIF($model == 'scrum' or $model == 'agileplus')->andWhere('execution')->eq($executionID)->fi()
            ->fetchAll();

        if(!empty($projectOutputs))
        {
            foreach($projectOutputs as $projectOutput)
            {
                $insertProjectOutput = new stdClass();
                $insertProjectOutput->project     = $projectID;
                if($model == 'scrum' or $model == 'agileplus') $insertProjectOutput->execution = $lastExecutionID;
                $insertProjectOutput->process     = $projectOutput->process;
                $insertProjectOutput->activity    = $projectOutput->activity;
                $insertProjectOutput->output      = $projectOutput->output;
                $insertProjectOutput->name        = $projectOutput->name;
                $insertProjectOutput->content     = $projectOutput->content;
                $insertProjectOutput->reason      = $projectOutput->reason;
                $insertProjectOutput->result      = $projectOutput->result;
                $insertProjectOutput->linkedBy    = $this->app->user->account;
                $insertProjectOutput->createdBy   = $this->app->user->account;
                $insertProjectOutput->createdDate = helper::today();

                $this->dao->insert(TABLE_PROGRAMOUTPUT)->data($insertProjectOutput)->exec();
            }
        }
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
        $auditplans = $this->dao->select('*')->from(TABLE_AUDITPLAN)
            ->where('deleted')->eq(0)
            ->andWhere('project')->eq($copyProjectID)
            ->andWhere('execution')->eq($executionID)
            ->fetchAll();

        if(!empty($auditplans))
        {
            foreach($auditplans as $auditplan)
            {
                $insertAuditplan = new stdClass();
                $insertAuditplan->objectID      = $auditplan->objectID;
                $insertAuditplan->objectType    = $auditplan->objectType;
                $insertAuditplan->process       = $auditplan->process;
                $insertAuditplan->processType   = $auditplan->processType;
                $insertAuditplan->status        = 'wait';
                $insertAuditplan->project       = $projectID;
                $insertAuditplan->execution     = $lastExecutionID;
                $insertAuditplan->createdBy     = $this->app->user->account;
                $insertAuditplan->assignedTo    = $auditplan->assignedTo;
                $insertAuditplan->createdDate   = helper::today();

                $this->dao->insert(TABLE_AUDITPLAN)->data($insertAuditplan)->exec();
            }
        }
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
        $tasks = $this->dao->select('*')->from(TABLE_TASK)
            ->where('project')->eq($copyProjectID)
            ->andWhere('execution')->eq($executionID)
            ->andWhere('deleted')->eq('0')
            ->orderBy('id asc')
            ->fetchAll();
        $execution = $this->loadModel('execution')->getByID($executionID);

        if(!empty($tasks))
        {
            $parentTaskIDs = array();
            foreach($tasks as $task)
            {
                $insertTask = new stdClass();

                $insertTask->project     = $projectID;
                $insertTask->type        = $task->type;
                $insertTask->module      = $task->module;
                $insertTask->name        = $task->name;
                $insertTask->pri         = $task->pri;
                $insertTask->status      = 'wait';
                $insertTask->desc        = $task->desc;
                $insertTask->mode        = $task->mode;
                $insertTask->execution   = $lastExecutionID;
                $insertTask->estimate    = $task->estimate;
                $insertTask->assignedTo  = $task->assignedTo == 'closed' ? '' : $task->assignedTo;
                $insertTask->mailto      = $task->mailto;
                $insertTask->openedBy    = $this->app->user->account;
                $insertTask->openedDate  = helper::now();
                $insertTask->parent      = $task->parent;

                if($task->parent > 0) $insertTask->parent = $parentTaskIDs[$task->parent];

                $this->dao->insert(TABLE_TASK)->data($insertTask)->exec();
                $lastTaskID = $this->dao->lastInsertId();
                if($task->parent == -1) $parentTaskIDs[$task->id] = $lastTaskID;

                /* Save multi task. */
                if(!empty($task->mode))
                {
                    $taskTeams = $this->dao->select('*')->from(TABLE_TASKTEAM)->where('task')->eq($task->id)->orderBy('order,id')->fetchAll();

                    if(!empty($taskTeams))
                    {
                        foreach($taskTeams as $taskTeam)
                        {
                            $insertTaskTeam = new stdClass();

                            $insertTaskTeam->task     = $lastTaskID;
                            $insertTaskTeam->account  = $taskTeam->account;
                            $insertTaskTeam->estimate = $taskTeam->estimate;
                            $insertTaskTeam->consumed = 0;
                            $insertTaskTeam->left     = $taskTeam->estimate;
                            $insertTaskTeam->order    = $taskTeam->order;
                            $insertTaskTeam->status   = 'wait';

                            $this->dao->insert(TABLE_TASKTEAM)->data($insertTaskTeam)->exec();
                        }
                    }
                }

                /* Update kanban cell. */
                if($execution->type == 'kanban')
                {
                    $this->dao->update(TABLE_KANBANCELL)->set("`cards` = REPLACE(`cards`, ',{$task->id},', ',{$lastTaskID},')")->where('kanban')->eq($lastExecutionID)->andWhere('type')->eq('task')->andWhere('cards')->like("%,{$task->id},%")->exec();
                }
            }
        }
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
        $executionDocLibs = $this->dao->select('*')->from(TABLE_DOCLIB)
            ->where('type')->eq('execution')
            ->andWhere('execution')->eq($executionID)
            ->andWhere('vision')->eq($this->config->vision)
            ->andWhere('deleted')->eq('0')
            ->fetchAll();

        if(!empty($executionDocLibs))
        {
            foreach($executionDocLibs as $executionDocLib)
            {
                $executionDocLib->execution = $lastExecutionID;
                $originDocLibID = $executionDocLib->id;
                unset($executionDocLib->id);
                $this->dao->insert(TABLE_DOCLIB)->data($executionDocLib)->exec();
                $executionDoclibId = $this->dao->lastInsertId();

                /* execution doc module */
                $executionModules = $this->dao->select('*')->from(TABLE_MODULE)
                    ->where('root')->eq($originDocLibID)
                    ->andWhere('type')->eq('doc')
                    ->andWhere('deleted')->eq('0')
                    ->orderBy('id asc')
                    ->fetchAll();
                $executionIdMap = array();
                if(!empty($executionModules))
                {
                    foreach($executionModules as $module)
                    {
                        $originModuleID = $module->id;
                        unset($module->id);
                        unset($module->root);
                        unset($module->path);
                        $this->dao->insert(TABLE_MODULE)->data($module)->exec();
                        $moduleID = $this->dao->lastInsertId();
                        $executionIdMap[$originModuleID] = $moduleID;

                        if(!empty($module->parent))
                        {
                            $path   = $this->dao->select('path')->from(TABLE_MODULE)->where('id')->eq($module->parent)->fetch('path');
                            $path  .= "{$moduleID},";
                            $parent = isset($executionIdMap[$module->parent]) ? $executionIdMap[$module->parent] : 0;
                        }
                        else
                        {
                            $path   = ",{$moduleID},";
                            $parent = 0;
                        }

                        $this->dao->update(TABLE_MODULE)
                            ->set('path')->eq($path)
                            ->set('root')->eq($executionDoclibId)
                            ->set('parent')->eq($parent)
                            ->where('id')->eq($moduleID)
                            ->limit(1)->exec();
                    }
                }
            }
        }
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
        $projectDocLibs = $this->dao->select('*')->from(TABLE_DOCLIB)
            ->where('type')->eq('project')
            ->andWhere('project')->eq($copyProjectID)
            ->andWhere('vision')->eq($this->config->vision)
            ->andWhere('deleted')->eq('0')
            ->fetchAll();

        if(!empty($projectDocLibs))
        {
            /* Delete origin project main lib. */
            $this->dao->delete()->from(TABLE_DOCLIB)
                ->where('type')->eq('project')
                ->andWhere('project')->eq((int)$projectID)
                ->andWhere('vision')->eq($this->config->vision)
                ->andWhere('name')->eq($this->lang->doclib->main['project'])
                ->exec();

            foreach($projectDocLibs as $projectDocLib)
            {
                $projectDocLib->project = $projectID;
                $originDocLibID = $projectDocLib->id;
                unset($projectDocLib->id);
                $this->dao->insert(TABLE_DOCLIB)->data($projectDocLib)->exec();
                $projectDoclibId = $this->dao->lastInsertId();

                /* project doc module */
                $projectModules = $this->dao->select('*')->from(TABLE_MODULE)
                    ->where('root')->eq($originDocLibID)
                    ->andWhere('type')->eq('doc')
                    ->andWhere('deleted')->eq('0')
                    ->fetchAll();
                $projectIdMap = array();
                if(!empty($projectModules))
                {
                    foreach($projectModules as $module)
                    {
                        $originModuleID = $module->id;
                        unset($module->id);
                        unset($module->root);
                        unset($module->path);
                        $this->dao->insert(TABLE_MODULE)->data($module)->exec();
                        $moduleID = $this->dao->lastInsertId();
                        $projectIdMap[$originModuleID] = $moduleID;

                        if(!empty($module->parent))
                        {
                            $path   = $this->dao->select('path')->from(TABLE_MODULE)->where('id')->eq($module->parent)->fetch('path');
                            $path  .= "{$moduleID},";
                            $parent = isset($projectIdMap[$module->parent]) ? $projectIdMap[$module->parent] : 0;
                        }
                        else
                        {
                            $path   = ",{$moduleID},";
                            $parent = 0;
                        }

                        $this->dao->update(TABLE_MODULE)
                            ->set('path')->eq($path)
                            ->set('root')->eq($projectDoclibId)
                            ->set('parent')->eq($parent)
                            ->where('id')->eq($moduleID)
                            ->limit(1)->exec();
                    }
                }
            }
        }
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
        $teams = $this->dao->select('*')->from(TABLE_TEAM)
            ->where('type')->eq($type)
            ->andWhere('root')->eq($copyObjectID)
            ->fetchAll();

        if(!empty($teams))
        {
            foreach($teams as $member)
            {
                $insertMenber = new stdClass();
                $insertMenber->root    = $objectID;
                $insertMenber->type    = $type;
                $insertMenber->account = $member->account;
                $insertMenber->role    = $member->role;
                $insertMenber->limited = $member->limited;
                $insertMenber->join    = helper::today();

                $this->dao->replace(TABLE_TEAM)->data($insertMenber)->exec();
            }

            if($type == 'project')
            {
                $acl = $this->dao->select('acl')->from(TABLE_PROJECT)->where('id')->eq($objectID)->fetch('acl');
                if($acl != 'open') $this->loadModel('user')->updateUserView($objectID, 'project');
            }
        }
    }

    /**
     * Save whitelist.
     *
     * @param mixed $whitelist
     * @param mixed $executionID
     * @access public
     * @return void
     */
    public function saveWhitelist($whitelist, $executionID)
    {
        if($whitelist)
        {
            $whitelist = array_filter(explode(',', $whitelist));
            foreach($whitelist as $account)
            {
                $data = new stdClass();
                $data->account    = $account;
                $data->objectType = 'sprint';
                $data->objectID   = $executionID;
                $data->type       = 'whitelist';
                $data->source     = 'add';
                $this->dao->insert(TABLE_ACL)->data($data)->exec();
            }
        }
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
        $stakeholders = $this->dao->select('*')->from(TABLE_STAKEHOLDER)
            ->where('objectType')->eq('project')
            ->andWhere('objectID')->eq($copyProjectID)
            ->fetchAll();

        if(!empty($stakeholders))
        {
            foreach($stakeholders as $stakeholder)
            {
                $insertStakeholders = new stdClass();
                $insertStakeholders->objectID    = $projectID;
                $insertStakeholders->objectType  = 'project';
                $insertStakeholders->user        = $stakeholder->user;
                $insertStakeholders->type        = $stakeholder->type;
                $insertStakeholders->key         = $stakeholder->key;
                $insertStakeholders->createdBy   = $this->app->user->account;
                $insertStakeholders->createdDate = helper::now();

                $this->dao->insert(TABLE_STAKEHOLDER)->data($insertStakeholders)->exec();
            }
        }
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
        $groups = $this->dao->select('*')->from(TABLE_GROUP)
            ->where('project')->eq($copyProjectID)
            ->andWhere('vision')->eq($this->config->vision)
            ->fetchAll();

        if(!empty($groups))
        {
            foreach($groups as $group)
            {
                $insertGroups = new stdClass();
                $insertGroups->project    = $projectID;
                $insertGroups->vision     = $group->vision;
                $insertGroups->name       = $group->name;
                $insertGroups->role       = $group->role;
                $insertGroups->desc       = $group->desc;
                $insertGroups->acl        = $group->acl;
                $insertGroups->desc       = $group->desc;
                $insertGroups->developer  = $group->developer;

                $this->dao->insert(TABLE_GROUP)->data($insertGroups)->exec();

                /* Add user group. */
                $lastGroupID = $this->dao->lastInsertID();
                $groupID     = $group->id;
                $userGroups  = $this->dao->select('*')->from(TABLE_USERGROUP)
                    ->where('`group`')->eq($groupID)
                    ->fetchAll();

                if(!empty($userGroups))
                {
                    foreach($userGroups as $userGroup)
                    {
                        $insertUserGroup = new stdClass();

                        $insertUserGroup->account = $userGroup->account;
                        $insertUserGroup->group   = $lastGroupID;

                        $this->dao->insert(TABLE_USERGROUP)->data($insertUserGroup)->exec();
                    }
                }
            }
        }
    }

    /**
     * Save RD kanban.
     *
     * @param  int    $executionID
     * @param  int    $lastExecutionID
     * @access public
     * @return void
     */
    public function saveKanban($executionID, $lastExecutionID)
    {
        $this->loadModel('kanban');
        $execution = new stdclass();
        $execution->id    = $lastExecutionID;
        $execution->space = 0;
        $this->kanban->copyRegions($execution, $executionID, 'execution', 'updateTaskCell');
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
        $objectID = parent::setMenu($objectID);
        $project  = $this->getByID($objectID);
        $model    = isset($project->model) ? $project->model : '';

        if($model == 'ipd')
        {
            $this->loadModel('baseline');
            $objectList = $this->lang->baseline->ipd->pointList;

            unset($objectList['other']);
            $this->lang->baseline->ipd->objectList = $this->lang->baseline->objectList;
            $this->lang->baseline->objectList = $objectList + $this->lang->baseline->objectList;
        }

        if(in_array($model, array('scrum', 'waterfall', 'agileplus', 'waterfallplus')))
        {
            $featureList = $this->config->featureGroup->$model;
            $menuKey     = in_array($model, array('scrum', 'agileplus')) ? 'scrum' : 'waterfall';
            foreach($featureList as $feature)
            {
                if(!helper::hasFeature("{$model}_$feature"))
                {
                    if($feature == 'measrecord')   $feature = 'report';
                    if($feature == 'process')      $feature = 'pssp';
                    if($feature == 'gapanalysis')  $feature = 'train';
                    if($feature == 'researchplan') $feature = 'research';
                    unset($this->lang->{$menuKey}->menu->other['dropMenu']->{$feature});
                }
            }

            if($menuKey == 'waterfall' and !helper::hasFeature("{$model}_track")) unset($this->lang->waterfall->menu->track);
        }

        /* Move the Gantt navigation to the first one for waterfall project menu. */
        if(in_array($model, array('waterfall', 'waterfallplus', 'ipd')))
        {
            list($stageCommon) = explode('|', $this->lang->waterfall->menu->execution['link']);
            $this->lang->waterfall->menu->execution['link'] = "{$stageCommon}|programplan|browse|projectID=$objectID&productID=0&type=gantt";
            if($this->app->rawMethod == 'execution') $this->lang->waterfall->menu->execution['subModule'] .= ',project';
        }
        return $objectID;
    }
}
