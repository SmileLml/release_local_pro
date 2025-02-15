<?php
class demand extends control
{
    public function __construct($module = '', $method = '')
    {
        parent::__construct($module, $method);
        $this->loadModel('story');
        $this->loadModel('demandpool');
    }

    /**
     * Browse demand list.
     *
     * @param  int    $poolID
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($poolID = 0, $browseType = '', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        if(empty($browseType))
        {
            $browseType = 'assignedtome';
            $demands = $this->demand->getList($poolID, $browseType, 0, $orderBy, null, '', true);
            if(empty($demands)) $browseType = 'all';
        }

        $this->loadModel('datatable');
        $datatableId  = $this->moduleName . ucfirst($this->methodName);
        if(!isset($this->config->datatable->$datatableId->mode))
        {
            $this->loadModel('setting')->setItem("{$this->app->user->account}.datatable.$datatableId.mode", 'datatable');
            $this->config->datatable->$datatableId = new stdclass();
            $this->config->datatable->$datatableId->mode = 'datatable';
        }

        $poolID = $this->demandpool->setMenu($poolID);

        $browseType = strtolower($browseType);

        $this->session->set('demandList', $this->app->getURI(true));

        setcookie('demandModule', 0, 0, $this->config->webRoot, '', $this->config->cookieSecure, false);

        $queryID = ($browseType == 'bysearch') ? (int)$param : 0;
        $actionURL = $this->createLink('demand', 'browse', "poolID=$poolID&browseType=bySearch&param=myQueryID");
        $this->demand->buildSearchForm($poolID, $queryID, $actionURL);

        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $demands = $this->demand->getList($poolID, $browseType, $queryID, $orderBy, $pager, '', true);
        if(!empty($demands)) $demands = $this->demand->mergeReviewer($demands);

        $this->view->title       = $this->lang->demand->browse;
        $this->view->demands     = $demands;
        $this->view->orderBy     = $orderBy;
        $this->view->pager       = $pager;
        $this->view->browseType  = $browseType;
        $this->view->poolID      = $poolID;
        $this->view->demandpools = $this->demandpool->getPairs();
        $this->view->users       = $this->loadModel('user')->getPairs('noletter');
        $this->display();
    }

    /**
     * Create a demand.
     *
     * @param  int    $poolID
     * @param  int    $demandID
     * @param  string $extra
     * @access public
     * @return void
     */
    public function create($poolID = 0, $demandID = 0, $extra = '')
    {
        if($this->app->rawMethod == 'todemand') $this->config->demand->create->requiredFields .= ',pool';
        $poolID = $this->demandpool->setMenu($poolID);
        $extra = str_replace(array(',', ' '), array('&', ''), $extra);
        parse_str($extra, $output);
        $fromType = isset($output['fromType']) ? $output['fromType'] : '';
        $fromID   = isset($output['fromID']) ? $output['fromID'] : '';

        $demand = new stdclass();
        if(empty($fromType))
        {
            $demand = $this->demand->getByID($demandID);
        }
        else if($fromType == 'feedback')
        {
            $feedback = $this->loadModel('feedback')->getById($fromID);
            $demand   = new stdclass();
            $demand->product    = $feedback->product;
            $demand->title      = $feedback->title;
            $demand->pri        = $feedback->pri;
            $demand->feedbackBy = $feedback->feedbackBy;
            $demand->mail       = $feedback->notifyEmail;
            $demand->spec       = $feedback->desc;
            $demand->keywords   = $feedback->keywords;
            $demand->files      = $feedback->files;

            $this->feedback->setMenu($feedback->product, 'demand');
        }

        if($_POST)
        {
            if($fromType == 'feedback') $_POST['feedback'] = $fromID;
            $demandID = $this->demand->create($poolID);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            if(!empty($fromType))
            {
                $this->loadModel('action')->create('demand', $demandID, 'From' . ucfirst($fromType), '', $fromID);
                $response['locate']  = $location = $this->createLink('feedback', 'adminView', "feedbackID=$fromID");
            }
            else
            {
                $this->loadModel('action')->create('demand', $demandID, 'created');
                $response['locate']  = inlink('browse', "poolID=$poolID&browseType=all");
            }

            if(!empty($fileIDPairs)) $this->dao->update(TABLE_FILE)->set('objectID')->eq($demandID)->where('id')->in($fileIDPairs)->exec();

            $this->send($response);
        }

        /* Set Custom*/
        $customFields = array();
        foreach(explode(',', $this->config->demand->list->customCreateFields) as $field) $customFields[$field] = $this->lang->demand->$field;

        $products = $this->loadModel('product')->getPairs('noclosed');
        $pool     = $this->demandpool->getByID($poolID);
        if(!empty($pool->products)) $products = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in($pool->products)->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()->andWhere('deleted')->eq(0)->andWhere('status')->eq('normal')->orderBy('id')->fetchPairs();
        if($poolID)
        {
            $this->view->parents   = $this->demand->getParentDemandPairs($poolID);
            $this->view->reviewers = $this->demandpool->getReviewers($poolID, $this->app->user->account);
            $this->view->assignTo  = $this->demandpool->getAssignedTo($poolID);
        }

        $this->view->title        = $this->lang->demand->create;
        $this->view->users        = $this->loadModel('user')->getPairs('noclosed');
        $this->view->pool         = $pool;
        $this->view->needReview   = ($this->config->demand->needReview == 0 or !$this->demand->checkForceReview()) ? "checked='checked'" : "";
        $this->view->demandpools  = array('' => '') + $this->demandpool->getPairs('noclosed');
        $this->view->products     = array(0 => '') + $products;
        $this->view->customFields = $customFields;
        $this->view->showFields   = $this->config->demand->custom->createFields;
        $this->view->demand       = $demand;
        $this->view->from         = $fromType;
        $this->display();
    }

