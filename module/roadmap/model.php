<?php
class roadmapModel extends model
{
    /**
     * Create a roadmap.
     *
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function create($productID = 0)
    {
        $today = helper::today();
        $roadmap = fixer::input('post')
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $today)
            ->add('product', $productID)
            ->setDefault('status', 'wait')
            ->remove('uid')
            ->stripTags($this->config->roadmap->editor->create['id'], $this->config->allowedTags)
            ->get();

        if($roadmap->begin > $roadmap->end)
        {
            dao::$errors['begin'][] = $this->lang->roadmap->beginGtEnd;
            return false;
        }

        $roadmap = $this->loadModel('file')->processImgURL($roadmap, $this->config->roadmap->editor->create['id'], $this->post->uid);

        $this->dao->insert(TABLE_ROADMAP)->data($roadmap)
            ->autoCheck()
            ->batchCheck($this->config->roadmap->create->requiredFields, 'notempty')->exec();

        if(!dao::isError()) return $this->dao->lastInsertID();

        return false;
    }

    /**
     * Update a roadmap.
     *
     * @param  int    $roadmapID
     * @access public
     * @return void
     */
    public function update($roadmapID)
    {
        $today = helper::today();

        $oldRoadmap = $this->getByID($roadmapID);

        $roadmap = fixer::input('post')
            ->cleanInt('product,branch')
            ->remove('uid')
            ->stripTags($this->config->roadmap->editor->edit['id'], $this->config->allowedTags)
            ->get();

        if($roadmap->begin > $roadmap->end)
        {
            dao::$errors['begin'][] = $this->lang->roadmap->beginGtEnd;
            return false;
        }

        $roadmap = $this->loadModel('file')->processImgURL($roadmap, $this->config->roadmap->editor->edit['id'], $this->post->uid);

        $this->dao->update(TABLE_ROADMAP)->data($roadmap)->autoCheck()
            ->batchCheck($this->config->roadmap->edit->requiredFields, 'notempty')
            ->where('id')->eq($roadmapID)
            ->exec();

        if(!dao::isError())
        {
            $this->loadModel('file')->updateObjectID($this->post->uid, $roadmapID, 'roadmap');
            $this->file->saveUpload('roadmap', $roadmapID);
            if(($oldRoadmap->branch != $roadmap->branch) and $roadmap->branch) $this->unlinkUrByBranch($roadmapID, $roadmap->branch);

            return common::createChanges($oldRoadmap, $roadmap);
        }

        return false;
    }

    /**
     * Close a roadmap.
     *
     * @param  int    $roadmapID
     * @access public
     * @return void
     */
    public function close($roadmapID)
    {
        $oldRoadmap = $this->getByID($roadmapID);
        $now        = helper::now();
        $roadmap    = fixer::input('post')
            ->add('status', 'closed')
            ->setDefault('closedDate', $now)
            ->setDefault('closedBy', $this->app->user->account)
            ->remove('uid')
            ->get();

        $this->dao->update(TABLE_ROADMAP)->data($roadmap, 'comment')
            ->where('id')->eq((int)$roadmapID)
            ->batchCheck($this->config->roadmap->close->requiredFields, 'notempty')
            ->exec();
        if(!dao::isError()) return common::createChanges($oldRoadmap, $roadmap);
        return false;
    }

    /**
     * Activate a roadmap.
     *
     * @param  int    $roadmapID
     * @access public
     * @return void
     */
    public function activate($roadmapID)
    {
        $oldRoadmap = $this->getByID($roadmapID);
        $lastStatus = $this->dao->select('*')->from(TABLE_ACTION)
            ->where('objectID')->eq($roadmapID)
            ->andWhere('objectType')->eq('roadmap')
            ->andWhere('action')->eq('closed')
            ->orderBy('id_desc')
            ->fetch('extra');

        $now     = helper::now();
        $roadmap = fixer::input('post')
            ->add('status', $lastStatus)
            ->setDefault('closedDate', '')
            ->setDefault('closedBy', null)
            ->remove('uid')
            ->get();

        $this->dao->update(TABLE_ROADMAP)->data($roadmap, 'comment')
            ->autoCheck()
            ->where('id')->eq((int)$roadmapID)
            ->exec();
        if(!dao::isError()) return common::createChanges($oldRoadmap, $roadmap);
        return false;
    }

