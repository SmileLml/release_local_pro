<?php
/**
 * Build test case browse menu.
 *
 * @param  object $case
 * @access public
 * @return string
 */
public function buildOperateBrowseMenu($case)
{
    $canBeChanged = common::canBeChanged('case', $case);
    if(!$canBeChanged) return '';

    $menu   = '';
    $params = "caseID=$case->id";

    if($case->needconfirm || $case->browseType == 'needconfirm')
    {
        return $this->buildMenu('testcase', 'confirmstorychange', $params, $case, 'browse', 'ok', 'hiddenwin', '', '', '', $this->lang->confirm);
    }

    $menu .= $this->buildMenu('testtask', 'runCase', "runID=0&$params&version=$case->version", $case, 'browse', 'play', '', 'runCase iframe', false, "data-width='95%'" . ((isset($case->canAction) && !$case->canAction) ? "disabled" : '' ));
    $menu .= $this->buildMenu('testtask', 'results', "runID=0&$params", $case, 'browse', '', '', 'iframe', true, "data-width='95%'");

    $editParams = $params;
    if($this->app->tab == 'project')   $editParams .= "&comment=false&projectID={$this->session->project}";
    if($this->app->tab == 'execution') $editParams .= "&comment=false&executionID={$this->session->execution}";
    $menu .= $this->buildMenu('testcase', 'edit', $editParams, $case, 'browse', '', '', '', false, ((isset($case->canAction) && !$case->canAction) ? "disabled" : '' ));

    if($this->config->testcase->needReview || !empty($this->config->testcase->forceReview))
    {
        common::printIcon('testcase', 'review', $params, $case, 'browse', 'glasses', '', 'showinonlybody iframe');
    }
    $menu .= $this->buildMenu('testcase', 'createBug', "product=$case->product&branch=$case->branch&extra=caseID=$case->id,version=$case->version,runID=", $case, 'browse', 'bug', '', 'iframe', '', "data-width='90%'" . ((isset($case->canAction) && !$case->canAction) ? "disabled" : '' ));
    $menu .= $this->buildMenu('testcase', 'create',  "productID=$case->product&branch=$case->branch&moduleID=$case->module&from=testcase&param=$case->id", $case, 'browse', 'copy', '' ,'', false, ((isset($case->canAction) && !$case->canAction) ? "disabled" : '' ));
    if($case->auto == 'auto') $menu .= $this->buildMenu('testcase', 'showScript', $params, $case, 'browse', 'file-code', '', 'runCase iframe', false);

    return $menu;
}

/**
 * Print cell data
 *
 * @param  object $col
 * @param  object $case
 * @param  array  $users
 * @param  array  $branches
 * @param  array  $modulePairs
 * @param  string $browseType
 * @param  string $mode         datatable|table
 * @access public
 * @return void
 */
