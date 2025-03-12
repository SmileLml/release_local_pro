<?php
/**
 * Build bug menu.
 *
 * @param  object $bug
 * @param  string $type
 * @access public
 * @return string
 */
public function buildOperateMenu($bug, $type = 'view')
{
    $menu          = '';
    $params        = "bugID=$bug->id";
    $extraParams   = "extras=bugID=$bug->id";
    if($this->app->tab == 'project')   $extraParams .= ",projectID={$bug->project}";
    if($this->app->tab == 'execution') $extraParams .= ",executionID={$bug->execution}";
    $copyParams    = "productID=$bug->product&branch=$bug->branch&$extraParams";
    $convertParams = "productID=$bug->product&branch=$bug->branch&moduleID=0&from=bug&bugID=$bug->id";
    $toStoryParams = "product=$bug->product&branch=$bug->branch&module=0&story=0&execution=0&bugID=$bug->id";
    $misc = '';
    if(isset($bug->canBeClosedByProject) && !$bug->canBeClosedByProject) $misc = 'disabled';
    $menu .= $this->buildMenu('bug', 'confirmBug', $params, $bug, $type, 'ok', '', "iframe", true, $misc);
    if($type == 'view' and $bug->status != 'closed') $menu .= $this->buildMenu('bug', 'assignTo', $params, $bug, $type, '', '', "iframe", true);
    $menu .= $this->buildMenu('bug', 'resolve', $params, $bug, $type, 'checked', '', "iframe showinonlybody", true, $misc);
    $menu .= $this->buildMenu('bug', 'close', $params, $bug, $type, '', '', "text-danger iframe showinonlybody", true, $misc);
    if($type == 'view') $menu .= $this->buildMenu('bug', 'activate', $params, $bug, $type, '', '', "text-success iframe showinonlybody", true);
    if($type == 'view' && $this->app->tab != 'product')
    {
        $tab   = $this->app->tab == 'qa' ? 'product' : $this->app->tab;
        if($tab == 'product')
        {
            $product = $this->loadModel('product')->getByID($bug->product);
            if(!empty($product->shadow)) $tab = 'project';
        }
        $menu .= $this->buildMenu('bug', 'toStory', $toStoryParams, $bug, $type, $this->lang->icons['story'], '', '', '', "data-app='$tab' id='tostory'", $this->lang->bug->toStory);
        if(common::hasPriv('task', 'create') and !isonlybody()) $menu .= html::a('#toTask', "<i class='icon icon-check'></i><span class='text'>{$this->lang->bug->toTask}</span>", '', "data-app='qa' data-toggle='modal' class='btn btn-link'");
        $menu .= $this->buildMenu('bug', 'createCase', $convertParams, $bug, $type, 'sitemap');
    }
    if($type == 'view')
    {
        $menu .= "<div class='divider'></div>";
        $menu .= $this->buildFlowMenu('bug', $bug, $type, 'direct');
        $menu .= "<div class='divider'></div>";
    }
    $menu .= $this->buildMenu('bug', 'edit', $params, $bug, $type, '', '', '', false, $misc);
    if($this->app->tab != 'product') $menu .= $this->buildMenu('bug', 'create', $copyParams, $bug, $type, 'copy','', '', false, $misc);
    if($type == 'view') $menu .= $this->buildMenu('bug', 'delete', $params, $bug, $type, 'trash', 'hiddenwin', "showinonlybody");

    return $menu;
}


/**
 * Print assigned html.
 *
 * @param  object $bug
 * @param  array  $users
 * @param  bool   $output
 * @access public
 * @return void
 */
public function printAssignedHtml($bug, $users, $output = true)
{
    $btnTextClass   = '';
    $btnClass       = '';
    $assignedToText = !empty($bug->assignedTo) ? zget($users, $bug->assignedTo) : $this->lang->bug->noAssigned;
    if(empty($bug->assignedTo))
    {
        $btnClass       = $btnTextClass = 'assigned-none';
        $assignedToText = $this->lang->bug->noAssigned;
        if((isset($bug->assignedToChange) && !$bug->assignedToChange) || (isset($bug->canBeClosedByProject) && !$bug->canBeClosedByProject))
        {
            if(!$output) return '';
            echo '';
    }
    } 
    if($bug->assignedTo == $this->app->user->account) $btnClass = $btnTextClass = 'assigned-current';
    if(!empty($bug->assignedTo) and $bug->assignedTo != $this->app->user->account) $btnClass = $btnTextClass = 'assigned-other';

    $btnClass    .= $bug->assignedTo == 'closed' ? ' disabled' : '';
    $btnClass    .= ' iframe btn btn-icon-left btn-sm';

    $assignToLink = helper::createLink('bug', 'assignTo', "bugID=$bug->id", '', true);
    $modalToggle  = $bug->assignedTo == 'closed' ? '' : "data-toggle='modal'";
    $assignToHtml = html::a($assignToLink, "<i class='icon icon-hand-right'></i> <span title='" . zget($users, $bug->assignedTo) . "'>{$assignedToText}</span>", '', "class='$btnClass' $modalToggle");

    $html = !common::hasPriv('bug', 'assignTo', $bug) || (isset($bug->assignedToChange) && !$bug->assignedToChange) || (isset($bug->canBeClosedByProject) && !$bug->canBeClosedByProject) ? "<span style='padding-left: 21px' class='{$btnTextClass}'>{$assignedToText}</span>" : $assignToHtml;
    if(!$output) return $html;

    echo $html;
}