    /**
     * Get roadmaps for gantt.
     *
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function getDataForGantt($productID)
    {
        $this->app->loadLang('task');
        $roadmaps = $this->dao->select('*')->from(TABLE_ROADMAP)
            ->where('product')->eq($productID)
            ->andWhere('deleted')->eq(0)
            ->beginIF($this->config->vision == 'rnd')->andWhere('status')->in('launched,closed')->fi()
            ->orderBy("begin_asc")
            ->fetchAll('id');

        $URCount = $this->dao->select('t1.roadmap, count(t2.id) as count')->from(TABLE_ROADMAPSTORY)->alias('t1')
            ->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story = t2.id')
            ->where('t2.deleted')->eq('0')
            ->andWhere('t2.product')->eq($productID)
            ->groupBy('roadmap')
            ->fetchPairs();

        $product  = $this->loadModel('product')->getByID($productID);
        $branches = $product->type != 'normal' ? $this->loadModel('branch')->getPairs($productID) : array();

        $gantt = array();
        $gantt['data'] = array();
        foreach($roadmaps as $id => $roadmap)
        {
            $params = "id=$id";
            $branch = zget($branches, $roadmap->branch);

            $data                = new stdclass();
            $data->id            = $roadmap->id;
            $data->text          = "<span title='$roadmap->name'>" . $roadmap->name . '</span>';
            $data->branch        = "<span title='$branch'>" . $branch . '</span>';
            $data->start_date    = date('d-m-Y', strtotime($roadmap->begin));
            $data->end_date      = date('d-m-Y', strtotime($roadmap->end));
            $data->status        = zget($this->lang->roadmap->statusList, $roadmap->status);
            $data->requirements  = zget($URCount, $id, 0);

            $data->actions       = '';
            if(common::hasPriv('roadmap', 'linkUR')) $data->actions .= $this->buildMenu('roadmap', 'view', $params . "&type=story&orderBy=order_desc&link=true", $roadmap, 'browse', 'link', '', '', '', '', $this->lang->roadmap->linkUR);
            $data->actions      .= $this->buildMenu('roadmap', 'edit', $params, $roadmap, 'browse');
            if($roadmap->status != 'closed') $data->actions .= $this->buildMenu('roadmap', 'close', $params, $roadmap, 'browse', 'off', '', 'iframe', true);
            if($roadmap->status == 'closed' and common::hasPriv('roadmap', 'activate')) $data->actions .= '<a class="btn" href="javascript:void(0);" onclick="if(confirm(\'' . $this->lang->roadmap->confirmActivate . '\')) location.href=\'' . helper::createLink('roadmap', 'activate', array('id' => $roadmap->id, 'confirm' => 'yes')) . '\'"><icon class="icon-magic"></icon></a>';

            $tip = $this->lang->roadmap->confirmDelete;

            if(common::hasPriv('roadmap', 'delete'))
            {
                $locateLink = helper::createLink('roadmap', 'delete', "id=$roadmap->id&confirm=yes");
                $misc       = "title='{$this->lang->delete}' class='btn' onclick=\"if(confirm('$tip')) location.href='$locateLink'\"";
                $actions    = html::a('javascript:void(0);', '<icon class="icon-trash"></icon>', '', $misc);

                if(!in_array($roadmap->status, array('wait', 'closed', 'reject'))) {
                    $tip     = sprintf($this->lang->roadmap->deleteRoadmapTips, zget($this->lang->roadmap->statusList, $roadmap->status));
                    $actions = html::a("javascript:alert(\"$tip\");", '<icon class="icon-trash"></icon>', '', "class='btn'");
                }
                $data->actions .= $actions;
            }

            $gantt['data'][] = $data;
        }

        return json_encode($gantt);
    }

    /**
     * Get $roadmap by id.
     *
     * @param  int    $roadmapID
     * @access public
     * @return object
     */
    public function getByID($roadmapID)
    {
        $roadmap = $this->dao->findByID((int)$roadmapID)->from(TABLE_ROADMAP)->fetch();
        $roadmap = $this->loadModel('file')->replaceImgURL($roadmap, 'desc');
        if(!$roadmap) return false;

        return $roadmap;
    }