public function printCell($col, $case, $users, $branches, $modulePairs = array(), $browseType = '', $mode = 'datatable')
{
    $isScene = $case->isScene;

    /* Check the product is closed. */
    $canBeChanged = common::canBeChanged('case', $case);

    $canBatchRun                = common::hasPriv('testtask', 'batchRun');
    $canBatchEdit               = common::hasPriv('testcase', 'batchEdit');
    $canBatchDelete             = common::hasPriv('testcase', 'batchDelete');
    $canBatchCaseTypeChange     = common::hasPriv('testcase', 'batchCaseTypeChange');
    $canBatchConfirmStoryChange = common::hasPriv('testcase', 'batchConfirmStoryChange');
    $canBatchChangeModule       = common::hasPriv('testcase', 'batchChangeModule');

    $canBatchAction             = ($canBatchRun or $canBatchEdit or $canBatchDelete or $canBatchCaseTypeChange or $canBatchConfirmStoryChange or $canBatchChangeModule);

    $canView    = common::hasPriv('testcase', 'view');
    $caseLink   = helper::createLink('testcase', 'view', "caseID=$case->id&version=$case->version");
    $account    = $this->app->user->account;
    $fromCaseID = $case->fromCaseID;
    $id = $col->id;
    if($col->show)
    {
        $class = $id == 'title' ? 'c-name' : 'c-' . $id;
        $title = '';
        if($id == 'title')
        {
            $class .= ' text-left';
            $title  = "title='{$case->title}'";
        }
        if($id == 'status')
        {
            $class .= $case->status;
            $title  = "title='" . $this->processStatus('testcase', $case) . "'";
        }
        if(strpos(',bugs,results,stepNumber,', ",$id,") !== false) $title = "title='{$case->$id}'";
        if($id == 'actions') $class .= ' c-actions';
        if($id == 'lastRunResult') $class .= " {$case->lastRunResult}";
        if(strpos(',stage,precondition,keywords,story,', ",{$id},") !== false) $class .= ' text-ellipsis';
        if($id == 'reviewedBy')
        {
            $reviewed = '';
            $reviewedBy = explode(',', $case->reviewedBy);
            foreach($reviewedBy as $account)
            {
                $account = trim($account);
                if(empty($account)) continue;
                $reviewed .= zget($users, $account) . " &nbsp;";
            }
            $title = "title='{$reviewed}'";
        }

        if($id == 'title')
        {
            if($isScene)
            {
                echo "<td class='c-name table-nest-title text-left sort-handler has-prefix has-suffix' {$title}><span class='table-nest-icon icon '></span>";
            }
            else
            {
                $icon = $case->auto == 'auto' ? 'icon-ztf' : 'icon-test';
                echo "<td class='c-name table-nest-title text-left sort-handler has-prefix has-suffix' {$title}><span class='table-nest-icon icon {$icon}'></span>";
            }
        }
        else
        {
            echo "<td class='{$class}' {$title}>";
        }

        if($this->config->edition != 'open') $this->loadModel('flow')->printFlowCell('testcase', $case, $id);
        switch($id)
        {
            case 'id':
                $showID = ($browseType == 'all' && !$this->cookie->onlyScene && $case->isScene) ? '': sprintf('%03d', $case->id);
                if($canBatchAction && $case->canAction)
                {
                    $disabled = $canBeChanged ? '' : 'disabled';
                    if(!$isScene)
                    {
                        echo html::checkbox('caseIDList', array($case->id => ''), '', $disabled) . html::a(helper::createLink('testcase', 'view', "caseID=$case->id"), $showID, '', "data-app='{$this->app->tab}'");
                    }
                    else
                    {
                        echo html::checkbox('sceneIDList', array($case->id => ''), '', $disabled) . $showID;
                    }
                }
                else
                {
                    echo $showID;
                }
                break;
            case 'pri':
                if(!$isScene)
                {
                    echo "<span class='label-pri label-pri-" . $case->pri . "' title='" . zget($this->lang->testcase->priList, $case->pri, $case->pri) . "'>";
                    echo zget($this->lang->testcase->priList, $case->pri, $case->pri);
                    echo "</span>";
                }
                break;
            case 'title':
                if(!$isScene){
                    if($this->app->tab == 'project')
                    {
                        $showBranch = isset($this->config->project->testcase->showBranch) ? $this->config->project->testcase->showBranch : 1;
                    }
                    else
                    {
                        $showBranch = isset($this->config->testcase->browse->showBranch) ? $this->config->testcase->browse->showBranch : 1;
                    }

                    if(isset($branches[$case->branch]) and $showBranch) echo "<span class='label label-outline label-badge'>{$branches[$case->branch]}</span> ";
                    if($modulePairs and $case->module and isset($modulePairs[$case->module])) echo "<span class='label label-gray label-badge'>{$modulePairs[$case->module]}</span> ";
                    echo $canView ? html::a($caseLink, $case->title, null, "style='color: $case->color' data-app='{$this->app->tab}'")
                        : "<span style='color: $case->color'>$case->title</span>";

                    if($fromCaseID and $canView)
                    {
                        $fromLink = helper::createLink('testcase', 'view', "caseID=$fromCaseID");
                        $title    = "[<i class='icon icon-share' title='{$this->lang->testcase->fromCaselib}'></i>#$fromCaseID]";
                        echo html::a($fromLink, $title, '', "data-app='{$this->app->tab}'");
                    }
                }
                else
                {
                    echo $case->title;
                }
                break;
            case 'branch':
                echo $branches[$case->branch];
                break;
            case 'type':
                echo $this->lang->testcase->typeList[$case->type];
                break;
            case 'stage':
                $stages = '';
                foreach(explode(',', trim($case->stage, ',')) as $stage) $stages .= $this->lang->testcase->stageList[$stage] . ',';
                $stages = trim($stages, ',');
                echo "<span title='$stages'>$stages</span>";
                break;
            case 'status':
                if($isScene) break;
                if($case->needconfirm)
                {
                    print("<span class='status-story status-changed' title='{$this->lang->story->changed}'>{$this->lang->story->changed}</span>");
                }
                elseif(isset($case->fromCaseVersion) and $case->fromCaseVersion > $case->version and !$case->needconfirm)
                {
                    print("<span class='status-story status-changed' title='{$this->lang->testcase->changed}'>{$this->lang->testcase->changed}</span>");
                }
                else
                {
                    print("<span class='status-testcase status-{$case->status}'>" . $this->processStatus('testcase', $case) . "</span>");
                }
                break;
            case 'story':
                static $stories = array();
                if(empty($stories)) $stories = $this->dao->select('id,title')->from(TABLE_STORY)->where('deleted')->eq('0')->andWhere('product')->eq($case->product)->fetchPairs('id', 'title');
                if($case->story and isset($stories[$case->story])) echo html::a(helper::createLink('story', 'view', "storyID=$case->story"), $stories[$case->story]);
                break;
            case 'precondition':
                echo $case->precondition;
                break;
            case 'keywords':
                echo $case->keywords;
                break;
            case 'version':
                if(!$isScene) echo $case->version;
                break;
            case 'openedBy':
                echo zget($users, $case->openedBy);
                break;
            case 'openedDate':
                echo substr($case->openedDate, 5, 11);
                break;
            case 'reviewedBy':
                echo $reviewed;
                break;
            case 'reviewedDate':
                echo helper::isZeroDate($case->reviewedDate) ? '' : substr($case->reviewedDate, 5, 11);
                break;
            case 'lastEditedBy':
                echo zget($users, $case->lastEditedBy);
                break;
            case 'lastEditedDate':
                echo helper::isZeroDate($case->lastEditedDate) ? '' : substr($case->lastEditedDate, 5, 11);
                break;
            case 'lastRunner':
                echo zget($users, $case->lastRunner);
                break;
            case 'lastRunDate':
                if(!helper::isZeroDate($case->lastRunDate)) echo substr($case->lastRunDate, 5, 11);
                break;
            case 'lastRunResult':
                if(!$isScene)
                {
                    $class = 'result-' . $case->lastRunResult;
                    $lastRunResultText = $case->lastRunResult ? zget($this->lang->testcase->resultList, $case->lastRunResult, $case->lastRunResult) : $this->lang->testcase->unexecuted;
                    echo "<span class='$class'>" . $lastRunResultText . "</span>";
                }
                break;
            case 'bugs':
                if(!$isScene) echo (common::hasPriv('testcase', 'bugs') and $case->bugs) ? html::a(helper::createLink('testcase', 'bugs', "runID=0&caseID={$case->id}"), $case->bugs, '', "class='iframe'") : $case->bugs;
                break;
            case 'results':
                if(!$isScene) echo (common::hasPriv('testtask', 'results') and $case->results) ? html::a(helper::createLink('testtask', 'results', "runID=0&caseID={$case->id}"), $case->results, '', "class='iframe'") : $case->results;
                break;
            case 'stepNumber':
                if(!$isScene) echo $case->stepNumber;
                break;
            case 'caseUpdateMark':
                if($isScene) break;
                if(isset($case->fromCaseVersion) and $case->fromCaseVersion > $case->version and !empty($case->product))
                {
                    echo "<span class='case-update-wait' title={$this->lang->testcase->caseUpdateWait}>{$this->lang->testcase->caseUpdateWait}</span>";
                }
                else if(isset($case->fromCaseVersion) and $case->fromCaseVersion == $case->version and !empty($case->product) and isset($case->fromCaseID) and !empty($case->fromCaseID))
                {
                    echo "<span class='case-update-done' title={$this->lang->testcase->caseUpdateDone}>{$this->lang->testcase->caseUpdateDone}</span>";
                }
                break;
            case 'actions':
                if(!$isScene)
                {
                    $case->browseType = $browseType;
                    echo $this->buildOperateMenu($case, 'browse');
                    break;
                }
                else
                {
                    echo $this->buildOperateBrowseSceneMenu($case);
                }
        }
        echo '</td>';
    }
}

