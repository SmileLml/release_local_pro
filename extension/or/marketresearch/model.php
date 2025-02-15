<?php
/**
 * The model file of marketresearch module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2023 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.zentao.net)
 * @license     ZPL(https://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Hu Fangzhou<hufangzhou@easycorp.ltd>
 * @package     marketresearch
 * @link        https://www.zentao.net
 */
class marketresearchModel extends Model
{
    /**
     * create a marketresearch.
     *
     * @access public
     * @return int|false
     */
    public function create()
    {
        $now        = helper::now();
        $marketName = $this->post->marketName;
        $account    = $this->app->user->account;

        if(empty($_POST['market']) and empty($marketName))
        {
            dao::$errors['market'] = $this->lang->marketresearch->marketNotEmpty;
            return false;
        }

        $this->loadModel('execution');

        $research = fixer::input('post')
            ->callFunc('name', 'trim')
            ->setIF($this->post->delta == 999, 'end', LONG_TIME)
            ->setIF($this->post->delta == 999, 'days', 0)
            ->setIF($this->post->acl   == 'open', 'whitelist', '')
            ->setIF(!isset($_POST['whitelist']), 'whitelist', '')
            ->remove('uid,newMarket,marketName,delta,contactListMenu,teamMembers')
            ->setDefault('status', 'wait')
            ->setDefault('vision', 'or')
            ->setDefault('model', 'research')
            ->setDefault('type', 'project')
            ->setDefault('multiple', '1')
            ->setDefault('team', $this->post->name)
            ->setDefault('openedBy', $account)
            ->setDefault('openedDate', $now)
            ->setDefault('days', '0')
            ->join('whitelist', ',')
            ->stripTags($this->config->marketresearch->editor->create['id'], $this->config->allowedTags)
            ->get();

        $this->lang->project->name = $this->lang->marketresearch->name;

        $this->dao->insert(TABLE_MARKETRESEARCH)->data($research)
            ->batchCheck($this->config->marketresearch->create->requiredFields, 'notempty')
            ->checkIF($research->end != '', 'end', 'ge', $research->begin)
            ->checkFlow()
            ->exec();

        if(!dao::isError())
        {
            $researchID = $this->dao->lastInsertID();
            $this->loadModel('program')->setTreePath($researchID);

            /* Set team of research. */
            $members = array_unique(array($research->openedBy, $research->PM));
            $roles   = $this->loadModel('user')->getUserRoles(array_values($members));

            $teamMembers = array();
            foreach($members as $account)
            {
                if(empty($account)) continue;

                $member = new stdClass();
                $member->root    = $researchID;
                $member->type    = 'project';
                $member->account = $account;
                $member->role    = zget($roles, $account, '');
                $member->join    = helper::today();
                $member->days    = zget($research, 'days', 0);
                $member->hours   = $this->config->execution->defaultWorkhours;
                $this->dao->insert(TABLE_TEAM)->data($member)->exec();
                $teamMembers[$account] = $member;
            }
            $this->execution->addProjectMembers($researchID, $teamMembers);

            if($research->acl != 'open' && isset($_POST['whitelist']))
            {
                $whitelist = $_POST['whitelist'];
                $this->loadModel('personnel')->updateWhitelist($whitelist, 'project', $researchID);
                $this->loadModel('user')->updateUserView($researchID, 'project');
            }

            if($marketName)
            {
                $marketID = $this->loadModel('market')->createMarketByName($marketName);
                if(!dao::isError())
                {
                    $this->loadModel('action')->create('market', $marketID, 'created');
                    $this->dao->update(TABLE_MARKETRESEARCH)
                        ->set('market')->eq($marketID)
                        ->where('id')->eq($researchID)
                        ->exec();
                }
            }

            return $researchID;
        }

        return false;
    }