    /**
     * Get stories list of a roadmap.
     *
     * @param  int    $roadmapID
     * @param  string $status
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getRoadmapStories($roadmapID, $status = 'all', $orderBy = 'id_desc', $pager = null)
    {
        $stories = $this->dao->select("distinct t1.story, t1.roadmap, t1.order, t2.*, IF(t2.`pri` = 0, {$this->config->maxPriValue}, t2.`pri`) as priOrder, t2.demand")
                ->from(TABLE_ROADMAPSTORY)->alias('t1')
                ->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story = t2.id')
                ->where('t1.roadmap')->eq($roadmapID)
                ->beginIF($status and $status != 'all')->andWhere('t2.status')->in($status)->fi()
                ->andWhere('t2.deleted')->eq(0)
                ->orderBy($orderBy)->page($pager)
                ->fetchAll('id');

        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'roadmap', false);

        return $stories;
    }

    /**
     * Get exclude stories.
     *
     * @param  object $roadmap
     * @access public
     * @return void
     */
    public function getExcludeStories($roadmap)
    {
        $roadmapStories = $this->getRoadmapStories($roadmap->id);
        $excludeStories = $this->dao->select('*')->from(TABLE_STORY)
            ->where('product')->eq($roadmap->product)
            ->andWhere('deleted')->eq('0')
            ->andWhere('status')->notin('active,launched')
            ->andWhere('id')->notin(array_keys($roadmapStories))
            ->fetchAll('id');

        return $roadmapStories + $excludeStories;
    }

    /**
     * Build operate menu.
     *
     * @param  object $roadmap
     * @param  string $type
     * @access public
     * @return string
     */
    public function buildOperateMenu($roadmap, $type = 'view')
    {
        $params = "roadmapID=$roadmap->id";

        $canEdit         = common::hasPriv('roadmap', 'edit');
        $canDelete       = common::hasPriv('roadmap', 'delete');
        $editClickable   = $this->buildMenu($this->app->rawModule, 'edit',   $params, $roadmap, $type, '', '', '', '', '', '', false);

        $menu  = '';

        if($canEdit and $editClickable) $menu .= html::a(helper::createLink('roadmap', 'edit', $params), "<i class='icon-common-edit icon-edit'></i> " . $this->lang->edit, '', "class='btn btn-link' title='{$this->lang->edit}'");
        if($roadmap->status == 'closed')
        {
            if(common::hasPriv('roadmap', 'activate')) $menu .= '<a class="btn btn-link" href="javascript:void(0);" onclick="if(confirm(\'' . $this->lang->roadmap->confirmActivate . '\')) location.href=\'' . helper::createLink('roadmap', 'activate', array('id' => $roadmap->id, 'confirm' => 'yes')) . '\'"><icon class="icon-magic">' . $this->lang->activate . '</icon></a>';
        }
        else
        {
            $menu .= $this->buildMenu('roadmap', 'close', $params, $roadmap, $type, 'off', '', 'iframe', true, '', $this->lang->close);
        }

        if($canDelete) $menu .= html::a(helper::createLink('roadmap', 'delete', $params), "<i class='icon-common-delete icon-trash'></i> " . $this->lang->delete, '', "class='btn btn-link' title='{$this->lang->delete}' target='hiddenwin'");

        return $menu;
    }