    /**
     * Batch create demands.
     *
     * @param  int    $poolID
     * @param  int    $demandID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function batchCreate($poolID = 0, $demandID = 0, $confirm = 'no')
    {
        $poolID = $this->demandpool->setMenu($poolID);

        if($demandID)
        {
            $demand = $this->demand->getByID($demandID);
            if($demand->status == 'distributed' and $confirm == 'no')
            {
                echo js::confirm($this->lang->demand->subdivideNotice, $this->createLink('demand', 'batchCreate', "poolID=$poolID&demand=$demandID&confirm=yes"), $this->session->demandList, 'parent', 'parent');
                exit;
            }
        }

        if($_POST)
        {
            $demandIdList = $this->demand->batchCreate($poolID, $demandID);
            if(dao::isError())
            {
                $response = array('result' => 'fail', 'message' => dao::getError());
                return $this->send($response);
            }

            if($demandID and !empty($demandIdList)) $this->demand->subdivide($demandID, $demandIdList);

            $response = array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "poolID=$poolID&browseType=all"));
            return $this->send($response);
        }

        /* Set Custom*/
        $customFields = array();
        foreach(explode(',', $this->config->demand->list->customBatchCreateFields) as $field) $customFields[$field] = $this->lang->demand->$field;

        /* Process upload images. */
        if($this->session->demandImagesFile)
        {
            $files = $this->session->demandImagesFile;
            foreach($files as $fileName => $file)
            {
                $title = $file['title'];
                $titles[$title] = $fileName;
            }
            $this->view->titles = $titles;
        }

        $products = $this->loadModel('product')->getPairs('noclosed');
        $pool     = $this->demandpool->getByID($poolID);
        if(!empty($pool->products)) $products = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in($pool->products)->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()->andWhere('deleted')->eq(0)->andWhere('status')->ne('closed')->orderBy('id')->fetchPairs();

        $this->view->customFields = $customFields;
        $this->view->showFields   = $this->config->demand->custom->batchCreateFields;

