<?php

/**
 * Create a test task.
 *
 * @param  int   $projectID
 * @access public
 * @return void
 */
public function create($projectID = 0)
{
    if($this->post->execution)
    {
        $execution = $this->loadModel('execution')->getByID($this->post->execution);
        $projectID = $execution->project;
    }

    if($this->post->build && empty($projectID))
    {
        $build     = $this->loadModel('build')->getById($this->post->build);
        $projectID = $build->project ?? 0;
    }

    $task = fixer::input('post')
        ->setDefault('build', '')
        ->setDefault('project', $projectID)
        ->setDefault('createdBy', $this->app->user->account)
        ->setDefault('createdDate', helper::now())
        ->setDefault('members', '')
        ->stripTags($this->config->testtask->editor->create['id'], $this->config->allowedTags)
        ->join('mailto', ',')
        ->join('type', ',')
        ->join('members', ',')
        ->remove('files,labels,uid,contactListMenu')
        ->get();
    $task->members = trim($task->members, ',');

    $task = $this->loadModel('file')->processImgURL($task, $this->config->testtask->editor->create['id'], $this->post->uid);
    $this->dao->insert(TABLE_TESTTASK)->data($task)
        ->autoCheck($skipFields = 'begin,end')
        ->batchcheck($this->config->testtask->create->requiredFields, 'notempty')
        ->checkIF($task->begin != '', 'begin', 'date')
        ->checkIF($task->end != '', 'end', 'date')
        ->checkIF($task->end != '', 'end', 'ge', $task->begin)
        ->checkFlow()
        ->exec();

    if(!dao::isError())
    {
        $taskID = $this->dao->lastInsertID();
        $this->file->updateObjectID($this->post->uid, $taskID, 'testtask');
        $this->file->saveUpload('testtask', $taskID);
        return $taskID;
    }
}



/**
 * Print cell data.
 *
 * @param  object  $col
 * @param  object  $run
 * @param  array   $users
 * @param  object  $task
 * @param  array   $branches
 * @access public
 * @return void
 */
