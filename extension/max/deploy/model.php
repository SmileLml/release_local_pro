<?php
/**
 * The model file of deploy module of ZenTaoCMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     deploy
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class deployModel extends model
{
    public function getById($deployID)
    {
        $deploy = $this->dao->select('*')->from(TABLE_DEPLOY)->where('id')->eq($deployID)->fetch();
        if(!$deploy) return false;
        $deploy = $this->loadModel('file')->replaceImgURL($deploy, 'desc');
        $deploy->products = $this->dao->select('*')->from(TABLE_DEPLOYPRODUCT)->where('deploy')->eq($deployID)->fetchAll();

        return $deploy;
    }

    public function getHasDeployDate($product = '')
    {
        $begins = $this->dao->select('DISTINCT t1.`begin`')->from(TABLE_DEPLOY)->alias('t1')
            ->leftJoin(TABLE_DEPLOYPRODUCT)->alias('t2')->on('t1.id=t2.deploy')
            ->where('1=1')
            ->beginIF($product)->andWhere('t2.product')->eq($product)->fi()
            ->orderBy('t1.`begin` desc')->fetchAll();

        $dateList = array();
        foreach($begins as $begin)
        {
            $year  = substr($begin->begin, 0, 4);
            $month = substr($begin->begin, 5, 2);
            $dateList[$year][$month] = (int)$month - 1;
        }

        return $dateList;
    }

    public function getList($product, $date)
    {
        return $this->dao->select('DISTINCT t1.*')->from(TABLE_DEPLOY)->alias('t1')
            ->leftJoin(TABLE_DEPLOYPRODUCT)->alias('t2')->on('t1.id=t2.deploy')
            ->where('t1.deleted')->eq(0)
            ->andWhere("(LEFT(t1.`begin`, " . strlen($date) . ") = '$date')")
            ->beginIF($product)->andWhere('t2.product')->eq($product)->fi()
            ->orderBy('t1.begin_asc')
            ->fetchAll();
    }

    public function getStepById($stepID)
    {
        return $this->dao->select('*')->from(TABLE_DEPLOYSTEP)->where('id')->eq($stepID)->fetch();
    }

    public function getScope($deployID)
    {
        return $this->dao->select('*')->from(TABLE_DEPLOYSCOPE)->where('deploy')->eq($deployID)->fetchAll();
    }

    public function getStepStageGroup($deployID, $stage = 'all')
    {
        if($stage != 'all' and !isset($this->lang->deploy->stageList[$stage])) return array();

        return $this->dao->select('*')->from(TABLE_DEPLOYSTEP)
            ->where('deploy')->eq($deployID)
            ->andWhere('deleted')->eq(0)
            ->beginIF($stage != 'all')->andWhere('stage')->eq($stage)->fi()
            ->orderBy('begin')
            ->fetchGroup('stage', 'id');
    }

    public function getMembes($deployID)
    {
        $deploy  = $this->dao->select('*')->from(TABLE_DEPLOY)->where('id')->eq($deployID)->fetch();
        $members = $deploy->owner . ',' . $deploy->createdBy . ',' . $deploy->members;
        $users   = $this->dao->select('*')->from(TABLE_USER)->where('account')->in($members)->fetchAll('account');

        $userPairs = array('' => '');
        foreach($users as $account => $user) $userPairs[$account] = empty($user->realname) ? $account : $user->realname;
        return $userPairs;
    }

    public function getLinkableCases($deploy, $productIdList, $type = 'all', $param = 0, $pager = null)
    {
        if($this->session->testcaseQuery == false) $this->session->set('testcaseQuery', ' 1 = 1');
        $query = $this->session->testcaseQuery;

        $cases = array();
        $linkedCases = $deploy->cases;
        if($type == 'all')     $cases = $this->getAllLinkableCases($productIdList, $query, $linkedCases, $pager);
        if($type == 'bysuite') $cases = $this->getLinkableCasesBySuite($productIdList, $query, $param, $linkedCases, $pager);

        return $cases;
    }

    public function getAllLinkableCases($productIdList, $query, $linkedCases, $pager)
    {
        return $this->dao->select('*')->from(TABLE_CASE)->where($query)
                ->andWhere('id')->notIN($linkedCases)
                ->andWhere('status')->ne('wait')
                ->andWhere('product')->in($productIdList)
                ->andWhere('deleted')->eq(0)
                ->orderBy('id desc')
                ->page($pager)
                ->fetchAll();
    }

    public function getLinkableCasesBySuite($productIdList, $query, $suite, $linkedCases, $pager)
    {
        return $this->dao->select('t1.*,t2.version as version')->from(TABLE_CASE)->alias('t1')
                ->leftJoin(TABLE_SUITECASE)->alias('t2')->on('t1.id=t2.case')
                ->where($query)
                ->andWhere('t2.suite')->eq((int)$suite)
                ->andWhere('t1.status')->ne('wait')
                ->andWhere('t1.product')->in($productIdList)
                ->beginIF($linkedCases)->andWhere('t1.id')->notIN($linkedCases)->fi()
                ->andWhere('t1.deleted')->eq(0)
                ->orderBy('id desc')
                ->page($pager)
                ->fetchAll();
    }

    public function create()
    {
        $data = fixer::input('post')
            ->add('status', 'wait')
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', helper::now())
            ->join('members', ',')
            ->stripTags($this->config->deploy->editor->create['id'], $this->config->allowedTags)
            ->get();
        if(!empty($data->begin) and !empty($data->end) and $data->begin >= $data->end) die(js::alert($this->lang->deploy->errorTime));

        $data = $this->loadModel('file')->processImgURL($data, $this->config->deploy->editor->create['id'], $this->post->uid);

        $this->dao->insert(TABLE_DEPLOY)->data($data, 'product,uid,release,package')->autocheck()->batchCheck($this->config->deploy->create->requiredFields, 'notempty')->exec();
        if(!dao::isError())
        {
            $deployID = $this->dao->lastInsertID();
            $this->file->updateObjectID($this->post->uid, $deployID, 'deploy');

            foreach($data->product as $i => $productID)
            {
                if(empty($productID)) continue;

                $release = $data->release[$i];
                $deployProduct = new stdclass();
                $deployProduct->deploy  = $deployID;
                $deployProduct->product = (int)$productID;
                $deployProduct->release = (int)$data->release[$i];
                $deployProduct->package = $data->package[$i];
                $this->dao->replace(TABLE_DEPLOYPRODUCT)->data($deployProduct)->exec();
            }

            return $deployID;
        }
        return false;
    }

    public function update($deployID)
    {
        $data = fixer::input('post')->join('members', ',')->stripTags($this->config->deploy->editor->edit['id'], $this->config->allowedTags)->remove('comment')->get();
        if(!empty($data->begin) and !empty($data->end) and $data->begin >= $data->end) die(js::alert($this->lang->deploy->errorTime));

        $data = $this->loadModel('file')->processImgURL($data, $this->config->deploy->editor->edit['id'], $this->post->uid);

        $oldDeploy = $this->dao->select('*')->from(TABLE_DEPLOY)->where('id')->eq($deployID)->fetch();
        $oldDeploy->begin   = substr($oldDeploy->begin, 0, 16);
        $oldDeploy->begin   = substr($oldDeploy->begin, 0, 16);
        $oldDeploy->end     = substr($oldDeploy->end, 0, 16);
        $oldDeploy->product = array();
        $oldDeploy->release = array();
        $oldDeploy->package = array();
        if(isset($data->product))
        {
            $deployProducts = $this->dao->select('*')->from(TABLE_DEPLOYPRODUCT)->where('deploy')->eq($deployID)->fetchAll();
            foreach($deployProducts as $deployProduct)
            {
                $oldDeploy->product[] = $deployProduct->product;
                $oldDeploy->release[] = $deployProduct->release;
                $oldDeploy->package[] = $deployProduct->package;
            }
            $oldDeploy->product = join("\n", $oldDeploy->product);
            $oldDeploy->release = join("\n", $oldDeploy->release);
            $oldDeploy->package = join("\n", $oldDeploy->package);
        }

        $this->dao->update(TABLE_DEPLOY)->data($data, 'product,uid,release,package,comment')->autocheck()->batchCheck($this->config->deploy->edit->requiredFields, 'notempty')->where('id')->eq($deployID)->exec();
        if(!dao::isError())
        {
            $this->file->updateObjectID($this->post->uid, $deployID, 'deploy');

            if(isset($data->product))
            {
                $this->dao->delete()->from(TABLE_DEPLOYPRODUCT)->where('deploy')->eq($deployID)->exec();
                $products = $releases = $packages = array();
                foreach($data->product as $i => $productID)
                {
                    if(empty($productID)) continue;

                    $release = $data->release[$i];
                    $deployProduct = new stdclass();
                    $deployProduct->deploy  = $deployID;
                    $deployProduct->product = (int)$productID;
                    $deployProduct->release = (int)$data->release[$i];
                    $deployProduct->package = $data->package[$i];
                    $this->dao->replace(TABLE_DEPLOYPRODUCT)->data($deployProduct)->exec();
                    $products[] = $deployProduct->product;
                    $releases[] = $deployProduct->release;
                    $packages[] = $deployProduct->package;
                }
                $data->product = join("\n", $products);
                $data->release = join("\n", $releases);
                $data->package = join("\n", $packages);
            }
            return common::createChanges($oldDeploy, $data);
        }
        return false;
    }

    public function manageScope($deployID)
    {
        $data = fixer::input('post')->get();
        $now  = helper::now();

        arsort($data->service);
        foreach($data->service as $i => $serviceID)
        {
            $intersect = join(',', array_intersect($data->remove[$i], $data->add[$i]));
            $intersect = trim(trim($intersect, ','));
            if(!empty($intersect)) die(js::alert($this->lang->deploy->errorOffline));

            $scope = new stdclass();
            $scope->deploy  = (int)$deployID;
            $scope->service = (int)$serviceID;
            $scope->hosts   = !empty($data->hosts) ? trim(join(',', $data->hosts[$i]), ',') : '';
            $scope->remove  = !empty($data->remove) ? trim(join(',', $data->remove[$i]), ',') : '';
            $scope->add     = !empty($data->add) ? trim(join(',', $data->add[$i]), ',') : '';
            $items[$serviceID] = $scope;
        }

        $this->dao->delete()->from(TABLE_DEPLOYSCOPE)->where('deploy')->eq($deployID)->exec();
        foreach($items as $scope) $this->dao->insert(TABLE_DEPLOYSCOPE)->data($scope)->exec();
    }

    public function linkCases($deployID)
    {
        $deploy = $this->getByID($deployID);
        $deploy->cases = explode(',', trim($deploy->cases, ','));

        $data   = fixer::input('post')->get();
        $cases  = array_merge($deploy->cases, $data->cases);
        $cases  = array_unique($cases);
        $this->dao->update(TABLE_DEPLOY)->set('cases')->eq(join(',', $cases))->where('id')->eq($deployID)->exec();
    }

    public function manageStep($deployID, $stage = 'all')
    {
        $oldSteps = $this->dao->select('*')->from(TABLE_DEPLOYSTEP)
            ->where('deploy')->eq($deployID)
            ->beginIF($stage != 'all' and isset($this->lang->deploy->stageList[$stage]))->andWhere('stage')->eq($stage)->fi()
            ->fetchAll('id');
        $data = fixer::input('post')->get();
        $now  = helper::now();

        $editSteps = $newSteps  = array();
        $preStage  = $preAssign = '';
        foreach($data->title as $i => $title)
        {
            if(empty($title)) continue;

            if($data->stage[$i] != 'ditto')      $preStage  = $data->stage[$i];
            if($data->assignedTo[$i] != 'ditto') $preAssign = $data->assignedTo[$i];

            $step = new stdclass();
            if(isset($data->id[$i])) $step->id = $data->id[$i];
            $step->deploy     = $deployID;
            $step->stage      = $data->stage[$i];
            $step->begin      = trim($data->begin[$i]);
            $step->end        = trim($data->end[$i]);
            $step->assignedTo = $data->assignedTo[$i];
            $step->title      = trim($title);
            $step->content    = nl2br($data->content[$i]);

            if(empty($step->begin)) die(js::alert(sprintf($this->lang->error->notempty, $this->lang->deploy->begin)));
            if(empty($step->end)) die(js::alert(sprintf($this->lang->error->notempty, $this->lang->deploy->end)));
            if($step->begin >= $step->end) die(js::alert($this->lang->deploy->errorTime));

            if($step->stage == 'ditto')      $step->stage      = $preStage;
            if($step->assignedTo == 'ditto') $step->assignedTo = $preAssign;
            if(!isset($step->id))
            {
                if($step->assignedTo) $step->assignedDate = $now;
                $step->status      = 'wait';
                $step->createdBy   = $this->app->user->account;
                $step->createdDate = $now;
                $newSteps[] = $step;
            }
            else
            {
                if($step->assignedTo != $oldSteps[$step->id]->assignedTo) $step->assignedDate = $now;
                $editSteps[$step->id] = $step;
            }
        }

        $deleteSteps = array_diff(array_keys($oldSteps), array_keys($editSteps));
        if($deleteSteps) $this->dao->update(TABLE_DEPLOYSTEP)->set('deleted')->eq(1)->where('id')->in($deleteSteps)->andWhere('deploy')->eq($deployID)->exec();

        $this->loadModel('action');
        foreach($editSteps as $stepID => $step)
        {
            $this->dao->update(TABLE_DEPLOYSTEP)->data($step)->where('id')->eq($stepID)->exec();

            if(!dao::isError())
            {
                $changes = common::createChanges($oldSteps[$step->id], $step);
                $actionID = $this->action->create('deploystep', $stepID, 'edited');
                $this->action->logHistory($actionID, $changes);
            }
        }

        foreach($newSteps as $step)
        {
            $this->dao->insert(TABLE_DEPLOYSTEP)->data($step)->exec();
            if(!dao::isError())
            {
                $stepID = $this->dao->lastInsertID();
                $this->action->create('deploystep', $stepID, 'created');
            }
        }
    }

    public function changeStatus($deployID, $action)
    {
        $status = '';
        if($action == 'finish')   $status = 'done';
        if($action == 'activate') $status = 'wait';
        if(empty($status)) return false;

        $deploy = new stdclass();
        $deploy->status = $status;
        if($action == 'activate') $deploy->result = '';
        if($action == 'finish')   $deploy->result = $this->post->result;

        $this->dao->update(TABLE_DEPLOY)->data($deploy)->where('id')->eq($deployID)->exec();
        return true;
    }

    public function finishStep($stepID)
    {
        $oldStep = $this->getStepById($stepID);
        $data    = fixer::input('post')
            ->add('status', 'done')
            ->add('finishedBy', $this->app->user->account)
            ->add('finishedDate', helper::now())
            ->remove('comment')
            ->get();
        if($oldStep->assignedTo != $data->assignedTo) $data->assignedDate = helper::now();
        $this->dao->update(TABLE_DEPLOYSTEP)->data($data)->where('id')->eq($stepID)->exec();
        if(!dao::isError()) return common::createChanges($oldStep, $data);
        return false;
    }

    public function assignTo($stepID)
    {
        $oldStep = $this->getStepById($stepID);
        $data    = fixer::input('post')->remove('comment')->get();
        if(!empty($data->begin) and !empty($data->end) and $data->begin >= $data->end) die(js::alert($this->lang->deploy->errorTime));
        if($oldStep->assignedTo != $data->assignedTo) $data->assignedDate = helper::now();
        $this->dao->update(TABLE_DEPLOYSTEP)->data($data)->where('id')->eq($stepID)->exec();

        $oldStep->begin = substr($oldStep->begin, 0, 16);
        $oldStep->end   = substr($oldStep->end, 0, 16);
        if(!dao::isError()) return common::createChanges($oldStep, $data);
        return false;
    }

    public function updateHostAdd($hosts, $adds)
    {
        $hosts = explode(',', trim($hosts, ','));
        $adds  = explode(',', trim($adds, ','));
        $hosts = array_unique(array_merge($host, $adds));
        return join(',', $hosts);
    }

    public function updateHostRemove($hosts, $removes)
    {
        $hosts   = trim($hosts, ',');
        $hosts   = ",$hosts,";
        $removes = explode(',', trim($removes, ','));
        foreach($removes as $hostID) $hosts = str_replace(",$hostID,", ',', $hosts);
        return trim($hosts, ',');
    }

    public function updateStep($stepID)
    {
        $oldStep = $this->getStepById($stepID);
        $data    = fixer::input('post')->get();
        if(!empty($data->begin) and !empty($data->end) and $data->begin >= $data->end) die(js::alert($this->lang->deploy->errorTime));

        $data->content = nl2br($data->content);
        if($data->status == 'wait' and !empty($data->finishedBy)) die(js::alert($this->lang->deploy->errorStatusWait));
        if($data->status == 'done' and empty($data->finishedBy)) die(js::alert($this->lang->deploy->errorStatusDone));
        if($data->finishedBy != $oldStep->finishedBy) $data->finishedDate = helper::now();
        if($data->assignedTo != $oldStep->assignedTo) $data->assignedDate = helper::now();

        $this->dao->update(TABLE_DEPLOYSTEP)->data($data)->where('id')->eq($stepID)->exec();
        return common::createChanges($oldStep, $data);
    }

    public static function isClickable($object, $action)
    {
        $action = strtolower($action);

        if($action == 'finish')   return $object->status == 'wait';
        if($action == 'activate') return $object->status != 'wait';

        return true;
    }

    public function sendmail($deployID, $actionID)
    {
        $this->loadModel('mail');
        $deploy = $this->getByID($deployID);
        $users  = $this->loadModel('user')->getPairs('noletter');

        /* Get action info. */
        $action             = $this->loadModel('action')->getById($actionID);
        $history            = $this->action->getHistory($actionID);
        $action->history    = isset($history[$actionID]) ? $history[$actionID] : array();

        /* Get mail content. */
        $modulePath = $this->app->getModulePath($appName = '', 'deploy');
        $oldcwd     = getcwd();
        $viewFile   = $modulePath . 'view/sendmail.html.php';
        chdir($modulePath . 'view');
        if(file_exists($modulePath . 'ext/view/sendmail.html.php'))
        {
            $viewFile = $modulePath . 'ext/view/sendmail.html.php';
            chdir($modulePath . 'ext/view');
        }
        ob_start();
        include $viewFile;
        foreach(glob($modulePath . 'ext/view/sendmail.*.html.hook.php') as $hookFile) include $hookFile;
        $mailContent = ob_get_contents();
        ob_end_clean();
        chdir($oldcwd);

        /* Set toList and ccList. */
        if($action->action == 'finished')
        {
            $toList = $deploy->createdBy;
        }
        else
        {
            $toList = $deploy->owner;
            $ccList = trim($deploy->members, ',');
            if(empty($toList))
            {
                if(empty($ccList)) return;
                if(strpos($ccList, ',') === false)
                {
                    $toList = $ccList;
                    $ccList = '';
                }
                else
                {
                    $commaPos = strpos($ccList, ',');
                    $toList = substr($ccList, 0, $commaPos);
                    $ccList = substr($ccList, $commaPos + 1);
                }
            }
        }

        /* Send it. */
        $this->mail->send($toList, $this->lang->deploy->common . ' #'. $deploy->id . ' ' . substr($deploy->begin, 0, 16) . ' ~ ' . substr($deploy->end, 0, 16) . ' ' . $deploy->name, $mailContent, $ccList);
        if($this->mail->isError()) error_log(join("\n", $this->mail->getError()));
    }
}
