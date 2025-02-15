<?php
class excelStory extends StoryModel
{
    public function setListValue($productID, $branch = 0)
    {
        $product    = $this->loadModel('product')->getByID($productID);
        $modules    = $this->loadModel('tree')->getOptionMenu($productID, 'story', 0, 'all');
        $plans      = $this->loadModel('productplan')->getPairs($productID, '', 'unexpired', true);
        $priList    = $this->lang->story->priList;
        $sourceList = $this->lang->story->sourceList;

        unset($plans['']);
        foreach($modules  as $id => $module) $modules[$id] .= "(#$id)";
        foreach($plans    as $id => $plan) $plans[$id] .= "(#$id)";

        if($product->type != 'normal')
        {
            $this->config->story->export->listFields[] = 'branch';

            $branches = $this->loadModel('branch')->getPairs($product->id);
            foreach($branches as $id => $branch) $branches[$id] .= "(#$id)";

            $this->post->set('branchList',   array_values($branches));
        }

        if($this->config->edition != 'open') $this->loadModel('workflowfield')->setFlowListValue('story');

        $this->post->set('moduleList', array_values($modules));
        $this->post->set('planList',   array_values($plans));
        $this->post->set('priList',    join(',', $priList));
        $this->post->set('sourceList', array_values($sourceList));
        $this->post->set('listStyle',  $this->config->story->export->listFields);
        $this->post->set('extraNum',   0);
        $this->post->set('product',    $product->name);
    }