    /**
     * Update a market research.
     *
     * @param  object  $oldResearch
     * @access public
     * @return array
     */
    public function update($oldResearch)
    {
        $research = fixer::input('post')
            ->add('id', $oldResearch->id)
            ->callFunc('name', 'trim')
            ->setDefault('team', $this->post->name)
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', helper::now())
            ->setIF($this->post->delta == 999, 'end', LONG_TIME)
            ->setIF($this->post->delta == 999, 'days', 0)
            ->setIF($this->post->begin == '0000-00-00', 'begin', '')
            ->setIF($this->post->end   == '0000-00-00', 'end', '')
            ->setIF(!isset($_POST['whitelist']), 'whitelist', '')
            ->join('whitelist', ',')
            ->stripTags($this->config->marketresearch->editor->edit['id'], $this->config->allowedTags)
            ->remove('delta,contactListMenu')
            ->get();

        $research = $this->loadModel('file')->processImgURL($research, $this->config->marketresearch->editor->edit['id'], $this->post->uid);

        $requiredFields = $this->config->marketresearch->edit->requiredFields;
        if($this->post->delta == 999) $requiredFields = trim(str_replace(',end,', ',', ",{$requiredFields},"), ',');

        $executionsCount = $this->dao->select('COUNT(*) as count')->from(TABLE_PROJECT)->where('project')->eq($research->id)->andWhere('deleted')->eq('0')->fetch('count');

        if(!empty($executionsCount))
        {
            $minExecutionBegin = $this->dao->select('`begin` as minBegin')->from(TABLE_PROJECT)->where('project')->eq($research->id)->andWhere('deleted')->eq('0')->orderBy('begin_asc')->fetch();
            $maxExecutionEnd   = $this->dao->select('`end` as maxEnd')->from(TABLE_PROJECT)->where('project')->eq($research->id)->andWhere('deleted')->eq('0')->orderBy('end_desc')->fetch();
            if($minExecutionBegin and $research->begin > $minExecutionBegin->minBegin) dao::$errors['begin'] = sprintf($this->lang->project->begigLetterExecution, $minExecutionBegin->minBegin);
            if($maxExecutionEnd and $research->end < $maxExecutionEnd->maxEnd) dao::$errors['end'] = sprintf($this->lang->project->endGreateExecution, $maxExecutionEnd->maxEnd);
            if(dao::isError()) return false;
        }

        /* Judge workdays is legitimate. */
        $workdays = helper::diffDate($research->end, $research->begin) + 1;
        if(isset($research->days) and $research->days > $workdays)
        {
            dao::$errors['days'] = sprintf($this->lang->project->workdaysExceed, $workdays);
            return false;
        }

        if(!isset($research->days)) $research->days = '0';

        $this->dao->update(TABLE_MARKETRESEARCH)->data($research)
            ->autoCheck($skipFields = 'begin,end')
            ->batchcheck($requiredFields, 'notempty')
            ->checkIF($research->begin != '', 'begin', 'date')
            ->checkIF($research->end != '', 'end', 'date')
            ->checkIF($research->end != '', 'end', 'gt', $research->begin)
            ->checkFlow()
            ->where('id')->eq($oldResearch->id)
            ->exec();
        if(dao::isError()) return false;

        $this->file->updateObjectID($this->post->uid, $research->id, 'project');

        /* Add PM to team. */
        $this->loadModel('execution');
        $members = array($research->PM);
        $roles   = $this->loadModel('user')->getUserRoles(array_values($members));

        $teamMembers = array();
        foreach($members as $account)
        {
            if(empty($account)) continue;

            $member = new stdClass();
            $member->root    = $research->id;
            $member->type    = 'project';
            $member->account = $account;
            $member->role    = zget($roles, $account, '');
            $member->join    = helper::today();
            $member->days    = zget($research, 'days', 0);
            $member->hours   = $this->config->execution->defaultWorkhours;
            $this->dao->replace(TABLE_TEAM)->data($member)->exec();
            $teamMembers[$account] = $member;
        }
        if(!empty($members)) $this->execution->addProjectMembers($research->id, $teamMembers);

        if($research->acl != 'open')
        {
            $whitelist = explode(',', $research->whitelist);
            $this->loadModel('user')->updateUserView($research, 'project');
            $this->loadModel('personnel')->updateWhitelist($whitelist, 'project', $research->id);
        }

        return common::createChanges($oldResearch, $research);
    }

    /**
     * Close research.
     *
     * @param  int    $researchID
     * @access public
     * @return array
     */
    public function close($researchID)
    {
        $oldResearch = $this->loadModel('project')->getByID($researchID);
        $now         = helper::now();

        $editorIdList = $this->config->marketresearch->editor->close['id'];

        $research = fixer::input('post')
            ->add('id', $researchID)
            ->setDefault('status', 'closed')
            ->setDefault('closedBy', $this->app->user->account)
            ->setDefault('closedDate', $now)
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->stripTags($editorIdList, $this->config->allowedTags)
            ->remove('comment')
            ->get();

        $this->lang->error->ge = $this->lang->project->ge;

        $research = $this->loadModel('file')->processImgURL($research, $editorIdList, $this->post->uid);

        $this->dao->update(TABLE_MARKETRESEARCH)->data($research)
            ->autoCheck()
            ->batchcheck($this->config->marketresearch->close->requiredFields, 'notempty')
            ->checkIF($research->realEnd != '', 'realEnd', 'le', helper::today())
            ->checkIF($research->realEnd != '', 'realEnd', 'ge', $oldResearch->realBegan)
            ->checkFlow()
            ->where('id')->eq((int)$researchID)
            ->exec();

        if(dao::isError())
        {
           if(count(dao::$errors['realEnd']) > 1) dao::$errors['realEnd'] = dao::$errors['realEnd'][0];
           return false;
        }
        return common::createChanges($oldResearch, $research);
    }

    /**
     * Activate stage.
     *
     * @param  int    $stageID
     * @access public
     * @return false|array
     */
    public function activateStage($stageID)
    {
        $oldStage = $this->getById($stageID);
        $now      = helper::now();

        $stage = fixer::input('post')
            ->add('id', $stageID)
            ->setDefault('realEnd', '')
            ->setDefault('status', 'doing')
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setDefault('closedBy', '')
            ->setDefault('closedDate', '')
            ->stripTags($this->config->execution->editor->activate['id'], $this->config->allowedTags)
            ->remove('comment,readjustTime,readjustTask')
            ->get();

        if(empty($oldStage->totalConsumed) and helper::isZeroDate($oldStage->realBegan)) $stage->status = 'wait';

        if(!$this->post->readjustTime)
        {
            unset($stage->begin);
            unset($stage->end);
        }

        if($this->post->readjustTime)
        {
            $begin = $stage->begin;
            $end   = $stage->end;

            if($begin > $end) dao::$errors["message"][] = sprintf($this->lang->execution->errorLetterPlan, $end, $begin);

            if($oldStage->grade > 1)
            {
                $parent      = $this->dao->select('begin,end')->from(TABLE_PROJECT)->where('id')->eq($oldStage->parent)->fetch();
                $parentBegin = $parent->begin;
                $parentEnd   = $parent->end;
                if($begin < $parentBegin)
                {
                    dao::$errors["message"][] = sprintf($this->lang->execution->errorLetterParent, $parentBegin);
                }

                if($end > $parentEnd)
                {
                    dao::$errors["message"][] = sprintf($this->lang->execution->errorGreaterParent, $parentEnd);
                }
            }
        }

        if(dao::isError()) return false;

        $stage = $this->loadModel('file')->processImgURL($stage, $this->config->execution->editor->activate['id'], $this->post->uid);
        $this->dao->update(TABLE_EXECUTION)->data($stage)
            ->autoCheck()
            ->checkFlow()
            ->where('id')->eq((int)$stageID)
            ->exec();

        /* Readjust task. */
        if($this->post->readjustTime and $this->post->readjustTask)
        {
            $this->readjustTask($oldStage, $stage, 'execution');
        }

        $changes = common::createChanges($oldStage, $stage);
        if($this->post->comment != '' or !empty($changes))
        {
            $this->loadModel('action');
            $actionID = $this->action->create('researchstage', $stageID, 'Activated', $this->post->comment);
            $this->action->logHistory($actionID, $changes);
        }

        return $changes;
    }