function printCell($col, $run, $users, $task, $branches, $modulePairs, $mode = 'datatable')
{
    $isScene        = $run->isScene;
    $canBatchEdit   = common::hasPriv('testcase', 'batchEdit');
    $canBatchUnlink = common::hasPriv('testtask', 'batchUnlinkCases');
    $canBatchAssign = common::hasPriv('testtask', 'batchAssign');
    $canBatchRun    = common::hasPriv('testtask', 'batchRun');

    $canBatchAction = ($canBatchEdit or $canBatchUnlink or $canBatchAssign or $canBatchRun);

    $canView     = common::hasPriv('testcase', 'view');
    $caseLink    = helper::createLink('testcase', 'view', "caseID=$run->case&version=$run->version&from=testtask&taskID=$run->task");
    $account     = $this->app->user->account;
    $id          = $col->id;
    $caseChanged = !$run->isScene && $run->version < $run->caseVersion;
    $fromCaseID  = $run->fromCaseID;
    if($run->auto == 'enable') $run->color = "green";
    if($col->show)
    {
        $class = "c-$id ";
        $title = '';
        if($id == 'status') $class .= $run->status;
        if($id == 'title')  
        {
            $class .= ' text-left';
            $title  = "title='{$run->title}'";
        }
        if($id == 'id')     $class .= ' cell-id';
        if($id == 'lastRunResult') $class .= " $run->lastRunResult";
        if($id == 'assignedTo' && $run->assignedTo == $account) $class .= ' red';
        if($id == 'actions') $class .= 'c-actions';

        if($id == 'title')
        {
            if($isScene)
            {
                echo "<td class='c-name table-nest-title text-left sort-handler has-prefix has-suffix' {$title}><span class='table-nest-icon icon '></span>";
            }
            else
            {
                $icon = $run->auto == 'auto' ? 'icon-ztf' : 'icon-test';
                echo "<td class='c-name table-nest-title text-left sort-handler has-prefix has-suffix' {$title}><span class='table-nest-icon icon {$icon}'></span>";
            }
        }
        else
        {
            echo "<td class='" . $class . "'" . ($id=='title' ? "title='{$run->title}'":'') . ">";
        }
        

        if(isset($this->config->bizVersion)) $this->loadModel('flow')->printFlowCell('testcase', $run, $id);
        switch ($id)
        {
            case 'id':
                if($canBatchAction)
                {
                    echo html::checkbox('caseIDList', array($run->case => sprintf('%03d', $run->case)));
                }
                else
                {
                    printf('%03d', $run->case);
                }
                break;
            case 'pri':
                echo "<span class='label-pri label-pri-" . $run->pri . "' title='" . zget($this->lang->testcase->priList, $run->pri, $run->pri) . "'>";
                echo zget($this->lang->testcase->priList, $run->pri, $run->pri);
                echo "</span>";
                break;
            case 'title':
                if($run->branch) echo "<span class='label label-info label-outline'>{$branches[$run->branch]}</span>";
                if($canView  and !$isScene)
                {
                    if($fromCaseID)
                    {
                        echo html::a($caseLink, $run->title, null, "style='color: $run->color'") . html::a(helper::createLink('testcase', 'view', "caseID=$fromCaseID"), "[<i class='icon icon-share' title='{$this->lang->testcase->fromCase}'></i>#$fromCaseID]");
                    }
                    else
                    {
                        echo html::a($caseLink, $run->title, null, "style='color: $run->color'");
                    }
                }
                else
                {
                    echo "<span style='color: $run->color'>$run->title</span>";
                }
                break;
            case 'branch':
                echo $branches[$run->branch];
                break;
            case 'type':
                echo "<span style='color: $run->color'>".$this->lang->testcase->typeList[$run->type]."</span>";
                break;
            case 'stage':
                foreach(explode(',', trim($run->stage, ',')) as $stage) echo "<span style='color: $run->color'>".$this->lang->testcase->stageList[$stage] ."</span>". '<br />';
                break;
            case 'status':
                if($caseChanged)
                {
                    echo "<span style='color: $run->color' title='{$this->lang->testcase->changed}' class='warning'>{$this->lang->testcase->changed}</span>";
                }
                else
                {
                    $status = $this->processStatus('testcase', $run);
                    if($run->status == $status) $status = $this->processStatus('testtask', $run);
                    echo "<span style='color: $run->color'>".$status."</span>";
                }
                break;
            case 'precondition':
                echo "<span style='color: $run->color'>".$run->precondition."</span>";
                break;
            case 'keywords':
                echo "<span style='color: $run->color'>".$run->keywords."</span>";
                break;
            case 'version':
                echo "<span style='color: $run->color'>".$run->version."</span>";
                break;
            case 'openedBy':
                echo "<span style='color: $run->color'>".zget($users, $run->openedBy)."</span>";
                break;
            case 'openedDate':
                echo "<span style='color: $run->color'>".substr($run->openedDate, 5, 11)."</span>";
                break;
            case 'reviewedBy':
                echo "<span style='color: $run->color'>".zget($users, $run->reviewedBy)."</span>";
                break;
            case 'reviewedDate':
                echo "<span style='color: $run->color'>".substr($run->reviewedDate, 5, 11)."</span>";
                break;
            case 'lastEditedBy':
                echo zget($users, $run->lastEditedBy);
                break;
            case 'lastEditedDate':
                echo substr($run->lastEditedDate, 5, 11);
                break;
            case 'lastRunner':
                echo zget($users, $run->lastRunner);
                break;
            case 'lastRunDate':
                if(!helper::isZeroDate($run->lastRunDate)) echo date(DT_MONTHTIME1, strtotime($run->lastRunDate));
                break;
            case 'lastRunResult':
                $lastRunResultText = $run->lastRunResult ? zget($this->lang->testcase->resultList, $run->lastRunResult, $run->lastRunResult) : $this->lang->testcase->unexecuted;
                $class = 'result-' . $run->lastRunResult;
                echo "<span class='$class' style='color: $run->color'>" . $lastRunResultText . "</span>";
                break;
            case 'story':
                if($run->story and $run->storyTitle) echo html::a(helper::createLink('story', 'view', "storyID=$run->story"), $run->storyTitle);
                break;
            case 'assignedTo':
                echo "<span class='$class' style='color: $run->color'>" . zget($users, $run->assignedTo). "</span>";
                break;
            case 'bugs':
                echo (common::hasPriv('testcase', 'bugs') and $run->bugs) ? html::a(helper::createLink('testcase', 'bugs', "runID={$run->run}&caseID={$run->case}"), $run->bugs, '', "class='iframe'") : $run->bugs;
                break;
            case 'results':
                echo (common::hasPriv('testtask', 'results') and $run->results) ? html::a(helper::createLink('testtask', 'results', "runID={$run->run}&caseID={$run->case}"), $run->results, '', "class='iframe'") : $run->results;
                break;
            case 'stepNumber':
                echo $run->stepNumber;
                break;
            case 'caseUpdateMark':
                if($run->caseVersion > $run->version)
                {
                    echo "<span class='case-update-wait' title={$this->lang->testcase->caseUpdateWait}>{$this->lang->testcase->caseUpdateWait}</span>";
                }
                else if($run->caseVersion == $run->version and $run->caseVersion > 1)
                {
                    echo "<span class='case-update-done' title={$this->lang->testcase->caseUpdateDone}>{$this->lang->testcase->caseUpdateDone}</span>";
                }
                break;
            case 'actions':
                if($caseChanged)
                {
                    common::printIcon('testcase', 'confirmChange', "id=$run->case&taskID=$run->task&from=list", $run, 'list', 'search', 'hiddenwin');
                    break;
                }

                common::printIcon('testcase', 'createBug', "product=$run->product&branch=$run->branch&extra=executionID=$task->execution,buildID=$task->build,caseID=$run->case,version=$run->version,runID=$run->run,testtask=$task->id", $run, 'list', 'bug', '', 'iframe', '', "data-width='90%'");

                common::printIcon('testtask', 'results', "id=$run->run", $run, 'list', '', '', 'iframe', '', "data-width='90%'");
                common::printIcon('testtask', 'runCase', "id=$run->run", $run, 'list', 'play', '', 'runCase iframe', false, "data-width='95%'");

                if(common::hasPriv('testtask', 'unlinkCase', $run))
                {
                    $unlinkURL = helper::createLink('testtask', 'unlinkCase', "caseID=$run->run&confirm=yes");
                    echo html::a("javascript:void(0)", '<i class="icon-unlink"></i>', '', "title='{$this->lang->testtask->unlinkCase}' class='btn' onclick='ajaxDelete(\"$unlinkURL\", \"casesForm\", confirmUnlink)'");
                }
                break;
        }
        echo '</td>';
    }
}