    /**
     * Unlink story
     *
     * @param  int    $storyID
     * @param  int    $roadmapID
     * @access public
     * @return array
     */
    public function unlinkUR($storyID, $roadmapID)
    {
        $story   = $this->dao->findByID($storyID)->from(TABLE_STORY)->fetch();
        $roadmap = $this->dao->findByID($roadmapID)->from(TABLE_ROADMAP)->fetch();

        if($roadmap->status == 'closed' && $story->status == 'closed') return array('result' => 'fail', 'storyID' => $storyID);
        $roadmaps = array_unique(explode(',', trim(str_replace(",$roadmapID,", ',', ',' . trim($story->roadmap) . ','). ',')));

        $data = fixer::input('post')
            ->setDefault('roadmap', join(',', $roadmaps))
            ->setDefault('status', 'active')
            ->setDefault('vision', 'or')
            ->remove('uid,comment')
            ->get();

        if(in_array($story->status, array('reviewing', 'changing'))) $data->status = $story->status;

        $this->dao->update(TABLE_STORY)
            ->data($data)
            ->where('id')->eq((int)$storyID)
            ->exec();

        $demandStatus = $this->loadModel('demand')->changeDemandStatus($story->demand);

        $this->dao->update(TABLE_DEMAND)
            ->set('status')->eq($demandStatus)
            ->where('id')->eq($story->demand)
            ->exec();

        /* Delete the story in the sort of the roadmap. */
        $this->loadModel('story');
        $this->story->updateStoryOrderOfRoadmap($storyID, '', $roadmapID);

        $actionID = $this->loadModel('action')->create('story', $storyID, 'unlinkedfromroadmap', $this->post->comment, $roadmapID);

        $data = $this->loadModel('file')->processImgURL($data, $this->config->roadmap->ublinkur->edit['id'], $this->post->uid);

        if(!dao::isError())
        {
            $this->loadModel('file')->updateObjectID($this->post->uid, $storyID, 'story');
            $this->file->saveUpload('story', $storyID);

            unset($data->vision);
            $changes = common::createChanges($story, $data);
            $this->action->logHistory($actionID, $changes);
        }
        return $storyID;
    }

    /**
     * Unlink story by branch.
     *
     * @param  int    $roadmapID
     * @param  int    $branchID
     * @access public
     * @return void
     */
    public function unlinkUrByBranch($roadmapID, $branchID)
    {
        $storyDemandList = $this->dao->select('id,demand')->from(TABLE_STORY)
            ->where('roadmap')->eq($roadmapID)
            ->andWhere('branch')->ne($branchID)
            ->andWhere('branch')->ne('0')
            ->fetchPairs();

        $storyIdList  = array_keys($storyDemandList);
        $demandIdList = array_values($storyDemandList);

        $this->dao->update(TABLE_STORY)
            ->set('roadmap')->eq('')
            ->set('status')->eq('active')
            ->set('vision')->eq('or')
            ->where('id')->in($storyIdList)
            ->exec();

        $this->dao->update(TABLE_DEMAND)
            ->set('status')->eq('distributed')
            ->where('id')->in($demandIdList)
            ->exec();

        $this->dao->delete()->from(TABLE_ROADMAPSTORY)->where('roadmap')->eq($roadmapID)->andWhere('story')->in($storyIdList)->exec();

        $this->loadModel('action');
        foreach($storyIdList as $storyID) $this->action->create('story', $storyID, 'unlinkedfromroadmap', '', $roadmapID);
        $this->action->create('roadmap', $roadmapID, 'unlinkur', '', implode(',', $storyIdList));
    }