/**
 * Synchronize use case updates in batches.
 * 
 * @param array $caseList
 * @param array $libCaseList
 * @access public
 * @return void
 */
public function batchConfirmCaseUpdate($caseList = array(), $libCaseList = array())
{
    foreach($caseList as $caseID => $case)
    {
        $this->dao->delete()->from(TABLE_FILE)->where('objectType')->eq('testcase')->andWhere('objectID')->eq($caseID)->exec();
        $libCase        = $libCaseList[$case->fromCaseID];
        $libCase->files = $this->loadModel('file')->getByObject('testcase', $libCase->id);
        foreach($libCase->files as $fileID => $file)
        {
            $fileName = pathinfo($file->pathname, PATHINFO_FILENAME);
            $datePath = substr($file->pathname, 0, 6);
            $realPath = $this->app->getAppRoot() . "www/data/upload/{$this->app->company->id}/" . "{$datePath}/" . $fileName;

            $rand        = rand();
            $newFileName = $fileName . 'copy' . $rand;
            $newFilePath = $this->app->getAppRoot() . "www/data/upload/{$this->app->company->id}/" . "{$datePath}/" .  $newFileName;
            copy($realPath, $newFilePath);

            $newFileName = $file->pathname;
            $newFileName = str_replace('.', "copy$rand.", $newFileName);

            unset($file->id, $file->realPath, $file->webPath);
            $file->objectID = $caseID;
            $file->pathname = $newFileName;
            $this->dao->insert(TABLE_FILE)->data($file)->exec();
        }
        
        $version = $case->version + 1;

        $this->dao->update(TABLE_CASE)
            ->set('version')->eq($version)
            ->set('fromCaseVersion')->eq($version)
            ->set('precondition')->eq($libCase->precondition)
            ->set('title')->eq($libCase->title)
            ->where('id')->eq($caseID)
            ->exec();

        $libCase->steps = $this->dao->select('*')->from(TABLE_CASESTEP)->where('`case`')->eq($libCase->id)->andWhere('version')->eq($libCase->version)->orderBy('id')->fetchAll('id');
        foreach($libCase->steps as $key => $step)
        {
            unset($step->id);
            $step->desc    = html_entity_decode($step->desc);
            $step->expect  = html_entity_decode($step->expect);
            $step->case    = $caseID;
            $step->version = $version;
            $this->dao->insert(TABLE_CASESTEP)->data($step)->exec();
        }
    }
}