/**
 * Get test task info by id.
 *
 * @param  int   $taskID
 * @param  bool  $setImgSize
 * @access public
 * @return void
 */
public function getById($taskID, $setImgSize = false, $originalDesc = false)
{
    $task = $this->dao->select("*")->from(TABLE_TESTTASK)->where('id')->eq((int)$taskID)->fetch();
    if($task)
    {
        $product = $this->dao->select('name,type')->from(TABLE_PRODUCT)->where('id')->eq($task->product)->fetch();
        $task->productName   = $product->name;
        $task->productType   = $product->type;
        $task->branch        = 0;
        $task->executionName = '';
        $task->buildName     = '';

        if($task->execution)
        {
            $task->executionName = $this->dao->select('name')->from(TABLE_EXECUTION)->where('id')->eq($task->execution)->fetch('name');
            $task->branch        = $this->dao->select('branch')->from(TABLE_PROJECTPRODUCT)->where('project')->eq($task->execution)->andWhere('product')->eq($task->product)->fetch('branch');
        }

        if($task->project) $task->projectStatus = $this->dao->select('status')->from(TABLE_PROJECT)->where('id')->eq($task->project)->fetch('status');

        $build = $this->dao->select('branch,name')->from(TABLE_BUILD)->where('id')->eq($task->build)->fetch();
        if($build)
        {
            $task->buildName = $build->name;
            $task->branch    = $build->branch;
        }
    }

    if(!$task) return false;

    if(!$originalDesc) $task = $this->loadModel('file')->replaceImgURL($task, 'desc');
    if($setImgSize) $task->desc = $this->loadModel('file')->setImgSize($task->desc);
    $task->files = $this->loadModel('file')->getByObject('testtask', $task->id);
    return $task;
}

