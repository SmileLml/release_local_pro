<?php
class demandModel extends model
{
    /**
     * Get demand list.
     *
     * @param  int    $poolID
     * @param  int    $browseType
     * @param  int    $queryID
     * @param  int    $orderBy
     * @param  int    $pager
     * @param  string $extra
     * @param  int    $hasChilds
     * @access public
     * @return void
     */
    public function getList($poolID = 0, $browseType = 'assignedtome', $queryID = 0, $orderBy = 'id_desc', $pager = null, $extra = '', $hasChilds = false)
    {
        $demandQuery = '';
        if($browseType == 'bysearch')
        {
            $query = $queryID ? $this->loadModel('search')->getQuery($queryID) : '';
            if($query)
            {
                $this->session->set('demandQuery', $query->sql);
                $this->session->set('demandForm', $query->form);
            }

            if($this->session->demandQuery == false) $this->session->set('demandQuery', ' 1 = 1');

            $demandQuery = $this->session->demandQuery;
            $pools       = $this->loadModel('demandpool')->getPairs();
            /* Limit current pool when no pool. */
            if(strpos($demandQuery, "`pool` =") === false) $demandQuery = $demandQuery . " AND `pool` = $poolID";
            $poolQuery   = "`pool` " . helper::dbIN(array_keys($pools));
            $demandQuery = str_replace("`pool` = 'all'", $poolQuery, $demandQuery); // Search all pool.
        }

        $demands = $this->dao->select('*')->from(TABLE_DEMAND)
            ->where('deleted')->eq('0')
            ->beginIF($browseType != 'bysearch' && $poolID)->andWhere('pool')->eq($poolID)->fi()
            ->beginIF($browseType != 'all' and $browseType != 'bysearch' and $browseType != 'assignedtome')->andWhere('status')->eq($browseType)->fi()
            ->beginIF($browseType == 'assignedtome')->andWhere('assignedTo')->eq($this->app->user->account)->fi()
            ->beginIF($browseType == 'bysearch')->andWhere($demandQuery)->fi()
            ->beginIF($browseType == 'pass')->andWhere('parent')->ne('-1')->fi()
            ->beginIF(strpos($extra, 'nodeleted') !== false)->andWhere('status')->ne('deleted')->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'demand', $browseType != 'bysearch');

        $parents = array();
        foreach($demands as $demand)
        {
            if($demand->parent) $parents[] = $demand->parent;
        }

        $parents = $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->in($parents)->fetchAll('id');
        if($hasChilds and $demands)
        {
            foreach($demands as $demand)
            {
                if($demand->parent != 0 and isset($demands[$demand->parent]))
                {
                    $demands[$demand->parent]->children[$demand->id] = $demand;
                    unset($demands[$demand->id]);
                }

                if($demand->parent > 0 and !isset($demands[$demand->parent]))
                {
                    $parent = $parents[$demand->parent];
                    $demand->parentName = $parent->title;
                }
            }
        }