    /**
     * Close stage.
     *
     * @param  int    $stageID
     * @access public
     * @return array
     */
    public function closeStage($stageID)
    {
        $this->app->loadLang('project');
        $oldStage = $this->loadModel('execution')->getById($stageID);
        $now      = helper::now();

        $stage = fixer::input('post')
            ->add('id', $stageID)
            ->setDefault('status', 'closed')
            ->setDefault('closedBy', $this->app->user->account)
            ->setDefault('closedDate', $now)
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->stripTags($this->config->execution->editor->close['id'], $this->config->allowedTags)
            ->remove('comment')
            ->get();

        $this->lang->error->ge = $this->lang->execution->ge;

        $stage = $this->loadModel('file')->processImgURL($stage, $this->config->execution->editor->close['id'], $this->post->uid);
        $this->dao->update(TABLE_MARKETRESEARCH)->data($stage)
            ->autoCheck()
            ->check($this->config->execution->close->requiredFields,'notempty')
            ->checkIF($stage->realEnd != '', 'realEnd', 'le', helper::today())
            ->checkIF($stage->realEnd != '', 'realEnd', 'ge', $oldStage->realBegan)
            ->checkFlow()
            ->where('id')->eq((int)$stageID)
            ->exec();

        if(!dao::isError())
        {
            $changes = common::createChanges($oldStage, $stage);
            if($this->post->comment != '' or !empty($changes))
            {
                $actionID = $this->loadModel('action')->create('researchstage', $stageID, 'Closed', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }
            return $changes;
        }
    }

    /**
     * Activate research.
     *
     * @param  int    $researchID
     * @access public
     * @return array
     */
    public function activate($researchID)
    {
        $oldResearch = $this->loadModel('project')->getById($researchID);
        $now         = helper::now();

        $editorIdList = $this->config->marketresearch->editor->activate['id'];

        $research = fixer::input('post')
            ->add('id', $researchID)
            ->setDefault('realEnd','')
            ->setDefault('status', 'doing')
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setDefault('closedReason', '')
            ->setIF(!helper::isZeroDate($oldResearch->realBegan), 'realBegan', helper::today())
            ->stripTags($editorIdList, $this->config->allowedTags)
            ->remove('comment,readjustTime,readjustTask')
            ->get();

        if(!$this->post->readjustTime)
        {
            unset($research->begin);
            unset($research->end);
        }

        $research = $this->loadModel('file')->processImgURL($research, $editorIdList, $this->post->uid);
        $this->dao->update(TABLE_MARKETRESEARCH)->data($research)
            ->autoCheck()
            ->checkFlow()
            ->where('id')->eq((int)$researchID)
            ->exec();

        if(dao::isError()) return false;

        /* Readjust task. */
        if($this->post->readjustTime and $this->post->readjustTask)
        {
            $this->readjustTask($oldResearch, $research, 'project');
        }

        return common::createChanges($oldResearch, $research);
    }

    /**
     * Readjust task.
     *
     * @param  object $oldProject
     * @param  object $project
     * @param  string $type project|execution
     * @access public
     * @return void
     */
    public function readjustTask($oldProject, $project, $type)
    {
        $beginTimeStamp = strtotime($project->begin);
        $tasks = $this->dao->select('id,estStarted,deadline,status')->from(TABLE_TASK)
            ->where('deadline')->notZeroDate()
            ->andWhere('status')->in('wait,doing')
            ->beginIF($type == 'project')->andWhere('project')->eq($project->id)->fi()
            ->beginIF($type == 'execution')->andWhere('execution')->eq($project->id)->fi()
            ->fetchAll();
        foreach($tasks as $task)
        {
            if($task->status == 'wait' and !helper::isZeroDate($task->estStarted))
            {
                $taskDays   = helper::diffDate($task->deadline, $task->estStarted);
                $taskOffset = helper::diffDate($task->estStarted, $oldProject->begin);

                $estStartedTimeStamp = $beginTimeStamp + $taskOffset * 24 * 3600;
                $estStarted = date('Y-m-d', $estStartedTimeStamp);
                $deadline   = date('Y-m-d', $estStartedTimeStamp + $taskDays * 24 * 3600);

                if($estStarted > $project->end) $estStarted = $project->end;
                if($deadline > $project->end)   $deadline   = $project->end;
                $this->dao->update(TABLE_TASK)->set('estStarted')->eq($estStarted)->set('deadline')->eq($deadline)->where('id')->eq($task->id)->exec();
            }
            else
            {
                $taskOffset = helper::diffDate($task->deadline, $oldProject->begin);
                $deadline   = date('Y-m-d', $beginTimeStamp + $taskOffset * 24 * 3600);

                if($deadline > $project->end) $deadline = $project->end;
                $this->dao->update(TABLE_TASK)->set('deadline')->eq($deadline)->where('id')->eq($task->id)->exec();
            }
        }
    }

    /**
     * Get market research by id.
     *
     * @param  int    $researchID
     * @access public
     * @return object
     */
    public function getById($researchID)
    {
        $research = $this->dao->select('*')->from(TABLE_MARKETRESEARCH)
            ->where('id')->eq($researchID)
            ->fetch();

        if(!$research) return false;

        if(helper::isZeroDate($research->end)) $research->end = '';
        $research = $this->loadModel('file')->replaceImgURL($research, 'desc');
        return $research;
    }

    /**
     * Get market research list.
     *
     * @param  int    $marketID
     * @param  string $status     all|doing|closed
     * @param  string $orderBy
     * @param  int    $involved
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getList($marketID = 0, $status = 'doing', $orderBy = 'id_desc', $involved = 0, $pager = null)
    {
        $stmt = $this->dao->select('DISTINCT t1.*')->from(TABLE_MARKETRESEARCH)->alias('t1')
        ->leftJoin(TABLE_MARKET)->alias('t2')->on('t1.market=t2.id');

        if($this->cookie->involvedResearch || $involved) $stmt->leftJoin(TABLE_TEAM)->alias('t3')->on('t1.id=t3.root');

        $stmt->where('t1.`type`')->eq('project')
            ->andWhere('vision')->eq('or')
            ->andWhere('model')->eq('research')
            ->andWhere('t1.deleted')->eq(0)
            ->beginIF(!$this->app->user->admin)->andWhere('t1.id')->in($this->app->user->view->projects)->fi()
            ->beginIF($marketID)->andWhere('market')->eq($marketID)->fi()
            ->beginIF(!$marketID)->andWhere('t2.deleted')->eq(0)->fi()
            ->beginIF($status != 'all')->andWhere('status')->eq($status)->fi();

        if($this->cookie->involvedResearch || $involved)
        {
            $stmt->andWhere('(t3.type', true)->eq('project')
                ->andWhere('t3.account')->eq($this->app->user->account)
                ->markRight(1)
                ->orWhere('t1.openedBy')->eq($this->app->user->account)
                ->orWhere('t1.PM')->eq($this->app->user->account)
                ->orWhere("CONCAT(',', t1.whitelist, ',')")->like("%,{$this->app->user->account},%")
                ->markRight(1);
        }

        return $stmt->orderBy($orderBy)->page($pager, 't1.id')->fetchAll('id');
    }

    /**
     * Get pairs.
     *
     * @access public
     * @param  int    $marketID
     * @return array
     */
    public function getPairs($marketID = 0)
    {
        return $this->dao->select('id,name')->from(TABLE_MARKETRESEARCH)
            ->where('deleted')->eq(0)
            ->andWhere('model')->eq('research')
            ->andWhere('type')->eq('project')
            ->andWhere('vision')->eq('or')
            ->beginIF(!empty($marketID))->andWhere('market')->eq($marketID)
            ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->projects)->fi()
            ->orderBy('id_desc')
            ->fetchPairs();
    }