function buildOperateBrowseMenuExt($task)
{
    $menu   = '';
    $params = "taskID=$task->id";
    $username = $this->app->user->account;
    $menu .= '<div id="action-divider">';
    $menu .= common::buildMyIconButton('testtask',   'autorun',    "tid=$task->id&username=$username", $task,'browse',  'play' ,'', 'iframe', true, "data-width='100%' data-height='800px'");
    $menu .= $this->buildMenu('testtask',   'cases',    $params, $task, 'browse', 'sitemap');
    $menu .= $this->buildMenu('testtask',   'linkCase', "$params&type=all&param=myQueryID", $task, 'browse', 'link');
    $menu .= $this->buildMenu('testreport', 'browse',   "objectID=$task->product&objectType=product&extra=$task->id", $task, 'browse', 'summary', '', '', false, '', $this->lang->testreport->common);
    $menu .= '</div>';
    $menu .= $this->buildMenu('testtask',   'view',     $params, $task, 'browse', 'list-alt', '', 'iframe', true, "data-width='90%'");
    $menu .= $this->buildMenu('testtask',   'edit',     $params, $task, 'browse');

    $copyClickable = $this->buildMenu('testtask', 'copy', $params, $task, 'browse', '', '', '', '', '', '', false);
    if(common::hasPriv('testtask', 'copy', $task))
    {
        $class = '';
        if(!$copyClickable) $class = ' disabled';
        $menu .= html::a("javascript:copyTesttask(\"$task->id\")", "<i class='icon-common-copy icon-copy'></i>", '', "data-app='qa' class='btn {$class}'");
        $menu .= html::a("#toCopy", "", '', "data-app='qa' data-toggle='modal' id='model{$task->id}'  class='btn hidden'");
    }

    $clickable = $this->buildMenu('testtask', 'delete', $params, $task, 'browse', '', '', '', '', '', '', false);
    if(common::hasPriv('testtask', 'delete', $task))
    {
        $deleteURL = helper::createLink('testtask', 'delete', "taskID=$task->id&confirm=yes");
        $class = 'btn';
        if(!$clickable) $class .= ' disabled';
        $menu .= html::a("javascript:ajaxDelete(\"$deleteURL\",\"taskList\",confirmDelete)", '<i class="icon-common-delete icon-trash"></i>', '', "title='{$this->lang->testtask->delete}' class='{$class}'");
    }
    return $menu;
}