        return $demands;
    }

    /**
     * Get demand list.
     *
     * @param  array  $idList
     * @access public
     * @return array
     */
    public function getByList($idList)
    {
        return $this->dao->select('*')->from(TABLE_DEMAND)
            ->where('deleted')->eq(0)
            ->andWhere('id')->in($idList)
            ->fetchAll('id');
    }

    /**
     * Get demand pairs.
     *
     * @param  int    $poolID
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function getPairs($poolID = 0, $orderBy = 'id_desc')
    {
        $demands = $this->dao->select('id,title')->from(TABLE_DEMAND)
            ->where('deleted')->eq(0)
            ->andWhere('pool')->eq($poolID)
            ->orderBy($orderBy)
            ->fetchPairs();

        return $demands;
    }

    /**
     * Get demand by id.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function getByID($demandID, $version = 0)
    {
        $demand = $this->dao->findByID($demandID)->from(TABLE_DEMAND)->fetch();
        if(!$demand) return array();

        if($version == 0) $version = $demand->version;
        $spec = $this->dao->select('title,spec,verify')->from(TABLE_DEMANDSPEC)->where('demand')->eq($demandID)->andWhere('version')->eq($version)->fetch();
        $demand->title  = !empty($spec->title)  ? $spec->title  : $demand->title;
        $demand->spec   = !empty($spec->spec)   ? $spec->spec   : '';
        $demand->verify = !empty($spec->verify) ? $spec->verify : '';
        $demand->poolName = $this->dao->select('*')->from(TABLE_DEMANDPOOL)->where('id')->eq($demand->pool)->fetch('name');

        $demand->files = $this->loadModel('file')->getByObject('demand', $demandID);

        $demand->reviewerResult = $this->getReviewerPairs($demandID, $demand->version);
        $demand->reviewers      = !empty($demand->reviewerResult) ? implode(',', array_keys($demand->reviewerResult)) : '';

        /* Check parent demand. */
        if($demand->parent > 0) $demand->parentName = $this->dao->findById($demand->parent)->from(TABLE_DEMAND)->fetch('title');

        $demand = $this->loadModel('file')->replaceImgURL($demand, 'spec,verify');

        $reviewerList     = $this->getReviewerPairs($demand->id, $demand->version);
        $demand->reviewer = array_keys($reviewerList);
        $demand->children = array();
        if($demand->parent == '-1') $demand->children = $this->dao->select('*')->from(TABLE_DEMAND)->where('parent')->eq($demandID)->andWhere('deleted')->eq(0)->fetchAll('id');
        $demand->stories = $this->getDemandStories($demandID);

        return $demand;
    }

    /**
     * Get demand stories.
     *
     * @param  int          $demandID
     * @param  string|array $productList
     * @access public
     * @return array
     */
    public function getDemandStories($demandID, $productList = '')
    {
        return $this->dao->select('*')->from(TABLE_STORY)
            ->where('deleted')->eq(0)
            ->andWhere('demand')->eq($demandID)
            ->andWhere('retractedBy')->eq('')
            ->beginIF(!empty($productList))->andWhere('product')->in($productList)->fi()
            ->fetchAll('id');
    }

    /**
     * Create a demand.
     *
     * @access public
     * @return void
     */
    public function create($poolID = 0)
    {
        if(isset($_POST['reviewer'])) $_POST['reviewer'] = array_filter($_POST['reviewer']);
        if(!$this->post->needNotReview and empty($_POST['reviewer']))
        {
            dao::$errors['reviewer'] = sprintf($this->lang->error->notempty, $this->lang->demand->reviewer);
            return false;
        }

        $today = helper::today();
        $now   = helper::now();
        $demand = fixer::input('post')
            ->add('version', 1)
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $now)
            ->setDefault('status', 'pass')
            ->setIF($this->post->assignedTo, 'assignedDate', $now)
            ->setIF($this->post->reviewer, 'status', 'reviewing')
            ->setIF($this->post->status == 'draft', 'status', 'draft')
            ->remove('uid,files,labels,contactListMenu,needNotReview,reviewer,undetermined,undeterminedProduct,deleteFiles')
            ->join('product', ',')
            ->join('mailto', ',')
            ->join('reviewer', ',')
            ->stripTags($this->config->demand->editor->create['id'], $this->config->allowedTags)
            ->get();

        if(isset($_POST['undetermined'])) $demand->product = '';

        $demand = $this->loadModel('file')->processImgURL($demand, $this->config->demand->editor->create['id'], $this->post->uid);
        $this->dao->insert(TABLE_DEMAND)->data($demand, 'spec,verify')
            ->autoCheck('pool')
            ->batchCheck($this->config->demand->create->requiredFields, 'notempty')->exec();

        if(!dao::isError())
        {
            $demandID = $this->dao->lastInsertID();

            $this->loadModel('file')->updateObjectID($this->post->uid, $demandID, 'demand');
            $files = $this->file->saveUpload('demand', $demandID);

            $data          = new stdclass();
            $data->demand  = $demandID;
            $data->version = 1;
            $data->title   = $demand->title;
            $data->spec    = $demand->spec;
            $data->verify  = $demand->verify;
            $data->files   = join(',', array_keys($files));
            $this->dao->insert(TABLE_DEMANDSPEC)->data($data)->exec();

            /* Save reviewers. */
            if(isset($_POST['reviewer']))
            {
                foreach($this->post->reviewer as $reviewer)
                {
                    if(empty($reviewer)) continue;

                    $reviewData = new stdclass();
                    $reviewData->demand   = $demandID;
                    $reviewData->version  = 1;
                    $reviewData->reviewer = $reviewer;
                    $this->dao->insert(TABLE_DEMANDREVIEW)->data($reviewData)->exec();
                }
            }

            if($demand->parent > 0)
            {
                $parent = $this->dao->findById($demand->parent)->from(TABLE_DEMAND)->fetch();

                /* Set parent to child demand. */
                $this->dao->update(TABLE_DEMAND)->set('parent')->eq($demand->parent)->where('id')->eq($demandID)->exec();

                $newDemand = new stdClass();
                $newDemand->parent         = '-1';
                $newDemand->lastEditedBy   = $this->app->user->account;
                $newDemand->lastEditedDate = helper::now();
                $newDemand->childDemands   = trim($parent->childDemands . ',' . $demandID, ',');

                /* Subdivide demand. */
                $this->dao->update(TABLE_DEMAND)->data($newDemand)->autoCheck()->where('id')->eq($demand->parent)->exec();

                $changes = common::createChanges($parent, $newDemand);
                if($changes)
                {
                    $actionID = $this->loadModel('action')->create('demand', $demand->parent, 'createChildrenDemand');
                    $this->action->logHistory($actionID, $changes);
                }

            }

            if($this->post->feedback || $this->post->ticket) $fileIDPairs = $this->loadModel('file')->copyObjectFiles('demand');
            if($demandID && ($this->post->feedback || $this->post->ticket))
            {
                /* If demand is from feedback, record action for feedback and add files to demand from feedback. */
                if($this->post->feedback)
                {
                    $feedbackID = $this->post->feedback;
                    $objectID   = $feedbackID;
                    $objectType = 'feedback';

                    $feedback = new stdclass();
                    $feedback->result   = $demandID;
                    $feedback->solution = 'todemand';

                    $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($feedbackID)->exec();

                    $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'ToDemand', '', $demandID);
                }

                /* If demand is from feedback, record action for feedback and add files to demand from feedback. */
                if($this->post->ticket)
                {
                    $ticketID   = $this->post->ticket;
                    $objectID   = $ticketID;
                    $objectType = 'ticket';

                    $ticket = new stdClass();
                    $ticket->ticketId   = $ticketID;
                    $ticket->objectId   = $demandID;
                    $ticket->objectType = 'demand';

                    $this->dao->insert(TABLE_TICKETRELATION)->data($ticket)->exec();

                    $actionID = $this->loadModel('action')->create('ticket', $ticketID, 'ToDemand', '', $demandID);
                }

                if(isset($objectID) && !empty($fileIDPairs))
                {
                    if(!empty($fileIDPairs)) $this->dao->update(TABLE_FILE)->set('objectID')->eq($demandID)->where('id')->in($fileIDPairs)->exec();
                }
            }
            return $demandID;
        }

        return false;
    }

    /**
     * Batch create demands.
     *
     * @param  int    $poolID
     * @access public
     * @return void
     */
    public function batchCreate($poolID = 0)
    {
        $this->loadModel('common');
        $today   = helper::today();
        $demands = fixer::input('post')->get();

        foreach($demands->title as $i => $title)
        {
            if(empty($title) and $this->common->checkValidRow('demand', $demands, $i))
            {
                dao::$errors["title$i"][] = sprintf($this->lang->error->notempty, $this->lang->demand->title);
            }

            $pri        = $demands->pri[$i] == 'ditto' ? $pri : $demands->pri[$i];
            $assignedTo = $demands->assignedTo[$i] == 'ditto' ? $assignedTo : $demands->assignedTo[$i];
            $BSA        = $demands->BSA[$i] == 'ditto' ? $BSA : $demands->BSA[$i];
            $duration   = $demands->duration[$i] == 'ditto' ? $duration : $demands->duration[$i];
            $source     = $demands->source[$i] == 'ditto' ? $source : $demands->source[$i];

            $demands->pri[$i]        = $pri;
            $demands->assignedTo[$i] = $assignedTo;
            $demands->BSA[$i]        = $BSA;
            $demands->duration[$i]   = $duration;
            $demands->source[$i]     = $source;

            foreach(explode(',', $this->config->demand->batchCreate->requiredFields) as $field)
            {
                $field = trim($field);
                if($field == 'title') continue;
                if($field and empty($demand->$field))
                {
                    dao::$errors['message'][] = sprintf($this->lang->error->notempty, $this->lang->demand->$field);
                }
            }
        }

        if(dao::isError()) return false;

        if(isset($demands->uploadImage)) $this->loadModel('file');

        $demandIdList = array();
        $this->loadModel('action');
        foreach($demands->title as $i => $title)
        {
            if(!$title) continue;

            $demand = new stdclass();
            $demand->title       = $title;
            $demand->pool        = $poolID;
            $demand->color       = $demands->color[$i];
            $demand->source      = $demands->source[$i];
            $demand->duration    = $demands->duration[$i];
            $demand->product     = isset($demands->products[$i]) ? implode(',', $demands->products[$i]) : '';
            $demand->BSA         = $demands->BSA[$i];
            $demand->pri         = $demands->pri[$i];
            $demand->category    = $demands->category[$i];
            $demand->assignedTo  = $demands->assignedTo[$i];
            $demand->keywords    = $demands->keywords[$i];
            $demand->status      = 'draft';
            $demand->version     = 1;
            $demand->createdDate = helper::now();
            $demand->createdBy   = $this->app->user->account;

            $this->dao->insert(TABLE_DEMAND)->data($demand)->autoCheck()->exec();

            if(!dao::isError())
            {
                $demandID = $this->dao->lastInsertID();

                $specData = new stdclass();
                $specData->demand  = $demandID;
                $specData->version = 1;
                $specData->title   = $demand->title;
                $specData->spec    = '';
                $specData->verify  = '';
                if(!empty($demands->spec[$i]))  $specData->spec   = nl2br($demands->spec[$i]);
                if(!empty($demands->verify[$i]))$specData->verify = nl2br($demands->verify[$i]);

                /* Batch upload images. */
                if(!empty($demands->uploadImage[$i]) and $demands->uploadImage[$i] !== 'undefined')
                {
                    $fileName = $demands->uploadImage[$i];
                    $file     = $this->session->demandImagesFile[$fileName];

                    $realPath = $file['realpath'];
                    unset($file['realpath']);

                    if(!is_dir($this->file->savePath)) mkdir($this->file->savePath, 0777, true);
                    if($realPath and rename($realPath, $this->file->savePath . $this->file->getSaveName($file['pathname'])))
                    {
                        $file['addedBy']    = $this->app->user->account;
                        $file['addedDate']  = $now;
                        $file['objectType'] = 'demand';
                        $file['objectID']   = $demandID;
                        if(in_array($file['extension'], $this->config->file->imageExtensions))
                        {
                            $file['extra'] = 'editor';
                            $this->dao->insert(TABLE_FILE)->data($file)->exec();

                            $fileID = $this->dao->lastInsertID();
                            $specData->spec .= '<img src="{' . $fileID . '.' . $file['extension'] . '}" alt="" />';
                        }
                        else
                        {
                            $this->dao->insert(TABLE_FILE)->data($file)->exec();
                        }
                    }
                }

                $this->dao->insert(TABLE_DEMANDSPEC)->data($specData)->exec();

                $this->action->create('demand', $demandID, 'created');
                $demandIdList[$demandID] = $demandID;
            }
        }

        /* Remove upload image file and session. */
        if(!empty($demands->uploadImage) and $this->session->demandImagesFile)
        {
            $classFile = $this->app->loadClass('zfile');
            $file = current($_SESSION['demandImagesFile']);
            $realPath = dirname($file['realpath']);
            if(is_dir($realPath)) $classFile->removeDir($realPath);
            unset($_SESSION['demandImagesFile']);
        }

        return $demandIdList;
    }

    /**
     * Create demands from import.
     *
     * @param  int    $poolID
     * @access public
     * @return void
     */
    public function createFromImport($poolID)
    {
        $this->loadModel('action');
        $this->loadModel('file');

        $today = helper::today();
        $now   = helper::now();
        $data  = fixer::input('post')->get();

        $forceReview = $this->checkForceReview();

        if(!empty($_POST['id']))
        {
            $oldDemands = $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->in(($data->id))->fetchAll('id');
            $oldSpecs   = $this->dao->select('*')->from(TABLE_DEMANDSPEC)->where('demand')->in(array_keys($oldDemands))->orderBy('version')->fetchAll('demand');
        }

        $this->app->loadClass('purifier', true);
        $purifierConfig = HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Filter.YouTube', 1);
        $purifier = new HTMLPurifier($purifierConfig);

        $demands = array();
        $line    = 1;
        foreach($data->title as $key => $title)
        {
            $hasEmptyField = false;
            if(isset($this->config->demand->create->requiredFields))
            {
                $requiredFields = explode(',', $this->config->demand->create->requiredFields);
                foreach($requiredFields as $requiredField)
                {
                    $requiredField = trim($requiredField);
                    if(empty($data->$requiredField[$key]))
                    {
                        dao::$errors[] = sprintf($this->lang->demand->noRequire, $line, $this->lang->demand->$requiredField);
                        $hasEmptyField = true;
                    }
                }
            }
            $line ++;
            if($hasEmptyField || empty($data->title[$key])) continue;

            $demandData = new stdclass();
            $specData   = new stdclass();

            $demandData->pool       = (int)$poolID;
            $demandData->title      = $data->title[$key];
            $demandData->product    = empty($data->product[$key])    ? 0  : implode(',', $data->product[$key]);
            $demandData->sourceNote = empty($data->sourceNote[$key]) ? '' : $data->sourceNote[$key];
            $demandData->assignedTo = empty($data->assignedTo[$key]) ? '' : $data->assignedTo[$key];
            $demandData->duration   = empty($data->duration[$key])   ? '' : $data->duration[$key];
            $demandData->category   = empty($data->category[$key])   ? '' : $data->category[$key];
            $demandData->source     = empty($data->source[$key])     ? '' : $data->source[$key];
            $demandData->BSA        = empty($data->BSA[$key])        ? '' : $data->BSA[$key];
            $demandData->pri        = empty($data->pri[$key])        ? '' : (int)$data->pri[$key];
            $demandData->keywords   = empty($data->keywords[$key])   ? '' : $data->keywords[$key];

            $specData->title  = $demandData->title;
            $specData->spec   = empty($data->spec[$key])   ? '' : nl2br($purifier->purify($data->spec[$key]));
            $specData->verify = empty($data->verify[$key]) ? '' : nl2br($purifier->purify($data->verify[$key]));

            $demands[$key]['demandData'] = $demandData;
            $demands[$key]['specData']   = $specData;
        }
        if(dao::isError()) die(js::error(dao::getError()));

        foreach($demands as $key => $newDemand)
        {
            $demandData = $newDemand['demandData'];
            $specData   = $newDemand['specData'];

            $demandID = 0;
            $version  = 0;

            if(!empty($_POST['id'][$key]) and empty($_POST['insert']))
            {
                $demandID = $data->id[$key];
                if(!isset($oldDemands[$demandID])) $demandID = 0;
            }

            if($demandID)
            {
                $specData->spec   = str_replace('src="' . common::getSysURL() . '/', 'src="', $specData->spec);
                $specData->verify = str_replace('src="' . common::getSysURL() . '/', 'src="', $specData->verify);

                $oldSpec   = (array)$oldSpecs[$demandID];
                $newSpec   = (array)$specData;
                $oldDemand = $oldDemands[$demandID];

                /* Ignore updating demands for different products. */
                if(!empty($oldDemand->product) && !empty($demandData->product) && $oldDemand->product != $demandData->product) continue;

                $oldSpec['spec']   = trim($this->file->excludeHtml($oldSpec['spec'], 'noImg'));
                $oldSpec['verify'] = trim($this->file->excludeHtml($oldSpec['verify'], 'noImg'));
                $newSpec['spec']   = trim($this->file->excludeHtml($newSpec['spec'], 'noImg'));
                $newSpec['verify'] = trim($this->file->excludeHtml($newSpec['verify'], 'noImg'));

                $demandChanges = common::createChanges($oldDemand, $demandData);
                $specChanges   = common::createChanges((object)$oldSpec, (object)$newSpec);

                if($specChanges)
                {
                    $demandData->version      = $oldDemand->version + 1;
                    $demandData->reviewedBy   = '';
                    $demandData->closedBy     = '';
                    $demandData->closedReason = '';
                    $demandData->status       = (!empty($_POST['reviewer'][$key]) and $forceReview) ? 'reviewing' : 'draft';

                    if($demandData->reviewedBy) $demandData->reviewedDate = '0000-00-00';
                    if($oldDemand->closedBy)    $demandData->closedDate   = '0000-00-00';

                    $newSpecData = $oldSpecs[$demandID];
                    $newSpecData->version += 1;

                    $version = $demandData->version;

                    foreach($specChanges as $specChange) $newSpecData->{$specChange['field']} = $specData->{$specChange['field']};
                }

                if($demandChanges or $specChanges)
                {
                    $demandData->lastEditedBy   = $this->app->user->account;
                    $demandData->lastEditedDate = $now;
                    $this->dao->update(TABLE_DEMAND)
                        ->data($demandData)
                        ->autoCheck()
                        ->batchCheck($this->config->demand->change->requiredFields, 'notempty')
                        ->where('id')->eq((int)$demandID)->exec();

                    if(!dao::isError())
                    {
                        if($specChanges)
                        {
                            $this->dao->insert(TABLE_DEMANDSPEC)->data($newSpecData)->exec();
                            $actionID = $this->action->create('demand', $demandID, 'Changed', '');
                            $this->action->logHistory($actionID, $specChanges);
                        }

                        if($demandChanges)
                        {
                            $actionID = $this->action->create('demand', $demandID, 'Edited', '');
                            $this->action->logHistory($actionID, $demandChanges);
                        }
                    }
                }
            }
            else
            {
                $demandData->status      = (!empty($_POST['reviewer'][$key]) and $forceReview) ? 'reviewing' : 'draft';
                $demandData->version     = $version + 1;
                $demandData->createdBy   = $this->app->user->account;
                $demandData->createdDate = $now;

                $this->dao->insert(TABLE_DEMAND)->data($demandData)->autoCheck()->exec();

                if(!dao::isError())
                {
                    $demandID = $this->dao->lastInsertID();
                    $specData->demand  = $demandID;
                    $specData->version = 1;
                    $this->dao->insert(TABLE_DEMANDSPEC)->data($specData)->exec();
                    $this->action->create('demand', $demandID, 'Opened', '');
                }
            }

            /* Save the demand reviewer to demandreview table. */
            if(isset($_POST['reviewer'][$key]))
            {
                $assignedTo = '';
                foreach($_POST['reviewer'][$key] as $reviewer)
                {
                    if(empty($reviewer)) continue;

                    $reviewData = new stdclass();
                    $reviewData->demand   = $demandID;
                    $reviewData->version  = $version;
                    $reviewData->reviewer = $reviewer;
                    $this->dao->insert(TABLE_DEMANDREVIEW)->data($reviewData)->exec();

                    if(empty($assignedTo)) $assignedTo = $reviewer;
                }
                if($assignedTo) $this->dao->update(TABLE_DEMAND)->set('assignedTo')->eq($assignedTo)->set('assignedDate')->eq($now)->where('id')->eq($demandID)->exec();
            }
        }

        if($this->post->isEndPage)
        {
            unlink($this->session->fileImportFileName);
            unset($_SESSION['fileImportFileName']);
            unset($_SESSION['fileImportExtension']);
        }
    }

    /**
     * Update a demand.
     *
     * @access int $demandID
     * @access public
     * @return void
     */
    public function update($demandID)
    {
        $now   = helper::now();
        $today = helper::today();
        $this->app->loadLang('story');

        $oldDemand = $this->getByID($demandID);

        if(strpos('draft,changing', $oldDemand->status) !== false and $this->checkForceReview() and empty($_POST['reviewer']))
        {
            dao::$errors[] = $this->lang->demand->notice->reviewerNotEmpty;
            return false;
        }

        $demand = fixer::input('post')
            ->cleanInt('pri')
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setIF($this->post->assignedTo != $oldDemand->assignedTo, 'assignedDate', $now)
            ->setIF(!isset($_POST['spec']), 'spec', $oldDemand->spec)
            ->setIF(!isset($_POST['verify']), 'verify', $oldDemand->verify)
            ->setIF(!isset($_POST['title']), 'title', $oldDemand->title)
            ->join('product', ',')
            ->join('mailto', ',')
            ->remove('uid,files,labels,comment,contactListMenu,reviewer,needNotReview,undetermined,undeterminedProduct')
            ->stripTags($this->config->demand->editor->edit['id'], $this->config->allowedTags)
            ->get();

        if(isset($_POST['undetermined'])) $demand->product = '';

        if($oldDemand->status == 'changing' and $demand->status == 'draft') $demand->status = 'changing';

        if(isset($_POST['reviewer']) or isset($_POST['needNotReview']))
        {
            $_POST['reviewer'] = isset($_POST['needNotReview']) ? array() : array_filter($_POST['reviewer']);
            $oldReviewer       = $this->getReviewerPairs($demandID, $oldDemand->version);

            /* Update demand reviewer. */
            $this->dao->delete()->from(TABLE_DEMANDREVIEW)
                ->where('demand')->eq($demandID)
                ->andWhere('version')->eq($oldDemand->version)
                ->beginIF($oldDemand->status == 'reviewing')->andWhere('reviewer')->notin(implode(',', $_POST['reviewer']))
                ->exec();

            foreach($_POST['reviewer'] as $reviewer)
            {
                if($oldDemand->status == 'reviewing' and in_array($reviewer, array_keys($oldReviewer))) continue;

                $reviewData = new stdclass();
                $reviewData->demand   = $demandID;
                $reviewData->version  = $oldDemand->version;
                $reviewData->reviewer = $reviewer;
                $this->dao->insert(TABLE_DEMANDREVIEW)->data($reviewData)->exec();
            }

            if($oldDemand->status == 'reviewing') $demand = $this->updateDemandByReview($demandID, $oldDemand, $demand);
            if(strpos('draft,changing', $oldDemand->status) != false) $demand->reviewedBy = '';

            $oldDemand->reviewers = implode(',', array_keys($oldReviewer));
            $demand->reviewers    = implode(',', array_keys($this->getReviewerPairs($demandID, $oldDemand->version)));
        }

        $demand = $this->loadModel('file')->processImgURL($demand, $this->config->demand->editor->edit['id'], $this->post->uid);
        $this->dao->update(TABLE_DEMAND)->data($demand, 'reviewers,spec,verify,finalResult')->autoCheck()
            ->batchCheck($this->config->demand->edit->requiredFields, 'notempty')
            ->where('id')->eq($demandID)
            ->exec();

        if(!dao::isError())
        {
            $this->loadModel('file')->updateObjectID($this->post->uid, $demandID, 'demand');
            $this->file->saveUpload('demand', $demandID);

            if($demand->spec != $oldDemand->spec or $demand->verify != $oldDemand->verify or $demand->title != $oldDemand->title)
            {
                $data = new stdclass();
                $data->title  = $demand->title;
                $data->spec   = $demand->spec;
                $data->verify = $demand->verify;
                $this->dao->update(TABLE_DEMANDSPEC)->data($data)->where('demand')->eq((int)$demandID)->andWhere('version')->eq($oldDemand->version)->exec();
            }

            $changed = $demand->parent != $oldDemand->parent;
            $this->updateParentStatus($demandID, $oldDemand->parent, !$changed);

            return common::createChanges($oldDemand, $demand);
        }

        return false;
    }

    /**
     * Assign a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function assign($demandID)
    {
        $oldDemand  = $this->dao->findById($demandID)->from(TABLE_DEMAND)->fetch();
        $assignedTo = $this->post->assignedTo;
        if($assignedTo == $oldDemand->assignedTo) return array();

        $demand = new stdclass();
        $demand->assignedTo   = $assignedTo;
        $demand->assignedDate = helper::now();

        $this->dao->update(TABLE_DEMAND)->data($demand)->autoCheck()->where('id')->eq((int)$demandID)->exec();
        if(!dao::isError()) return common::createChanges($oldDemand, $demand);
        return false;
    }

    /**
     * Close a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function close($demandID)
    {
        $oldDemand = $this->dao->findById($demandID)->from(TABLE_DEMAND)->fetch();
        $now       = helper::now();
        $demand    = fixer::input('post')
            ->add('status', 'closed')
            ->add('assignedTo', 'closed')
            ->setDefault('lastEditedBy',   $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setDefault('closedDate',     $now)
            ->setDefault('closedBy',       $this->app->user->account)
            ->setDefault('duplicateDemand', 0)
            ->remove('uid')
            ->get();

        $this->dao->update(TABLE_DEMAND)->data($demand, 'comment')
            ->autoCheck()
            ->batchCheck($this->config->demand->close->requiredFields, 'notempty')
            ->where('id')->eq((int)$demandID)
            ->exec();
        if(!dao::isError()) return common::createChanges($oldDemand, $demand);
        return false;
    }

    /**
     * Activate a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function activate($demandID)
    {
        $oldDemand = $this->dao->findById($demandID)->from(TABLE_DEMAND)->fetch();
        $status    = $this->getStatusBeforeClosed($demandID);
        if(in_array($status, array('distributed', 'launched'))) $status = $this->changeDemandStatus($demandID, 0, false);

        $now    = helper::now();
        $demand = fixer::input('post')
            ->add('status', $status)
            ->add('closedBy', '')
            ->add('closedReason', '')
            ->add('closedDate', '0000-00-00')
            ->setDefault('lastEditedBy',   $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setDefault('assignedDate',   $now)
            ->setDefault('activatedDate', $now)
            ->remove('uid,comment')
            ->get();

        $this->dao->update(TABLE_DEMAND)->data($demand)
            ->autoCheck()
            ->where('id')->eq((int)$demandID)
            ->exec();

        if(!dao::isError()) return common::createChanges($oldDemand, $demand);
        return false;
    }

    /**
     * Submit review.
     *
     * @param  int    $demandID
     * @access public
     * @return array|bool
     */
    public function submitReview($demandID)
    {
        if(isset($_POST['reviewer'])) $_POST['reviewer'] = array_filter($_POST['reviewer']);
        if(!$this->post->needNotReview and empty($_POST['reviewer']))
        {
            dao::$errors[] = $this->lang->demand->errorEmptyReviewedBy;
            return false;
        }

        $oldDemand    = $this->dao->findById($demandID)->from(TABLE_DEMAND)->fetch();
        $reviewerList = $this->getReviewerPairs($oldDemand->id, $oldDemand->version);
        $oldDemand->reviewer = implode(',', array_keys($reviewerList));

        $demand = fixer::input('post')
            ->setDefault('status', 'pass')
            ->setDefault('reviewer', '')
            ->setDefault('reviewedBy', '')
            ->setDefault('submitedBy', $this->app->user->account)
            ->remove('needNotReview')
            ->join('reviewer', ',')
            ->get();

        $this->dao->delete()->from(TABLE_DEMANDREVIEW)->where('demand')->eq($demandID)->andWhere('version')->eq($oldDemand->version)->exec();

        if(isset($_POST['reviewer']))
        {
            foreach($this->post->reviewer as $reviewer)
            {
                if(empty($reviewer)) continue;

                $reviewData = new stdclass();
                $reviewData->demand   = $demandID;
                $reviewData->version  = $oldDemand->version;
                $reviewData->reviewer = $reviewer;
                $this->dao->insert(TABLE_DEMANDREVIEW)->data($reviewData)->exec();
            }
            $demand->status = 'reviewing';
        }

        $this->dao->update(TABLE_DEMAND)->data($demand, 'reviewer')->where('id')->eq($demandID)->exec();

        $changes = common::createChanges($oldDemand, $demand);
        if(!dao::isError()) return $changes;

        return false;
    }

    /**
     * Review a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return bool
     */
    public function review($demandID)
    {
        $this->app->loadLang('story');

        if(strpos($this->config->demand->review->requiredFields, 'comment') !== false and !$this->post->comment)
        {
            dao::$errors[] = sprintf($this->lang->error->notempty, $this->lang->comment);
            return false;
        }

        if($this->post->result == false)
        {
            dao::$errors[] = $this->lang->story->mustChooseResult;
            return false;
        }

        $now       = helper::now();
        $date      = helper::today();
        $oldDemand = $this->dao->findById($demandID)->from(TABLE_DEMAND)->fetch();
        $demand    = fixer::input('post')
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->setDefault('status', $oldDemand->status)
            ->setDefault('duplicateDemand', 0)
            ->setIF((strpos($this->config->demand->review->requiredFields, 'reviewedDate') === false and empty($_POST['reviewedDate'])), 'reviewedDate', $date)
            ->stripTags($this->config->demand->editor->review['id'], $this->config->allowedTags)
            ->setIF(!$this->post->assignedTo, 'assignedTo', '')
            ->removeIF($this->post->result != 'reject', 'closedReason, duplicateDemand, childStories')
            ->removeIF($this->post->result == 'reject' and $this->post->closedReason != 'duplicate', 'duplicateDemand')
            ->removeIF($this->post->result == 'reject' and $this->post->closedReason != 'subdivided', 'childStories')
            ->add('reviewedBy', $oldDemand->reviewedBy . ',' . $this->app->user->account)
            ->add('id', $demandID)
            ->remove('result,comment')
            ->get();

        $demand->reviewedBy = implode(',', array_unique(explode(',', $demand->reviewedBy)));
        $demand = $this->loadModel('file')->processImgURL($demand, $this->config->demand->editor->review['id'], $this->post->uid);

        $this->lang->demand->closedReason = $this->lang->story->rejectedReason;

        $this->dao->update(TABLE_DEMANDREVIEW)
            ->set('result')->eq($this->post->result)
            ->set('reviewDate')->eq($now)
            ->where('demand')->eq($demandID)
            ->andWhere('version')->eq($oldDemand->version)
            ->andWhere('reviewer')->eq($this->app->user->account)
            ->exec();

        $demand = $this->updateDemandByReview($demandID, $oldDemand, $demand);

        $skipFields      = 'finalResult';
        $isSuperReviewer = strpos(',' . trim(zget($this->config->demand, 'superReviewers', ''), ',') . ',', ',' . $this->app->user->account . ',');
        if($isSuperReviewer === false)
        {
            $reviewers = $this->getReviewerPairs($demandID, $oldDemand->version);
            if(count($reviewers) > 1) $skipFields .= ',closedReason';
        }

        $this->dao->update(TABLE_DEMAND)->data($demand, $skipFields)
            ->autoCheck()
            ->batchCheck($this->config->demand->review->requiredFields, 'notempty')
            ->checkIF($this->post->result == 'reject', 'closedReason', 'notempty')
            ->checkFlow()
            ->where('id')->eq($demandID)
            ->exec();
        if(dao::isError()) return false;

        if(isset($demand->closedReason) and $isSuperReviewer === false) unset($demand->closedReason);
        $changes = common::createChanges($oldDemand, $demand);
        if($changes)
        {
            $actionID = $this->recordReviewAction($demand, $this->post->result, $this->post->closedReason);
            $this->action->logHistory($actionID, $changes);
        }

        $this->changeDemandStatus($demandID);

        return true;
    }

    /**
     * Change a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return array  the change of the demand.
     */
    public function change($demandID)
    {
        $specChanged = false;
        $oldDemand   = $this->getById($demandID);

        if(!empty($_POST['lastEditedDate']) and $oldDemand->lastEditedDate != $this->post->lastEditedDate)
        {
            dao::$errors[] = $this->lang->error->editedByOther;
            return false;
        }

        if(strpos($this->config->demand->change->requiredFields, 'comment') !== false and !$this->post->comment)
        {
            dao::$errors[] = sprintf($this->lang->error->notempty, $this->lang->comment);
            return false;
        }

        if(isset($_POST['reviewer'])) $_POST['reviewer'] = array_filter($_POST['reviewer']);
        if(!$this->post->needNotReview and empty($_POST['reviewer']))
        {
            dao::$errors[] = $this->lang->demand->errorEmptyReviewedBy;
            return false;
        }

        $demand = fixer::input('post')->stripTags($this->config->demand->editor->change['id'], $this->config->allowedTags)->get();

        $oldDemandReviewers  = $this->getReviewerPairs($demandID, $oldDemand->version);
        $_POST['reviewer']  = isset($_POST['reviewer']) ? $_POST['reviewer'] : array();
        $reviewerHasChanged = (array_diff(array_keys($oldDemandReviewers), $_POST['reviewer']) or array_diff($_POST['reviewer'], array_keys($oldDemandReviewers)));
        if($demand->spec != $oldDemand->spec or $demand->verify != $oldDemand->verify or $demand->title != $oldDemand->title or $this->loadModel('file')->getCount() or $reviewerHasChanged or isset($demand->deleteFiles)) $specChanged = true;

        $now   = helper::now();
        $demand = fixer::input('post')
            ->callFunc('title', 'trim')
            ->setDefault('lastEditedBy', $this->app->user->account)
            ->setDefault('deleteFiles', array())
            ->add('id', $demandID)
            ->add('lastEditedDate', $now)
            ->setIF($specChanged, 'version', $oldDemand->version + 1)
            ->setIF($specChanged, 'reviewedBy', '')
            ->setIF($specChanged, 'changedBy', $this->app->user->account)
            ->setIF($specChanged, 'changedDate', $now)
            ->setIF($specChanged, 'closedBy', '')
            ->setIF($specChanged, 'closedReason', '')
            ->setIF($specChanged and $oldDemand->reviewedBy, 'reviewedDate', '0000-00-00')
            ->setIF($specChanged and $oldDemand->closedBy, 'closedDate', '0000-00-00')
            ->setIF(!$specChanged, 'status', $oldDemand->status)
            ->stripTags($this->config->demand->editor->change['id'], $this->config->allowedTags)
            ->remove('files,labels,reviewer,comment,needNotReview,uid')
            ->get();

        $demand = $this->loadModel('file')->processImgURL($demand, $this->config->demand->editor->change['id'], $this->post->uid);
        $this->dao->update(TABLE_DEMAND)->data($demand, 'spec,verify,deleteFiles')
            ->autoCheck()
            ->batchCheck($this->config->demand->change->requiredFields, 'notempty')
            ->checkFlow()
            ->where('id')->eq((int)$demandID)->exec();

        if(!dao::isError())
        {
            if($specChanged)
            {
                $this->file->updateObjectID($this->post->uid, $demandID, 'demand');
                $this->file->saveUpload('demand', $demandID, $demand->version);

                $data          = new stdclass();
                $data->demand  = $demandID;
                $data->version = $demand->version;
                $data->title   = $demand->title;
                $data->spec    = $demand->spec;
                $data->verify  = $demand->verify;
                $this->dao->insert(TABLE_DEMANDSPEC)->data($data)->exec();

                /* Update the reviewer. */
                foreach($_POST['reviewer'] as $reviewer)
                {
                    $reviewData = new stdclass();
                    $reviewData->demand   = $demandID;
                    $reviewData->version  = $demand->version;
                    $reviewData->reviewer = $reviewer;
                    $this->dao->insert(TABLE_DEMANDREVIEW)->data($reviewData)->exec();
                }

                if($reviewerHasChanged)
                {
                    $oldDemand->reviewers = implode(',', array_keys($oldDemandReviewers));
                    $demand->reviewers    = implode(',', $_POST['reviewer']);
                }
            }

            return common::createChanges($oldDemand, $demand);
        }
    }

    /**
     * Subdivide demand.
     *
     * @param  int    $demandID
     * @param  array  $demandIdList
     * @access public
     * @return void
     */
    public function subdivide($demandID, $demandIdList)
    {
        $now       = helper::now();
        $oldDemand = $this->dao->findById($demandID)->from(TABLE_DEMAND)->fetch();

        /* Set parent to child demand. */
        $this->dao->update(TABLE_DEMAND)->set('parent')->eq($demandID)->where('id')->in($demandIdList)->exec();

        /* Set childDemands. */
        $childDemands = join(',', $demandIdList);

        $newDemand = new stdClass();
        $newDemand->parent         = '-1';
        $newDemand->lastEditedBy   = $this->app->user->account;
        $newDemand->lastEditedDate = $now;
        $newDemand->childDemands   = trim($oldDemand->childDemands . ',' . $childDemands, ',');

        if($oldDemand->status == 'distributed')
        {
            $newDemand->status = 'pass';
            $newDemand->story  = 0;
            $this->dao->update(TABLE_STORY)->set('deleted')->eq('1')->where('demand')->eq($demandID)->exec();
        }

        /* Subdivide demand. */
        $this->dao->update(TABLE_DEMAND)->data($newDemand)->autoCheck()->where('id')->eq($demandID)->exec();

        $changes = common::createChanges($oldDemand, $newDemand);
        if($changes)
        {
            $actionID = $this->loadModel('action')->create('demand', $demandID, 'createChildrenDemand', '', $childDemands);
            $this->action->logHistory($actionID, $changes);
        }
    }

    /**
     * Record demand review actions.
     *
     * @param  object $demand
     * @param  string $result
     * @param  string $reason
     * @access public
     * @return int|string
     */
    public function recordReviewAction($demand, $result = '', $reason = '')
    {
        $isSuperReviewer = strpos(',' . trim(zget($this->config->demand, 'superReviewers', ''), ',') . ',', ',' . $this->app->user->account . ',');

        $comment = isset($_POST['comment']) ? $this->post->comment : '';

        if($isSuperReviewer !== false and $this->app->rawMethod != 'edit')
        {
            $actionID = $this->loadModel('action')->create('demand', $demand->id, 'Reviewed', $comment, ucfirst($result) . '|superReviewer');
            return $actionID;
        }

        $reasonParam = $result == 'reject' ? ',' . $reason : '';
        $actionID    = !empty($result) ? $this->loadModel('action')->create('demand', $demand->id, 'Reviewed', $comment, ucfirst($result) . $reasonParam) : '';

        if(isset($demand->finalResult))
        {
            if($demand->finalResult == 'reject')  $this->action->create('demand', $demand->id, 'ReviewRejected');
            if($demand->finalResult == 'pass')    $this->action->create('demand', $demand->id, 'ReviewPassed');
            if($demand->finalResult == 'clarify') $this->action->create('demand', $demand->id, 'ReviewClarified');
            if($demand->finalResult == 'revert')  $this->action->create('demand', $demand->id, 'ReviewReverted');
        }

        return $actionID;
    }

    /**
     * Build search form.
     *
     * @param  int    $poolID
     * @param  int    $queryID
     * @param  string $actionURL
     * @access public
     * @return void
     */
    public function buildSearchForm($poolID, $queryID, $actionURL)
    {
        $this->app->loadLang('roadmap');
        $this->config->demand->search['actionURL'] = $actionURL;
        $this->config->demand->search['queryID']   = $queryID;
        $this->config->demand->search['params']['product']['values'] = array('' => '') + $this->loadModel('product')->getPairs() + array('null' => $this->lang->roadmap->future);

        $pool = $this->loadModel('demandpool')->getById($poolID);
        $this->config->demand->search['params']['pool']['values'] = array('' => '') + array($poolID => $pool->name) + array('all' => $this->lang->demandpool->all);

        $this->loadModel('search')->setSearchParams($this->config->demand->search);
    }

    /**
     * Build demand operate menu.
     *
     * @param  object $demand
     * @param  string $type
     * @access public
     * @return void
     */
    public function buildOperateMenu($demand, $type = 'view')
    {
        $this->app->loadLang('story');
        $menu    = '';
        $params  = "id={$demand->id}";
        $account = $this->app->user->account;
        $submitedBy = '';
        $changedBy      = '';

        if($demand->status == 'reviewing') $submitedBy = $demand->submitedBy;
        if($demand->status == 'changing')  $changedBy  = $demand->changedBy;

        if($type == 'view')
        {
            $menu .= $this->buildMenu('demand', 'change', $params, $demand, $type, 'alter', '', 'showinonlybody');
            if($demand->status != 'reviewing')
            {
                if($demand->status != 'reviewing') $menu .= $this->buildMenu('demand', 'submitReview', $params, $demand, $type, 'confirm', '', 'showinonlybody iframe', true);
            }
            else
            {
                $isClick = $this->isClickable($demand, 'review');
                $title   = $this->lang->demand->review;
                $menu .= $this->buildMenu('demand', 'review', $params, $demand, $type, 'search', '', 'showinonlybody', false, '', $title);
            }

            $title = $demand->status == 'changing' ? $this->lang->story->recallChange : $this->lang->story->recall;
            $menu .= $this->buildMenu('demand', 'recall', $params, $demand, $type, 'undo', 'hiddenwin', 'showinonlybody', false, '', $title);

            if($demand->status) $menu .= $this->buildMenu('demand', 'distribute', $params, $demand, $type, 'sitemap', '', 'showinonlybody iframe', true, '');

            if(!isonlybody())
            {
                $hiddenwin = $demand->status == 'distributed' ? 'hiddenwin' : '';
                $menu .= $this->buildMenu('demand', 'batchCreate', "poolID={$demand->pool}&" . $params, $demand, $type, 'split', $hiddenwin, 'divideStory', false, '', $this->lang->demand->subdivide);
            }

            $menu .= $this->buildMenu('demand', 'close',    $params, $demand, $type, '', '', 'iframe showinonlybody', true);
            $menu .= $this->buildMenu('demand', 'activate', $params, $demand, $type, '', '', 'iframe showinonlybody', true);
            $menu .= "<div class='divider'></div>";

            $menu .= $this->buildMenu('demand', 'edit', $params, $demand, $type);
            $menu .= $this->buildMenu('demand', 'create', "poolID=$demand->pool&demandID=$demand->id", $demand, $type, 'copy');
            $menu .= $this->buildMenu('demand', 'delete', $params, $demand, 'button', 'trash', 'hiddenwin', 'showinonlybody', true);
        }
        elseif($type == 'browse')
        {
            $menu .= $this->buildMenu('demand', 'change', $params, $demand, $type, 'alter', '', 'showinonlybody');
            if($demand->status != 'reviewing')
            {
                if($demand->status != 'reviewing') $menu .= $this->buildMenu('demand', 'submitReview', $params, $demand, $type, 'confirm', '', 'showinonlybody iframe', true);
                $title = $demand->status == 'changing' ? $this->lang->story->recallChange : $this->lang->story->recall;
            }
            else
            {
                $isClick = $this->isClickable($demand, 'review');
                $title   = $this->lang->demand->review;
                $menu .= $this->buildMenu('demand', 'review', $params, $demand, $type, 'search', '', 'showinonlybody', false, '', $title);
            }

            $title = $demand->status == 'changing' ? $this->lang->story->recallChange : $this->lang->story->recall;
            $menu .= $this->buildMenu('demand', 'recall', $params, $demand, $type, 'undo', 'hiddenwin', 'showinonlybody', false, '', $title);
            $menu .= $this->buildMenu('demand', 'distribute', $params, $demand, $type, 'sitemap', '', 'iframe showinonlybody', true);
            $menu .= $this->buildMenu('demand', 'edit', $params, $demand, $type);

            if(!isonlybody())
            {
                $hiddenwin = $demand->status == 'distributed' ? 'hiddenwin' : '';
                $menu .= $this->buildMenu('demand', 'batchCreate', "poolID={$demand->pool}&" . $params, $demand, $type, 'split', $hiddenwin, 'divideStory', false, '', $this->lang->demand->subdivide);
            }

            if($demand->status != 'closed') $menu .= $this->buildMenu('demand', 'close',    $params, $demand, $type, '', '', 'iframe showinonlybody', true);
            if($demand->status == 'closed') $menu .= $this->buildMenu('demand', 'activate', $params, $demand, $type, '', '', 'iframe showinonlybody', true);
        }

        return $menu;
    }

    /**
     * Judge btn is clickable.
     *
     * @param  object   $demand
     * @param  string   $action
     * @static
     * @access public
     * @return void
     */
    public static function isClickable($demand, $action)
    {
        global $app, $config;
        $account = $app->user->account;
        $action  = strtolower($action);
        static $distribute = array();

        if($action == 'distribute' and empty($distribute))
        {
            $self = new self();
            $distribute = $self->isDistribute($demand->pool);
        }

        $isSuperReviewer = strpos(',' . trim(zget($config->demand, 'superReviewers', ''), ',') . ',', ',' . $app->user->account . ',');
        $demand->reviewer  = isset($demand->reviewer)  ? $demand->reviewer  : array();
        $demand->notReview = isset($demand->notReview) ? $demand->notReview : array();

        if($action == 'change')       return (($isSuperReviewer !== false or count($demand->reviewer) == 0 or count($demand->notReview) == 0) and $demand->status == 'pass');
        if($action == 'review')       return (($isSuperReviewer !== false or in_array($account, $demand->notReview)) and $demand->status == 'reviewing');
        if($action == 'recall')       return strpos('reviewing,changing', $demand->status) !== false;
        if($action == 'close')        return $demand->status != 'closed';
        if($action == 'submitreview') return ($demand->status == 'draft' or $demand->status == 'changing');
        if($action == 'activate')     return $demand->status == 'closed';
        if($action == 'assignto')     return $demand->status != 'closed';
        if(!empty($distribute[$demand->id]) and $action == 'distribute') return ($distribute[$demand->id] and in_array($demand->status, array('pass', 'distributed', 'launched')));
        if($action == 'distribute')   return ($demand->parent >= 0 and $demand->status == 'pass');
        if($action == 'batchcreate')  return ($demand->parent <= 0) and in_array($demand->status, array('draft', 'pass', 'distributed'));

        return true;
    }

    /**
     * Is distribute .
     *
     * @param  int    $poolID
     * @access public
     * @return void
     */
    public function isDistribute($poolID = 0)
    {
        $pool = $this->loadModel('demandpool')->getByID($poolID);

        $demandProducts = $this->dao->select('id,name')->from(TABLE_PRODUCT)
            ->where('deleted')->eq(0)
            ->beginIF($pool->products)->andWhere('id')->in($pool->products)->fi()
            ->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()
            ->andWhere('status')->ne('closed')
            ->andWhere('vision')->ne('lite')
            ->orderBy('id')
            ->fetchPairs();

        $distributeStory = $this->dao->select('demand,product')->from(TABLE_STORY)
            ->where('deleted')->eq(0)
            ->andWhere('retractedBy')->eq('')
            ->andWhere('demand')->ne(0)
            ->fetchGroup('demand', 'product');

        foreach($distributeStory as $demandID => $products)
        {
            foreach($products as $productID => $productName)
            {
                if(!isset($demandProducts[$productID])) unset($distributeStory[$demandID][$productID]);
            }

            $isDistributed = true;
            if(count($distributeStory[$demandID]) == count($demandProducts)) $isDistributed = false;
            $distributeStory[$demandID] = $isDistributed;
        }

        return $distributeStory;
    }

    /**
     * Get last submit reviewer.
     *
     * @param  int    $demand
     * @access public
     * @return void
     */
    public function getSubmitReviewer($demand)
    {
        if($demand->status != 'reviewing') return;

        return $this->dao->select('*')->from(TABLE_ACTION)
            ->where('objectID')->eq($demand->id)
            ->andWhere('objectType')->eq('demand')
            ->andWhere('action')->eq('submitreview')
            ->orderBy('id_desc')
            ->fetch('actor');
    }

    /**
     * Print assigned html.
     *
     * @param  object $demand
     * @param  array  $users
     * @access public
     * @return void
     */
    public function printAssignedHtml($demand, $users)
    {
        $this->loadModel('task');
        $btnTextClass   = '';
        $assignedToText = zget($users, $demand->assignedTo);

        if(empty($demand->assignedTo))
        {
            $btnTextClass   = 'text-primary';
            $assignedToText = $this->lang->task->noAssigned;
        }
        if($demand->assignedTo == $this->app->user->account) $btnTextClass = 'text-red';

        $btnClass     = $demand->assignedTo == 'closed' ? ' disabled' : '';
        $btnClass     = "iframe btn btn-icon-left btn-sm {$btnClass}";
        $assignToLink = helper::createLink('demand', 'assignTo', "demandID=$demand->id", '', true);
        $assignToHtml = html::a($assignToLink, "<i class='icon icon-hand-right'></i> <span class='{$btnTextClass}'>{$assignedToText}</span>", '', "class='$btnClass'");

        echo !common::hasPriv('demand', 'assignTo', $demand) ? "<span style='padding-left: 21px' class='{$btnTextClass}'>{$assignedToText}</span>" : $assignToHtml;
    }

    /* Print cell in browse.
     *
     * @param  object $col
     * @param  object $demand
     * @param  array  $users
     * @param  array  $modulePairs
     * @param  string $mode
     * @access public
     * @return void
     */
    public function printCell($col, $demand, $users, $mode = 'datatable')
    {
        static $products = '';
        if(!is_array($products)) $products = $this->loadModel('product')->getPairs('noclosed');

        $demandLink = helper::createLink('demand', 'view', "demandID=$demand->id");
        $canView    = common::hasPriv('demand', 'view');
        $account    = $this->app->user->account;
        $id         = $col->id;

        $canBatchAction = common::hasPriv('demand', 'export');
        if($col->show)
        {
            $class = "c-$id";
            $title = '';
            switch($id)
            {
                case 'id':
                    $class .= ' cell-id';
                    break;
                case 'status':
                    $class .= ' demand-' . $demand->status;
                    $title  = "title='" . $this->processStatus('demand', $demand) . "'";
                    break;
                case 'title':
                    $class .= ' text-left text-ellipsis';
                    $title  = "title='{$demand->title}'";
                    if(!empty($demand->children)) $class .= ' has-child';
                    break;
                case 'category':
                    $title  = "title='" . zget($this->lang->demand->categoryList, $demand->category) . "'";
                    break;
                case 'assignedTo':
                    $class .= ' has-btn text-left';
                    if($demand->assignedTo == $account) $class .= ' red';
                    break;
                case 'openedBy':
                    $class .= ' c-user';
                    $title  = "title='" . zget($users, $demand->openedBy) . "'";
                    break;
                case 'keywords':
                    $title = "title='$demand->keywords'";
                    break;
                case 'product':
                    $productList = '';
                    foreach(explode(',', $demand->product) as $product) $productList .= zget($products, $product) . ',';
                    $productList = trim($productList, ',') ? trim($productList, ',') : $this->lang->demand->undetermined;
                    $title       = "title='$productList'";
                    break;
                case 'reviewedBy':
                    $reviewedBy = '';
                    foreach(explode(',', trim($demand->reviewedBy, ',')) as $reviewer) $reviewedBy .= zget($users, $reviewer) . ',';
                    $reviewedBy = trim($reviewedBy, ',');
                    $title = "title='$reviewedBy'";
                    break;
            }

            echo "<td class='" . $class . "' $title>";
            if($this->config->edition != 'open') $this->loadModel('flow')->printFlowCell('demand', $demand, $id);
            switch($id)
            {
            case 'id':
                if($canBatchAction)
                {
                    echo html::checkbox('demandIDList', array($demand->id => '')) . html::a(helper::createLink('demand', 'view', "demandID=$demand->id"), sprintf('%03d', $demand->id));
                }
                else
                {
                    printf('%03d', $demand->id);
                }
                break;
            case 'title':
                if($demand->parent > 0) echo '<span class="label label-badge label-light" title="' . $this->lang->demand->children . '">' . $this->lang->demand->childrenAB . '</span> ';
                if($demand->parent > 0 and isset($demand->parentName)) $demand->title = "{$demand->parentName} / $demand->title";
                echo $canView ? html::a($demandLink, $demand->title, '', "title='$demand->title' style='color: $demand->color'") : "<span style='color: $demand->color'>{$demand->title}</span>";
                if(!empty($demand->children)) echo '<a class="demand-toggle" data-id="' . $demand->id . '"><i class="icon icon-angle-right"></i></a>';
                break;
            case 'pri':
                echo "<span class='label-pri label-pri-" . $demand->pri . "' title='" . zget($this->lang->demand->priList, $demand->pri, $demand->pri) . "'>";
                echo zget($this->lang->demand->priList, $demand->pri, $demand->pri);
                echo "</span>";
                break;
            case 'status':
                echo "<span class='status-demand status-{$demand->status}'>";
                echo zget($this->lang->demand->statusList, $demand->status);
                echo  '</span>';
                break;
            case 'assignedTo':
                $this->printAssignedHtml($demand, $users);
                break;
            case 'category':
                echo zget($this->lang->demand->categoryList, $demand->category);
                break;
            case 'duration':
                echo zget($this->lang->demand->durationList, $demand->duration);
                break;
            case 'BSA':
                echo zget($this->lang->demand->bsaList, $demand->BSA);
                break;
            case 'product':
                $productList = '';
                foreach(explode(',', $demand->product) as $product) $productList .= zget($products, $product) . ',';
                echo trim($productList, ',') ? trim($productList, ',') : $this->lang->demand->undetermined;
                break;
            case 'source':
                echo zget($this->lang->demand->sourceList, $demand->source);
                break;
            case 'sourceNote':
                echo $demand->sourceNote;
                break;
            case 'feedbackedBy':
                echo zget($users, $demand->feedbackedBy);
                break;
            case 'email':
                echo $demand->email;
                break;
            case 'reviewedDate':
                echo helper::isZeroDate($demand->reviewedDate) ? '' : substr($demand->reviewedDate, 5, 11);
                break;
            case 'reviewedBy':
                $reviewedBy = '';
                foreach(explode(',', trim($demand->reviewedBy, ',')) as $reviewer) $reviewedBy .= zget($users, $reviewer) . ',';
                echo trim($reviewedBy, ',');
                break;
            case 'createdBy':
                echo zget($users, $demand->createdBy);
                break;
            case 'createdDate':
                echo helper::isZeroDate($demand->createdDate) ? '' : substr($demand->createdDate, 5, 11);
                break;
            case 'assignedDate':
                echo helper::isZeroDate($demand->assignedDate) ? '' : substr($demand->assignedDate, 5, 11);
                break;
            case 'closedBy':
                echo zget($users, $demand->closedBy);
                break;
            case 'closedDate':
                echo helper::isZeroDate($demand->closedDate) ? '' : substr($demand->closedDate, 5, 11);
                break;
            case 'closedReason':
                echo zget($this->lang->demand->reasonList, $demand->closedReason);
                break;
            case 'lastEditedBy':
                echo zget($users, $demand->lastEditedBy, $demand->lastEditedBy);
                break;
            case 'lastEditedDate':
                echo helper::isZeroDate($demand->lastEditedDate) ? '' : substr($demand->lastEditedDate, 5, 11);
                break;
            case 'activatedDate':
                echo helper::isZeroDate($demand->activatedDate) ? '' : substr($demand->activatedDate, 5, 11);
                break;
            case 'mailto':
                $mailto = explode(',', $demand->mailto);
                foreach($mailto as $account)
                {
                    $account = trim($account);
                    if(empty($account)) continue;
                    echo zget($users, $account) . " &nbsp;";
                }
                break;
            case 'version':
                echo $demand->version;
                break;
            case 'keywords':
                echo $demand->keywords;
                break;
            case 'actions':
                echo $this->buildOperateMenu($demand, 'browse');
                break;
            }
            echo '</td>';
        }
    }

    /**
     * Sendmail
     *
     * @param  int    $demandID
     * @param  int    $actionID
     * @access public
     * @return void
     */
    public function sendmail($demandID, $actionID)
    {
        $this->loadModel('mail');
        $demand = $this->getById($demandID);
        $users  = $this->loadModel('user')->getPairs('noletter');

        /* Get action info. */
        $action          = $this->loadModel('action')->getById($actionID);
        $history         = $this->action->getHistory($actionID);
        $action->history = isset($history[$actionID]) ? $history[$actionID] : array();

        /* Get mail content. */
        $modulePath = $this->app->getModulePath($appName = '', 'demand');
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

        $sendUsers = $this->getToAndCcList($demand);
        if(!$sendUsers) return;
        list($toList, $ccList) = $sendUsers;
        $subject = $this->getSubject($demand, $action->action);

        /* Send mail. */
        $this->mail->send($toList, $subject, $mailContent, $ccList);
        if($this->mail->isError()) trigger_error(join("\n", $this->mail->getError()));
    }

    /**
     * Get mail subject.
     *
     * @param  object $demand
     * @param  string $actionType created|edited
     * @access public
     * @return string
     */
    public function getSubject($demand, $actionType)
    {
        /* Set email title. */
        return sprintf($this->lang->demand->mail->$actionType, $this->app->user->realname, $demand->id, $demand->name);
    }

    /**
     * Get toList and ccList.
     *
     * @param  object     $demand
     * @access public
     * @return bool|array
     */
    public function getToAndCcList($demand)
    {
        /* Set toList and ccList. */
        $toList  = $demand->assignedTo;
        $ccList  = str_replace(' ', '', trim($demand->mailto, ','));
        $ccList .= ",$demand->createdBy";

        $reviewers = $this->getReviewerPairs($demand->id, $demand->version);
        $reviewers = array_keys($reviewers);
        if($reviewers) $ccList .= ',' . implode(',', $reviewers);

        if(empty($toList))
        {
            if(empty($ccList)) return false;
            if(strpos($ccList, ',') === false)
            {
                $toList = $ccList;
                $ccList = '';
            }
            else
            {
                $commaPos = strpos($ccList, ',');
                $toList   = substr($ccList, 0, $commaPos);
                $ccList   = substr($ccList, $commaPos + 1);
            }
        }
        return array($toList, $ccList);
    }

    public static function createDemandLink($type, $module, $poolID)
    {
        return html::a(helper::createLink('demand', 'browse', "poolID=$poolID&type=byModule&param={$module->id}"), $module->name, '', "id='module{$module->id}'");
    }

    /**
     * Get tracks.
     *
     * @param  array    $demands
     * @access public
     * @return array
     */
    public function getTracks($demands = array())
    {
        $this->loadModel('story');

        $stories = array();
        foreach($demands as $demandID => $demand)
        {
            $requirements = $this->dao->select('*')->from(TABLE_STORY)
                ->where('deleted')->eq('0')
                ->andWhere('type')->eq('requirement')
                ->andWhere('demand')->eq($demandID)
                ->andWhere('retractedBy')->eq('')
                ->andWhere('retractedDate IS NULL')
                ->fetchAll('id');

            $demand->trackCount = 0;
            foreach($requirements as $requirement)
            {
                $relatedStories = $this->story->getRelation($requirement->id, 'requirement', array('id', 'title', 'parent'));
                $relatedStories = empty($relatedStories) ? array() : $relatedStories;
                foreach($relatedStories as $id => $story)
                {
                    $stories[] = $id;

                    $relatedStories[$id] = new stdclass();
                    $relatedStories[$id]->parent  = $story->parent;
                    $relatedStories[$id]->title   = $story->title;
                    $relatedStories[$id]->cases   = $this->loadModel('testcase')->getStoryCases($id);
                    $relatedStories[$id]->bugs    = $this->loadModel('bug')->getStoryBugs($id);
                    $relatedStories[$id]->tasks   = $this->loadModel('task')->getStoryTasks($id);
                    $relatedStories[$id]->designs = $this->dao->select('id, name')->from(TABLE_DESIGN)
                        ->where('story')->eq($id)
                        ->andWhere('deleted')->eq('0')
                        ->fetchAll('id');
                    $relatedStories[$id]->revisions = $this->dao->select('BID, t2.comment')->from(TABLE_RELATION)->alias('t1')
                        ->leftjoin(TABLE_REPOHISTORY)->alias('t2')->on('t1.BID = t2.id')
                        ->where('t1.AType')->eq('design')
                        ->andWhere('t1.BType')->eq('commit')
                        ->andWhere('t1.AID')->in(array_keys($relatedStories[$id]->designs))
                        ->fetchPairs();
                }

                $requirement->track      = $relatedStories;
                $requirement->trackCount = count($relatedStories);
                $demand->trackCount     += count($relatedStories) ? count($relatedStories) - 1 : 0;
            }

            $demands[$demandID]->requirements = $requirements;

            $demand->track       = $requirements;
            $demand->trackCount += count($requirements);
        }

        return $demands;
    }

    public function getRelation($storyID, $storyType, $fields = array())
    {
        $BType    = $storyType == 'story' ? 'requirement' : 'story';
        $relation = $storyType == 'story' ? 'subdividedfrom' : 'subdivideinto';

        $relations = $this->dao->select('BID')->from(TABLE_RELATION)
            ->where('AType')->eq($storyType)
            ->andWhere('BType')->eq($BType)
            ->andWhere('relation')->eq($relation)
            ->andWhere('AID')->eq($storyID)
            ->fetchPairs();

        if(empty($relations)) return array();

        return $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->in($relations)->andWhere('deleted')->eq(0)->fetchAll('id');
    }

    /**
     * Get parent demand pairs.
     *
     * @param  int    $poolID
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function getParentDemandPairs($poolID, $demandID = 0)
    {
        $stories = $this->dao->select('id, title')->from(TABLE_DEMAND)
            ->where('deleted')->eq(0)
            ->andWhere('parent')->le(0)
            ->andWhere('status')->in('draft,pass')
            ->andWhere('pool')->eq($poolID)
            ->beginIF($demandID)->andWhere('id')->ne($demandID)->fi()
            ->fetchPairs();
        return array(0 => '') + $stories ;
    }

    /**
     * Update parent status by child.
     *
     * @param  int    $demandID
     * @param  int    $parentID
     * @param  bool   $createAction
     * @access public
     * @return void
     */
    public function updateParentStatus($demandID, $parentID = 0, $createAction = true)
    {
        $childDemand = $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->eq($demandID)->fetch();
        if(empty($parentID)) $parentID = $childDemand->parent;
        if($parentID <= 0) return true;

        $oldParentDemand = $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->eq($parentID)->andWhere('deleted')->eq(0)->fetch();
        if(empty($oldParentDemand)) return $this->dao->update(TABLE_DEMAND)->set('parent')->eq('0')->where('id')->eq($demandID)->exec();
        if($oldParentDemand->parent != '-1') $this->dao->update(TABLE_DEMAND)->set('parent')->eq('-1')->where('id')->eq($parentID)->exec();

        $childrenStatus = $this->dao->select('id,status')->from(TABLE_DEMAND)->where('parent')->eq($parentID)->andWhere('deleted')->eq(0)->fetchPairs('status', 'status');
        if(empty($childrenStatus)) return $this->dao->update(TABLE_DEMAND)->set('parent')->eq('0')->where('id')->eq($parentID)->exec();

        $status = $oldParentDemand->status;
        if(count($childrenStatus) == 1 and current($childrenStatus) == 'closed') $status = current($childrenStatus); // Close parent demand.

        if($status and $oldParentDemand->status != $status)
        {
            $now  = helper::now();
            $demand = new stdclass();
            $demand->status = $status;
            if(strpos('launched,active,changing,draft', $status) !== false)
            {
                $demand->assignedTo   = $oldParentDemand->openedBy;
                $demand->assignedDate = $now;
                $demand->closedBy     = '';
                $demand->closedReason = '';
                $demand->closedDate   = '0000-00-00';
                $demand->reviewedBy   = '';
                $demand->reviewedDate = '0000-00-00';
            }

            if($status == 'closed')
            {
                $demand->assignedTo   = 'closed';
                $demand->assignedDate = $now;
                $demand->closedBy     = $this->app->user->account;
                $demand->closedDate   = $now;
                $demand->closedReason = 'done';
                $demand->closedReason = 'done';
            }

            $demand->lastEditedBy   = $this->app->user->account;
            $demand->lastEditedDate = $now;
            $demand->parent         = '-1';
            $this->dao->update(TABLE_DEMAND)->data($demand)->where('id')->eq($parentID)->exec();
            if(!dao::isError())
            {
                if(!$createAction) return $demand;

                $newParentDemand = $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->eq($parentID)->fetch();
                $changes   = common::createChanges($oldParentDemand, $newParentDemand);
                $action    = '';
                $preStatus = '';
                if(strpos('launched,active,draft,changing', $status) !== false) $action = 'Activated';
                if($status == 'closed')
                {
                    /* Record the status before closed. */
                    $action    = 'closedbysystem';
                    $preStatus = $oldParentDemand->status;
                    $isChanged = $oldParentDemand->changedBy ? true : false;
                    if($preStatus == 'reviewing') $preStatus = $isChanged ? 'changing' : 'draft';
                }
                if($action)
                {
                    $actionID = $this->loadModel('action')->create('demand', $parentID, $action, '', $preStatus, '', false);
                    $this->action->logHistory($actionID, $changes);
                }
            }
        }
        else
        {
            if(!dao::isError())
            {
                $newParentDemand = $this->dao->select('*')->from(TABLE_DEMAND)->where('id')->eq($parentID)->fetch();
                $changes = common::createChanges($oldParentDemand, $newParentDemand);
                if($changes)
                {
                    $actionID = $this->loadModel('action')->create('demand', $parentID, 'Edited', '', '', '', false);
                    $this->action->logHistory($actionID, $changes);
                }
            }
        }
    }

    /**
     * Check force review for user.
     *
     * @access public
     * @return bool
     */
    public function checkForceReview()
    {
        $forceReview = false;

        $forceField       = $this->config->demand->needReview == 0 ? 'forceReview' : 'forceNotReview';
        $forceReviewRoles = !empty($this->config->demand->{$forceField . 'Roles'}) ? $this->config->demand->{$forceField . 'Roles'} : '';
        $forceReviewDepts = !empty($this->config->demand->{$forceField . 'Depts'}) ? $this->config->demand->{$forceField . 'Depts'} : '';

        $forceUsers = '';
        if(!empty($this->config->demand->{$forceField})) $forceUsers = $this->config->demand->{$forceField};

        if(!empty($forceReviewRoles) or !empty($forceReviewDepts))
        {
            $users = $this->dao->select('account')->from(TABLE_USER)
                ->where('deleted')->eq(0)
                ->andWhere(0, true)
                ->beginIF(!empty($forceReviewRoles))
                ->orWhere('(role', true)->in($forceReviewRoles)
                ->andWhere('role')->ne('')
                ->markRight(1)
                ->fi()
                ->beginIF(!empty($forceReviewDepts))->orWhere('dept')->in($forceReviewDepts)->fi()
                ->markRight(1)
                ->fetchAll('account');

            $forceUsers .= "," . implode(',', array_keys($users));
        }

        $forceReview = $this->config->demand->needReview == 0 ? strpos(",{$forceUsers},", ",{$this->app->user->account},") !== false : strpos(",{$forceUsers},", ",{$this->app->user->account},") === false;

        return $forceReview;
    }

    /**
     * Update the demand fields value by review.
     *
     * @param  int    $demandID
     * @param  object $oldDemand
     * @param  object $demand
     * @access public
     * @return object
     */
    public function updateDemandByReview($demandID, $oldDemand, $demand)
    {
        $isSuperReviewer = strpos(',' . trim(zget($this->config->demand, 'superReviewers', ''), ',') . ',', ',' . $this->app->user->account . ',');
        if($isSuperReviewer !== false) return $this->superReview($demandID, $oldDemand, $demand);

        $reviewerList = $this->getReviewerPairs($demandID, $oldDemand->version);
        $reviewedBy   = explode(',', trim($demand->reviewedBy, ','));
        if(!array_diff(array_keys($reviewerList), $reviewedBy))
        {
            $reviewResult = $this->getReviewResult($reviewerList);
            $demand       = $this->setStatusByReviewResult($demand, $oldDemand, $reviewResult);
        }

        return $demand;
    }

    /**
     * Set demand status by reeview result.
     *
     * @param  int    $demand
     * @param  int    $oldDemand
     * @param  int    $result
     * @param  string $reason
     * @access public
     * @return array
     */
    public function setStatusByReviewResult($demand, $oldDemand, $result, $reason = '')
    {
        if($result == 'pass') $demand->status = 'pass';

        if($result == 'clarify')
        {
            /* When the review result of the changed demand is clarify, the status should be changing. */
            $isChanged = $oldDemand->changedBy ? true : false;
            $demand->status = $isChanged ? 'changing' : 'draft';
        }

        if($result == 'revert')
        {
            $demand->status  = 'pass';
            $demand->version = $oldDemand->version - 1;
            if($oldDemand->story) $demand->status = 'distributed';
            $demand->title   = $this->dao->select('title')->from(TABLE_DEMANDSPEC)->where('demand')->eq($demand->id)->andWHere('version')->eq($oldDemand->version - 1)->fetch('title');

            /* Delete versions that is after this version. */
            $this->dao->delete()->from(TABLE_DEMANDSPEC)->where('demand')->eq($demand->id)->andWHere('version')->in($oldDemand->version)->exec();
            $this->dao->delete()->from(TABLE_DEMANDREVIEW)->where('demand')->eq($demand->id)->andWhere('version')->in($oldDemand->version)->exec();
        }

        if($result == 'reject')
        {
            $now    = helper::now();
            $reason = (!empty($demand->closedReason)) ? $demand->closedReason : $reason;

            $demand->status       = 'closed';
            $demand->closedBy     = $this->app->user->account;
            $demand->closedDate   = $now;
            $demand->assignedTo   = 'closed';
            $demand->assignedDate = $now;
            $demand->closedReason = $reason;
        }

        $demand->finalResult = $result;
        return $demand;
    }

    /**
     * Get review result.
     *
     * @param  array $reviewerList
     * @access public
     * @return void
     */
    public function getReviewResult($reviewerList)
    {
        $results      = '';
        $passCount    = 0;
        $rejectCount  = 0;
        $revertCount  = 0;
        $clarifyCount = 0;
        $reviewRule   = $this->config->demand->reviewRules;
        foreach($reviewerList as $reviewer => $result)
        {
            $passCount    = $result == 'pass'    ? $passCount    + 1 : $passCount;
            $rejectCount  = $result == 'reject'  ? $rejectCount  + 1 : $rejectCount;
            $revertCount  = $result == 'revert'  ? $revertCount  + 1 : $revertCount;
            $clarifyCount = $result == 'clarify' ? $clarifyCount + 1 : $clarifyCount;

            $results .= $result . ',';
        }

        $finalResult = '';
        if($reviewRule == 'allpass' and $passCount == count($reviewerList)) $finalResult = 'pass';
        if($reviewRule == 'halfpass' and $passCount >= floor(count($reviewerList) / 2) + 1) $finalResult = 'pass';

        if(empty($finalResult))
        {
            if($clarifyCount >= floor(count($reviewerList) / 2) + 1) return 'clarify';
            if($revertCount  >= floor(count($reviewerList) / 2) + 1) return 'revert';
            if($rejectCount  >= floor(count($reviewerList) / 2) + 1) return 'reject';

            if(strpos($results, 'clarify') !== false) return 'clarify';
            if(strpos($results, 'revert')  !== false) return 'revert';
            if(strpos($results, 'reject')  !== false) return 'reject';
        }

        return $finalResult;
    }

    /**
     * Get the last reviewer.
     *
     * @param  int $demandID
     * @access public
     * @return string
     */
    public function getLastReviewer($demandID)
    {
        return $this->dao->select('t2.new')->from(TABLE_ACTION)->alias('t1')
            ->leftJoin(TABLE_HISTORY)->alias('t2')->on('t1.id = t2.action')
            ->where('t1.objectType')->eq('demand')
            ->andWhere('t1.objectID')->eq($demandID)
            ->andWhere('t2.field')->in('reviewer,reviewers')
            ->andWhere('t2.new')->ne('')
            ->orderBy('t1.id_desc')
            ->fetch('new');
    }

    /**
     * Get status before closed.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function getStatusBeforeClosed($demandID)
    {
        $status = 'draft';
        $action = $this->dao->select('*')->from(TABLE_ACTION)
            ->where('objectID')->eq($demandID)
            ->andWhere('objectType')->eq('demand')
            ->andWhere('action')->in('closed,submitreview')
            ->orderBy('id_desc')
            ->fetch();

        if($action->action == 'closed' and strpos($action->extra, '|') !== false) $status = substr($action->extra, strpos($action->extra, '|') + 1);

        if($action->action == 'submitreview')
        {
            $status = $this->dao->select('*')->from(TABLE_HISTORY)
                ->where('action')->eq($action->id)
                ->andWhere('field')->eq('status')
                ->fetch('old');
        }

        return $status;
    }

    public function superReview($demandID, $oldDemand, $demand, $result = '', $reason = '')
    {
        $result = isset($_POST['result']) ? $this->post->result : $result;
        if(empty($result)) return $demand;

        $reason = isset($_POST['closedReason']) ? $_POST['closedReason'] : $reason;
        $demand  = $this->setStatusByReviewResult($demand, $oldDemand, $result, $reason);

        $this->dao->delete()->from(TABLE_DEMANDREVIEW)
            ->where('demand')->eq($demandID)
            ->andWhere('version')->eq($oldDemand->version)
            ->andWhere('result')->eq('')
            ->exec();

        return $demand;
    }

    public function getReviewerPairs($demandID, $version)
    {
        return $this->dao->select('reviewer,result')->from(TABLE_DEMANDREVIEW)->where('demand')->eq($demandID)->andWhere('version')->eq($version)->fetchPairs('reviewer', 'result');
    }

    /**
     * Merge demand reviewers.
     *
     * @param  array|object  $demands
     * @param  bool          $isObject
     * @access public
     * @return array|object
     */
    public function mergeReviewer($demands, $isObject = false)
    {
        if($isObject)
        {
            $demand   = $demands;
            $demands = (array)$demands;
            $demands[$demand->id] = $demand;
        }

        /* Set child demand id into array. */
        $demandIdList = isset($demands['id']) ? array($demands['id'] => $demands['id']) : array_keys($demands);
        if(isset($demands['id']) and isset($demand->children)) $demandIdList = array_merge($demandIdList, array_keys($demand->children));
        if(!isset($demands['id']))
        {
            foreach($demands as $demand)
            {
                if(isset($demand->children)) $demandIdList = array_merge($demandIdList, array_keys($demand->children));
            }
        }

        $allReviewers = $this->dao->select('t2.demand,t2.reviewer,t2.result')->from(TABLE_DEMAND)->alias('t1')
            ->leftJoin(TABLE_DEMANDREVIEW)->alias('t2')->on('t1.version=t2.version and t1.id=t2.demand')
            ->where('t2.demand')->in($demandIdList)
            ->fetchGroup('demand', 'reviewer');

        foreach($allReviewers as $demandID => $reviewerList)
        {
            if(isset($demands[$demandID]))
            {
                $demands[$demandID]->reviewer  = array_keys($reviewerList);
                $demands[$demandID]->notReview = array();
                foreach($reviewerList as $reviewer => $reviewInfo)
                {
                    if($reviewInfo->result == '') $demands[$demandID]->notReview[] = $reviewer;
                }
            }
            else
            {
                foreach($demands as $id => $demand)
                {
                    if(!isset($demand->children)) continue;
                    if(isset($demand->children[$demandID]))
                    {
                        $demand->children[$demandID]->reviewer  = array_keys($reviewerList);
                        $demand->children[$demandID]->notReview = array();
                        foreach($reviewerList as $reviewer => $reviewInfo)
                        {
                            if($reviewInfo->result == '') $demand->children[$demandID]->notReview[] = $reviewer;
                        }
                    }
                }
            }
        }

        if($isObject) return $demands[$demand->id];
        return $demands;
    }

    /**
     * Recall the demand review.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function recallReview($demandID)
    {
        $oldDemand  = $this->getById($demandID);
        $isChanged = $oldDemand->changedBy ? true : false;

        $demand = clone $oldDemand;
        $demand->status = $isChanged ? 'changing' : 'draft';
        if($oldDemand->story) $demand->status = 'distributed';
        $this->dao->update(TABLE_DEMAND)->set('status')->eq($demand->status)->where('id')->eq($demandID)->exec();

        $this->dao->delete()->from(TABLE_DEMANDREVIEW)->where('demand')->eq($demandID)->andWhere('version')->eq($oldDemand->version)->exec();

        $changes = common::createChanges($oldDemand, $demand);
    }

    /**
     * Recall the demand change.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function recallChange($demandID)
    {
        $oldDemand = $this->getById($demandID);

        /* Update demand title and version and status. */
        $demand = clone $oldDemand;
        $demand->version = $oldDemand->version - 1;
        $demand->title   = $this->dao->select('title')->from(TABLE_DEMANDSPEC)->where('demand')->eq($demandID)->andWHere('version')->eq($demand->version)->fetch('title');
        $demand->status  = 'pass';

        if($oldDemand->story) $demand->status = 'distributed';

        $this->dao->update(TABLE_DEMAND)->set('title')->eq($demand->title)->set('version')->eq($demand->version)->set('status')->eq($demand->status)->where('id')->eq($demandID)->exec();
        $this->changeDemandStatus($demandID);

        /* Delete versions that is after this version. */
        $this->dao->delete()->from(TABLE_DEMANDSPEC)->where('demand')->eq($demandID)->andWHere('version')->eq($oldDemand->version)->exec();
        $this->dao->delete()->from(TABLE_DEMANDREVIEW)->where('demand')->eq($demandID)->andWhere('version')->eq($oldDemand->version)->exec();

        $changes = common::createChanges($oldDemand, $demand);
    }

    /**
     * Distribute.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function distribute($demandID)
    {
        $now       = helper::now();
        $oldDemand = $this->getByID($demandID);
        $products  = isset($_POST['product']) ? $_POST['product'] : array();
        $roadmaps  = isset($_POST['roadmap']) ? $_POST['roadmap'] : array();

        if(isset($_POST['newProduct'])) $products = array($this->createProduct());
        if(dao::isError()) return false;

        foreach($products as $index => $productID)
        {
            if(empty($productID))
            {
                unset($products[$index]);
                unset($roadmaps[$index]);
                continue;
            }

            if(isset($_POST["newRoadmap$index"]))
            {
                $roadmaps[$index] = $this->createRoadmap($index);
                if(dao::isError()) return false;
                unset($_POST["newRoadmap$index"]);
            }
        }

        if(empty($products)) dao::$errors['product'] = $this->lang->demand->errorEmptyProduct;
        if(dao::isError()) return false;

        $demandStatus = $oldDemand->status == 'launched' ? 'launched' : 'distributed';
        foreach($products as $index => $productID)
        {
            $roadmapID = $roadmaps[$index];
            if(!$productID) continue;

            /* Create new story. */
            $story = new stdclass();
            $story->title          = $oldDemand->title;
            $story->pri            = $oldDemand->pri;
            $story->status         = 'active';
            $story->mailto         = $oldDemand->mailto;
            $story->product        = $productID;
            $story->demand         = $demandID;
            $story->roadmap        = $roadmaps[$index];
            $story->branch         = !empty($_POST['branch'][$index]) ? $_POST['branch'][$index] : 0;
            $story->vision         = 'or';
            $story->type           = 'requirement';
            $story->category       = $oldDemand->category;
            $story->duration       = $oldDemand->duration;
            $story->BSA            = $oldDemand->BSA;
            $story->source         = $oldDemand->source;
            $story->sourceNote     = $oldDemand->sourceNote;
            $story->feedbackBy     = $oldDemand->feedbackedBy;
            $story->notifyEmail    = $oldDemand->email;
            $story->keywords       = $oldDemand->keywords;
            $story->openedBy       = $this->app->user->account;
            $story->openedDate     = $now;
            $story->lastEditedBy   = $this->app->user->account;
            $story->lastEditedDate = $now;

            $this->dao->insert(TABLE_STORY)->data($story)
                ->autoCheck()
                ->exec();

            if(dao::isError()) return false;
            $storyID = $this->dao->lastInsertID();

            /* Copy demand files to story. */
            $copiedFiles = '';
            if($oldDemand->files)
            {
                $copiedFiles = '';
                $flag = 'demand' . $oldDemand->id;
                foreach($oldDemand->files as $file)
                {
                    $originName = pathinfo($file->pathname, PATHINFO_FILENAME);
                    $datePath   = substr($file->pathname, 0, 6);
                    $originFile = $this->app->getAppRoot() . "www/data/upload/{$this->app->company->id}/" . "{$datePath}/" . $originName;

                    $copyName = $originName . $flag;
                    $copyFile = $this->app->getAppRoot(). "www/data/upload/{$this->app->company->id}/" . "{$datePath}/" .  $copyName;
                    copy($originFile, $copyFile);

                    $newFileName    = $file->pathname;
                    $newFileName    = str_replace(',', $flag . '.', $newFileName);
                    $file->pathname = $newFileName;

                    $file->objectType = 'story';
                    $file->objectID   = $storyID;
                    $file->addedBy    = $this->app->user->account;
                    $file->addedDate  = helper::now();
                    $file->downloads  = 0;
                    $file->extra      = '';
                    unset($file->id);
                    unset($file->realPath);
                    unset($file->webPath);
                    $this->dao->insert(TABLE_FILE)->data($file)->exec();
                    $copiedFiles .= $this->dao->lastInsertId() . ',';
                }
            }

            $storySpec = new stdclass();
            $storySpec->story   = $storyID;
            $storySpec->version = 1;
            $storySpec->title   = $story->title;
            $storySpec->spec    = $oldDemand->spec;
            $storySpec->verify  = $oldDemand->verify;
            $storySpec->files   = $copiedFiles;

            $this->dao->insert(TABLE_STORYSPEC)->data($storySpec)->exec();

            if(dao::isError()) return false;
            $this->loadModel('action')->create('story', $storyID, 'distributed', '', "$demandID");

            if($roadmapID)
            {
                $roadmap = $this->loadModel('roadmap')->getByID($roadmapID);
                if($roadmap->status == 'launched')
                {
                    $this->dao->update(TABLE_STORY)->set("status")->eq('launched')->set('vision')->eq('or,rnd')->where('id')->eq((int)$storyID)->exec();
                    $demandStatus = 'launched';
                    if(!empty($oldDemand->feedback)) $this->dao->update(TABLE_FEEDBACK)->set('status')->eq('commenting')->where('id')->eq((int)$oldDemand->feedback)->exec();
                }

                /* Update the roadmap linked with the story and the order of the story in the roadmap. */
                $this->dao->update(TABLE_STORY)->set("roadmap")->eq($roadmapID)->where('id')->eq((int)$storyID)->exec();

                $this->loadModel('story')->updateStoryOrderOfRoadmap($storyID, $roadmapID);

                $this->action->create('story', $storyID, 'linked2roadmap', '', $roadmapID);
                $this->action->create('roadmap', $roadmapID, 'linkur', '', $storyID);
            }
        }

        $demand = fixer::input('post')
            ->add('status', $demandStatus)
            ->setDefault('distributedBy',   $this->app->user->account)
            ->setDefault('distributedDate', $now)
            ->setDefault('lastEditedBy',   $this->app->user->account)
            ->setDefault('lastEditedDate', $now)
            ->remove('uid,productName,roadmapName,roadmapStart,roadmapEnd,newProduct,branch,newRoadmap,product,roadmap,branch,files')
            ->get();

        $oldDemand->product = explode(',', $oldDemand->product);
        $demand->product    = array_unique(array_merge($oldDemand->product, $products));
        $demand->product    = trim(implode(',', $demand->product), ',');

        $this->dao->update(TABLE_DEMAND)->data($demand, 'comment')
            ->autoCheck()
            ->where('id')->eq((int)$demandID)
            ->exec();

        if(!dao::isError()) return common::createChanges($oldDemand, $demand);
        return false;
    }

    /**
     * Retract a story.
     *
     * @param  object $story
     * @access public
     * @return void
     */
    public function retract($story)
    {
        $_POST['closedReason']  = 'cancel';
        $_POST['retractedBy']   = $this->app->user->account;
        $_POST['retractedDate'] = helper::now();

        if(!isset($_POST['comment']))         $_POST['comment'] = '';
        if(!isset($_POST['retractedReason'])) $_POST['retractedReason'] = 'omit';

        $changes = $this->loadModel('story')->close($story->id);

        if($changes)
        {
            $preStatus = $story->status;
            $isChanged = $story->changedBy ? true : false;
            if($preStatus == 'reviewing') $preStatus = $isChanged ? 'changing' : 'draft';

            $actionID  = $this->loadModel('action')->create('story', $story->id, 'retractClosed', '', ucfirst($this->post->closedReason));
            $this->action->logHistory($actionID, $changes);
        }

        $this->executeHooks($story->id);

        if(dao::isError())
        {
            $response['result']  = 'fail';
            $response['message'] = dao::getError();
            $this->send($response);
        }

        /* Change demand status. */
        $this->changeDemandStatus($story->demand);

        $this->loadModel('action')->create('demand', $story->demand, 'retracted', $this->post->comment, $story->id);
    }

    /**
     * Change demand status.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function changeDemandStatus($demandID = 0, $filterID = 0, $isUpdate = true)
    {
        $demand = $this->getByID($demandID);
        if($demand->status == 'reviewing' and $this->app->rawMethod != 'review') return 'reviewing';
        if($demand->status == 'changing') return 'changing';

        $status = 'distributed';
        $demandStories = $this->dao->select('*')->from(TABLE_STORY)
            ->where('deleted')->eq(0)
            ->andWhere('demand')->eq($demandID)
            ->andWhere('retractedBy')->eq('')
            ->beginIf($filterID)->andWhere('id')->notIN($filterID)->fi()
            ->fetchGroup('status', 'id');

        if(empty($demandStories)) $status = 'pass';
        if(isset($demandStories['launched']) or isset($demandStories['developing'])) $status = 'launched';

        if($isUpdate and $demand->status != 'closed') $this->dao->update(TABLE_DEMAND)->set('status')->eq($status)->where('id')->eq($demandID)->exec();
        return $status;
    }

    /**
     * Create roadmap.
     *
     * @param  int $index
     * @access public
     * @return void
     */
    public function createRoadmap($index = 0)
    {
        $this->loadModel('roadmap');
        /* If parent not empty, link products or create products. */
        $roadmap = new stdclass();
        $roadmap->name        = $_POST['roadmapName'][$index];
        $roadmap->product     = $_POST['product'][$index];
        $roadmap->branch      = isset($_POST['branch'][$index]) ? $_POST['branch'][$index] : 0;
        $roadmap->createdBy   = $this->app->user->account;
        $roadmap->createdDate = helper::today();
        $roadmap->status      = 'wait';

        $this->dao->insert(TABLE_ROADMAP)->data($roadmap)
            ->autoCheck()
            ->batchCheck($this->config->roadmap->create->requiredFields, 'notempty')->exec();

        if(dao::isError()) return false;
        $roadmapID = $this->dao->lastInsertId();
        $this->loadModel('action')->create('roadmap', $roadmapID, 'created');

        return $roadmapID;
    }

    /**
     * Create product when distribute demand.
     *
     * @access public
     * @return void
     */
    public function createProduct()
    {
        $this->app->loadLang('product');
        /* If parent not empty, link products or create products. */
        $product = new stdclass();
        $product->name           = $this->post->productName;
        $product->acl            = 'open';
        $product->PMT            = $this->app->user->account;
        $product->createdBy      = $this->app->user->account;
        $product->createdDate    = helper::now();
        $product->status         = 'wait';
        $product->createdVersion = $this->config->version;
        $product->vision         = $this->config->vision;

        $this->dao->insert(TABLE_PRODUCT)->data($product)->autoCheck()->check('name', 'notempty')->exec();

        if(dao::isError()) return false;
        $productID = $this->dao->lastInsertId();
        $this->loadModel('action')->create('product', $productID, 'opened');
        $this->dao->update(TABLE_PRODUCT)->set('`order`')->eq($productID * 5)->where('id')->eq($productID)->exec();
        return $productID;
    }

    /**
     * Get export demands.
     *
     * @param  int    $poolID
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function getExportDemands($poolID, $orderBy = 'id_desc')
    {
        $this->loadModel('file');

        /* Create field lists. */
        $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $this->config->demand->exportFields);
        foreach($fields as $key => $fieldName)
        {
            $fieldName = trim($fieldName);
            $fields[$fieldName] = isset($this->lang->demand->$fieldName) ? $this->lang->demand->$fieldName : $fieldName;
            unset($fields[$key]);
        }

        /* Get demands. */
        $demands        = array();
        $selectedIDList = $this->cookie->checkedItem ? $this->cookie->checkedItem : '0';
        if($this->session->demandOnlyCondition)
        {
            $queryFields = 'id,title,pri,status,assignedTo,category,duration,BSA';
            if($this->post->exportType == 'selected')
            {
                $demands = $this->dao->select($queryFields)->from(TABLE_DEMAND)
                    ->where('id')->in($selectedIDList)
                    ->andWhere('pool')->eq($poolID)
                    ->orderBy($orderBy)
                    ->fetchAll('id');
            }
            else
            {
                $demands = $this->dao->select($queryFields)->from(TABLE_DEMAND)
                    ->where($this->session->demandQueryCondition)
                    ->andWhere('pool')->eq($poolID)
                    ->orderBy($orderBy)
                    ->fetchAll('id');
            }
        }
        else
        {
            if($this->post->exportType == 'selected')
            {
                $stmt  = $this->dbh->query("SELECT * FROM " . TABLE_DEMAND . "WHERE `id` IN({$selectedIDList})" . " ORDER BY " . strtr($orderBy, '_', ' '));
            }
            else
            {
                $stmt  = $this->dbh->query($this->session->demandQueryCondition . " ORDER BY " . strtr($orderBy, '_', ' '));
            }
            while($row = $stmt->fetch()) $demands[$row->id] = $row;
        }

        if(empty($demands)) return $demands;

        $demandIdList = array_keys($demands);
        $children     = array();
        foreach($demands as $demand)
        {
            if($demand->parent > 0 and isset($demands[$demand->parent]))
            {
                $children[$demand->parent][$demand->id] = $demand;
                unset($demands[$demand->id]);
            }
        }

        if(!empty($children))
        {
            $recordDemands = array();
            foreach($demands as $demand)
            {
                $recordDemands[$demand->id] = $demand;
                if(isset($children[$demand->id]))
                {
                    foreach($children[$demand->id] as $childrenID => $childrenDemand)
                    {
                        $recordDemands[$childrenID] = $childrenDemand;
                    }
                }
                unset($demands[$demand->id]);
            }
            $demands = $recordDemands;
        }

        /* Get users, products and relations. */
        $users           = $this->loadModel('user')->getPairs('noletter');
        $products        = $this->loadModel('product')->getPairs('nocode');
        $relatedDemandsIds = array();

        foreach($demands as $demand) $relatedDemandsIds[$demand->id] = $demand->id;

        /* Get related objects title or names. */
        $relatedSpecs   = $this->dao->select('*')->from(TABLE_DEMANDSPEC)->where('`demand`')->in($demandIdList)->orderBy('version desc')->fetchGroup('demand');
        $relatedDemands = $this->dao->select('*')->from(TABLE_DEMAND)->where('`id`')->in($relatedDemandsIds)->fetchPairs('id', 'title');

        $fileIdList = array();
        foreach($relatedSpecs as $demandID => $relatedSpec)
        {
            if(!empty($relatedSpec[0]->files)) $fileIdList[] = $relatedSpec[0]->files;
        }
        $fileIdList   = array_unique($fileIdList);
        $relatedFiles = $this->dao->select('id, objectID, pathname, title')->from(TABLE_FILE)->where('objectType')->eq('demand')->andWhere('objectID')->in($demandIdList)->andWhere('extra')->ne('editor')->fetchGroup('objectID');
        $filesInfo    = $this->dao->select('id, objectID, pathname, title')->from(TABLE_FILE)->where('id')->in($fileIdList)->andWhere('extra')->ne('editor')->fetchAll('id');

        foreach($demands as $demand)
        {
            $demand->spec   = '';
            $demand->verify = '';
            if(isset($relatedSpecs[$demand->id]))
            {
                $demandSpec     = $relatedSpecs[$demand->id][0];
                $demand->title  = $demandSpec->title;
                $demand->spec   = $demandSpec->spec;
                $demand->verify = $demandSpec->verify;

                if(!empty($demandSpec->files) and empty($relatedFiles[$demand->id]) and !empty($filesInfo[$demandSpec->files]))
                {
                    $relatedFiles[$demand->id][0] = $filesInfo[$demandSpec->files];
                }
            }


            $demand->spec = htmlspecialchars_decode($demand->spec);
            $demand->spec = str_replace("<br />", "\n", $demand->spec);
            $demand->spec = str_replace('"', '""', $demand->spec);
            $demand->spec = str_replace('&nbsp;', ' ', $demand->spec);
            $demand->spec = strip_tags($demand->spec, '<img>');

            $demand->verify = htmlspecialchars_decode($demand->verify);
            $demand->verify = str_replace("<br />", "\n", $demand->verify);
            $demand->verify = str_replace('"', '""', $demand->verify);
            $demand->verify = str_replace('&nbsp;', ' ', $demand->verify);
            $demand->verify = strip_tags($demand->verify, '<img>');

            if($demand->childDemands)
            {
                $tmpChildDemands = array();
                $childDemandsIdList = explode(',', $demand->childDemands);
                foreach($childDemandsIdList as $childDemandID)
                {
                    if(empty($childDemandID)) continue;

                    $childDemandID = trim($childDemandID);
                    $tmpChildDemands[] = zget($relatedDemands, $childDemandID);
                }
                $demand->childDemands = join("; \n", $tmpChildDemands);
            }

            /* Set related files. */
            $demand->files = '';
            if(isset($relatedFiles[$demand->id]))
            {
                foreach($relatedFiles[$demand->id] as $file)
                {
                    $fileURL = common::getSysURL() . helper::createLink('file', 'download', "fileID=$file->id");
                    $demand->files .= html::a($fileURL, $file->title, '_blank') . '<br />';
                }
            }

            $demand->mailto = trim(trim($demand->mailto), ',');
            $mailtos = explode(',', $demand->mailto);
            $demand->mailto = '';
            foreach($mailtos as $mailto)
            {
                $mailto = trim($mailto);
                if(isset($users[$mailto])) $demand->mailto .= $users[$mailto] . ',';
            }
            $demand->mailto = rtrim($demand->mailto, ',');

            $demand->reviewedBy = trim(trim($demand->reviewedBy), ',');
            $reviewedBys = explode(',', $demand->reviewedBy);
            $demand->reviewedBy = '';
            foreach($reviewedBys as $reviewedBy)
            {
                $reviewedBy = trim($reviewedBy);
                if(isset($users[$reviewedBy])) $demand->reviewedBy .= $users[$reviewedBy] . ',';
            }
            $demand->reviewedBy = rtrim($demand->reviewedBy, ',');

            /* Set child demand title. */
            if($demand->parent > 0 && strpos($demand->title, htmlentities('>', ENT_COMPAT | ENT_HTML401, 'UTF-8')) !== 0) $demand->title = '>' . $demand->title;
        }

        return $demands;
    }

    /**
     * Get distributed products and status.
     *
     * @param  int    $demandID
     * @access public
     * @return array
     */
    public function getDistributedProducts($demandID)
    {
        return $this->dao->select('product, status')->from(TABLE_STORY)->where('demand')->eq($demandID)->andWhere('deleted')->eq('0')->andWhere('retractedBy')->eq('')->fetchPairs();
    }
}