    /**
     * Print cell data.
     *
     * @param object $col
     * @param object $research
     * @param array  $users
     * @param array  $markets
     *
     * @access public
     * @return void
     */
    public function printCell($col, $research, $users, $markets)
    {
        $canView      = common::hasPriv('marketresearch', 'stage');
        $researchLink = helper::createLink('marketresearch', 'stage', "id=$research->id&browseType=unclosed&param=0&orderBy=id_desc&recTotal=0&recPerPage=100&pageID=1");
        $id           = $col->id;
        if($col->show)
        {
            $class = "c-{$id}";
            if($id == 'status') $class .= ' c-status';
            if($id == 'name' || $id == 'market') $class .= ' text-left c-name';

            $title = '';
            if($id == 'name') $title = " title='{$research->name}'";
            if($id == 'market') $title = " title='" . zget($markets, $research->market, '') . "'";
            if($id == 'openedBy') $title = " title='" . zget($users, $research->openedBy) . "'";

            echo "<td class='" . $class . "'" . $title . ">";
            if($this->config->edition != 'open') $this->loadModel('flow')->printFlowCell('marketresearch', $research, $id);
            switch($id)
            {
            case 'id':
                printf('%03d', $research->id);
                break;
            case 'name':
                echo $canView ? html::a($researchLink, trim($research->name), '', "title='$research->name'") : "<span>$research->name</span>";
                break;
            case 'status':
                echo "<span class='status-{$research->status}'>" . $this->processStatus('marketresearch', $research) . "</span>";
                break;
            case 'market':
                echo zget($markets, $research->market, '');
                break;
            case 'PM':
                echo zget($users, $research->PM);
                break;
            case 'begin':
                echo helper::isZeroDate($research->begin) ? '' : $research->begin;
                break;
            case 'end':
                echo helper::isZeroDate($research->end) ? '' : $research->end;
                break;
            case 'realBegan':
                echo helper::isZeroDate($research->realBegan) ? '' : $research->realBegan;
                break;
            case 'realEnd':
                echo helper::isZeroDate($research->realEnd) ? '' : $research->realEnd;
                break;
            case 'openedBy':
                echo zget($users, $research->openedBy);
                break;
            case 'progress':
                echo html::ring($research->progress);
                break;
            case 'actions':
                echo $this->buildOperateMenu($research, 'browse');
                break;
            }
            echo '</td>';
        }
    }