public function copy($task, $copyNumber)
{
    $now    = helper::now();
    $tasks  = fixer::input('post')->get();
    $builds = array();
    for($i = 1; $i <= $copyNumber; $i++)
    {
        if(empty($tasks->build[$i]))
        {
            dao::$errors['message'][] = sprintf($this->lang->testtask->buildNotEmpty, $i);
            return false;
        }
        $builds[] = $tasks->build[$i];
    }

    $builds   = $this->loadModel('build')->getByList(array_unique($builds));
    $newTasks = array();
    for($i = 1; $i <= $copyNumber; $i++)
    {
        $newTask              = new stdClass();
        $newTask->name        = $tasks->name[$i];
        $newTask->project     = isset($builds[$tasks->build[$i]]->project)   ? $builds[$tasks->build[$i]]->project : $task->project;
        $newTask->execution   = isset($builds[$tasks->build[$i]]->execution) ? $builds[$tasks->build[$i]]->execution : $task->execution;
        $newTask->product     = isset($builds[$tasks->build[$i]]->product)   ? $builds[$tasks->build[$i]]->product : $task->product;
        $newTask->build       = isset($builds[$tasks->build[$i]])            ? $tasks->build[$i] : $task->build;
        $newTask->desc        = $task->desc;
        $newTask->report      = $task->report;
        $newTask->testreport  = $task->testreport;
        $newTask->status      = $tasks->status[$i];
        $newTask->mailto      = $task->mailto;
        $newTask->autocount   = $task->autocount;
        $newTask->owner       = $tasks->owner[$i];
        $newTask->pri         = $tasks->pri[$i];
        $newTask->begin       = $tasks->begin[$i];
        $newTask->end         = $tasks->end[$i];
        $newTask->createdBy   = $this->app->user->account;
        $newTask->createdDate = $now;
        $newTask->type        = '';
        $newTask->members     = '';
        if(isset($tasks->type[$i]))   $newTask->type    = implode(',', $tasks->type[$i]);
        if(isset($tasks->member[$i])) $newTask->members = implode(',', $tasks->member[$i]);

        if($newTask->begin && $newTask->end && $newTask->begin > $newTask->end)
        {
            dao::$errors['message'][] = $this->lang->testtask->beginGreaterEnd;
            return false;
        }

        foreach(explode(',', $this->config->testtask->create->requiredFields) as $field)
        {
            $field = trim($field);
            if($field and empty($newTask->$field))
            {
                dao::$errors['message'][] = sprintf($this->lang->error->notempty, $this->lang->testtask->$field);
                return false;
            }
        }
        $newTask->fileIDPairs = array();
        if($task->files)
        {
            $newTask->fileIDPairs = $this->loadModel('file')->copyObjectFiles('testtask', $task);
        }

        $newTasks[$i] = $newTask;
    }

    $cases = $this->dao->select('`case`,version')->from(TABLE_TESTRUN)->where('task')->eq($task->id)->fetchAll();

    $taskIDs = array();
    foreach($newTasks as $newTask)
    {
        $newTaskFileID = $newTask->fileIDPairs;
        unset($newTask->fileIDPairs);
        $this->dao->insert(TABLE_TESTTASK)->data($newTask)
            ->autoCheck($skipFields = 'begin,end')
            ->batchCheck($this->config->testtask->create->requiredFields, 'notempty')
            ->checkIF($newTask->begin != '', 'begin', 'date')
            ->checkIF($newTask->end != '', 'end', 'date')
            ->checkIF($newTask->end != '', 'end', 'ge', $newTask->begin)
            ->checkFlow()
            ->exec();
        if(!dao::isError())
        {
            $taskIDs[] = $taskID = $this->dao->lastInsertID();
            $this->dao->update(TABLE_FILE)->set('objectID')->eq($taskID)->where('id')->in($newTaskFileID)->exec();
        }
        foreach($cases as $case)
        {
            $row = new stdclass();
            $row->task       = $taskID;
            $row->case       = $case->case;
            $row->version    = $case->version;
            $row->assignedTo = '';
            $row->status     = 'normal';
            $this->dao->replace(TABLE_TESTRUN)->data($row)->exec();
            $this->loadModel('action')->create('case', $case->case, 'linked2testtask', '', $taskID);
        }
    }
    return $taskIDs;
}

/*
    * Build search form.
    *
    * @param int     $queryID
    * @param string  $actionURL
    *
    * @return 0
    * */
public function buildSearchForm($productID, $products, $type, $queryID, $actionURL, $branch = 0)
{
    $projectID     = $this->lang->navGroup->bug == 'qa' ? 0 : $this->session->project;
    if($type == 'all')
    {
        $productParams = $products;
    }
    else
    {
        $productParams = ($productID and isset($products[$productID])) ? array($productID => $products[$productID]) : $products;
    }

    $this->config->testtask->search['queryID']   = $queryID;
    $this->config->testtask->search['actionURL'] = $actionURL;

    unset($this->config->testtask->search['fields']['begin']);
    unset($this->config->testtask->search['fields']['end']);

    $this->config->testtask->search['params']['build']['values']         = $this->loadModel('build')->getBuildPairs($productID, 'all', 'notrunk,withbranch,releasetag');
    $this->config->testtask->search['params']['product']['values']       = array('' => '') + $productParams;
    $this->config->testtask->search['params']['execution']['values']     = $this->loadModel('product')->getExecutionPairsByProduct($productID, 0, 'id_desc', $projectID);

    $this->loadModel('search')->setSearchParams($this->config->testtask->search);
}