    /**
     * Get roadmap pairs.
     *
     * @param  array|int        $product
     * @param  int|string|array $branch
     * @param  string           $param unexpired|noclosed|nolaunched|nolaunching|wait
     * @access public
     * @return array
     */
    public function getPairs($product = 0, $branch = '', $param = '')
    {
        $this->app->loadLang('branch');

        $date = date('Y-m-d');

        $branchQuery = '';
        if($branch !== '' and $branch != 'all')
        {
            $branchQuery .= '(';
            $branchCount = count(explode(',', $branch));
            foreach(explode(',', $branch) as $index => $branchID)
            {
                $branchQuery .= "FIND_IN_SET('$branchID', t1.branch)";
                if($index < $branchCount - 1) $branchQuery .= ' OR ';
            }
            $branchQuery .= ')';
        }

        $roadmaps = $this->dao->select('t1.id,t1.name,t1.begin,t1.end,t3.type as productType,t1.branch')->from(TABLE_ROADMAP)->alias('t1')
            ->leftJoin(TABLE_PRODUCT)->alias('t3')->on('t3.id=t1.product')
            ->where('t1.deleted')->eq(0)
            ->beginIF(!empty($branchQuery))->andWhere($branchQuery)->fi()
            ->beginIF($product)->andwhere('t1.product')->in($product)->fi()
            ->beginIF(strpos($param, 'unexpired') !== false)->andWhere('t1.end')->ge($date)->fi()
            ->beginIF(strpos($param, 'noclosed') !== false)->andWhere('t1.status')->ne('closed')->fi()
            ->beginIF(strpos($param, 'nolaunched') !== false)->andWhere('t1.status')->in('wait,launching')->fi()
            ->beginIF(strpos($param, 'nolaunching') !== false)->andWhere('t1.status')->in('wait,reject,launching')->fi()
            ->beginIF(strpos($param, 'wait') !== false)->andWhere('t1.status')->eq('wait')->fi()
            ->beginIF(strpos($param, 'linkRoadmap') !== false)->andWhere('t1.status')->in('wait,reject')->fi()
            ->beginIF(strpos($param, 'distributable') !== false)->andWhere('t1.status')->in('wait,launching,launched')->fi()
            ->orderBy('t1.begin desc')
            ->fetchAll('id');

        $roadmaps     = $this->relationBranch($roadmaps);
        $roadmapPairs = array();
        foreach($roadmaps as $roadmap)
        {
            $roadmapPairs[$roadmap->id] = $roadmap->name . " [{$roadmap->begin} ~ {$roadmap->end}]";
            if($roadmap->begin == $this->config->roadmap->future and $roadmap->end == $this->config->roadmap->future) $roadmapPairs[$roadmap->id] = $roadmap->title . ' ' . $this->lang->roadmap->future;
            if($roadmap->productType != 'normal') $roadmapPairs[$roadmap->id] = $roadmapPairs[$roadmap->id] . ' / ' . ($roadmap->branchName ? $roadmap->branchName : $this->lang->branch->main);
        }
        return array('' => '') + $roadmapPairs;
    }

    /**
     * 根据项目ID获取路标。
     * Get pairs by projectID.
     *
     * @param  int $projectID
     * @access public
     * @return array
     */
    public function getPairsByProjectID($projectID = 0)
    {
        return $this->dao->select('t1.*')->from(TABLE_ROADMAP)->alias('t1')
            ->leftJoin(TABLE_CHARTER)->alias('t2')->on('t2.roadmap=t1.id')
            ->leftJoin(TABLE_PROJECT)->alias('t3')->on('t3.charter=t2.id')
            ->where('t3.id')->eq($projectID)
            ->fetchPairs('id', 'name');
    }

    /**
     * Get roadmap count.
     *
     * @access public
     * @return void
     */
    public function getRoadmapCount()
    {
        return $this->dao->select('product,status,count(id) as count')->from(TABLE_ROADMAP)
            ->where('deleted')->eq('0')
            ->andWhere('status')->in('launched,wait')
            ->groupBy('product,status')
            ->fetchGroup('product', 'status');
    }

    /**
     * Get productLine roadmap count.
     *
     * @access public
     * @return array
     */
    public function getLineRoadmapCount()
    {
        $lineProductPairs = $this->dao->select('id,line,0 as wait,0 as launched')->from(TABLE_PRODUCT)
            ->where('line')->ne(0)
            ->fetchGroup('line', 'id');
        $productRoadmapCount = $this->getRoadmapCount();
        foreach($lineProductPairs as $line => $products)
        {
            foreach($products as $product => $count)
            {
                if(isset($productRoadmapCount[$product]['wait']))     $lineProductPairs[$line][$product]->wait     += $productRoadmapCount[$product]['wait']->count;
                if(isset($productRoadmapCount[$product]['launched'])) $lineProductPairs[$line][$product]->launched += $productRoadmapCount[$product]['launched']->count;
            }
        }

        $lineRoadmaps = array();
        foreach($lineProductPairs as $line => $products)
        {
            foreach($products as $product => $productRoadmap)
            {
                if(!isset($lineRoadmaps[$line]->wait) || !isset($lineRoadmaps[$line]->launched))
                {
                    $lineRoadmaps[$line] = new stdclass();
                    $lineRoadmaps[$line]->wait     = 0;
                    $lineRoadmaps[$line]->launched = 0;
                }
                $lineRoadmaps[$line]->wait     += $productRoadmap->wait;
                $lineRoadmaps[$line]->launched += $productRoadmap->launched;
            }
        }
        return $lineRoadmaps;
    }