    /**
     * Build operate menu.
     *
     * @param  object $research
     * @param  string $type
     * @access public
     * @return string
     */
    public function buildOperateMenu($research, $type = 'view')
    {
        $menu = '';

        if($research->deleted) return $menu;

        if($type == 'view')
        {
            $startClass = $research->status == 'doing' ? 'hidden' : '';
            if($research->status == 'wait' || $research->status == 'doing') $menu .= $this->buildMenu('marketresearch', 'start', "researchID=$research->id&browseType=unclosed&param=0&orderBy=id_desc&recTotal=0&recPerPage=100&pageID=1", $research, 'view', 'play', '', $startClass . ' iframe', true, '', $this->lang->marketresearch->start);

            if($research->status == 'closed') $menu .= $this->buildMenu('marketresearch', 'activate', "researchID=$research->id", $research, 'view', 'magic', '', 'iframe', true, '', $this->lang->marketresearch->activate);
            if($research->status != 'closed') $menu .= $this->buildMenu('marketresearch', 'close', "researchID=$research->id", $research, 'view', 'off', '', 'iframe', true, '', $this->lang->marketresearch->close);
            $menu .= $this->buildMenu('marketresearch', 'edit', "researchID=$research->id", $research, 'browse');
        }

        if($type == 'browse')
        {
            $startClass  = $research->status == 'doing' ? ($type == 'view' ? 'hidden' : 'disabled') : '';
            if($research->status == 'wait' || $research->status == 'doing') $menu .= $this->buildMenu('marketresearch', 'start', "researchID=$research->id", $research, 'browse', 'play', '', $startClass . ' iframe', true, '', $this->lang->marketresearch->start);
            if($research->status == 'closed') $menu .= $this->buildMenu('marketresearch', 'activate', "researchID=$research->id", $research, 'browse', 'magic', '', 'iframe', true, '', $this->lang->marketresearch->activate);

            $closeClass = $research->status == 'doing' ? '' : 'disabled';
            $menu .= $this->buildMenu('marketresearch', 'close', "researchID=$research->id", $research, 'browse', 'off', '', $closeClass . ' iframe', true);

            $menu .= $this->buildMenu('marketresearch', 'edit', "researchID=$research->id", $research, 'browse');
            $menu .= $this->buildMenu('marketresearch', 'team', "researchID=$research->id", $research, 'browse', 'group');

            $reportClass = $research->status == 'wait' ? 'disabled' : '';
            $menu .= $this->buildMenu('marketresearch', 'reports', "researchID=$research->id", $research, 'browse', 'list-alt', '', $reportClass);
        }

        $menu .= $this->buildMenu('marketresearch', 'delete', "researchID=$research->id", $research, 'browse', 'trash', 'hiddenwin');

        return $menu;
    }

    /*
     * Print stage nested list.
     *
     * @param mixed   $stage
     * @param mixed   $isChild
     * @param mixed   $users
     * @param string  $research
     * @access public
     * @return void
     */
    public function printNestedList($stage, $isChild, $users, $research = '')
    {
        $this->loadModel('task');
        $this->loadModel('execution');
        $this->loadModel('programplan');

        $today = helper::today();

        if(!$isChild)
        {
            $trClass = 'is-top-level table-nest-child-hide';
            $trAttrs = "data-id='$stage->id' data-order='$stage->order' data-nested='true' data-status={$stage->status}";
        }
        else
        {
            if(strpos($stage->path, ",$stage->project,") !== false)
            {
                $path = explode(',', trim($stage->path, ','));
                $path = array_slice($path, array_search($stage->project, $path) + 1);
                $path = implode(',', $path);
            }

            $trClass  = 'table-nest-hide';
            $trAttrs  = "data-id={$stage->id} data-parent={$stage->parent} data-status={$stage->status}";
            $trAttrs .= " data-nest-parent='$stage->parent' data-order='$stage->order' data-nest-path='$path'";
        }

        echo "<tr $trAttrs data-type='stage' class='$trClass'>";
        echo "<td class='c-name text-left flex sort-handler'>";
        echo "<span id=$stage->id class='table-nest-icon icon table-nest-toggle'></span>";
        echo "<span class='project-type-label label label-outline label-warning'>{$this->lang->execution->typeList[$stage->type]}</span> ";
        echo "<span class='text-ellipsis' title='$stage->name'>" . $stage->name . '</span>';

        if(!helper::isZeroDate($stage->end))
        {
            if($stage->status != 'closed')
            {
                echo strtotime($today) > strtotime($stage->end) ? '<span class="label label-danger label-badge">' . $this->lang->execution->delayed . '</span>' : '';
            }
        }
        echo "</td>";
        echo "<td class='status-{$stage->status} text-center'>" . zget($this->lang->project->statusList, $stage->status) . '</td>';
        echo "<td class='c-pm'>" . zget($users, $stage->PM) . '</td>';
        echo helper::isZeroDate($stage->begin) ? '<td class="c-date"></td>' : '<td class="c-date">' . $stage->begin . '</td>';
        echo helper::isZeroDate($stage->end) ? '<td class="endDate c-date"></td>' : '<td class="endDate c-date">' . $stage->end . '</td>';
        echo "<td class='hours text-right' title='{$stage->estimate}{$this->lang->execution->workHour}'>" . $stage->estimate . $this->lang->execution->workHourUnit . '</td>';
        echo "<td class='hours text-right' title='{$stage->consumed}{$this->lang->execution->workHour}'>" . $stage->consumed . $this->lang->execution->workHourUnit . '</td>';
        echo "<td class='hours text-right' title='{$stage->left}{$this->lang->execution->workHour}'>" . $stage->left . $this->lang->execution->workHourUnit . '</td>';
        echo '<td>' . html::ring($stage->progress) . '</td>';
        echo '<td class="c-actions text-left">';

        $title = '';
        $disabled = '';
        $this->app->loadLang('stage');
        common::printIcon('marketresearch', 'startStage', "stageID={$stage->id}", $stage, 'list', 'play', '', 'iframe', true, $disabled, $title);

        $class = !empty($stage->children) ? 'disabled' : '';
        common::printIcon('marketresearch', 'createTask', "researchID={$stage->project}&stageID={$stage->id}&taskID=0", '', 'list', 'plus', '', $class, false);

        $isCreateTask = $this->loadModel('programplan')->isCreateTask($stage->id);
        $disabled     = ($isCreateTask and $stage->type == 'stage') ? '' : ' disabled';
        $title        = !$isCreateTask ? $this->lang->programplan->error->createdTask : $this->lang->programplan->createSubPlan;
        $title        = (!empty($disabled) and $stage->type != 'stage') ? $this->lang->programplan->error->notStage : $title;
        common::printIcon('marketresearch', 'batchStage', "researchID={$stage->project}&stageID=$stage->id", $stage, 'list', 'split', '', $disabled, '', '', $title);
        common::printIcon('marketresearch', 'editStage', "stageID=$stage->id&projectID=$stage->project", $stage, 'list', 'edit', '', 'iframe', true);

        $disabled = !empty($stage->children) ? ' disabled' : '';
        if($stage->status != 'closed')
        {
            common::printIcon('marketresearch', 'closeStage', "stageID=$stage->id", $stage, 'list', 'off', 'hiddenwin' , $disabled . ' iframe', true, '', $this->lang->marketresearch->closeStage);
        }
        elseif($stage->status == 'closed')
        {
            common::printIcon('marketresearch', 'activateStage', "stageID=$stage->id", $stage, 'list', 'magic', 'hiddenwin' , $disabled . ' iframe', true, '', $this->lang->execution->activate);
        }

        common::printIcon('marketresearch', 'deleteStage', "stageID=$stage->id&confirm=no", $stage, 'list', 'trash', 'hiddenwin' , $disabled, '', '', $this->lang->delete);
        echo '</td>';
        echo '</tr>';

        if(!empty($stage->children))
        {
            foreach($stage->children as $child)
            {
                $child->division = $stage->division;
                $this->printNestedList($child, true, $users, $research);
            }
        }

        if(!empty($stage->tasks))
        {
            foreach($stage->tasks as $task)
            {
                $showmore = (count($stage->tasks) == 50) && ($task == end($stage->tasks));
                echo $this->buildTaskNestedList($stage, $task, false, $showmore, $users);
            }
        }
    }