/**
 * Get taskrun by case id.
 *
 * @param  int    $taskID
 * @param  int    $caseID
 * @access public
 * @return void
 */
public function getCasesByTask($taskID, $caseIDList = array())
{
    return $this->dao->select('t1.id,t1.task,t1.`case`,t1.version,t2.version as caseVersion')->from(TABLE_TESTRUN)->alias('t1')
                ->leftJoin(TABLE_CASE)->alias('t2')->on('t1.case = t2.id')
                ->where('t1.task')->eq($taskID)
                ->andWhere('t1.case')->in($caseIDList)
                ->fetchAll('id');
}

public function getModuleCases($productID, $branch = 0, $moduleIdList = 0, $browseType = '', $auto = 'no', $caseType = '', $orderBy = 'id_desc', $pager = null)
{
    $stmt = $this->dao->select('t1.*, t2.title as storyTitle, t2.deleted as storyDeleted, true as canAction')->from(TABLE_CASE)->alias('t1')
        ->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story=t2.id');

    if($this->app->tab == 'project') $stmt = $stmt->leftJoin(TABLE_PROJECTCASE)->alias('t3')->on('t1.id=t3.case');

    $showAutoCase = ($this->cookie->showAutoCase && !(defined('RUN_MODE') && RUN_MODE == 'api'));

    return $stmt ->where('t1.product')->eq((int)$productID)
        ->beginIF($this->app->tab == 'project')->andWhere('t3.project')->eq($this->session->project)->fi()
        ->beginIF($branch !== 'all')->andWhere('t1.branch')->eq($branch)->fi()
        ->beginIF($moduleIdList)->andWhere('t1.module')->in($moduleIdList)->fi()
        ->beginIF($browseType == 'all')->andWhere('t1.scene')->eq(0)->fi()
        ->beginIF($browseType == 'wait')->andWhere('t1.status')->eq($browseType)->fi()
        ->beginIF($auto == 'unit')->andWhere('t1.auto')->eq('unit')->fi()
        ->beginIF($auto != 'unit')->andWhere('t1.auto')->ne('unit')->fi()
        ->beginIF($showAutoCase)->andWhere('t1.auto')->eq('auto')->fi()
        ->beginIF($caseType)->andWhere('t1.type')->eq($caseType)->fi()
        ->andWhere('t1.deleted')->eq('0')
        ->orderBy($orderBy)
        ->page($pager)
        ->fetchAll('id');
}

