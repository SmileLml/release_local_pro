<?php
class ipdStory extends storyModel
{
    /**
     * Batch change the roadmap of story.
     *
     * @param  array  $storyIdList
     * @param  int    $roadmapID
     * @access public
     * @return array
     */
    public function batchChangeRoadmap($storyIdList, $roadmapID)
    {
        $now        = helper::now();
        $allChanges = array();
        $oldStories = $this->getByList($storyIdList);
        $lastOrder  = $this->dao->select('`order`')->from(TABLE_ROADMAPSTORY)->where('roadmap')->eq($roadmapID)->orderBy('order_desc')->fetch('order');

        foreach($storyIdList as $storyID)
        {
            $oldStory = $oldStories[$storyID];
            if($roadmapID == $oldStory->roadmap) continue;

            $story = new stdclass();
            $story->lastEditedBy   = $this->app->user->account;
            $story->lastEditedDate = $now;
            $story->roadmap        = $roadmapID;

            $this->dao->update(TABLE_STORY)->data($story)->autoCheck()->where('id')->eq((int)$storyID)->exec();
            if(!dao::isError())
            {
                $this->dao->delete()->from(TABLE_ROADMAPSTORY)->where('roadmap')->eq($oldStory->roadmap)->andWhere('story')->eq($storyID)->exec();

                $roadmapStory = new stdclass();
                $roadmapStory->roadmap = $roadmapID;
                $roadmapStory->story   = $storyID;
                $roadmapStory->order   = ++ $lastOrder;
                $this->dao->insert(TABLE_ROADMAPSTORY)->data($roadmapStory)->autoCheck()->exec();

                $allChanges[$storyID] = common::createChanges($oldStory, $story);
            }
        }
        return $allChanges;
    }