    /**
     * buildTaskNestedList
     *
     * @param  mixed  $stage
     * @param  mixed  $task
     * @param  mixed  $isChild
     * @param  mixed  $showmore
     * @param  array  $users
     * @access public
     * @return void
     */
    public function buildTaskNestedList($stage, $task, $isChild = false, $showmore = false, $users = array())
    {
        $this->loadModel('task');
        $this->app->loadLang('execution');

        $today    = helper::today();
        $showmore = $showmore ? 'showmore' : '';
        $trAttrs  = "data-id='t$task->id' data-type='task'";

        /* Remove projectID in stage path. */
        $stagePath = $stage->path;
        $stagePath = trim($stagePath, ',');
        $stagePath = substr($stagePath, strpos($stagePath, ',') + 1);

        if(!$isChild)
        {
            $path     = ",{$stagePath},t{$task->id},";
            $trAttrs .= " data-parent='$stage->id' data-nest-parent='$stage->id' data-nest-path='$path'";
            if(empty($task->children)) $trAttrs .= " data-nested='false'";
            $trClass  = empty($task->children) ? '' : " has-nest-child";
        }
        else
        {
            $path     = ",{$stagePath},{$task->parent},t{$task->id},";
            $trClass  = 'is-nest-child no-nest';
            $trAttrs .= " data-nested='false' data-parent='t$task->parent' data-nest-parent='t$task->parent' data-nest-path='$path'";
        }

        $taskPri  = zget($this->lang->task->priList, $task->pri);

        $list  = "<tr $trAttrs class='$trClass $showmore'>";
        $list .= '<td>';
        $list .= "<span class='label-pri label-pri-$task->pri' title='$taskPri'>$taskPri</span> ";
        if($task->parent > 0) $list .= '<span class="label label-badge label-light" title="' . $this->lang->task->children . '">' . $this->lang->task->childrenAB . '</span> ';
        $list .= common::hasPriv('marketresearch', 'viewTask') ? html::a(helper::createLink('marketresearch', 'viewtask', "id=$task->id"), $task->name, '', "style='color: $task->color'", "data-app='project'") : "<span style='color:$task->color'>$task->name</span>";
        if(!helper::isZeroDate($task->deadline))
        {
            if($task->status != 'done')
            {
                $list .= strtotime($today) > strtotime($task->deadline) ? '<span class="label label-danger label-badge">' . $this->lang->task->delayed . '</span>' : '';
            }
        }
        $list .= '</td>';
        $list .= "<td class='status-{$task->status} text-center'>" . $this->processStatus('task', $task) . '</td>';
        $list .= '<td>' . $this->getAssignToHtml($task, $users) . '</td>';
        $list .= helper::isZeroDate($task->estStarted) ? '<td class="c-date"></td>' : '<td class="c-date">' . $task->estStarted . '</td>';
        $list .= helper::isZeroDate($task->deadline) ? '<td class="c-date"></td>' : '<td class="c-date">' . $task->deadline . '</td>';
        $list .= '<td class="hours text-right">' . $task->estimate . $this->lang->execution->workHourUnit . '</td>';
        $list .= '<td class="hours text-right">' . $task->consumed . $this->lang->execution->workHourUnit . '</td>';
        $list .= '<td class="hours text-right">' . $task->left . $this->lang->execution->workHourUnit . '</td>';
        $list .= '<td></td>';
        $list .= '<td class="c-actions">';
        $list .= $this->buildResearchTaskBrowseMenu($task);
        $list .= '</td></tr>';

        if(!empty($task->children))
        {
            foreach($task->children as $child)
            {
                $showmore = (count($task->children) == 50) && ($child == end($task->children));
                $list .= $this->buildTaskNestedList($stage, $child, true, $showmore, $users);
            }
        }

        return $list;
    }