    public function createFromImport($productID, $branch = 0, $type = 'story', $projectID = 0)
    {
        $this->loadModel('action');
        $this->loadModel('story');
        $this->loadModel('file');

        $forceReview = $this->story->checkForceReview();

        foreach($_POST['title'] as $index => $title)
        {
            if($_POST['title'][$index] and isset($_POST['reviewer'][$index])) $_POST['reviewer'][$index] = array_filter($_POST['reviewer'][$index]);
            if(empty($_POST['reviewer'][$index]) and $forceReview)
            {
                dao::$errors[] = $this->lang->story->errorEmptyReviewedBy;
                return false;
            }
        }

        $now    = helper::now();
        $branch = (int)$branch;
        $data   = fixer::input('post')->get();

        $this->app->loadClass('purifier', true);
        $purifierConfig = HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Filter.YouTube', 1);
        $purifier = new HTMLPurifier($purifierConfig);

        if(!empty($_POST['id']))
        {
            $oldStories = $this->dao->select('*')->from(TABLE_STORY)->where('id')->in(($_POST['id']))->andWhere('product')->eq($productID)->fetchAll('id');
            $oldSpecs   = $this->dao->select('*')->from(TABLE_STORYSPEC)->where('story')->in(array_keys($oldStories))->orderBy('version')->fetchAll('story');
        }

        $stories      = array();
        $line         = 1;
        $planIdList   = array();
        $extendFields = array();

        if($this->config->edition != 'open')
        {
            $extendFields = $this->getFlowExtendFields();
            $notEmptyRule = $this->loadModel('workflowrule')->getByTypeAndRule('system', 'notempty');

            foreach($extendFields as $extendField)
            {
                if(strpos(",$extendField->rules,", ",$notEmptyRule->id,") !== false)
                {
                    $this->config->story->create->requiredFields .= ',' . $extendField->field;
                }
            }
        }

        foreach($data->product as $key => $product)
        {
            $storyData = new stdclass();
            $specData  = new stdclass();

            $storyData->product    = $product;
            $storyData->branch     = isset($data->branch[$key]) ? (int)$data->branch[$key] : $branch;
            $storyData->module     = (int)$data->module[$key];
            $storyData->plan       = isset($data->plan[$key]) ? (int)$data->plan[$key] : 0;
            $storyData->source     = $data->source[$key];
            $storyData->sourceNote = $data->sourceNote[$key];
            $storyData->title      = trim($data->title[$key]);
            $storyData->pri        = (int)$data->pri[$key];
            $storyData->estimate   = (float)$data->estimate[$key];
            $storyData->keywords   = $data->keywords[$key];
            $storyData->type       = $type;
            $storyData->vision     = $this->config->vision;

            $specData->title  = $storyData->title;
            $specData->spec   = nl2br($purifier->purify($this->post->spec[$key]));
            $specData->verify = nl2br($purifier->purify($this->post->verify[$key]));

            if(empty($specData->title)) continue;

            foreach($extendFields as $extendField)
            {
                $dataArray = $_POST[$extendField->field];
                $storyData->{$extendField->field} = $dataArray[$key];
                if(is_array($storyData->{$extendField->field})) $storyData->{$extendField->field} = join(',', $storyData->{$extendField->field});

                $storyData->{$extendField->field} = htmlSpecialString($storyData->{$extendField->field});
            }

            if(isset($this->config->story->create->requiredFields))
            {
                $requiredFields = explode(',', $this->config->story->create->requiredFields);
                foreach($requiredFields as $requiredField)
                {
                    $requiredField = trim($requiredField);
                    $tmpData = $storyData;
                    if($requiredField == 'spec' || $requiredField == 'verify') $tmpData = $specData;
                    if(empty($tmpData->$requiredField)) dao::$errors[] = sprintf($this->lang->story->noRequire, $line, $this->lang->story->$requiredField);
                }
            }

            $stories[$key]['storyData'] = $storyData;
            $stories[$key]['specData']  = $specData;
            $line++;

            $planIdList[$storyData->plan] = $storyData->plan;
        }
        if(dao::isError()) die(js::error(dao::getError()));

        $maxPlanStoryOrders = $this->dao->select('plan,max(`order`) as `order`')->from(TABLE_PLANSTORY)->where('plan')->in($planIdList)->groupBy('plan')->fetchPairs('plan', 'order');
        foreach($stories as $key => $newStory)
        {
            $storyData = $newStory['storyData'];
            $specData  = $newStory['specData'];

            $storyID = 0;
            $version = 0;
            if(!empty($_POST['id'][$key]) and empty($_POST['insert']))
            {
                $storyID = $data->id[$key];
                if(!isset($oldStories[$storyID])) $storyID = 0;
            }

            if($storyID)
            {
                $specData->spec   = str_replace('src="' . common::getSysURL() . '/', 'src="', $specData->spec);
                $specData->verify = str_replace('src="' . common::getSysURL() . '/', 'src="', $specData->verify);

                $oldSpec  = (array)$oldSpecs[$storyID];
                $newSpec  = (array)$specData;
                $oldStory = $oldStories[$storyID];

                /* Ignore updating stories for different products. */
                if($oldStory->product != $storyData->product) continue;

                $oldSpec['spec']   = trim($this->file->excludeHtml($oldSpec['spec'], 'noImg'));
                $oldSpec['verify'] = trim($this->file->excludeHtml($oldSpec['verify'], 'noImg'));
                $newSpec['spec']   = trim($this->file->excludeHtml($newSpec['spec'], 'noImg'));
                $newSpec['verify'] = trim($this->file->excludeHtml($newSpec['verify'], 'noImg'));
                $storyChanges = common::createChanges($oldStory, $storyData);
                $specChanges  = common::createChanges((object)$oldSpec, (object)$newSpec);

                if($specChanges)
                {
                    $storyData->version      = $oldStory->version + 1;
                    $storyData->reviewedBy   = '';
                    $storyData->closedBy     = '';
                    $storyData->closedReason = '';
                    $storyData->status       = (empty($_POST['reviewer'][$key]) and !$forceReview) ? 'active' : 'reviewing';
                    if($oldStory->reviewedBy) $storyData->reviewedDate   = '0000-00-00';
                    if($oldStory->closedBy) $storyData->closedDate       = '0000-00-00';

                    $newSpecData = $oldSpecs[$storyID];
                    $newSpecData->version += 1;

                    $version = $storyData->version;

                    foreach($specChanges as $specChange)$newSpecData->{$specChange['field']} = $specData->{$specChange['field']};
                }

                if($storyChanges or $specChanges)
                {
                    $storyData->lastEditedBy   = $this->app->user->account;
                    $storyData->lastEditedDate = $now;
                    $this->dao->update(TABLE_STORY)
                        ->data($storyData)
                        ->autoCheck()
                        ->checkFlow()
                        ->batchCheck($this->config->story->change->requiredFields, 'notempty')
                        ->where('id')->eq((int)$storyID)->exec();

                    if(!dao::isError())
                    {
                        if($specChanges)
                        {
                            $this->dao->insert(TABLE_STORYSPEC)->data($newSpecData)->exec();
                            $actionID = $this->action->create('story', $storyID, 'Changed', '');
                            $this->action->logHistory($actionID, $specChanges);
                        }

                        if($oldStory->plan != $storyData->plan)
                        {
                            if($oldStory->plan) $this->dao->delete()->from(TABLE_PLANSTORY)->where('plan')->eq($oldStory->plan)->andWhere('story')->eq($storyID)->exec();
                            if($storyData->plan)
                            {
                                $maxOrder  = (int)zget($maxPlanStoryOrders, $storyData->plan, 0) + 1;
                                $planStory = new stdclass();
                                $planStory->plan  = $storyData->plan;
                                $planStory->story = $storyID;
                                $planStory->order = $maxOrder;
                                $this->dao->replace(TABLE_PLANSTORY)->data($planStory)->exec();

                                $maxPlanStoryOrders[$storyData->plan] = $maxOrder;
                            }
                        }

                        if($storyChanges)
                        {
                            $actionID = $this->action->create('story', $storyID, 'Edited', '');
                            $this->action->logHistory($actionID, $storyChanges);
                        }
                    }
                }
            }
            else
            {
                $storyData->status     = (empty($_POST['reviewer'][$key]) and !$forceReview) ? 'active' : 'reviewing';
                $storyData->version    = $version = 1;
                if($storyData->plan > 0) $storyData->stage = 'planned';
                $storyData->openedBy   = $this->app->user->account;
                $storyData->openedDate = $now;

                $this->dao->insert(TABLE_STORY)->data($storyData)->autoCheck()->checkFlow()->exec();

                if(!dao::isError())
                {
                    $storyID = $this->dao->lastInsertID();
                    $specData->story   = $storyID;
                    $specData->version = 1;
                    $this->dao->insert(TABLE_STORYSPEC)->data($specData)->exec();

                    if($projectID)
                    {
                        $projectStory = new stdclass();
                        $projectStory->project = $projectID;
                        $projectStory->product = $storyData->product;
                        $projectStory->story   = $storyID;
                        $projectStory->version = $storyData->version;

                        $this->dao->insert(TABLE_PROJECTSTORY)->data($projectStory)->exec();
                        $this->setStage($storyID);
                    }

                    if($storyData->plan)
                    {
                        $maxOrder  = (int)zget($maxPlanStoryOrders, $storyData->plan, 0) + 1;
                        $planStory = new stdclass();
                        $planStory->plan  = $storyData->plan;
                        $planStory->story = $storyID;
                        $planStory->order = $maxOrder;
                        $this->dao->replace(TABLE_PLANSTORY)->data($planStory)->exec();

                        $maxPlanStoryOrders[$storyData->plan] = $maxOrder;
                    }

                    $this->action->create('story', $storyID, 'Opened', '');
                }
            }

            /* Save the story reviewer to storyreview table. */
            if(isset($_POST['reviewer'][$key]))
            {
                $assignedTo = '';
                foreach($_POST['reviewer'][$key] as $reviewer)
                {
                    if(empty($reviewer)) continue;

                    $reviewData = new stdclass();
                    $reviewData->story    = $storyID;
                    $reviewData->version  = $version;
                    $reviewData->reviewer = $reviewer;
                    $this->dao->insert(TABLE_STORYREVIEW)->data($reviewData)->exec();

                    if(empty($assignedTo)) $assignedTo = $reviewer;
                }
                if($assignedTo) $this->dao->update(TABLE_STORY)->set('assignedTo')->eq($assignedTo)->set('assignedDate')->eq($now)->where('id')->eq($storyID)->exec();
            }
        }

        if($this->post->isEndPage)
        {
            unlink($this->session->fileImportFileName);
            unset($_SESSION['fileImportFileName']);
            unset($_SESSION['fileImportExtension']);
        }
    }

    public function replaceUserRequirementLang()
    {
        $SRCommon = $this->lang->SRCommon;
        $URCommon = $this->lang->URCommon;

        $this->lang->story->title          = str_replace($SRCommon, $URCommon, $this->lang->story->title);
        $this->lang->story->importCase     = str_replace($SRCommon, $URCommon, $this->lang->story->importCase);
        $this->lang->story->num            = str_replace($SRCommon, $URCommon, $this->lang->story->num);
        $this->lang->story->linkStories    = str_replace($URCommon, $SRCommon, $this->lang->story->linkStories);
        $this->lang->story->duplicateStory = str_replace($SRCommon, $URCommon, $this->lang->story->duplicateStory);
        $this->lang->excel->help->story    = str_replace($SRCommon, $URCommon, $this->lang->excel->help->story);
    }
}