public function getSceneGroups($productID, $branch = 0, $moduleID = 0, $caseType = '', $orderBy = 'id_desc', $pager = null)
{
    $modules = $moduleID ? $this->loadModel('tree')->getAllChildId($moduleID) : '0';
    $scenes = $this->dao->select('*, true as canAction')->from(TABLE_SCENE)
        ->where('deleted')->eq('0')
        ->andWhere('product')->eq($productID)
        ->beginIF($branch !== 'all')->andWhere('branch')->eq($branch)->fi()
        ->beginIF($modules)->andWhere('module')->in($modules)->fi()
        ->orderBy('grade_desc, sort_asc')
        ->fetchAll('id');

    $pager->recTotal = 0;

    if(!$scenes) return array();

    $cases = array();
    if($scenes && !$this->cookie->onlyScene)
    {
        $stmt = $this->dao->select("t1.*, true as canAction")->from(TABLE_CASE)->alias('t1');

        if($this->app->tab == 'project')
        {
            $stmt = $this->dao->select("t1.*, IF(t3.status='closed', false, true) as canAction")->from(TABLE_CASE)->alias('t1')
                ->leftJoin(TABLE_PROJECTCASE)->alias('t2')->on('t1.id=t2.case')->leftJoin(TABLE_PROJECT)->alias('t3')->on('t2.project=t3.id');
        }
        $caseList = $stmt->where('t1.deleted')->eq('0')
            ->andWhere('t1.scene')->ne(0)
            ->andWhere('t1.product')->eq($productID)
            ->beginIF($this->app->tab == 'project')->andWhere('t2.project')->eq($this->session->project)->fi()
            ->beginIF($branch !== 'all')->andWhere('t1.branch')->eq($branch)->fi()
            ->beginIF($modules)->andWhere('t1.module')->in($modules)->fi()
            ->beginIF($this->cookie->showAutoCase)->andWhere('t1.auto')->eq('auto')->fi()
            ->beginIF($caseType)->andWhere('t1.type')->eq($caseType)->fi()
            ->orderBy($orderBy)
            ->fetchAll('id');

        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'testcase', false);
        $caseList = $this->loadModel('story')->checkNeedConfirm($caseList);
        $caseList = $this->appendData($caseList);
        foreach($caseList as $case) $cases[$case->scene][$case->id] = $case;
    }

    $this->dao->setTable(TABLE_CASE);
    $fieldTypes = $this->dao->getFieldsType();

    foreach($scenes as $id => $scene)
    {
        /* Set default value for the fields exist in TABLE_CASE but not in TABLE_SCENE. */
        foreach($fieldTypes as $field => $type)
        {
            if(isset($scene->$field)) continue;
            $scene->$field = $type['rule'] == 'int' ? '0' : '';
        }

        $scene->bugs       = 0;
        $scene->results    = 0;
        $scene->caseFails  = 0;
        $scene->stepNumber = 0;
        $scene->isScene    = true;

        if(isset($cases[$id]))
        {
            foreach($cases[$id] as $case)
            {
                $case->id      = 'case_' . $case->id;
                $case->parent  = $id;
                $case->grade   = $scene->grade + 1;
                $case->path    = $scene->path . $case->id . ',';
                $case->isScene = false;

                $scene->cases[$case->id] = $case;
            }
        }

        if(!isset($scenes[$scene->parent])) continue;

        $parent = $scenes[$scene->parent];
        $parent->children[$id] = $scene;

        unset($scenes[$id]);
    }

    $pager->recTotal  = count($scenes);
    $pager->pageTotal = ceil($pager->recTotal / $pager->recPerPage);

    return array_slice($scenes, $pager->recPerPage * ($pager->pageID - 1), $pager->recPerPage);
}