    /**
     * Get relation branch for roadmaps.
     *
     * @param  array    $roadmaps
     * @access public
     * @return array
     */
    public function relationBranch($roadmaps)
    {
        if(empty($roadmaps)) return $roadmaps;

        $this->app->loadLang('branch');
        $branchMap = $this->dao->select('id, name')->from(TABLE_BRANCH)
            ->where('status')->eq('active')
            ->andWhere('deleted')->eq('0')
            ->fetchPairs('id', 'name');
        $branchMap[BRANCH_MAIN] = $this->lang->branch->main;

        foreach($roadmaps as &$roadmap)
        {
            if($roadmap->branch)
            {
                $branchName = '';
                foreach(explode(',', $roadmap->branch) as $roadmapBranch)
                {
                    $branchName .= isset($branchMap[$roadmapBranch]) ? $branchMap[$roadmapBranch] : '';
                    $branchName .= ',';
                }

                $roadmap->branchName = trim($branchName, ',');
            }
            else
            {
                $roadmap->branchName = $this->lang->branch->main;
            }
        }

        return $roadmaps;
    }

    /**
     * Get roadmap pairs for story.
     *
     * @param  array|int    $product
     * @param  int          $branch
     * @param  string       $param withMainRoadmap|unexpired|noclosed
     * @access public
     * @return array
     */
    public function getPairsForStory($product = 0, $branch = '', $param = '')
    {
        $date   = date('Y-m-d');
        $param  = strtolower($param);
        $branch = strpos($param, 'withmainroadmap') !== false ? "0,$branch" : $branch;

        $branchQuery = '';
        if($branch !== '' and $branch != 'all')
        {
            $branchQuery .= '(';
            $branchCount = count(explode(',', $branch));
            foreach(explode(',', $branch) as $index => $branchID)
            {
                $branchQuery .= "FIND_IN_SET('$branchID', branch)";
                if($index < $branchCount - 1) $branchQuery .= ' OR ';
            }
            $branchQuery .= ')';
        }

        $roadmaps  = $this->dao->select('id,name,begin,end')->from(TABLE_ROADMAP)
            ->where('product')->in($product)
            ->andWhere('deleted')->eq(0)
            ->beginIF(strpos($param, 'unexpired') !== false)->andWhere('end')->ge($date)->fi()
            ->beginIF(strpos($param, 'noclosed') !== false)->andWhere('status')->ne('closed')->fi()
            ->beginIF($branch !== 'all' and $branch !== '' and !empty($branchQuery))->andWhere($branchQuery)->fi()
            ->orderBy('begin desc')
            ->fetchAll('id');

        $roadmapPairs   = array();
        foreach($roadmaps as $roadmap)
        {
            $roadmapPairs[$roadmap->id] = $roadmap->name . " [{$roadmap->begin} ~ {$roadmap->end}]";
            if($roadmap->begin == $this->config->roadmap->future and $roadmap->end == $this->config->roadmap->future) $roadmapPairs[$roadmap->id] = $roadmap->name . ' ' . $this->lang->roadmap->future;
        }

        return array('' => '') + $roadmapPairs;
    }