/**
 * Get test tasks of a product.
 *
 * @param  int         $productID
 * @param  int|string  $branch
 * @param  string      $orderBy
 * @param  object      $pager
 * @param  array       $scopeAndStatus
 * @param  int         $beginTime
 * @param  int         $endTime
 * @access public
 * @return array
 */
public function getProductTasks($productID, $branch = 'all', $orderBy = 'id_desc', $pager = null, $scopeAndStatus = array(), $queryID = 0, $beginTime = 0, $endTime = 0)
{
    $products = $scopeAndStatus[0] == 'all' ? $this->app->user->view->products : array();
    $branch   = $scopeAndStatus[0] == 'all' ? 'all' : $branch;

    if($queryID)
    {
        $query = $this->loadModel('search')->getQuery($queryID);
        if($query)
        {
            $this->session->set('testtaskQuery', $query->sql);
            $this->session->set('testtaskForm', $query->form);
        }
        else
        {
            $this->session->set('testtaskQuery', ' 1 = 1');
        }
    }
    else
    {
        if(strtolower($scopeAndStatus[1]) == 'bysearch' and $this->session->testtaskQuery == false) $this->session->set('testtaskQuery', ' 1 = 1');
    }

    $query = str_replace('`name`','t1.name', $this->session->testtaskQuery);
    $query = str_replace('`status`','t1.status', $query);
    $query = str_replace('`product`','t1.product', $query);
    $query = str_replace('`id`','t1.id', $query);

    $tasks = $this->dao->select("t1.*, t5.multiple, IF(t2.shadow = 1, t5.name, t2.name) AS productName, t3.name as executionName, t4.name AS buildName, t4.branch AS branch, t5.name AS projectName")
        ->from(TABLE_TESTTASK)->alias('t1')
        ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product = t2.id')
        ->leftJoin(TABLE_EXECUTION)->alias('t3')->on('t1.execution = t3.id')
        ->leftJoin(TABLE_BUILD)->alias('t4')->on('t1.build = t4.id')
        ->leftJoin(TABLE_PROJECT)->alias('t5')->on('t3.project = t5.id')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t1.auto')->ne('unit')
        ->beginIF(!$this->app->user->admin)->andWhere('t1.execution')->in("0,{$this->app->user->view->sprints}")->fi()
        ->beginIF($scopeAndStatus[0] == 'local')->andWhere('t1.product')->eq((int)$productID)->fi()
        ->beginIF($scopeAndStatus[0] == 'all' and strtolower($scopeAndStatus[1]) != 'bysearch')->andWhere('t1.product')->in($products)->fi()
        ->beginIF(strtolower($scopeAndStatus[1]) == 'myinvolved')
        ->andWhere('(t1.owner')->eq($this->app->user->account)
        ->orWhere("FIND_IN_SET('{$this->app->user->account}', t1.members)")
        ->markRight(1)
        ->fi()
        ->beginIF(strtolower($scopeAndStatus[1]) == 'totalstatus')->andWhere('t1.status')->in('blocked,doing,wait,done')->fi()
        ->beginIF(!in_array(strtolower($scopeAndStatus[1]), array('totalstatus', 'review', 'myinvolved', 'bysearch'), true))->andWhere('t1.status')->eq($scopeAndStatus[1])->fi()
        ->beginIF($branch !== 'all' and strtolower($scopeAndStatus[1]) != 'bysearch')->andWhere("CONCAT(',', t4.branch, ',')")->like("%,$branch,%")->fi()
        ->beginIF($beginTime)->andWhere('t1.begin')->ge($beginTime)->fi()
        ->beginIF($endTime)->andWhere('t1.end')->le($endTime)->fi()
        ->beginIF($branch == BRANCH_MAIN and strtolower($scopeAndStatus[1]) != 'bysearch')
        ->orWhere('(t1.build')->eq('trunk')
        ->andWhere('t1.deleted')->eq(0)
        ->andWhere('t1.product')->eq((int)$productID)
        ->markRight(1)
        ->fi()
        ->beginIF($scopeAndStatus[1] == 'review')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.reviewers)")
        ->andWhere('t1.reviewStatus')->eq('doing')
        ->fi()
        ->beginIF(strtolower($scopeAndStatus[1]) == 'bysearch' and $query)->andWhere($query)->fi()
        ->orderBy($orderBy)
        ->page($pager)
        ->fetchAll('id');

    foreach($tasks as $taskID => $task)
    {
        if($task->multiple)
        {
            if($task->projectName and $task->executionName)
            {
                $tasks[$taskID]->executionName = $task->projectName . '/' . $task->executionName;
            }
            elseif(!$task->executionName)
            {
                $tasks[$taskID]->executionName = $task->projectName;
            }
        }
    }

    return $tasks;
}