    /**
     * Batch update stories.
     *
     * @access public
     * @return array.
     */
    public function batchUpdate()
    {
        /* Init vars. */
        $stories     = array();
        $allChanges  = array();
        $now         = helper::now();
        $data        = fixer::input('post')->get();
        $storyIdList = $this->post->storyIdList ? $this->post->storyIdList : array();
        $unlinkPlans = array();
        $link2Plans  = array();

        /* Init $stories. */
        if(!empty($storyIdList))
        {
            $oldStories = $this->getByList($storyIdList);

            /* Process the data if the value is 'ditto'. */
            foreach($storyIdList as $storyID)
            {
                if(isset($data->pris) and $data->pris[$storyID] == 'ditto') $data->pris[$storyID]     = isset($prev['pri'])    ? $prev['pri']    : 0;
                if(isset($data->branches) and $data->branches[$storyID] == 'ditto') $data->branches[$storyID] = isset($prev['branch']) ? $prev['branch'] : 0;
                if($data->modules[$storyID]  == 'ditto') $data->modules[$storyID]  = isset($prev['module']) ? $prev['module'] : 0;
                if(isset($data->plans) and $data->plans[$storyID] == 'ditto') $data->plans[$storyID]    = isset($prev['plan'])   ? $prev['plan']   : '';
                if(isset($data->sources) and $data->sources[$storyID] == 'ditto') $data->sources[$storyID]  = isset($prev['source']) ? $prev['source'] : '';
                if(isset($data->stages[$storyID])        and ($data->stages[$storyID]        == 'ditto')) $data->stages[$storyID]        = isset($prev['stage'])        ? $prev['stage']        : '';
                if(isset($data->closedBys[$storyID])     and ($data->closedBys[$storyID]     == 'ditto')) $data->closedBys[$storyID]     = isset($prev['closedBy'])     ? $prev['closedBy']     : '';
                if(isset($data->closedReasons[$storyID]) and ($data->closedReasons[$storyID] == 'ditto')) $data->closedReasons[$storyID] = isset($prev['closedReason']) ? $prev['closedReason'] : '';

                $prev['pri']    = $data->pris[$storyID];
                $prev['branch'] = isset($data->branches[$storyID]) ? $data->branches[$storyID] : 0;
                $prev['module'] = $data->modules[$storyID];
                $prev['plan']   = isset($data->plans[$storyID]) ? $data->plans[$storyID] : '';
                $prev['source'] = isset($data->sources[$storyID]) ? $data->sources[$storyID] : '';
                if(isset($data->stages[$storyID]))        $prev['stage']        = $data->stages[$storyID];
                if(isset($data->closedBys[$storyID]))     $prev['closedBy']     = $data->closedBys[$storyID];
                if(isset($data->closedReasons[$storyID])) $prev['closedReason'] = $data->closedReasons[$storyID];
            }

            $extendFields = $this->getFlowExtendFields();
            foreach($storyIdList as $storyID)
            {
                $oldStory = $oldStories[$storyID];

                $story                 = new stdclass();
                $story->id             = $storyID;
                $story->lastEditedBy   = $this->app->user->account;
                $story->lastEditedDate = $now;
                $story->status         = $oldStory->status;
                $story->color          = $data->colors[$storyID];
                $story->title          = $data->titles[$storyID];
                $story->estimate       = $data->estimates[$storyID];
                $story->category       = $data->category[$storyID];
                $story->pri            = $data->pris[$storyID];
                $story->assignedTo     = $data->assignedTo[$storyID];
                $story->assignedDate   = $oldStory == $data->assignedTo[$storyID] ? $oldStory->assignedDate : $now;
                $story->branch         = isset($data->branches[$storyID]) ? $data->branches[$storyID] : 0;
                $story->module         = $data->modules[$storyID];
                $story->plan           = isset($data->plans[$storyID]) ? ($oldStory->parent < 0 ? '' : $data->plans[$storyID]) : $oldStory->plan;
                $story->source         = isset($data->sources[$storyID]) ? $data->sources[$storyID] : $oldStory->source;
                $story->sourceNote     = isset($data->sourceNote[$storyID]) ? $data->sourceNote[$storyID] : $oldStory->sourceNote;
                $story->keywords       = isset($data->keywords[$storyID]) ? $data->keywords[$storyID] : $oldStory->keywords;
                $story->stage          = isset($data->stages[$storyID])             ? $data->stages[$storyID]             : $oldStory->stage;
                $story->closedBy       = isset($data->closedBys[$storyID])          ? $data->closedBys[$storyID]          : $oldStory->closedBy;
                $story->closedReason   = isset($data->closedReasons[$storyID])      ? $data->closedReasons[$storyID]      : $oldStory->closedReason;
                $story->duplicateStory = isset($data->duplicateStories[$storyID])   ? $data->duplicateStories[$storyID]   : $oldStory->duplicateStory;
                $story->childStories   = isset($data->childStoriesIDList[$storyID]) ? $data->childStoriesIDList[$storyID] : $oldStory->childStories;
                $story->version        = $story->title == $oldStory->title ? $oldStory->version : (int)$oldStory->version + 1;
                $story->roadmap        = isset($data->roadmaps[$storyID]) ? $data->roadmaps[$storyID] : $oldStory->roadmap;
                $story->duration       = isset($data->durations[$storyID]) ? $data->durations[$storyID] : $oldStory->duration;
                $story->BSA            = isset($data->BSAs[$storyID]) ? $data->BSAs[$storyID] : $oldStory->BSA;
                $story->mailto         = isset($data->mailtos[$storyID]) ? implode(',', $data->mailtos[$storyID]) : $oldStory->mailto;
                $story->type           = $oldStory->type;
                if($story->stage != $oldStory->stage) $story->stagedBy = (strpos('tested|verified|released|closed', $story->stage) !== false) ? $this->app->user->account : '';

                if($story->title != $oldStory->title and $story->status != 'draft')  $story->status     = 'changing';
                if($story->closedBy     != false  and $oldStory->closedDate == '')   $story->closedDate = $now;
                if($story->closedReason != false  and $oldStory->closedDate == '')   $story->closedDate = $now;
                if($story->closedBy     != false  or  $story->closedReason != false) $story->status     = 'closed';
                if($story->closedReason != false  and $story->closedBy     == false) $story->closedBy   = $this->app->user->account;
                if(empty($story->roadmap)) $story->roadmap = 0;

                if($story->plan != $oldStory->plan)
                {
                    if($story->plan != $oldStory->plan and !empty($oldStory->plan)) $unlinkPlans[$oldStory->plan] = empty($unlinkPlans[$oldStory->plan]) ? $storyID : "{$unlinkPlans[$oldStory->plan]},$storyID";
                    if($story->plan != $oldStory->plan and !empty($story->plan))    $link2Plans[$story->plan]  = empty($link2Plans[$story->plan]) ? $storyID : "{$link2Plans[$story->plan]},$storyID";
                }

                foreach($extendFields as $extendField)
                {
                    $story->{$extendField->field} = $this->post->{$extendField->field}[$storyID];
                    if(is_array($story->{$extendField->field})) $story->{$extendField->field} = join(',', $story->{$extendField->field});

                    $story->{$extendField->field} = htmlSpecialString($story->{$extendField->field});
                }

                $stories[$storyID] = $story;
            }

            foreach($stories as $storyID => $story)
            {
                $oldStory = $oldStories[$storyID];

                $this->dao->update(TABLE_STORY)->data($story)
                    ->autoCheck()
                    ->checkIF($story->closedBy, 'closedReason', 'notempty')
                    ->checkIF($story->closedReason == 'done', 'stage', 'notempty')
                    ->checkIF($story->closedReason == 'duplicate',  'duplicateStory', 'notempty')
                    ->checkFlow()
                    ->where('id')->eq((int)$storyID)
                    ->exec();
                if($story->title != $oldStory->title)
                {
                    $data          = new stdclass();
                    $data->story   = $storyID;
                    $data->version = $story->version;
                    $data->title   = $story->title;
                    $data->spec    = $oldStory->spec;
                    $data->verify  = $oldStory->verify;
                    $this->dao->insert(TABLE_STORYSPEC)->data($data)->exec();
                }

                if(!dao::isError())
                {
                    /* Update story sort of plan when story plan has changed. */
                    if($oldStory->plan != $story->plan) $this->updateStoryOrderOfPlan($storyID, $story->plan, $oldStory->plan);

                    $this->executeHooks($storyID);
                    if($story->type == 'story') $this->batchChangeStage(array($storyID), $story->stage);
                    if($story->closedReason == 'done') $this->loadModel('score')->create('story', 'close');
                    $allChanges[$storyID] = common::createChanges($oldStory, $story);

                    if($this->config->edition != 'open' && $oldStory->feedback && !isset($feedbacks[$oldStory->feedback]))
                    {
                        $feedbacks[$oldStory->feedback] = $oldStory->feedback;
                        $this->loadModel('feedback')->updateStatus('story', $oldStory->feedback, $story->status, $oldStory->status);
                    }

                    /* Update roadmap story. */
                    if($story->roadmap != $oldStory->roadmap)
                    {
                        $this->dao->delete()->from(TABLE_ROADMAPSTORY)->where('roadmap')->eq($oldStory->roadmap)->andWhere('story')->eq($storyID)->exec();

                        $roadmapStory = new stdclass();
                        $roadmapStory->roadmap = $story->roadmap;
                        $roadmapStory->story   = $storyID;
                        $this->dao->insert(TABLE_ROADMAPSTORY)->data($roadmapStory)->autoCheck()->exec();
                    }

                }
                else
                {
                    return print(js::error('story#' . $storyID . dao::getError(true)));
                }
            }
        }
        if(!dao::isError())
        {
            $this->loadModel('score')->create('ajax', 'batchEdit');

            $this->loadModel('action');
            foreach($unlinkPlans as $planID => $stories) $this->action->create('productplan', $planID, 'unlinkstory', '', $stories);
            foreach($link2Plans as $planID => $stories) $this->action->create('productplan', $planID, 'linkstory', '', $stories);

        }
        return $allChanges;
    }