/**
 * Get user bugs.
 *
 * @param  string $account
 * @param  string $type
 * @param  string $orderBy
 * @param  int    $limit
 * @param  object $pager
 * @param  int    $executionID
 * @param  int    $queryID
 * @access public
 * @return array
 */
public function getUserBugs($account, $type = 'assignedTo', $orderBy = 'id_desc', $limit = 0, $pager = null, $executionID = 0, $queryID = 0)
{
    $moduleName = $this->app->rawMethod == 'work' ? 'workBug' : 'contributeBug';
    $queryName  = $moduleName . 'Query';
    $formName   = $moduleName . 'Form';
    $bugIDList  = array();
    if($moduleName == 'contributeBug')
    {
        $bugsAssignedByMe = $this->loadModel('my')->getAssignedByMe($account, 0, '', $orderBy, 'bug');
        foreach($bugsAssignedByMe as $bugID => $bug) $bugIDList[$bugID] = $bugID;
    }

    if($queryID)
    {
        $query = $this->loadModel('search')->getQuery($queryID);
        if($query)
        {
            $this->session->set($queryName, $query->sql);
            $this->session->set($formName, $query->form);
        }
        else
        {
            $this->session->set($queryName, ' 1 = 1');
        }
    }
    else
    {
        if($this->session->$queryName == false) $this->session->set($queryName, ' 1 = 1');
    }
    $query = $this->session->$queryName;
    $query = preg_replace('/`(\w+)`/', 't1.`$1`', $query);

    if($type != 'bySearch' and !$this->loadModel('common')->checkField(TABLE_BUG, $type)) return array();
    return $this->dao->select("t1.*, t2.name AS productName, t2.shadow, t3.name AS projectName, IF(t1.`pri` = 0, {$this->config->maxPriValue}, t1.`pri`) AS priOrder, IF(t1.`severity` = 0, {$this->config->maxPriValue}, t1.`severity`) AS severityOrder")->from(TABLE_BUG)->alias('t1')
        ->leftJoin(TABLE_PRODUCT)->alias('t2')->on('t1.product = t2.id')
        ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t1.project = t3.id')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t2.deleted')->eq('0')
        ->beginIF($type == 'bySearch')->andWhere($query)->fi()
        ->beginIF($executionID)->andWhere('t1.execution')->eq($executionID)->fi()
        ->beginIF($type != 'closedBy' and $this->app->moduleName == 'block')->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF($type != 'all' and $type != 'bySearch')->andWhere("t1.`$type`")->eq($account)->fi()
        ->beginIF($type == 'bySearch' and $moduleName == 'workBug')->andWhere("t1.assignedTo")->eq($account)->fi()
        ->beginIF($type == 'assignedTo' and $moduleName == 'workBug')->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF($type == 'bySearch' and $moduleName == 'contributeBug')
        ->andWhere('t1.openedBy', 1)->eq($account)
        ->orWhere('t1.closedBy')->eq($account)
        ->orWhere('t1.resolvedBy')->eq($account)
        ->orWhere('t1.id')->in($bugIDList)
        ->markRight(1)
        ->fi()
        ->orderBy($orderBy)
        ->beginIF($limit > 0)->limit($limit)->fi()
        ->page($pager)
        ->fetchAll();
}
public function batchSetAdjust()
{
    $bugs        = array();
    $allChanges  = array();
    $now         = helper::now();
    $data        = fixer::input('post')->get();
    $bugIDList   = $this->post->bugIDList ? $this->post->bugIDList : array();
    if(!empty($bugIDList))
    {
        $oldBugs = $bugIDList ? $this->getByList($bugIDList) : array();
        foreach($bugIDList as $bugID)
        {
            $oldBug = $oldBugs[$bugID];
            $bug = new stdclass();
            $bug->id             = $bugID;
            $bug->lastEditedBy   = $this->app->user->account;
            $bug->lastEditedDate = $now;
            /* if($this->app->tab != 'qa') */
            {
                $bug->product    = $data->adjustProduct;
                $bug->branch     = empty($data->adjustBranch) ? 0 : $data->adjustBranch;
            }
            $bug->project        = $data->adjustProject;
            $bug->execution      = $data->adjustExecution;
            $bug->openedBuild    = $data->adjustBuild;
            $this->dao->update(TABLE_BUG)->data($bug)
                ->autoCheck()
                ->batchCheck('openedBuild', 'notempty')
                ->where('id')->eq((int)$bugID)
                ->exec();
            $this->executeHooks($bugID, $bugID);
            $allChanges[$bugID] = common::createChanges($oldBug, $bug);
        }
    }
    return $allChanges;
}
public function batchAdjust($productID = 0, $branchID = 0)
{
    $bugs        = array();
    $allChanges  = array();
    $now         = helper::now();
    $data        = fixer::input('post')->get();

    $bugIDList   = $this->post->bugIDList ? $this->post->bugIDList : array();
    if(!empty($bugIDList))
    {
        $oldBugs = $bugIDList ? $this->getByList($bugIDList) : array();
        foreach($bugIDList as $bugID)
        {
            $oldBug = $oldBugs[$bugID];
            $bug = new stdclass();
            $bug->id             = $bugID;
            $bug->lastEditedBy   = $this->app->user->account;
            $bug->lastEditedDate = $now;
            if($this->app->tab != 'qa')
            {
                $bug->product    = $data->product[$bugID];
                $bug->branch     = empty($data->branch[$bugID]) ? 0 : $data->branch[$bugID];
            }
            $bug->project        = empty($data->project[$bugID]) ? 0 : $data->project[$bugID];
            if(empty($bug->project)) return helper::end(js::error('bug#' . $bugID . sprintf($this->lang->error->notempty, $this->lang->bug->project)));
            $bug->openedBuild    = !empty($data->build[$bugID]) ? implode(',', $data->build[$bugID]) : '';
            if(empty($bug->openedBuild)) return helper::end(js::error('bug#' . $bugID . sprintf($this->lang->error->notempty, $this->lang->bug->openedBuild)));
            $this->dao->update(TABLE_BUG)->data($bug)
                ->autoCheck()
                ->batchCheck('openedBuild', 'notempty')
                ->where('id')->eq((int)$bugID)
                ->exec();
            if(!dao::isError())
            {
                $this->executeHooks($bugID, $bugID);
                $allChanges[$bugID] = common::createChanges($oldBug, $bug);
            }
            else
            {
                return helper::end(js::error('bug#' . $bugID . dao::getError(true)));
            }
        }
    }
    return $allChanges;
}