    /**
     * Get assignTo html.
     *
     * @param  object $task
     * @param  array  $users
     * @access public
     * @return string
     */
    public function getAssignToHtml($task, $users)
    {
        $this->app->loadLang('task');
        $assignedToText = $assignedToTitle = zget($users, $task->assignedTo);
        if(empty($task->assignedTo))
        {
            $btnClass       = $btnTextClass = 'assigned-none';
            $assignedToText = $this->lang->task->noAssigned;
        }
        if($task->assignedTo == $this->app->user->account) $btnClass = $btnTextClass = 'assigned-current';
        if(!empty($task->assignedTo) and $task->assignedTo != $this->app->user->account) $btnClass = $btnTextClass = 'assigned-other';
        $btnClass    .= $task->assignedTo == 'closed' ? ' disabled' : '';
        $btnClass    .= ' iframe btn btn-icon-left btn-sm';
        $assignToLink = $task->assignedTo == 'closed' ? '#' : helper::createLink('marketresearch', 'taskAssignTo', "executionID=$task->project&taskID=$task->id", '', true);
        $assignToHtml = html::a($assignToLink, "<i class='icon icon-hand-right'></i> <span title='" . $assignedToTitle . "'>{$assignedToText}</span>", '', "class='$btnClass'");

        return $assignToHtml;
    }


    /**
     * Get stat data.
     *
     * @param  int    $researchID
     * @param  array  $stageTasks
     * @param  string $orderBy
     * @param  mixed  $pager
     * @access public
     * @return void
     */
    public function getStatData($researchID = 0, $stageTasks = array(), $orderBy = 'id_asc', $pager = null)
    {
        if(defined('TUTORIAL')) return $this->loadModel('tutorial')->getExecutionStats($browseType);
        $stages = $this->dao->select('*')->from(TABLE_EXECUTION)
            ->where('type')->in('stage')
            ->andWhere('deleted')->eq('0')
            ->andWhere('vision')->eq($this->config->vision)
            ->andWhere('parent')->eq($researchID)
            ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->sprints)->fi()
            ->beginIF($researchID)->andWhere('project')->eq($researchID)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        /* Build stage tree. */
        $childList = $this->dao->select('*')->from(TABLE_EXECUTION)
            ->where('type')->in('stage')
            ->andWhere('deleted')->eq('0')
            ->andWhere('project')->eq($researchID)
            ->orderBy($orderBy)
            ->fetchAll('id');

        foreach($childList as $child)
        {
            if($stageTasks and isset($stageTasks[$child->id]))
            {
                $tasks = array_chunk($stageTasks[$child->id], $this->config->task->defaultLoadCount, true);
                $child->tasks = $tasks[0];
            }
            if($child->parent != $researchID) $childList[$child->parent]->children[$child->id] = $child;
        }

        $today = helper::today();
        foreach($stages as $key => $stage)
        {
            if(isset($childList[$stage->id])) $stages[$key] = $childList[$stage->id];

            /* Process the end time. */
            $stage->end = date(DT_DATE1, strtotime($stage->end));

            /* Judge whether the stage is delayed. */
            if($stage->status != 'done' and $stage->status != 'closed' and $stage->status != 'suspended')
            {
                $delay = helper::diffDate($today, $stage->end);
                if($delay > 0) $stage->delay = $delay;
            }

            if($stageTasks and isset($stageTasks[$stage->id]))
            {
                $tasks = array_chunk($stageTasks[$stage->id], $this->config->task->defaultLoadCount, true);
                $stage->tasks = $tasks[0];
            }
        }