    /**
     * Update a story.
     *
     * @param  int    $storyID
     * @access public
     * @return array  the changes of the story.
     */
    public function update($storyID)
    {
        $now      = helper::now();
        $oldStory = $this->getById($storyID);

        if($oldStory->status == 'draft' or $oldStory->status == 'changing')
        {
            if(isset($_POST['reviewer'])) $_POST['reviewer'] = array_filter($_POST['reviewer']);
            if(!$this->post->needNotReview and empty($_POST['reviewer']))
            {
                dao::$errors['reviewer'] = sprintf($this->lang->error->notempty, $this->lang->story->reviewers);
                return false;
            }
        }

        if(!empty($_POST['lastEditedDate']) and $oldStory->lastEditedDate != $this->post->lastEditedDate)
        {
            dao::$errors[] = $this->lang->error->editedByOther;
            return false;
        }

        if(strpos('draft,changing', $oldStory->status) !== false and $this->checkForceReview() and empty($_POST['reviewer']))
        {
            dao::$errors[] = $this->lang->story->notice->reviewerNotEmpty;
            return false;
        }

        $storyPlan = array();
        if(!empty($_POST['plan'])) $storyPlan = is_array($_POST['plan']) ? array_filter($_POST['plan']) : array($_POST['plan']);
        if(count($storyPlan) > 1)
        {
            $oldStoryPlan  = !empty($oldStory->planTitle) ? array_keys($oldStory->planTitle) : array();
            $oldPlanDiff   = array_diff($storyPlan, $oldStoryPlan);
            $storyPlanDiff = array_diff($oldStoryPlan, $storyPlan);
            if(!empty($oldPlanDiff) or !empty($storyPlanDiff))
            {
                dao::$errors[] = $this->lang->story->notice->changePlan;
                return false;
            }
        }

        if($this->config->vision != 'or')
        {
            /* Unchanged product when editing requirements on site. */
            $hasProduct = $this->dao->select('t2.hasProduct')->from(TABLE_PROJECTPRODUCT)->alias('t1')
                ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
                ->where('t1.product')->eq($oldStory->product)
                ->andWhere('t2.deleted')->eq(0)
                ->fetch('hasProduct');
            $_POST['product'] = (!empty($hasProduct) && !$hasProduct) ? $oldStory->product : $this->post->product;
        }

        $story = fixer::input('post')
            ->cleanInt('product,module,pri,duplicateStory')
            ->cleanFloat('estimate')
            ->setDefault('assignedDate', $oldStory->assignedDate)
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('reviewedBy', $oldStory->reviewedBy)
            ->setDefault('mailto', '')
            ->setDefault('deleteFiles', array())
            ->add('id', $storyID)
            ->add('lastEditedDate', $now)
            ->setDefault('plan,notifyEmail', '')
            ->setDefault('product', $oldStory->product)
            ->setDefault('branch', $oldStory->branch)
            ->setIF(!$this->post->linkStories, 'linkStories', '')
            ->setIF($this->post->assignedTo   != $oldStory->assignedTo, 'assignedDate', $now)
            ->setIF($this->post->closedBy     != false and $oldStory->closedDate == '', 'closedDate', $now)
            ->setIF($this->post->closedReason != false and $oldStory->closedDate == '', 'closedDate', $now)
            ->setIF($this->post->closedBy     != false or  $this->post->closedReason != false, 'status', 'closed')
            ->setIF($this->post->closedReason != false and $this->post->closedBy     == false, 'closedBy', $this->app->user->account)
            ->setIF($this->post->stage == 'released', 'releasedDate', $now)
            ->setIF(!in_array($this->post->source, $this->config->story->feedbackSource), 'feedbackBy', '')
            ->setIF(!in_array($this->post->source, $this->config->story->feedbackSource), 'notifyEmail', '')
            ->setIF(!empty($_POST['plan'][0]) and $oldStory->stage == 'wait', 'stage', 'planned')
            ->setIF(!isset($_POST['title']), 'title', $oldStory->title)
            ->setIF(!isset($_POST['spec']), 'spec', $oldStory->spec)
            ->setIF(!isset($_POST['verify']), 'verify', $oldStory->verify)
            ->stripTags($this->config->story->editor->edit['id'], $this->config->allowedTags)
            ->join('mailto', ',')
            ->join('linkStories', ',')
            ->join('linkRequirements', ',')
            ->join('childStories', ',')
            ->remove('files,labels,comment,contactListMenu,reviewer,needNotReview')
            ->get();

        /* Relieve twins when change product. */
        if(!empty($oldStory->twins) and $story->product != $oldStory->product)
        {
            $this->dbh->exec("UPDATE " . TABLE_STORY . " SET twins = REPLACE(twins, ',$storyID,', ',') WHERE `product` = $oldStory->product");
            $this->dao->update(TABLE_STORY)->set('twins')->eq('')->where('id')->eq($storyID)->orWhere('twins')->eq(',')->exec();
            $oldStory->twins = '';
        }

        if($oldStory->type == 'story' and !isset($story->linkStories)) $story->linkStories = '';
        if($oldStory->type == 'requirement' and !isset($story->linkRequirements)) $story->linkRequirements = '';
        if($oldStory->status == 'changing' and $story->status == 'draft') $story->status = 'changing';

        if(isset($story->plan) and is_array($story->plan)) $story->plan = trim(join(',', $story->plan), ',');
        if(isset($_POST['branch']) and $_POST['branch'] == 0) $story->branch = 0;

        if(isset($story->stage) and $oldStory->stage != $story->stage) $story->stagedBy = (strpos('tested|verified|released|closed', $story->stage) !== false) ? $this->app->user->account : '';
        $story = $this->loadModel('file')->processImgURL($story, $this->config->story->editor->edit['id'], $this->post->uid);

        if(isset($_POST['reviewer']) or isset($_POST['needNotReview']))
        {
            $_POST['reviewer'] = isset($_POST['needNotReview']) ? array() : array_filter($_POST['reviewer']);
            $oldReviewer       = $this->getReviewerPairs($storyID, $oldStory->version);

            /* Update story reviewer. */
            $this->dao->delete()->from(TABLE_STORYREVIEW)
                ->where('story')->eq($storyID)
                ->andWhere('version')->eq($oldStory->version)
                ->beginIF($oldStory->status == 'reviewing')->andWhere('reviewer')->notin(implode(',', $_POST['reviewer']))
                ->exec();

            /* Sync twins. */
            if(!empty($oldStory->twins))
            {
                foreach(explode(',', trim($oldStory->twins, ',')) as $twinID)
                {
                    $this->dao->delete()->from(TABLE_STORYREVIEW)
                        ->where('story')->eq($twinID)
                        ->andWhere('version')->eq($oldStory->version)
                        ->beginIF($oldStory->status == 'reviewing')->andWhere('reviewer')->notin(implode(',', $_POST['reviewer']))
                        ->exec();
                }
            }

            foreach($_POST['reviewer'] as $reviewer)
            {
                if($oldStory->status == 'reviewing' and in_array($reviewer, array_keys($oldReviewer))) continue;

                $reviewData = new stdclass();
                $reviewData->story    = $storyID;
                $reviewData->version  = $oldStory->version;
                $reviewData->reviewer = $reviewer;
                $this->dao->insert(TABLE_STORYREVIEW)->data($reviewData)->exec();

                /* Sync twins. */
                if(!empty($oldStory->twins))
                {
                    foreach(explode(',', trim($oldStory->twins, ',')) as $twinID)
                    {
                        $reviewData->story = $twinID;
                        $this->dao->insert(TABLE_STORYREVIEW)->data($reviewData)->exec();
                    }
                }
            }

            if($oldStory->status == 'reviewing') $story = $this->updateStoryByReview($storyID, $oldStory, $story);
            if(strpos('draft,changing', $oldStory->status) != false) $story->reviewedBy = '';

            $oldStory->reviewers = implode(',', array_keys($oldReviewer));
            $story->reviewers    = implode(',', array_keys($this->getReviewerPairs($storyID, $oldStory->version)));
        }

        $this->dao->update(TABLE_STORY)
            ->data($story, 'reviewers,spec,verify,finalResult,deleteFiles')
            ->autoCheck()
            ->batchCheck($this->config->story->edit->requiredFields, 'notempty')
            ->checkIF(isset($story->closedBy), 'closedReason', 'notempty')
            ->checkIF(isset($story->closedReason) and $story->closedReason == 'done', 'stage', 'notempty')
            ->checkIF(isset($story->closedReason) and $story->closedReason == 'duplicate',  'duplicateStory', 'notempty')
            ->checkIF($story->notifyEmail, 'notifyEmail', 'email')
            ->checkFlow()
            ->where('id')->eq((int)$storyID)->exec();
        if(dao::isError()) return false;

        if(!dao::isError())
        {
            $this->file->updateObjectID($this->post->uid, $storyID, 'story');
            $addedFiles = $this->file->saveUpload($oldStory->type, $storyID, $oldStory->version);

            if($story->spec != $oldStory->spec or $story->verify != $oldStory->verify or $story->title != $oldStory->title or !empty($story->deleteFiles) or !empty($addedFiles))
            {
                $addedFiles = empty($addedFiles) ? '' : join(',', array_keys($addedFiles)) . ',';
                $storyFiles = $oldStory->files = join(',', array_keys($oldStory->files));
                foreach($story->deleteFiles as $fileID) $storyFiles = str_replace(",$fileID,", ',', ",$storyFiles,");

                $data = new stdclass();
                $data->title  = $story->title;
                $data->spec   = $story->spec;
                $data->verify = $story->verify;
                $data->files  = $story->files = $addedFiles . trim($storyFiles, ',');
                $this->dao->update(TABLE_STORYSPEC)->data($data)->where('story')->eq((int)$storyID)->andWhere('version')->eq($oldStory->version)->exec();

                /* Sync twins. */
                if(!empty($oldStory->twins))
                {
                    foreach(explode(',', trim($oldStory->twins, ',')) as $twinID)
                    {
                        $this->dao->update(TABLE_STORYSPEC)->data($data)
                            ->where('story')->eq((int)$twinID)
                            ->andWhere('version')->eq($oldStory->version)
                            ->exec();
                    }
                }
            }

            if($story->product != $oldStory->product)
            {
                $this->updateStoryProduct($storyID, $story->product);
                if($oldStory->parent == '-1')
                {
                    $childStories = $this->dao->select('id')->from(TABLE_STORY)->where('parent')->eq($storyID)->andWhere('deleted')->eq(0)->fetchPairs('id');
                    foreach($childStories as $childStoryID) $this->updateStoryProduct($childStoryID, $story->product);
                }
            }

            $this->loadModel('action');

            if($this->config->edition == 'ipd' and !empty($oldStory->demand))
            {
                $otherURS = $this->dao->select('id')->from(TABLE_STORY)
                    ->where('product')->eq($oldStory->product)
                    ->andWhere('demand')->eq($oldStory->demand)
                    ->andWhere('type')->eq('requirement')
                    ->andWhere('deleted')->eq(0)
                    ->fetchPairs('id');

                $demand = $this->loadModel('demand')->getByID($oldStory->demand);
                $demand->product = trim($demand->product, ',') . ",$story->product";
                if(empty($otherURS)) $demand->product = str_replace(",$oldStory->product,", ',', ",$demand->product,");

                $demand->product = implode(',', array_unique(explode(',', $demand->product)));
                $this->dao->update(TABLE_DEMAND)->set('product')->eq(trim($demand->product, ','))->where('id')->eq($oldStory->demand)->exec();

                $distributedProducts = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in(trim($demand->product, ','))->fetchPairs();
                $actionExtra = '';
                foreach($distributedProducts as $productID => $productName) $actionExtra .= ", #$productID $productName";
                $this->action->create('demand', $oldStory->demand, 'ManageDistributedProducts', '', trim($actionExtra, ', '));
            }

            if($story->plan != $oldStory->plan)
            {
                if(!empty($oldStory->plan)) $this->action->create('productplan', $oldStory->plan, 'unlinkstory', '', $storyID);
                if(!empty($story->plan)) $this->action->create('productplan', $story->plan, 'linkstory', '', $storyID);
            }

            $changed = (isset($story->parent) && $story->parent != $oldStory->parent);
            if($oldStory->parent > 0)
            {
                $oldParentStory = $this->dao->select('*')->from(TABLE_STORY)->where('id')->eq($oldStory->parent)->fetch();
                $this->updateParentStatus($storyID, $oldStory->parent, !$changed);

                if($changed)
                {
                    $oldChildren = $this->dao->select('id')->from(TABLE_STORY)->where('parent')->eq($oldStory->parent)->andWhere('deleted')->eq(0)->fetchPairs('id', 'id');
                    if(empty($oldChildren)) $this->dao->update(TABLE_STORY)->set('parent')->eq(0)->where('id')->eq($oldStory->parent)->exec();
                    $this->dao->update(TABLE_STORY)->set('childStories')->eq(join(',', $oldChildren))->set('lastEditedBy')->eq($this->app->user->account)->set('lastEditedDate')->eq(helper::now())->where('id')->eq($oldStory->parent)->exec();
                    $this->action->create('story', $storyID, 'unlinkParentStory', '', $oldStory->parent, '', false);

                    $actionID = $this->action->create('story', $oldStory->parent, 'unLinkChildrenStory', '', $storyID, '', false);

                    $newParentStory = $this->dao->select('*')->from(TABLE_STORY)->where('id')->eq($oldStory->parent)->fetch();
                    $changes = common::createChanges($oldParentStory, $newParentStory);
                    if(!empty($changes)) $this->action->logHistory($actionID, $changes);
                }
            }

            if(isset($story->parent) && $story->parent > 0)
            {
                $parentStory = $this->dao->select('*')->from(TABLE_STORY)->where('id')->eq($story->parent)->fetch();
                $this->dao->update(TABLE_STORY)->set('parent')->eq(-1)->where('id')->eq($story->parent)->exec();
                $this->updateParentStatus($storyID, $story->parent, !$changed);

                if($changed)
                {
                    $children = $this->dao->select('id')->from(TABLE_STORY)->where('parent')->eq($story->parent)->andWhere('deleted')->eq(0)->fetchPairs('id', 'id');
                    $this->dao->update(TABLE_STORY)
                        ->set('parent')->eq('-1')
                        ->set('childStories')->eq(join(',', $children))
                        ->set('lastEditedBy')->eq($this->app->user->account)
                        ->set('lastEditedDate')->eq(helper::now())
                        ->where('id')->eq($story->parent)
                        ->exec();

                    $this->action->create('story', $storyID, 'linkParentStory', '', $story->parent, '', false);
                    $actionID = $this->action->create('story', $story->parent, 'linkChildStory', '', $storyID, '', false);

                    $newParentStory = $this->dao->select('*')->from(TABLE_STORY)->where('id')->eq($story->parent)->fetch();
                    $changes = common::createChanges($parentStory, $newParentStory);
                    if(!empty($changes)) $this->action->logHistory($actionID, $changes);
                }
            }

            if(isset($story->closedReason) and $story->closedReason == 'done') $this->loadModel('score')->create('story', 'close');

            /* Set new stage and update story sort of plan when story plan has changed. */
            if($oldStory->plan != $story->plan)
            {
                $this->updateStoryOrderOfPlan($storyID, $story->plan, $oldStory->plan); // Insert a new story sort in this plan.

                if(empty($oldStory->plan) or empty($story->plan)) $this->setStage($storyID); // Set new stage for this story.
            }

            if(isset($story->stage) and $oldStory->stage != $story->stage)
            {
                $executionIdList = $this->dao->select('t1.project')->from(TABLE_PROJECTSTORY)->alias('t1')
                    ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
                    ->where('t1.story')->eq($storyID)
                    ->andWhere('t2.deleted')->eq(0)
                    ->andWhere('t2.type')->in('sprint,stage,kanban')
                    ->fetchPairs();

                $this->loadModel('kanban');
                foreach($executionIdList as $executionID) $this->kanban->updateLane($executionID, 'story', $storyID);
            }

            unset($oldStory->parent);
            unset($story->parent);
            if($this->config->edition != 'open' && $oldStory->feedback) $this->loadModel('feedback')->updateStatus('story', $oldStory->feedback, $story->status, $oldStory->status);

            $linkStoryField = $oldStory->type == 'story' ? 'linkStories' : 'linkRequirements';
            $linkStories    = explode(',', $story->{$linkStoryField});
            $oldLinkStories = explode(',', $oldStory->{$linkStoryField});
            $addStories     = array_diff($linkStories, $oldLinkStories);
            $removeStories  = array_diff($oldLinkStories, $linkStories);
            $changeStories  = array_merge($addStories, $removeStories);
            $changeStories  = $this->dao->select("id,$linkStoryField")->from(TABLE_STORY)->where('id')->in(array_filter($changeStories))->fetchPairs();
            foreach($changeStories as $changeStoryID => $changeStory)
            {
                if(in_array($changeStoryID, $addStories))
                {
                    $stories = empty($changeStory) ? $storyID : $changeStory . ',' . $storyID;
                    $this->dao->update(TABLE_STORY)->set($linkStoryField)->eq($stories)->where('id')->eq((int)$changeStoryID)->exec();
                }

                if(in_array($changeStoryID, $removeStories))
                {
                    $linkStories = str_replace(",$storyID,", ',', ",$changeStory,");
                    $this->dao->update(TABLE_STORY)->set($linkStoryField)->eq(trim($linkStories, ','))->where('id')->eq((int)$changeStoryID)->exec();
                }
            }

            $changes = common::createChanges($oldStory, $story);
            if($this->post->uid != '' and isset($_SESSION['album']['used'][$this->post->uid])) $files = $this->file->getPairs($_SESSION['album']['used'][$this->post->uid]);

            if($this->post->comment != '' or !empty($changes))
            {
                $action   = !empty($changes) ? 'Edited' : 'Commented';
                $actionID = $this->action->create('story', $storyID, $action, $this->post->comment);
                $this->action->logHistory($actionID, $changes);

                if(isset($story->finalResult)) $this->recordReviewAction($story);
            }

            if(!empty($oldStory->twins)) $this->syncTwins($oldStory->id, $oldStory->twins, $changes, 'Edited');

            return true;
        }
    }
}