public function batchSetDeadline()
{
    $allChanges = array();
    $bugIDList  = $this->post->bugIDList ? $this->post->bugIDList : array();
    $oldBugs    = $bugIDList ? $this->getByList($bugIDList) : array();
    $this->dao->update(TABLE_BUG)->set('deadline')->eq($this->post->deadline)->where('id')->in($bugIDList)->exec();
    if(!dao::isError())
    {
        foreach($bugIDList as $bugID)
        {
            $oldBug = $oldBugs[$bugID];
            $bug = new stdclass();
            $bug->id = $bugID;
            $bug->deadline = $this->post->deadline;
            $allChanges[$bugID] = common::createChanges($oldBug, $bug);
        }
        return $allChanges;
    }
    else
    {
        return helper::end(js::error($this->lang->bug->batchSetdeadlineError) . js::locate($this->session->bugList));
    }
}

public function assign($bugID)
{
    $now = helper::now();
    $oldBug = $this->getById($bugID);
    if($oldBug->status == 'closed') return array();

    $bug = fixer::input('post')
        ->add('id', $bugID)
        ->setDefault('lastEditedBy', $this->app->user->account)
        ->setDefault('lastEditedDate', $now)
        ->setDefault('assignedDate', $now)
        ->setDefault('mailto', '')
        ->stripTags($this->config->bug->editor->assignto['id'], $this->config->allowedTags)
        ->remove('comment,showModule,adjustProduct,adjustBranch,adjustProject,adjustExecution,adjustBuild,deadline,batchCommentVal')
        ->join('mailto', ',')
        ->get();

    if($this->app->rawMethod == 'batchassignto') unset($bug->mailto);

    $bug = $this->loadModel('file')->processImgURL($bug, $this->config->bug->editor->assignto['id'], $this->post->uid);
    $this->dao->update(TABLE_BUG)
        ->data($bug)
        ->autoCheck()
        ->checkFlow()
        ->where('id')->eq($bugID)->exec();

    if(!dao::isError()) return common::createChanges($oldBug, $bug);
}