function update($caseID, $testtasks = array())
{
    $steps   = $this->post->steps;
    $expects = $this->post->expects;
    foreach($expects as $key => $value)
    {
        if(!empty($value) and empty($steps[$key]))
        {
            dao::$errors[] = sprintf($this->lang->testcase->stepsEmpty, $key);
            return false;
        }
    }

    $now     = helper::now();
    $oldCase = $this->getById($caseID);

    $result = $this->getStatus('update', $oldCase);
    if(!$result or !is_array($result)) return $result;

    list($stepChanged, $status) = $result;

    $version = $stepChanged ? (int)$oldCase->version + 1 : (int)$oldCase->version;

    // if(!empty($_POST['auto']))
    // {
    //     $_POST['auto'] = 'auto';
    //     if($this->post->script) $_POST['script'] = htmlentities($_POST['script']);
    // }
    // else
    // {
    //     $_POST['auto']   = 'no';
    //     $_POST['script'] = '';
    // }

    $case = fixer::input('post')
        ->add('id', $caseID)
        ->add('version', $version)
        ->setIF($this->post->story != false and $this->post->story != $oldCase->story, 'storyVersion', $this->loadModel('story')->getVersion($this->post->story))
        ->setIF(!$this->post->linkCase, 'linkCase', '')
        ->setDefault('lastEditedBy',   $this->app->user->account)
        ->add('lastEditedDate', $now)
        ->setDefault('story,branch', 0)
        ->setDefault('stage', '')
        ->setDefault('deleteFiles', array())
        ->join('stage', ',')
        ->join('linkCase', ',')
        ->setForce('status', $status)
        ->cleanInt('story,product,branch,module')
        ->stripTags($this->config->testcase->editor->edit['id'], $this->config->allowedTags)
        ->remove('comment,steps,expects,files,labels,linkBug,stepType,scriptFile,scriptName')
        ->removeIF($this->post->auto == 'auto' && !$this->post->script, 'script')
        ->get();

    $requiredFields = $this->config->testcase->edit->requiredFields;
    if($oldCase->lib != 0)
    {
        /* Remove the require field named story when the case is a lib case.*/
        $requiredFields = str_replace(',story,', ',', ",$requiredFields,");
    }
    $case = $this->loadModel('file')->processImgURL($case, $this->config->testcase->editor->edit['id'], $this->post->uid);
    $this->dao->update(TABLE_CASE)->data($case, 'deleteFiles')->autoCheck()->batchCheck($requiredFields, 'notempty')->checkFlow()->where('id')->eq((int)$caseID)->exec();
    if(!$this->dao->isError())
    {
        $this->updateCase2Project($oldCase, $case, $caseID);

        if($stepChanged)
        {
            $parentStepID = 0;
            $isLibCase    = ($oldCase->lib and empty($oldCase->product));
            $titleChanged = ($case->title != $oldCase->title);
            $autoChanged = ($case->auto != $oldCase->auto);
            if($isLibCase and $autoChanged) {
                $this->dao->update(TABLE_CASE)->set('auto')->eq($case->auto)->where('`fromCaseID`')->eq($caseID)->exec(); 
            }
            if($isLibCase and $titleChanged) {
                $this->dao->update(TABLE_CASE)->set('`title`')->eq($case->title)->where('`fromCaseID`')->eq($caseID)->exec();
            }
            if($isLibCase)
            {
                $this->dao->update(TABLE_CASE)->set('`fromCaseVersion`')->eq($version)->where('`fromCaseID`')->eq($caseID)->exec();
            }

            /* Ignore steps when post has no steps. */
            if($this->post->steps)
            {
                $data = fixer::input('post')->get();

                foreach($data->steps as $stepID => $stepDesc)
                {
                    if(empty($stepDesc)) continue;
                    $stepType = $this->post->stepType;
                    $step = new stdclass();
                    $step->type    = ($stepType[$stepID] == 'item' and $parentStepID == 0) ? 'step' : $stepType[$stepID];
                    $step->parent  = ($step->type == 'item') ? $parentStepID : 0;
                    $step->case    = $caseID;
                    $step->version = $version;
                    $step->desc    = rtrim(htmlSpecialString($stepDesc));
                    $step->expect  = $step->type == 'group' ? '' : rtrim(htmlSpecialString($data->expects[$stepID]));
                    $this->dao->insert(TABLE_CASESTEP)->data($step)->autoCheck()->exec();
                    if($step->type == 'group') $parentStepID = $this->dao->lastInsertID();
                    if($step->type == 'step')  $parentStepID = 0;
                }
            }
            else
            {
                foreach($oldCase->steps as $step)
                {
                    unset($step->id);
                    $step->version = $version;
                    $this->dao->insert(TABLE_CASESTEP)->data($step)->autoCheck()->exec();
                }
            }
        }

        /* Link bugs to case. */
        $this->post->linkBug = $this->post->linkBug ? $this->post->linkBug : array();
        $linkedBugs = array_keys($oldCase->toBugs);
        $linkBugs   = $this->post->linkBug;
        $newBugs    = array_diff($linkBugs, $linkedBugs);
        $removeBugs = array_diff($linkedBugs, $linkBugs);

        if($newBugs)
        {
            foreach($newBugs as $bugID)
            {
                $this->dao->update(TABLE_BUG)
                    ->set('`case`')->eq($caseID)
                    ->set('caseVersion')->eq($case->version)
                    ->set('`story`')->eq($case->story)
                    ->set('storyVersion')->eq($case->storyVersion)
                    ->where('id')->eq($bugID)->exec();
            }
        }

        if($removeBugs)
        {
            foreach($removeBugs as $bugID)
            {
                $this->dao->update(TABLE_BUG)
                    ->set('`case`')->eq(0)
                    ->set('caseVersion')->eq(0)
                    ->set('`story`')->eq(0)
                    ->set('storyVersion')->eq(0)
                    ->where('id')->eq($bugID)->exec();
            }
        }

        /* Join the steps to diff. */
        if($stepChanged and $this->post->steps)
        {
            $oldCase->steps = $this->joinStep($oldCase->steps);
            $case->steps    = $this->joinStep($this->getById($caseID, $version)->steps);
        }
        else
        {
            unset($oldCase->steps);
        }

        if($case->branch and !empty($testtasks))
        {
            $this->loadModel('action');
            foreach($testtasks as $taskID => $testtask)
            {
                if($testtask->branch != $case->branch and $taskID)
                {
                    $this->dao->delete()->from(TABLE_TESTRUN)
                        ->where('task')->eq($taskID)
                        ->andWhere('`case`')->eq($caseID)
                        ->exec();
                    $this->action->create('case' ,$caseID, 'unlinkedfromtesttask', '', $taskID);
                }
            }
        }

        $this->file->processFile4Object('testcase', $oldCase, $case);
        return common::createChanges($oldCase, $case);
    }
}

public function getByList($caseIDList = 0, $query = '', $deleted = false)
{
    return $this->dao->select('*')->from(TABLE_CASE)
        ->beginIF(!$deleted)->where('deleted')->eq(0)->fi()
        ->beginIF($deleted)->where('1=1')->fi()
        ->beginIF($caseIDList)->andWhere('id')->in($caseIDList)->fi()
        ->beginIF($query)->andWhere($query)->fi()
        ->fetchAll('id');
}