        return array_values($stages);
    }

    /**
     * Build task search form.
     *
     * @param  int    $executionID
     * @param  int    $queryID
     * @param  string $actionURL
     * @access public
     * @return void
     */
    public function buildTaskSearchForm($executionID, $queryID, $actionURL)
    {
        $this->loadModel('execution');
        $this->config->execution->search['actionURL'] = $actionURL;
        $this->config->execution->search['queryID']   = $queryID;
        unset($this->config->execution->search['fields']['module']);
        unset($this->config->execution->search['fields']['execution']);
        unset($this->config->execution->search['fields']['fromBug']);
        unset($this->config->execution->search['fields']['closedReason']);

        $this->loadModel('search')->setSearchParams($this->config->execution->search);
    }

    public function getTasks($executionID, $browseType, $queryID, $sort = 'id_desc', $pager = null)
    {
        $this->loadModel('task');
        $this->loadModel('execution');

        /* Get tasks. */
        $tasks = array();
        if($browseType != "bysearch")
        {
            $queryStatus = $browseType == 'byexecution' ? 'all' : $browseType;
            if($queryStatus == 'unclosed')
            {
                $queryStatus = $this->lang->task->statusList;
                unset($queryStatus['closed']);
                $queryStatus = array_keys($queryStatus);
            }
            $tasks = $this->task->getResearchTasks($executionID, $queryStatus, $sort, $pager);
        }
        else
        {
            if($queryID)
            {
                $query = $this->loadModel('search')->getQuery($queryID);
                if($query)
                {
                    $this->session->set('taskQuery', $query->sql);
                    $this->session->set('taskForm', $query->form);
                }
                else
                {
                    $this->session->set('taskQuery', ' 1 = 1');
                }
            }
            else
            {
                if($this->session->taskQuery == false) $this->session->set('taskQuery', ' 1 = 1');
            }

            if(strpos($this->session->taskQuery, "deleted =") === false) $this->session->set('taskQuery', $this->session->taskQuery . " AND deleted = '0'");

            $taskQuery = $this->session->taskQuery;
            $this->session->set('taskQueryCondition', $taskQuery, $this->app->tab);
            $this->session->set('taskOnlyCondition', true, $this->app->tab);

            $tasks = $this->execution->getSearchTasks($taskQuery, $pager, $sort);
        }

        return $tasks;
    }

    /**
     * Build task browse action menu.
     *
     * @param  object $task
     * @access public
     * @return string
     */
    public function buildResearchTaskBrowseMenu($task)
    {
        $menu   = '';
        $params = "taskID=$task->id";

        if($task->status != 'pause') $menu .= $this->buildMenu('marketresearch', 'startTask',   $params, $task, 'browse', 'play', '', 'iframe', true);
        if($task->status == 'pause') $menu .= $this->buildMenu('marketresearch', 'restartTask', $params, $task, 'browse', 'play', '', 'iframe', true);
        $menu .= $this->buildMenu('marketresearch', 'finishTask', $params, $task, 'browse', 'checked', '', 'iframe', true);
        $menu .= $this->buildMenu('marketresearch', 'closeTask',  $params, $task, 'browse', 'off', '', 'iframe', true);
        $menu .= $this->buildMenu('marketresearch', 'recordTaskEstimate', $params, $task, 'browse', 'time', '', 'iframe', true);
        $menu .= $this->buildMenu('marketresearch', 'editTask',   $params, $task, 'browse', 'edit');
        $menu .= $this->buildMenu('marketresearch', 'batchCreateTask', "execution=$task->execution&taskID=$task->id", $task, 'browse', 'split', '', '', '', '', $this->lang->task->children);

        return $menu;
    }

    /**
     * Build task view menu.
     *
     * @param  object $task
     * @access public
     * @return string
     */
    public function buildResearchTaskViewMenu($task)
    {
        if($task->deleted) return '';

        $menu   = '';
        $params = "taskID=$task->id";

        $menu .= $this->buildMenu('marketresearch', 'batchCreateTask', "execution=$task->execution&taskID=$task->id", $task, 'view', 'split', '', '', '', "title='{$this->lang->task->children}'", $this->lang->task->children);
        $menu .= $this->buildMenu('marketresearch', 'taskAssignTo', "executionID=$task->execution&taskID=$task->id", $task, 'button', 'hand-right', '', 'iframe', true, '', $this->lang->task->assignTo);
        $menu .= $this->buildMenu('marketresearch', 'startTask',    $params, $task, 'view', 'play', '', 'iframe showinonlybody', true);
        $menu .= $this->buildMenu('marketresearch', 'finishTask',   $params, $task, 'view', 'checked', '', 'iframe showinonlybody text-success', true);
        $menu .= $this->buildMenu('marketresearch', 'activateTask', $params, $task, 'view', 'magic', '', 'iframe showinonlybody text-success', true);
        $menu .= $this->buildMenu('marketresearch', 'recordTaskEstimate', $params, $task, 'view', 'time', '', 'iframe', true);
        $menu .= $this->buildMenu('marketresearch', 'closeTask',    $params, $task, 'view', 'off', '', 'iframe showinonlybody', true);
        $menu .= $this->buildMenu('marketresearch', 'cancelTask',   $params, $task, 'view', 'ban-circle', '', 'iframe showinonlybody', true);

        $menu .= "<div class='divider'></div>";
        $menu .= $this->buildMenu('marketresearch', 'editTask', $params, $task, 'view', 'edit', '', 'showinonlybody', false, "title={$this->lang->task->edit}", ' ');
        $menu .= $this->buildMenu('marketresearch', 'createTask', "researchID={$task->project}&stageID={$task->execution}&taskID=$task->id", $task, 'view', 'copy', '', '', false, "title={$this->lang->task->copy}", ' ');
        $menu .= $this->buildMenu('marketresearch', 'deleteTask', "executionID=$task->execution&taskID=$task->id", $task, 'view', 'trash', 'hiddenwin', '', false, "title={$this->lang->task->delete}", ' ');
        if($task->parent > 0) $menu .= $this->buildMenu('marketresearch', 'viewTask', "taskID=$task->parent", $task, 'view', 'chevron-double-up', '', '', '', '', $this->lang->task->parent);

        return $menu;
    }

    /**
     * Judge an action is clickable or not.
     *
     * @param  object    $task
     * @param  string    $action
     * @access public
     * @return bool
     */
    public static function isClickable($object, $action)
    {
        $action = strtolower($action);

        if($action == 'startstage')    return $object->status == 'wait';
        if($action == 'closestage')    return $object->status != 'closed';
        if($action == 'activatestage') return $object->status == 'suspended' or $object->status == 'closed';

        if($action == 'starttask'          and $object->parent < 0) return false;
        if($action == 'finishtask'         and $object->parent < 0) return false;
        if($action == 'pausetask'          and $object->parent < 0) return false;
        if($action == 'assigntotask'       and $object->parent < 0) return false;
        if($action == 'closetask'          and $object->parent < 0) return false;
        if($action == 'batchcreatetask'    and !empty($obejct->team))     return false;
        if($action == 'batchcreatetask'    and $object->parent > 0)       return false;
        if($action == 'recordtaskestimate' and $object->parent == -1)     return false;
        if($action == 'deletetask'         and $object->parent < 0)       return false;

        if($action == 'starttask')    return $object->status == 'wait';
        if($action == 'restarttask')  return $object->status == 'pause';
        if($action == 'pausetask')    return $object->status == 'doing';
        if($action == 'assigntotask') return $object->status != 'closed' and $object->status != 'cancel';
        if($action == 'closetask')    return $object->status == 'done'   or  $object->status == 'cancel';
        if($action == 'activatetask') return $object->status == 'done'   or  $object->status == 'closed'  or  $object->status == 'cancel';
        if($action == 'finishtask')   return $object->status != 'done'   and $object->status != 'closed'  and $object->status != 'cancel';
        if($action == 'canceltask')   return $object->status != 'done'   and $object->status != 'closed'  and $object->status != 'cancel';

        return true;
    }
}