    /**
     * Link stories.
     *
     * @param  int    $roadmapID
     * @access public
     * @return void
     */
    public function linkUR($roadmapID)
    {
        $this->loadModel('story');
        $this->loadModel('action');

        $stories = $this->story->getByList($this->post->stories);
        $roadmap = $this->getByID($roadmapID);

        foreach($this->post->stories as $storyID)
        {
            if(!isset($stories[$storyID])) continue;

            $story = $stories[$storyID];
            if(strpos(",$story->roadmap,", ",{$roadmapID},") !== false) continue;

            /* Update the roadmap linked with the story and the order of the story in the roadmap. */
            $this->dao->update(TABLE_STORY)
                ->set("roadmap")->eq($roadmapID)
                ->beginIF($roadmap->status == 'launched')
                ->set("status")->eq('launched')
                ->set("vision")->eq('or,rnd')
                ->fi()
                ->where('id')->eq((int)$storyID)
                ->exec();

            $this->story->updateStoryOrderOfRoadmap($storyID, $roadmapID, $story->roadmap);

            $this->action->create('story', $storyID, 'linked2roadmap', '', $roadmapID);
            // $this->story->setStage($storyID);
            if($roadmap->status == 'launched' and $story->demand)
            {
                $this->dao->update(TABLE_DEMAND)
                    ->set('status')->eq('launched')
                    ->where('id')->eq($story->demand)
                    ->exec();
            }

            if($roadmap->status == 'launched' and $story->feedback)
            {
                $feedback = new stdclass();
                $feedback->status        = 'commenting';
                $feedback->processedBy   = $this->app->user->account;
                $feedback->processedDate = helper::now();

                $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($story->feedback)->exec();
            }
        }

        $this->action->create('roadmap', $roadmapID, 'linkur', '', implode(',', $this->post->stories));
    }

    /**
     * Update the story order according to the plan.
     *
     * @param  int    $planID
     * @param  array  $sortIDList
     * @param  string $orderBy
     * @param  int    $pageID
     * @param  int    $recPerPage
     * @access public
     * @return void
     */
    public function sortStoriesOfPlan($planID, $sortIDList, $orderBy = 'id_desc', $pageID = 1, $recPerPage = 100)
    {
        /* Append id for secend sort. */
        $orderBy = common::appendOrder($orderBy);

        /* Get all stories by plan. */
        $stories     = $this->getPlanStories($planID, 'all', $orderBy);
        $storyIDList = array_keys($stories);

        /* Calculate how many numbers there are before the sort list and after the sort list. */
        $frontStoryCount   = $recPerPage * ($pageID - 1);
        $behindStoryCount  = $recPerPage * $pageID;
        $frontStoryIDList  = array_slice($storyIDList, 0, $frontStoryCount);
        $behindStoryIDList = array_slice($storyIDList, $behindStoryCount, count($storyIDList) - $behindStoryCount);

        /* Merge to get a new sort list. */
        $newSortIDList = array_merge($frontStoryIDList, $sortIDList, $behindStoryIDList);
        if(strpos($orderBy, 'order_desc') !== false) $newSortIDList = array_reverse($newSortIDList);

        /* Loop update the story order of plan. */
        $order = 1;
        foreach($newSortIDList as $storyID)
        {
            $this->dao->update(TABLE_PLANSTORY)->set('`order`')->eq($order)->where('story')->eq($storyID)->andWhere('plan')->eq($planID)->exec();
            $order++;
        }
    }

    /**
     * Merge roadmap title.
     *
     * @param  int|array $productID
     * @param  array     $stories
     *
     * @access public
     * @return array
     */
    public function mergeRoadmapTitle($productID, $stories)
    {
        $roadmaps = $this->dao->select('id,name')->from(TABLE_ROADMAP)
            ->Where('deleted')->eq(0)
            ->beginIF($productID)->andWhere('product')->in($productID)->fi()
            ->fetchPairs('id', 'name');

        foreach($stories as $storyID => $story)
        {
            $story->roadmapTitle = '';
            $storyRoadmaps = explode(',', trim($story->roadmap, ','));
            foreach($storyRoadmaps as $roadmapID) $story->roadmapTitle .= zget($roadmaps, $roadmapID, '') . ' ';
        }

        return $stories;
    }

    /**
     * Change roadmap status.
     *
     * @param  int    $roadmapID
     * @param  string $status
     * @access public
     * @return bool
     */
    public function setStatus($roadmapID, $status)
    {
        $this->dao->update(TABLE_ROADMAP)->set('status')->eq($status)->where('id')->eq($roadmapID)->exec();
        return !dao::isError();
    }

    /**
     * Get roadmap list.
     *
     * @access public
     * @return void
     */
    public function getList()
    {
        return $this->dao->select('id,name,status')->from(TABLE_ROADMAP)->where('deleted')->eq('0')->fetchAll('id');
    }
}