/**
 * Get test tasks of a execution.
 *
 * @param  int    $executionID
 * @param  string $orderBy
 * @param  object $pager
 * @access public
 * @return array
 */
public function getExecutionTasks($executionID, $objectType = 'execution', $browseType = 'all', $queryID = 0, $orderBy = 'id_desc', $pager = null)
{
    if($queryID)
    {
        $query = $this->loadModel('search')->getQuery($queryID);
        if($query)
        {
            $this->session->set('testtaskQuery', $query->sql);
            $this->session->set('testtaskForm', $query->form);
        }
        else
        {
            $this->session->set('testtaskQuery', ' 1 = 1');
        }
    }
    else
    {
        if(strtolower($browseType) == 'bysearch' and $this->session->testtaskQuery == false) $this->session->set('testtaskQuery', ' 1 = 1');
    }

    $query = str_replace('`id`','t1.`id`', $this->session->testtaskQuery);
    $query = str_replace('`product`','t1.`product`', $query);

    $allProduct = "t1.`product` = 'all'";
    if(strpos($query, $allProduct) !== false)
    {
        $products = $this->app->user->view->products;
        $query = str_replace($allProduct, '1', $query);
        $query = $query . ' AND t1.`product` ' . helper::dbIN($products);
    }

    return $this->dao->select('t1.*, t2.name AS buildName')
        ->from(TABLE_TESTTASK)->alias('t1')
        ->leftJoin(TABLE_BUILD)->alias('t2')->on('t1.build = t2.id')
        ->where('t1.deleted')->eq(0)
        ->beginIF($objectType == 'execution')->andWhere('t1.execution')->eq((int)$executionID)->fi()
        ->beginIF($objectType == 'project')->andWhere('t1.project')->eq((int)$executionID)->fi()
        ->andWhere('t1.auto')->ne('unit')
        ->beginIF(strtolower($browseType) == 'bysearch' and $query)->andWhere($query)->fi()
        ->orderBy($orderBy)
        ->page($pager)
        ->fetchAll('id');
}

public function getProjectTasks($projectID, $browseType = 'all', $queryID = 0, $orderBy = 'id_desc', $pager = null)
{
    if($queryID)
    {
        $query = $this->loadModel('search')->getQuery($queryID);
        if($query)
        {
            $this->session->set('testtaskQuery', $query->sql);
            $this->session->set('testtaskForm', $query->form);
        }
        else
        {
            $this->session->set('testtaskQuery', ' 1 = 1');
        }
    }
    else
    {
        if(strtolower($browseType) == 'bysearch' and $this->session->testtaskQuery == false) $this->session->set('testtaskQuery', ' 1 = 1');
    }

    $query = str_replace('`id`','t1.`id`', $this->session->testtaskQuery);
    $query = str_replace('`product`','t1.`product`', $query);

    $allProduct = "t1.`product` = 'all'";
    if(strpos($query, $allProduct) !== false)
    {
        $products = $this->app->user->view->products;
        $query = str_replace($allProduct, '1', $query);
        $query = $query . ' AND t1.`product` ' . helper::dbIN($products);
    }

    return $this->dao->select('t1.*, t2.name AS buildName')
        ->from(TABLE_TESTTASK)->alias('t1')
        ->leftJoin(TABLE_BUILD)->alias('t2')->on('t1.build = t2.id')
        ->where('t1.project')->eq((int)$projectID)
        ->andWhere('t1.auto')->ne('unit')
        ->andWhere('t1.deleted')->eq(0)
        ->beginIF(strtolower($browseType) == 'bysearch' and $query)->andWhere($query)->fi()
        ->orderBy($orderBy)
        ->page($pager)
        ->fetchAll('id');
}