        $this->view->title        = $this->lang->demand->batchCreate;
        $this->view->users        = $this->loadModel('user')->getPairs('nodeleted|noclosed');
        $this->view->assignToList = $this->demandpool->getAssignedTo($poolID);
        $this->view->pool         = $pool;
        $this->view->poolID       = $poolID;
        $this->view->demand       = $this->demand->getByID($demandID);
        $this->view->demandID     = $demandID;
        $this->view->products     = array(0 => '') + $products;
        $this->display();
    }

    /**
     * Edit a demand.
     *
     * @param  int $demandID
     * @access public
     * @return void
     */
    public function edit($demandID = 0)
    {
        $demand = $this->loadModel('demand')->getByID($demandID);
        $this->demandpool->setMenu($demand->pool);

        if($_POST)
        {
            $_POST['product'] = isset($_POST['product']) ? $_POST['product'] : array();
            $changes = $this->demand->update($demandID);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            if($changes or $this->post->comment != '')
            {
                $actionID = $this->loadModel('action')->create('demand', $demandID, 'edited', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            $oldProducts  = $demand->product;
            $products     = isset($_POST['product']) ? $_POST['product'] : array();
            $oldProducts  = explode(',', trim($oldProducts, ','));
            $diffProducts = array_diff($oldProducts, $products);

            if(!empty($diffProducts))
            {
                unset($_POST);
                $retractStories = $this->demand->getDemandStories($demandID, $diffProducts);
                foreach($retractStories as $story) $this->demand->retract($story);
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = inlink('view', "demandID=$demandID");

            $this->send($response);
        }

        $products = $this->loadModel('product')->getPairs('noclosed');
        $pool     = $this->demandpool->getByID($demand->pool);
        if(!empty($pool->products))
        {
            $productIdList = trim($pool->products, ',') . ',' . trim($demand->product, ',');
            $products = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in($productIdList)->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()->andWhere('deleted')->eq(0)->andWhere('status')->ne('closed')->orderBy('id')->fetchPairs();
        }

        $this->view->title               = $this->lang->demand->edit;
        $this->view->users               = $this->loadModel('user')->getPairs('noclosed');
        $this->view->demand              = $demand;
        $this->view->actions             = $this->loadModel('action')->getList('demand', $demandID);
        $this->view->products            = array(0 => '') + $products;
        $this->view->parents             = $this->demand->getParentDemandPairs($demand->pool, $demandID);
        $this->view->reviewers           = $this->demandpool->getReviewers($demand->pool, $demand->createdBy);
        $this->view->assignToList        = $this->demandpool->getAssignedTo($demand->pool);
        $this->view->needReview          = (($this->config->demand->needReview == 0 or !$this->demand->checkForceReview()) and empty($demand->reviewer)) ? "checked='checked'" : "";
        $this->view->demandpools         = $this->demandpool->getPairs();
        $this->view->distributedProducts = $this->demand->getDistributedProducts($demandID);
        $this->display();
    }

    /**
     * View a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function view($demandID = 0, $version = 0)
    {
        $demand = $this->demand->getByID($demandID, $version);
        if(!$demand) return print(js::error($this->lang->notFound) . js::locate($this->createLink('demandpool', 'browse')));

        $uri = $this->app->getURI(true);
        $this->session->set('demandList', $uri);

        $demand = $this->demand->mergeReviewer($demand, true);
        $this->loadModel('demandpool')->setMenu($demand->pool);

        $version = empty($version) ? $demand->version : $version;

        $this->view->title        = $this->lang->demand->view;
        $this->view->users        = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions      = $this->loadModel('action')->getList('demand', $demandID);
        $this->view->demand       = $demand;
        $this->view->version      = $version;
        $this->view->demandpools  = $this->demandpool->getPairs();
        $this->view->products     = array(0 => '') + $this->loadModel('product')->getPairs();
        $this->view->roadmaps     = $this->loadModel('roadmap')->getPairs($demand->product);
        $this->view->preAndNext   = $this->loadModel('common')->getPreAndNextObject('demand', $demandID);

        $this->display();
    }

    /**
     * AssignTo a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function assignTo($demandID)
    {
        if(!empty($_POST))
        {
            $changes = $this->demand->assign($demandID);
            if(dao::isError()) die(js::error(dao::getError()));
            if($changes)
            {
                $actionID = $this->loadModel('action')->create('demand', $demandID, 'Assigned', $this->post->comment, $this->post->assignedTo);
                $this->action->logHistory($actionID, $changes);
            }

            return print(js::reload('parent.parent'));
        }

        $demand = $this->demand->getByID($demandID);

        $this->view->demand  = $demand;
        $this->view->actions = $this->loadModel('action')->getList('demand', $demandID);
        $this->view->users   = $this->loadModel('demandpool')->getAssignedTo($demand->pool);
        $this->display();
    }

    /**
     * Review a demand.
     *
     * @param  int    $demandID
     * @param  string $from      product|project
     * @param  string $demandType demand|requirement
     * @access public
     * @return void
     */
    public function review($demandID)
    {
        $this->loadModel('story');

        if(!empty($_POST))
        {
            $this->demand->review($demandID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('demand', 'view', "demandID=$demandID"), 'parent'));
        }

        /* Get demand and product. */
        $demand     = $this->demand->getById($demandID);
        $demandpool = $this->loadModel('demandpool')->getByID($demand->pool);
        $this->demandpool->setMenu($demandpool->id);

        /* Set the review result options. */
        $reviewers = $this->demand->getReviewerPairs($demandID, $demand->version);
        $this->lang->demand->resultList = $this->lang->demand->reviewResultList;

        if($demand->status == 'reviewing')
        {
            if($demand->version == 1) unset($this->lang->demand->resultList['revert']);
            if($demand->version > 1)  unset($this->lang->demand->resultList['reject']);
        }

        $this->view->title        = $this->lang->demand->review . $this->lang->colon . $demand->title;
        $this->view->demand       = $demand;
        $this->view->actions      = $this->loadModel('action')->getList('demand', $demandID);
        $this->view->users        = $this->loadModel('user')->getPairs('nodeleted|noclosed');
        $this->view->assignToList = $this->demandpool->getAssignedTo($demand->pool);
        $this->view->reviewers    = $reviewers;
        $this->view->isLastOne    = count(array_diff(array_keys($reviewers), explode(',', $demand->reviewedBy))) == 1 ? true : false;

        $this->display();
    }

    /**
     * Submit review.
     *
     * @param  int    $demandID
     * @param  string $demandType demand|requirement
     * @access public
     * @return void
     */
    public function submitReview($demandID, $demandType = 'demand')
    {
        $this->loadModel('demandpool');

        if($_POST)
        {
            $changes = $this->demand->submitReview($demandID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $actionID = $this->loadModel('action')->create('demand', $demandID, 'submitReview');
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return print(js::closeModal('parent.parent', 'this'));
            return print(js::locate($this->createLink('demand', 'view', "demandID=$demandID"), 'parent'));
        }

        $demand     = $this->demand->getById($demandID);
        $demandpool = $this->demandpool->getById($demand->pool);

        /* Get demand reviewer. */
        if(!$demand->reviewer and $this->demand->checkForceReview())
        {
            $demand->reviewer = current(explode(',', trim($demandpool->reviewer, ',')));
            if(!$demand->reviewer) $demand->reviewer = current(explode(',', trim($demandpool->owner, ',')));
        }

        $reviewers = $this->demandpool->getReviewers($demand->pool, $demand->createdBy);

        $this->view->demand       = $demand;
        $this->view->actions      = $this->loadModel('action')->getList('demand', $demandID);
        $this->view->reviewers    = $reviewers;
        $this->view->users        = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->needReview   = (($this->config->demand->needReview == 0 or !$this->demand->checkForceReview()) and empty($demand->reviewer)) ? "checked='checked'" : "";
        $this->view->lastReviewer = $this->demand->getLastReviewer($demand->id);

        $this->display();
    }

    /**
     * Recall the demand review or demand change.
     *
     * @param  int    $demandID
     * @param  string $confirm   no|yes
     * @access public
     * @return void
     */
    public function recall($demandID, $confirm = 'no')
    {
        $this->app->loadLang('demand');
        $demand = $this->demand->getById($demandID);

        if($confirm == 'no')
        {
            $confirmTips = $demand->status == 'changing' ? $this->lang->story->confirmRecallChange : $this->lang->story->confirmRecallReview;
            return print(js::confirm($confirmTips, $this->createLink('demand', 'recall', "demandID=$demandID&confirm=yes")));
        }
        else
        {
            if($demand->status == 'changing')  $this->demand->recallChange($demandID);
            if($demand->status == 'reviewing') $this->demand->recallReview($demandID);

            $action = $demand->status == 'changing' ? 'recalledChange' : 'Recalled';
            $this->loadModel('action')->create('demand', $demandID, $action);

            return print(js::reload('parent'));
        }
    }

    /**
     * Change a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function change($demandID)
    {
        $this->loadModel('file');
        $this->loadModel('story');

        if(!empty($_POST))
        {
            $changes = $this->demand->change($demandID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            if($this->post->comment != '' or !empty($changes))
            {
                $action   = !empty($changes) ? 'Changed' : 'Commented';
                $actionID = $this->loadModel('action')->create('demand', $demandID, $action, $this->post->comment);
                $this->action->logHistory($actionID, $changes);

                /* Record submit review action. */
                $demand = $this->dao->findById((int)$demandID)->from(TABLE_DEMAND)->fetch();
                if($demand->status == 'reviewing') $this->action->create('demand', $demandID, 'submitReview');
            }

            $link = $this->createLink('demand', 'view', "demandID=$demandID");
            return print($this->send(array('locate' => $link, 'message' => $this->lang->saveSuccess, 'result' => 'success')));
        }

        $demand = $this->demand->getByID($demandID);
        $this->demandpool->setMenu($demand->pool);

        $reviewer = $this->demand->getReviewerPairs($demandID, $demand->version);

        /* Assign. */
        $this->view->title        = $this->lang->demand->change . $this->lang->colon . $demand->title;
        $this->view->demand       = $demand;
        $this->view->needReview   = ($this->config->demand->needReview == 0 or !$this->demand->checkForceReview()) ? "checked='checked'" : "";
        $this->view->reviewer     = implode(',', array_keys($reviewer));
        $this->view->reviewers    = $this->demandpool->getReviewers($demand->pool, $demand->createdBy);
        $this->view->lastReviewer = $this->demand->getLastReviewer($demand->id);
        $this->view->users        = $this->loadModel('user')->getPairs('nodeleted');
        $this->view->actions      = $this->loadModel('action')->getList('demand', $demandID);

        $this->display();
    }

    /**
     * Delete a demand.
     *
     * @param  int    $demandID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($demandID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            echo js::confirm($this->lang->demand->confirmDelete, $this->createLink('demand', 'delete', "demand=$demandID&confirm=yes"), '');
            exit;
        }
        else
        {
            $demand = $this->demand->getByID($demandID);
            $this->demand->delete(TABLE_DEMAND, $demandID);

            if($demand->parent > 0)
            {
                $this->demand->updateParentStatus($demandID);
                $this->loadModel('action')->create('demand', $demand->parent, 'deleteChildrenDemand', '', $demandID);
            }

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));

            if(isonlybody()) return print(js::reload('parent.parent'));

            $locateLink = $this->createLink('demand', 'browse', "poolID=$demand->pool");
            die(js::locate($locateLink, 'parent'));
        }
    }

    /**
     * Close a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function close($demandID = 0)
    {
        $demand = $this->demand->getByID($demandID);
        if($_POST)
        {
            $changes = $this->demand->close($demandID);

            if(dao::isError()) return print(js::error(dao::getError()));

            if(!empty($demand->feedback) && $_POST['closedReason'] == 'done')
            {
                $this->loadModel('feedback')->updateStatus('demand', $demand->feedback, 'closed');
            }

            if($changes || $this->post->comment != '')
            {
                $actionID = $this->loadModel('action')->create('demand', $demandID, 'closed', $this->post->comment, ucfirst($this->post->closedReason) . ($this->post->duplicateDemand ? ':' . (int)$this->post->duplicateDemand : '') . "|$demand->status");
                $this->action->logHistory($actionID, $changes);
            }

            return print(js::closeModal('parent.parent'));
        }

        $demands = $this->demand->getPairs($demand->pool);

        if($demands)
        {
            if(isset($demands[$demand->id])) unset($demands[$demand->id]);
            foreach($demands as $id => $title)
            {
                $demands[$id] = "$id:$title";
            }
        }

        $this->view->title   = $this->lang->demand->close;
        $this->view->demand  = $demand;
        $this->view->demands = $demands;
        $this->view->users   = $this->loadModel('user')->getPairs('nodeleted');
        $this->view->actions = $this->loadModel('action')->getList('demand', $demandID);
        $this->display();
    }

    /**
     * Activate a demand.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function activate($demandID = 0)
    {
        if($_POST)
        {
            $changes = $this->demand->activate($demandID);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            if($changes || $this->post->comment != '')
            {
                $actionID = $this->loadModel('action')->create('demand', $demandID, 'activated', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = 'parent';

            $this->send($response);
        }
        $demand = $this->demand->getByID($demandID);

        $this->view->title   = $this->lang->demand->activate;
        $this->view->demand  = $demand;
        $this->view->users   = $this->loadModel('demandpool')->getAssignedTo($demand->pool);
        $this->view->actions = $this->loadModel('action')->getList('demand', $demandID);
        $this->display();
    }

    /**
     * Distribute.
     *
     * @param  int    $demandID
     * @access public
     * @return void
     */
    public function distribute($demandID = 0)
    {
        if($_POST)
        {
            $changes = $this->demand->distribute($demandID);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            if($changes || $this->post->comment != '')
            {
                $actionID = $this->loadModel('action')->create('demand', $demandID, 'distributed', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = 'parent';

            $this->send($response);
        }

        $demand         = $this->demand->getByID($demandID);
        $products       = $this->loadModel('product')->getPairs('noclosed');
        $pool           = $this->demandpool->getByID($demand->pool);
        $demandProducts = $demand->product ? explode(',', $demand->product) : array();

        if(!empty($pool->products)) $products = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in($pool->products)->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()->andWhere('status')->ne('closed')->andWhere('deleted')->eq(0)->orderBy('id')->fetchPairs();
        $distributedProducts = $this->demand->getDistributedProducts($demandID);
        foreach($products as $id => $name)
        {
            if(isset($distributedProducts[$id])) unset($products[$id]);
        }

        $demandProducts = array_intersect(array_keys($products), $demandProducts);

        $this->view->title       = $this->lang->demand->activate;
        $this->view->roadmaps    = array();
        $this->view->demand      = $demand;
        $this->view->users       = $this->loadModel('demandpool')->getAssignedTo($demand->pool);
        $this->view->actions     = $this->loadModel('action')->getList('demand', $demandID);
        $this->view->products    = array('') + $products;
        $this->view->preProducts = $demandProducts + array('');

        $this->display();
    }

    /**
     * Retract distributed story.
     *
     * @param  int    $storyID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function retract($storyID, $confirm = 'no')
    {
        $story = $this->loadModel('story')->getById($storyID);
        if(in_array($story->status, array('closed', 'developing')) && $confirm == 'no')
        {
            return print(js::confirm($this->lang->demand->retractedTips[$story->status], $this->createLink('demand', 'retract', "storyID=$storyID&confirm=yes"), helper::createLink('demand', 'view', "demandID=$story->demand"), 'self', 'parent.parent'));
        }

        if($_POST)
        {
            $this->demand->retract($story);
            if(dao::isError())
            {
                if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => dao::getError()));
                return print(js::error(dao::getError()));
            }
            return print(js::closeModal('parent.parent'));
        }

        $this->story->getAffectedScope($story);
        $this->app->loadLang('task');
        $this->app->loadLang('testcase');
        $this->app->loadLang('product');

        $this->view->title   = $this->lang->demand->retract;
        $this->view->story   = $story;
        $this->view->users   = $this->loadModel('user')->getPairs();
        $this->view->actions = $this->loadModel('action')->getList('story', $storyID);
        $this->display();
    }

    /**
     * AjaxGetOptions.
     *
     * @param  int    $poolID
     * @param  string $type
     * @access public
     * @return void
     */
    public function ajaxGetOptions($poolID = 0, $type = '')
    {
        if($type == 'assignedTo')
        {
            $options = $this->loadModel('demandpool')->getAssignedTo($poolID);
            return print(html::select('assignedTo', $options, '', "class='from-control picker-select'"));
        }

        if($type == 'reviewer')
        {
            $options = $this->loadModel('demandpool')->getReviewers($poolID, $this->app->user->account);
            return print(html::select('reviewer[]', $options, '', "class='from-control picker-select' multiple"));
        }
    }

    /**
     * Ajax get roadmaps.
     *
     * @param  int    $productID
     * @param  string $branch
     * @param  string $param
     * @param  string $index
     * @access public
     * @return string
     */
    public function ajaxGetRoadmaps($productID = 0, $branch = '', $param = '', $index = '')
    {
        $options = $this->loadModel('roadmap')->getPairs($productID, $branch, $param);

        if($index != '') return print(html::select("roadmap[$index]", $options, '', "class='from-control picker-select'"));
        return print(html::select('roadmap', $options, '', "class='from-control multiple picker-select'"));
    }

    /**
     * Export template.
     *
     * @access public
     * @return void
     */
    public function exportTemplate($poolID = 0)
    {
        $this->session->set('demandTransferParams', array('poolID' => $poolID));
        echo $this->fetch('transfer', 'exportTemplate', 'model=demand');
    }

    /**
     * Import excel file.
     *
     * @access public
     * @return void
     */
    public function import($poolID)
    {
        $url = inlink('showImport', "poolID=$poolID");
        $this->session->set('showImportURL', $url);
        echo $this->fetch('transfer', 'import', "model=demand");
    }

    /**
     * Import excel template file.
     *
     * @param  int    $poolID
     * @param  int    $pagerID
     * @param  int    $maxImport
     * @param  string $insert
     * @access public
     * @return void
     */
    public function showImport($poolID, $pagerID = 1, $maxImport = 0, $insert = '')
    {
        $this->session->set('demandTransferParams', array('poolID' => $poolID));
        if($_POST)
        {
            $this->demand->createFromImport($poolID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->isEndPage)
            {
                return print(js::locate($this->createLink('demand','browse', "poolID=$poolID"), 'parent'));
            }
            else
            {
                return print(js::locate(inlink('showImport', "poolID=$poolID&pagerID=" . ($this->post->pagerID + 1) . "&maxImport=$maxImport&insert=" . zget($_POST, 'insert', '')), 'parent'));
            }
        }

        $poolID  = $this->demandpool->setMenu($poolID);
        $demands = $this->loadModel('transfer')->readExcel('demand', $pagerID, $insert);

        $this->view->title       = $this->lang->demand->common . $this->lang->colon . $this->lang->demand->showImport;
        $this->view->datas       = $demands;
        $this->view->backLink    = $this->createLink('demand', 'browse', "poolID=$poolID");

        $this->display('transfer', 'showImport');
    }

    /**
     * Export demands.
     *
     * @param  int    $poolID
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($poolID, $orderBy)
    {
        if($_POST)
        {
            $this->session->set('demandTransferParams', array('poolID' => $poolID));

            $this->post->set('rows', $this->demand->getExportDemands($poolID, $orderBy));
            $this->fetch('transfer', 'export', "model=demand");
        }

        $demandPool = $this->loadModel('demandpool')->getByID($poolID);
        $fileName   = $this->lang->demand->common . $this->lang->dash . $demandPool->name;

        $this->view->fileName        = $fileName;
        $this->view->allExportFields = $this->config->demand->exportFields;
        $this->view->selectedFields  = $this->config->demand->selectedFields;
        $this->view->customExport    = true;
        $this->display();
    }

    /**
     * AJAX: Get parent demands by demand pool.
     *
     * @param  int    $poolID
     * @access public
     * @return void
     */
    public function ajaxGetParentDemands($poolID)
    {
        $parents = $this->demand->getParentDemandPairs($poolID);
        echo html::select('parent', empty($parents) ? array('' => '') : $parents, '', "class='form-control chosen'");
    }

    /**
     * AJAX: Get branches.
     *
     * @param  int    $productID
     * @param  string $value
     * @param  int    $index
     * @param  mixed  $multiple
     * @access public
     * @return void
     */
    public function ajaxGetBranches($productID = 0, $value = '', $index = 0, $multiple = false)
    {
        $branches = $this->loadModel('branch')->getPairs($productID, 'active');
        $name     = $multiple == 'multiple' ? "branch[$index]" : 'branch';

        if(empty($branches)) return '';
        return print(html::select($name, $branches, $value, "class='form-control' onchange='loadBranch(this, $index)'"));
    }

    /**
     * AJAX: Get products by demand pool.
     *
     * @param  int    $poolID
     * @access public
     * @return void
     */
    public function ajaxGetProducts($poolID)
    {
        $products = $this->loadModel('product')->getPairs('noclosed');
        $pool     = $this->loadModel('demandpool')->getByID($poolID);
        if(!empty($pool->products)) $products = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in($pool->products)->beginIF(!$this->app->user->admin)->andWhere('id')->in($this->app->user->view->products)->fi()->andWhere('deleted')->eq(0)->andWhere('status')->eq('normal')->orderBy('id')->fetchPairs();

        return print(html::select('product[]', $products, '', "class='from-control picker-select' multiple"));
    }

    /**
     * AJAX: Get assigned by demand pool.
     *
     * @param  int    $poolID
     * @access public
     * @return void
     */
    public function ajaxGetAssignedTo($poolID)
    {
        $users = $this->loadModel('demandpool')->getAssignedTo($poolID);
        return print(html::select('assignedTo', $users, '', "class='from-control picker-select'"));
    }
}
