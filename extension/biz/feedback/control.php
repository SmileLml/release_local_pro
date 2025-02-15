<?php
/**
 * The control file of feedback of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     feedback
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class feedback extends control
{
    /**
     * Index.
     *
     * @access public
     * @return void
     */
    public function index()
    {
        $this->locate(inlink('browse'));
    }

    /**
     * Common actions.
     *
     * @access public
     * @return void
     */
    public function commonActions()
    {
        $productID = $this->session->feedbackProduct ? $this->session->feedbackProduct : 0;
        $this->feedback->setMenu($productID);
    }

    /**
     * Create feedback.
     *
     * @param  string $extras
     * @access public
     * @return void
     */
    public function create($extras = '')
    {
        if($_POST)
        {
            $feedbackID = $this->feedback->create();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'Opened');

            $needReview = !$this->feedback->forceNotReview();
            if($this->feedback->getStatus('create', $needReview) == 'noreview') $this->action->create('feedback', $feedbackID, 'submitReview');

            $this->executeHooks($feedbackID);

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'id' => $feedbackID));

            $browseLink = $this->session->feedbackList ? $this->session->feedbackList : $this->createLink('feedback', 'browse');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $browseLink));
        }

        $products = $this->feedback->getGrantProducts(true, false, true);
        $this->commonActions();

        /* Get workflow relation by extras. */
        $relation = '';
        if($extras)
        {
            $extras = str_replace(array(',', ' '), array('&', ''), $extras);
            parse_str($extras, $params);

            if(isset($params['prevModule']) and isset($params['prevDataID']))
            {
                $relation = $this->loadModel('workflowrelation')->getByPrevAndNext($params['prevModule'], 'feedback');
                if($relation) $relation->prevDataID = $params['prevDataID'];
            }

            if(isset($params['moduleID'])) $this->view->moduleID = $params['moduleID'];
            if(isset($params['productID'])) $this->view->productID = $params['productID'];
        }

        $this->view->title    = $this->lang->feedback->create;
        $this->view->modules  = $this->loadModel('tree')->getOptionMenu(0, $viewType = 'feedback', $startModuleID = 0);
        $this->view->products = array('' => '') + $products;
        $this->view->relation = $relation;
        $this->view->users    = $this->loadModel('user')->getPairs('devfirst|noclosed|nodeleted');
        $this->view->pri      = 3;
        $this->display();
    }

    /**
     * Edit feedback.
     *
     * @param  int    $id
     * @param  string $browseType
     * @access public
     * @return void
     */
    public function edit($id, $browseType)
    {
        $this->commonActions();
        $feedback = $this->feedback->getById($id);
        if(!$this->feedback->hasPriv($feedback->product)) return print(js::error($this->lang->feedback->accessDenied) . js::locate('back'));
        if($_POST)
        {
            $changes = $this->feedback->update($id);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            if($changes)
            {
                $actionID = $this->loadModel('action')->create('feedback', $id, 'Edited');
                if(!empty($changes)) $this->action->logHistory($actionID, $changes);
            }

            $this->executeHooks($id);

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'id' => $id));

            $browseLink = $this->session->feedbackList ? $this->session->feedbackList : $this->createLink('feedback', 'browse');
            if(isonlybody()) return print(js::closeModal('parent.parent'));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $browseLink));
        }
        $products = $this->feedback->getGrantProducts(true, false, true);

        $this->view->title      = $this->lang->feedback->edit;
        $this->view->feedback   = $feedback;
        $this->view->modules    = $this->loadModel('tree')->getOptionMenu($feedback->product, 'feedback', 0, 'all');
        $this->view->products   = array('' => '') + $products;
        $this->view->browseType = $browseType;
        $this->view->users      = $this->loadModel('user')->getPairs('devfirst|noclosed|nodeleted');
        $this->display();
    }

    /**
     * Batch edit feedback.
     *
     * @param  string $browseType
     * @access public
     * @return void
     */
    public function batchEdit($browseType)
    {
        if(isset($_POST['feedbackIDList']))
        {
            if($this->app->tab == 'my')
            {
                /* Set my menu. */
                $this->loadModel('my');
                $this->lang->my->menu->work['subModule'] = 'feedback';
            }

            $type      = '';
            $hasPriv   = common::hasPriv('feedback', 'editOthers');
            $noChangeIDList = '';

            if(!$hasPriv) $type = 'openedbyme';

            $this->view->title      = $this->lang->feedback->edit;
            $this->view->users      = $this->loadModel('user')->getPairs('noletter|noclosed');
            $this->view->moduleList = $this->feedback->getModuleListGroupByProduct();
            $this->view->feedbacks  = $this->feedback->getByList($_POST['feedbackIDList'], $type);
            $this->view->products   = array('' => '') + $this->feedback->getGrantProducts(true, false, true);
            $this->view->browseType = $browseType;

            if(!$hasPriv) $noChangeIDList = array_diff($_POST['feedbackIDList'], array_keys($this->view->feedbacks));

            $this->view->noChangeIDList  = !empty($noChangeIDList) ? implode(',', $noChangeIDList) : '';
            $this->display();
        }
        elseif($_POST)
        {
            $allChanges = $this->feedback->batchUpdate();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action');
            if(!empty($allChanges))
            {
                foreach($allChanges as $feedbackID => $changes)
                {
                    if(empty($changes)) continue;

                    $actionID = $this->action->create('feedback', $feedbackID, 'Edited');
                    $this->action->logHistory($actionID, $changes);
                }
            }
            $browseLink = $this->session->feedbackList ? $this->session->feedbackList : $this->createLink('feedback', 'browse');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $browseLink));
        }
    }

    /**
     * Browse feedback.
     *
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($browseType = 'wait', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->loadModel('datatable');
        $this->lang->datatable->moduleSetting  = str_replace($this->lang->module, $this->lang->feedback->moduleAB, $this->lang->datatable->moduleSetting);
        $this->lang->datatable->showModule     = str_replace($this->lang->module, $this->lang->feedback->moduleAB, $this->lang->datatable->showModule);
        $this->lang->datatable->showModuleList = str_replace($this->lang->module, $this->lang->feedback->moduleAB, $this->lang->datatable->showModuleList);

        $this->session->set('feedbackList', $this->app->getURI(true), 'feedback');

        /* Set menu.*/
        $productID = $param;

        /* Load tree model. */
        $this->loadModel('tree');

        if(!$this->session->feedbackProduct) $this->session->feedbackProduct = 'all';
        if($browseType == 'byModule' and $param) $productID = $this->tree->getByID($param)->root;
        if($this->session->feedbackProduct and !$productID and $browseType != 'byProduct') $productID = $this->session->feedbackProduct;
        if($browseType == 'bysearch') $productID = $this->session->feedbackProduct = 'all';
        if(in_array($browseType, array('byProduct', 'byModule')))
        {
            $this->session->set('browseType', $browseType, 'feedback');
            $this->session->set('objectID', $param == 'all' ? 0 : $param, 'feedback');
        }
        elseif(!$param and $browseType == 'all')
        {
            $this->session->set('objectID', 0);
            $productID = 'all';
        }
        if(!$param and $browseType == 'byProduct') $productID = 'all';

        $this->feedback->setMenu($productID);
        if(!in_array($browseType, array('byModule', 'byProduct'))) $this->session->set('feedbackBrowseType', $browseType);
        if(!in_array($browseType, array('byModule', 'byProduct')) and $this->session->feedbackBrowseType == 'bysearch') $this->session->set('feedbackBrowseType', 'wait');

        $moduleName = $this->lang->feedback->allModule;
        $moduleID = $this->session->objectID ? $this->session->objectID : 0;

        if($this->session->browseType == 'byModule'  and $moduleID and $this->session->feedbackProduct != 'all') $moduleName = $this->tree->getById($moduleID)->name;
        if($this->session->browseType == 'byProduct' and $moduleID and $this->session->feedbackProduct != 'all') $moduleName = $this->loadModel('product')->getById($moduleID)->name;

        $queryID = $browseType == 'bysearch' ? (int)$param : 0;
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        if($browseType != 'bysearch')
        {
            if($this->session->feedbackBrowseType) $browseType = $this->session->feedbackBrowseType;
            $feedbacks = $this->feedback->getList($browseType, $orderBy, $pager, $moduleID);
        }
        else
        {
            $feedbacks = $this->feedback->getBySearch($queryID, $orderBy, $pager);
        }

        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'feedback', false);

        $storyIdList = $bugIdList = $todoIdList = $taskIdList =  $ticketIdList = $demandIdList = array();
        foreach($feedbacks as $feedback)
        {
            if($feedback->solution == 'tobug')       $bugIdList[]    = $feedback->result;
            if($feedback->solution == 'tostory')     $storyIdList[]  = $feedback->result;
            if($feedback->solution == 'touserstory') $storyIdList[]  = $feedback->result;
            if($feedback->solution == 'totodo')      $todoIdList[]   = $feedback->result;
            if($feedback->solution == 'totask')      $taskIdList[]   = $feedback->result;
            if($feedback->solution == 'toticket')    $ticketIdList[] = $feedback->result;
            if($feedback->solution == 'todemand')    $demandIdList[] = $feedback->result;
        }
        $bugs    = $bugIdList    ? $this->loadModel('bug')->getByList($bugIdList) : array();
        $stories = $storyIdList  ? $this->loadModel('story')->getByList($storyIdList) : array();
        $todos   = $todoIdList   ? $this->loadModel('todo')->getByList($todoIdList) : array();
        $tasks   = $taskIdList   ? $this->loadModel('task')->getByList($taskIdList) : array();
        $tickets = $ticketIdList ? $this->loadModel('ticket')->getByList($ticketIdList) : array();
        $demands = ($demandIdList and $this->config->vision == 'or') ? $this->loadModel('demand')->getByList($demandIdList) : array();

        $products = $this->feedback->getGrantProducts();

        $this->config->feedback->search['actionURL'] = inlink($this->app->getMethodName(), "browseType=bysearch&param=myQueryID&orderBy=$orderBy");
        $this->config->feedback->search['queryID']   = $queryID;
        $this->config->feedback->search['onMenuBar'] = 'no';
        $this->config->feedback->search['params']['product']['values']     = array('' => '') + $products;
        $this->config->feedback->search['params']['module']['values']      = $productID == 'all' ? $this->feedback->getModuleList('feedback', true) : array('' => '') + $this->tree->getOptionMenu(intVal($productID), $viewType = 'feedback', $startModuleID = 0, 'all');
        $this->config->feedback->search['params']['processedBy']['values'] = array('' => '') + $this->feedback->getFeedbackPairs('admin');
        if($this->app->getMethodName() == 'browse')
        {
            unset($this->config->feedback->search['fields']['openedBy']);
            unset($this->config->feedback->search['fields']['openedDate']);
            unset($this->config->feedback->search['params']['openedBy']);
            unset($this->config->feedback->search['params']['openedDate']);
            unset($this->config->feedback->search['fields']['assignedDate']);
            unset($this->config->feedback->search['params']['assignedDate']);
        }
        $this->loadModel('search')->setSearchParams($this->config->feedback->search);

        $this->loadModel('user');
        $userPairs     = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $productViewID = $productID != 'all' ? $productID : 0;
        $productAcl    = $this->dao->select('acl')->from(TABLE_PRODUCT)->where('id')->eq($productViewID)->fetch();
        if(isset($productAcl->acl) and $productAcl->acl != 'open' and $productViewID)
        {
            $users = $this->user->getProductViewUsers($productViewID);

            $userPairs = array('' => '');
            $users = $this->loadModel('user')->getListByAccounts($users, 'account');
            foreach($users as $account => $user) $userPairs[$account] = $user->realname ? $user->realname : $user->account;
        }

        $this->view->title       = $this->lang->feedback->browse;
        $this->view->browseType  = $browseType;
        $this->view->feedbacks   = $feedbacks;
        $this->view->orderBy     = $orderBy;
        $this->view->pager       = $pager;
        $this->view->param       = $param;
        $this->view->moduleID    = $moduleID;
        $this->view->productID   = $productID;
        $this->view->bugs        = $bugs;
        $this->view->todos       = $todos;
        $this->view->stories     = $stories;
        $this->view->tasks       = $tasks;
        $this->view->tickets     = $tickets;
        $this->view->demands     = $demands;
        $showModule              = !empty($this->config->datatable->feedbackAdmin->showModule) ? $this->config->datatable->feedbackAdmin->showModule : '';
        $this->view->setModule   = true;
        $this->view->showBranch  = false;
        $this->view->modulePairs = $showModule ? $this->tree->getModulePairs(0, 'feedback', $showModule) : array();
        $this->view->moduleTree  = $this->tree->getFeedbackTreeMenu(array('treeModel', 'createFeedbackLink'));
        $this->view->moduleName  = $moduleName;
        $this->view->modules     = $this->tree->getOptionMenu(0, $viewType = 'feedback', 0);
        $this->view->depts       = $this->loadModel('dept')->getOptionMenu();
        $this->view->users       = $userPairs;
        $this->view->projects    = $this->loadModel('project')->getPairsByProgram(0, 'noclosed');
        $this->view->allProducts = $products;

        $this->display();
    }

    /**
     * View feedback.
     *
     * @param  int    $feedbackID
     * @param  string $browseType
     * @access public
     * @return void
     */
    public function view($feedbackID, $browseType = '')
    {
        $feedbackID     = (int)$feedbackID;
        $feedback       = $this->feedback->getById($feedbackID);
        $locateFunction = $this->config->vision == 'lite' ? 'browse' : 'admin';
        $this->commonActions();

        if(empty($feedback)) return print(js::error($this->lang->notFound) . js::locate($this->createLink('feedback', $locateFunction)));

        $products = $this->feedback->getGrantProducts();
        if(!isset($products[$feedback->product])) return print(js::error($this->lang->feedback->accessDenied) . js::locate('back'));;

        /* Different icons are displayed for likes and dislikes. */
        $feedback->likeIcon = (!empty($feedback->likes) and in_array($this->app->user->account, explode(',', $feedback->likes))) ? 'thumbs-up-solid' : 'thumbs-up';

        $uri = $this->app->getURI(true);
        $this->session->set('bugList',   $uri, 'qa');
        $this->session->set('storyList', $uri, 'product');
        $this->session->set('taskList',  $uri, 'execution');
        if((!empty($this->app->user->feedback) or $this->cookie->feedbackView) and $feedback->public == 0 and $this->app->user->account != $feedback->openedBy) return;

        $actions = $this->loadModel('action')->getList('feedback', $feedbackID);
        $actions = $this->feedback->processActions($feedbackID, $actions);

        $this->executeHooks($feedbackID);

        $this->view->title       = $this->lang->feedback->view;
        $this->view->feedbackID  = $feedbackID;
        $this->view->feedback    = $feedback;
        $this->view->modulePath  = $this->loadModel('tree')->getParents($feedback->module);
        $this->view->product     = $this->loadModel('product')->getById($feedback->product);
        $this->view->users       = $this->loadModel('user')->getPairs('noletter|nodeleted');
        $this->view->preAndNext  = $this->loadModel('common')->getPreAndNextObject('feedback', $feedbackID);
        $this->view->actions     = $actions;
        $this->view->contacts    = $this->dao->select('*')->from(TABLE_USER)->where('account')->in("{$feedback->openedBy},{$feedback->assignedTo},{$feedback->processedBy}")->fetchAll('account');
        $this->view->browseType  = $browseType;

        if($this->config->vision != 'lite') $this->view->relations = $this->feedback->getRelations($feedbackID, $feedback);
        if($this->config->vision == 'or')
        {
            $this->view->products = $this->product->getPairs();
            $this->view->roadmaps = $this->loadModel('roadmap')->getPairs();
        }

        $this->lang->action->desc->commented = $this->lang->feedback->commented;

        $this->view->products = $products;
        $this->display();
    }

    /**
     * Feedback to todo.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function toTodo($feedbackID)
    {
        $this->commonActions();
        $this->app->loadLang('todo');
        $this->lang->todo->idvalue = $this->lang->todo->name;
        if($this->config->vision == 'or')
        {
            $typeList['feedback'] = $this->lang->todo->typeList['feedback'];
            $this->lang->todo->typeList = $typeList;
        }
        echo $this->fetch('todo', 'create', "date=today&account=&from=feedback&feedbackID=$feedbackID");
    }

    /**
     * Feedback to story.
     *
     * @param  int    $product
     * @param  string $extra
     * @access public
     * @return void
     */
    public function toStory($product, $extra)
    {
        $this->commonActions();
        echo $this->fetch('story', 'create', "product=$product&branch=0&moduleID=0&storyID=0&executionID=0&bugID=0&planID=0&todoID=0&extra=$extra&type=story");
    }

    /**
     * Feedback to user story.
     *
     * @param  int    $product
     * @param  string $extra
     * @access public
     * @return void
     */
    public function toUserStory($product, $extra)
    {
        $this->commonActions();
        echo $this->fetch('story', 'create', "product=$product&branch=0&moduleID=0&storyID=0&executionID=0&bugID=0&planID=0&todoID=0&extra=$extra&type=requirement");
    }

    /**
     * Feedback to ticket.
     *
     * @param  string    $extra
     * @access public
     * @return void
     */
    public function toTicket($extra)
    {
        $this->commonActions();
        echo $this->fetch('ticket', 'create', "productID=&extras=$extra");
    }

    /**
     * Feedback to demand.
     *
     * @param  string $extra
     * @access public
     * @return void
     */
    public function toDemand($extra)
    {
        $this->commonActions();
        echo $this->fetch('demand', 'create', "poolID=0&demandID=0&extra=$extra");
    }

    /**
     * Review feedback.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function review($feedbackID)
    {
        if($_POST)
        {
            $changes = $this->feedback->review($feedbackID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $result   = $this->post->result;
                $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'reviewed', $this->post->comment, ucfirst($result));
                $this->action->logHistory($actionID, $changes);
            }

            $this->executeHooks($feedbackID);

            return print(js::reload('parent.parent'));
        }

        $feedback = $this->feedback->getById($feedbackID);

        $this->view->users      = $this->loadModel('user')->getPairs('noletter|noclosed|nodeleted');
        $this->view->feedback   = $feedback;
        $this->view->actions    = $this->loadModel('action')->getList('feedback', $feedbackID);
        $this->view->assignedTo = $this->dao->findByID($feedback->product)->from(TABLE_PRODUCT)->fetch('feedback');
        $this->display();
    }

    /**
     * Batch review.
     *
     * @param  int    $result
     * @access public
     * @return void
     */
    public function batchReview($result)
    {
        $feedbackIdList = $this->post->feedbackIDList;
        if(empty($feedbackIdList)) return print(js::locate($this->session->feedbackList, 'parent'));

        $feedbackIdList = array_unique($feedbackIdList);
        $actions        = $this->feedback->batchReview($feedbackIdList, $result);

        if(dao::isError()) return print(js::error(dao::getError()));
        return print(js::locate($this->session->feedbackList, 'parent'));
    }

    /**
     * Ajax set like.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function ajaxLike($feedbackID)
    {
        $likes = $this->feedback->like($feedbackID);
        $users = $this->loadModel('user')->getPairs('noletter');
        $count = 0;
        $title = '';

        if($likes)
        {
            $likes = explode(',', $likes);
            $count = count($likes);
            foreach($likes as $account) $title .= zget($users, $account, $account) . ',';
            $title .= $this->lang->feedback->feelLike;
        }

        $likeIcon = (!empty($likes) and in_array($this->app->user->account, $likes)) ? 'thumbs-up-solid' : 'thumbs-up';
        $output   = html::a("javascript:like($feedbackID)", "<i class='icon icon-{$likeIcon}''></i>({$count})", '', "class='btn' title='$title'");

        if($this->app->viewType == 'mhtml') $output = html::a("javascript:like($feedbackID)", "<i class='icon icon-thumbs-up'></i> ({$count})", '', "id='likeLink'");

        return print($output);
    }

    /**
     * AJAX: return feedbacks of a user in html select.
     *
     * @param  int    $userID
     * @param  int    $id
     * @param  int    $appendID
     * @access public
     * @return void
     */
    public function ajaxGetUserFeedback($userID = '', $id = '', $appendID = '')
    {
        if($userID == '') $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;

        $feedbacks = $this->feedback->getUserFeedbackPairs($account, 0, $appendID);

        if($id) return print(html::select("feedbacks[$id]", $feedbacks, '', 'class="form-control"'));
        return print(html::select('feedback', $feedbacks, '', 'class=form-control'));
    }

    /**
     * Ask or reply feedback.
     *
     * @param  int    $feedbackID
     * @param  string $type
     * @access public
     * @return void
     */
    public function comment($feedbackID, $type = 'commented')
    {
        if($_POST)
        {
            if(!$this->post->comment) return print(js::error($this->lang->feedback->mustInputComment[$type]));

            $oldFeedback = $this->dao->findById($feedbackID)->from(TABLE_FEEDBACK)->fetch();

            $now  = helper::now();
            $data = fixer::input('post')->stripTags($this->config->feedback->editor->comment['id'])->remove('comment,faq,labels')->get();
            $data->status = $type;

            if($type == 'asked')
            {
                $this->app->methodName = 'ask';

                $data->editedBy   = $this->app->user->account;
                $data->editedDate = $now;
            }

            if($type == 'replied')
            {
                $this->app->methodName = 'reply';

                $data->processedBy   = $this->app->user->account;
                $data->processedDate = $now;
            }

            /* Comment do not change status. */
            if($type == 'commented') $data->status = $oldFeedback->status;

            $data = $this->loadModel('file')->processImgURL($data, $this->config->feedback->editor->comment['id'], $this->post->uid);
            $this->dao->update(TABLE_FEEDBACK)->data($data)->autoCheck()->checkFlow()->where('id')->eq($feedbackID)->exec();

            if(dao::isError()) return print(js::error(dao::getError()));

            $files      = $this->loadModel('file')->saveUpload('feedback', $feedbackID);
            $fileAction = !empty($files) ? $this->lang->addFiles . join(',', $files) . "<br />" : '';

            $changes = common::createChanges($oldFeedback, $data);
            if($changes or $this->post->comment)
            {
                $actionID = $this->loadModel('action')->create('feedback', $feedbackID, htmlspecialchars($type), $fileAction . $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            $this->executeHooks($feedbackID);

            return print(js::reload('parent.parent'));
        }

        $feedback = $this->feedback->getByID($feedbackID);
        $title    = $this->lang->feedback->reply;
        if($type == 'asked')     $title = $this->lang->feedback->ask;
        if($type == 'commented') $title = $this->lang->feedback->comment;

        $this->view->title      = $title;
        $this->view->feedbackID = $feedbackID;
        $this->view->feedback   = $feedback;
        $this->view->type       = $type;
        $this->view->faqs       = array('' => '') + $this->loadModel('faq')->getPairs($feedback->product);
        $this->display();
    }

    /**
     * Reply feedback.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function reply($feedbackID)
    {
        echo $this->fetch('feedback', 'comment', "feedbackID=$feedbackID&type=replied");
    }

    /**
     * Ask feedback.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function ask($feedbackID)
    {
        echo $this->fetch('feedback', 'comment', "feedbackID=$feedbackID&type=asked");
    }

    /**
     * Update assign of feedback.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function assignTo($feedbackID)
    {
        $feedback = $this->feedback->getById($feedbackID);

        if(!empty($_POST))
        {
            $this->loadModel('action');
            $changes = $this->feedback->assign($feedbackID);
            if(dao::isError()) return print(js::error(dao::getError()));
            $actionID = $this->action->create('feedback', $feedbackID, 'Assigned', $this->post->comment, $this->post->assignedTo);
            $this->action->logHistory($actionID, $changes);

            $this->executeHooks($feedbackID);

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('feedback', 'view', "feedbackID=$feedbackID"), 'parent'));
        }

        $product = $this->loadModel('product')->getById($feedback->product);
        $this->loadModel('user');
        if($product->acl != 'open')
        {
            $users = $this->user->getProductViewUsers($product->id);
            if($feedback->assignedTo) $users[$feedback->assignedTo] = $feedback->assignedTo;

            $userPairs = array('' => '');
            $users = $this->loadModel('user')->getListByAccounts($users, 'account');
            foreach($users as $account => $user) $userPairs[$account] = $user->realname ? $user->realname : $user->account;
        }
        else
        {
            $userPairs = $this->loadModel('user')->getPairs('noclosed|nodeleted', $feedback->assignedTo);
        }

        $this->view->users      = $userPairs;
        $this->view->feedback   = $feedback;
        $this->view->feedbackID = $feedbackID;
        $this->view->actions    = $this->loadModel('action')->getList('feedback', $feedbackID);
        $this->display();
    }

    /**
     * Batch assignTo.
     *
     * @access public
     * @return void
     */
    public function batchAssignTo()
    {
        $allChanges = $this->feedback->batchAssign();

        $this->loadModel('action');
        if(!empty($allChanges))
        {
            foreach($allChanges as $feedbackID => $changes)
            {
                if(empty($changes)) continue;
                $actionID = $this->action->create('feedback', $feedbackID, 'Assigned', '', $this->post->assignedTo);
                $this->action->logHistory($actionID, $changes);
            }
        }
        return print(js::reload('parent'));
    }

    /**
     * Delete feedback.
     *
     * @param  int    $feedbackID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($feedbackID, $confirm = 'no')
    {
        if($confirm != 'yes') return print(js::confirm($this->lang->feedback->confirmDelete, inlink('delete', "feedbackID=$feedbackID&confirm=yes")));

        $this->feedback->delete(TABLE_FEEDBACK, $feedbackID);

        $this->executeHooks($feedbackID);

        if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success', 'message' => $this->lang->saveSuccess));

        return print(js::reload('parent'));
    }

    /**
     * Close feedback.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function close($feedbackID)
    {
        if($_POST)
        {
            $this->feedback->close($feedbackID);
            if(dao::isError())
            {
                if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => dao::getError()));
                return print(js::error(dao::getError()));
            }

            $this->executeHooks($feedbackID);

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success', 'message' => $this->lang->saveSuccess));
            return print(js::reload('parent.parent'));
        }

        $feedbackList = array('' => '');
        $feedbacks = $this->feedback->getList('all');

        if($feedbacks)
        {
            foreach($feedbacks as $key => $feedback) $feedbackList[$feedback->id] = "#$feedback->id " . $feedback->title;
            if(!empty($feedbackList[$feedbackID])) unset($feedbackList[$feedbackID]);
        }

        $this->view->feedbackID   = $feedbackID;
        $this->view->feedback     = $this->feedback->getById($feedbackID);
        $this->view->feedbacks    = $feedbackList;
        $this->view->closedReason = (!empty($this->app->user->feedback) or $this->cookie->feedbackView) ? 'commented' : '';
        $this->display();
    }

    /**
     * Activate feedback.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function activate($feedbackID)
    {
        $feedback = $this->feedback->getById((int)$feedbackID);
        $actions  = $this->loadModel('action')->getList('feedback', $feedbackID);
        $actions  = $this->feedback->processActions($feedbackID, $actions);
        $product  = $this->loadModel('product')->getById($feedback->product);

        if(!empty($_POST))
        {
            $changes = $this->feedback->activate($feedbackID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'Activated', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('feedback', 'view', "feedbackID=$feedbackID"), 'parent'));
        }
        $this->commonActions();

        $this->view->assignedTo = $product->feedback ? $product->feedback : '';
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|noclosed|nodeleted');
        $this->view->actions    = $actions;
        $this->view->feedback   = $feedback;

        $this->display();
    }

    /**
     * Batch close feedback.
     *
     * @param  string $from
     * @access public
     * @return void
     */
    public function batchClose($from = '')
    {
        $this->app->loadLang('bug');
        if(!$this->post->feedbackIdList and !$this->post->feedbackIDList) return print(js::locate($this->session->feedbackList, 'parent'));
        $feedbackIdList = $this->post->feedbackIdList ? $this->post->feedbackIdList : $this->post->feedbackIDList;
        $feedbackIdList = array_unique($feedbackIdList);
        $feedbackList   = array('' => '');
        $feedbackPairs  = $this->feedback->getList('all');

        /* Get edited feedbacks. */
        $feedbacks = $this->feedback->getByList($feedbackIdList);
        foreach($feedbacks as $feedback)
        {
            if($feedback->status == 'closed')
            {
                $closedFeedback[] = $feedback->id;
                unset($feedbacks[$feedback->id]);
            }
        }

        if($feedbackPairs)
        {
            foreach($feedbackPairs as $value) $feedbackList[$value->id] = "#$value->id " . $value->title;
            foreach(array_keys($feedbacks) as $id)
            {
                if(!empty($feedbackList[$id])) unset($feedbackList[$id]);
            }
        }

        if($this->post->comments)
        {
            $data       = fixer::input('post')->get();
            $allChanges = $this->feedback->batchClose();

            if($allChanges)
            {
                foreach($allChanges as $feedbackID => $changes)
                {
                    if(empty($changes)) continue;
                    $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'closed', $this->post->comments[$feedbackID], $this->post->closedReasons[$feedbackID] . ($this->post->repeatFeedbackIDList[$feedbackID] ? ':' . (int)$this->post->repeatFeedbackIDList[$feedbackID] : ''));
                    $this->action->logHistory($actionID, $changes);
                }
            }

            if(!dao::isError()) $this->loadModel('score')->create('ajax', 'batchOther');
            return print(js::locate($this->session->feedbackList, 'parent'));
        }

        if($this->app->tab == 'my')
        {
            $this->lang->feedback->menu      = $this->lang->my->menu;
            $this->lang->feedback->menuOrder = $this->lang->my->menuOrder;
            if($from == 'work') $this->lang->my->menu->work['subModule'] = 'feedback';
        }

        $errorTips = '';
        if(isset($closedFeedback)) $errorTips .= sprintf($this->lang->feedback->closedFeedback, join(',', $closedFeedback));
        if(isset($closedFeedback)) echo js::alert($errorTips);

        $this->view->title          = $this->lang->feedback->batchClose;
        $this->view->position[]     = $this->lang->feedback->common;
        $this->view->position[]     = $this->lang->feedback->batchClose;
        $this->view->feedbacks      = $feedbacks;
        $this->view->feedbackIdList = $feedbackIdList;
        $this->view->reasonList     = $this->lang->feedback->closedReasonList;
        $this->view->feedbackList   = $feedbackList;

        $this->display();
    }

    /**
     * Browse feedback in admin.
     *
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function admin($browseType = 'wait', $param = 0, $orderBy = 'editedDate_desc,id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->session->set('todoList', $this->app->getURI(true), 'feedback');

        $deptPairs     = $this->loadModel('dept')->getOptionMenu();
        $userDeptPairs = $this->dao->select('account,dept')->from(TABLE_USER)->where('deleted')->eq(0)->fetchPairs('account', 'dept');

        if(!$this->config->URAndSR or $this->config->vision == 'lite') unset($this->lang->feedback->moreSelects['admin']['more']['touserstory']);

        /* Build the search form. */
        $actionURL = $this->createLink('feedback', 'admin', "browseType=$browseType&param=$param&orderBy=$orderBy");
        $this->feedback->buildSearchForm($actionURL);

        $this->view->position[]    = $this->lang->feedback->browse;
        $this->view->userDeptPairs = $userDeptPairs;
        foreach($userDeptPairs as $user => $dept) $userDeptPairs[$user] = isset($deptPairs[$dept]) ? $deptPairs[$dept] : '';
        return $this->browse($browseType, $param, $orderBy, $recTotal, $recPerPage, $pageID);
    }

    /**
     * View feedback in admin.
     *
     * @param  int    $feedbackID
     * @access public
     * @return void
     */
    public function adminView($feedbackID, $browseType = '')
    {
        $feedback = $this->feedback->getById($feedbackID);
        if(!$this->feedback->hasPriv($feedback->product)) return print(js::error($this->lang->feedback->accessDenied) . js::locate('back'));
        $this->session->set('todoList', $this->app->getURI(true), 'feedback');
        $product = $feedback->product;
        $products = $this->feedback->getGrantProducts();
        if(!$this->app->user->admin && !in_array($product, $products)) $product = 'all';
        $this->session->set('feedbackProduct', $product, 'feedback');

        if(empty($browseType)) $browseType = $feedback->solution == 'touserstory' ? 'tostory' : $feedback->solution;

        $this->view->position[] = html::a(inlink('admin'), $this->lang->feedback->browse);
        $this->view->position[] = $this->lang->feedback->view;
        return $this->view($feedbackID, $browseType);
    }

    /**
     * Products.
     *
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function products($recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $users = $this->loadModel('user')->getHasFeedbackPriv(null, 'all');
        $productSettingList = isset($this->config->global->productSettingList) ? json_decode($this->config->global->productSettingList, true) : array();

        $this->loadModel('product');
        $orderBy = 'program_asc';
        /* Process product structure. */
        if($this->config->systemMode == 'light' and $orderBy == 'program_asc') $orderBy = 'line_desc,order_asc';
        $productStats = $this->product->getStats($orderBy, null, 'feedback|noclosed|nowait', '', 'story', 0, 0, 'all');
        $feedbackView = $this->feedback->getFeedbackView(array_keys($productStats));

        if($productSettingList and !$this->app->user->admin)
        {
            $account = $this->app->user->account;
            foreach($productStats as $productID => $product)
            {
                if(!in_array($productID, $productSettingList)) unset($productStats[$productID]);
            }
        }

        $this->app->loadClass('pager', $static = true);
        $recTotal     = count($productStats);
        $pager        = pager::init($recTotal, $recPerPage, $pageID);
        $productStats = array_slice($productStats, ($pageID - 1) * $pager->recPerPage, $pager->recPerPage, true);

        $productStructure = $this->product->statisticProgram($productStats);
        $productLines     = $this->dao->select('*')->from(TABLE_MODULE)->where('type')->eq('line')->andWhere('deleted')->eq(0)->orderBy('`order` asc')->fetchAll();
        $programLines     = array();

        foreach($productLines as $index => $productLine)
        {
            if(!isset($programLines[$productLine->root])) $programLines[$productLine->root] = array();
            $programLines[$productLine->root][$productLine->id] = $productLine->name;
        }

        $this->view->title = $this->lang->feedback->products;
        $this->view->users = $users;
        $this->view->pager = $pager;

        $this->view->productStats     = $productStats;
        $this->view->productStructure = $productStructure;
        $this->view->productLines     = $productLines;
        $this->view->programLines     = $programLines;
        $this->view->feedbackView     = $feedbackView;

        $this->display();
    }

    /**
     * Manage product.
     *
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function manageProduct($productID)
    {
        $product = $this->loadModel('product')->getById($productID);
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $this->feedback->manageProduct($productID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if(isonlybody()) return print(js::closeModal('parent.parent', 'this'));
            return print(js::locate($this->createLink('feedback', 'products'), 'parent'));
        }

        $users = $this->loadModel('user')->getHasFeedbackPriv(null, 'all');
        $view  = $this->feedback->getFeedbackView($productID);
        if(isset($view[$productID])) $view = array_keys($view[$productID]);

        $this->view->users   = $users;
        $this->view->product = $product;
        $this->view->view    = $view;
        $this->display();
    }

    /**
     * Sync product module.
     *
     * @param  int    $productID
     * @param  string $module
     * @param  string $parent
     * @access public
     * @return void
     */
    public function syncProduct($productID = 0, $module = 'feedback', $parent = '')
    {
        $syncConfig      = json_decode($this->config->global->syncProduct, true);
        $feedbacks       = $this->dao->select('id')->from(TABLE_FEEDBACK)->where('product')->eq($productID)->fetchAll();
        $feedbackModules = $this->loadModel('tree')->getOptionMenu($productID, $module, 0, 0, 'nodeleted|noproduct');

        if($_POST)
        {
            $syncLevel = fixer::input('post')->get('syncLevel');
            $needMerge = fixer::input('post')->get('needMerge');
            $syncConfig[$module][$productID] = $syncLevel;

            $this->loadModel('setting')->setItem('system.common.global.syncProduct', json_encode($syncConfig));

            if($parent == 'onlybody') $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => 1, 'callback' => array('name' => 'closeParentModal')));

            if(($syncLevel and $needMerge == 'no') or (count($feedbackModules) == 1 and !$feedbacks)) return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'closeModal' => 1, 'callback' => array('name' => 'jumpBrowse')));

            return $this->send(array('result' => 'success', 'locate' => inlink('mergeProductModule', "productID=$productID&syncLevel={$syncLevel}&module=$module")));
        }

        $browseLink = $this->createLink($module, 'browse', '', '', false);
        if($module == 'feedback') $browseLink = $this->createLink('feedback', 'admin', '', '', false);
        $browseLink = str_replace('onlybody=yes', '', str_replace('onlybody=yes&', '', $browseLink));

        $this->view->browseLink          = $browseLink;
        $this->view->feedbackCount       = count($feedbacks);
        $this->view->feedbackModuleCount = count($feedbackModules);
        $this->display();
    }

    /**
     * Merge product module.
     *
     * @param  int    $productID
     * @param  int    $syncLevel
     * @param  string $module
     * @access public
     * @return void
     */
    public function mergeProductModule($productID = 0, $syncLevel = 0, $module = 'feedback')
    {
        if(!common::hasPriv($module, 'syncProduct')) $this->loadModel('common')->deny($module, 'syncProduct');

        $this->app->loadLang('upgrade');
        $this->app->loadLang('tree');
        $table           = $this->config->objectTables[$module];
        $productModules  = $this->loadModel('tree')->getOptionMenu($productID, 'story', 0, 'all', 'nodeleted', $syncLevel);
        $feedbackModules = $this->loadModel('tree')->getOptionMenu($productID, $module, 0, 0, 'nodeleted|noproduct');

        if(empty($this->session->mergeList))
        {
            $this->session->set('mergeList', $feedbackModules);
            $this->session->set('mergeCount', count($this->session->mergeList));
        }
        $mergeList = $this->session->mergeList;

        if($_POST)
        {
            $mergeFrom = fixer::input('post')->get('mergeFrom');
            $mergeTo   = fixer::input('post')->get('mergeTo');

            $objects = $this->dao->select('*')->from($table)->where('module')->in($mergeFrom)->fetchAll('id');

            foreach($mergeFrom as $k => $from)
            {
                $to = $mergeTo[$k];

                /* Deleted old feedback module.*/
                $this->dao->update(TABLE_MODULE)->set('deleted')->eq('1')->where('type')->eq($module)->andWhere('id')->eq($from)->exec();

                /* Move feedbacks to new module.*/
                $this->dao->update($table)->set('module')->eq($to)->where('module')->eq($from)->exec();
                unset($mergeList[$from]);

                /* Add action for feedback.*/
                foreach($objects as $id => $oldObject)
                {
                    if($from != $oldObject->module) continue;
                    $actionID = $this->loadModel('action')->create($module, $id, 'SyncModule', '', $productModules[$to]);
                    $changes  = common::createChanges($oldObject, array('module' => $to));
                    if(!empty($changes)) $this->action->logHistory($actionID, $changes);
                }
            }

            $this->session->set('mergeList', $mergeList);
            if(empty($mergeList))
            {
                $browseLink = $this->createLink('feedback', 'admin', '', '', false);
                if($module == 'ticket') $browseLink = $this->createLink('ticket', 'browse', '', '', false);
                $browseLink = str_replace('onlybody=yes', '', str_replace('onlybody=yes&', '', $browseLink));
                return print(js::locate($browseLink, 'parent'));
            }
        }

        $this->view->product        = $this->loadModel('product')->getByID($productID);
        $this->view->mergeCount     = $this->session->mergeCount;
        $this->view->mergeList      = $this->session->mergeList;
        $this->view->recPerPage     = 50;
        $this->view->productModules = $productModules;
        $this->display();
    }

    /**
     * AJAX: Get product FM.
     *
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function ajaxGetProductFM($productID)
    {
        $product = $this->loadModel('product')->getByID($productID);
        $FM      = $product->feedback;
        return print($FM);
    }

    /**
     * Get status by ajax.
     *
     * @param  string $methodName
     * @param  string $status
     * @access public
     * @return void
     */
    public function ajaxGetStatus($methodName, $status = '')
    {
        $needReview = !$this->feedback->forceNotReview();

        $status = $this->feedback->getStatus($methodName, $needReview, $status);

        return print($status);
    }

    /**
     * Get projects by productId.
     *
     * @param  int    $productID
     * @param  string $field
     * @param  string $onchange
     * @access public
     * @return void
     */
    public function ajaxGetProjects($productID = 0, $field = 'bugProjects', $onchange = '')
    {
        $branches = array();
        $branches = array_keys($this->loadModel('branch')->getPairs($productID, 'active'));
        if($onchange) $onchange .= '()';
        $projects = $this->loadModel('product')->getProjectPairsByProduct($productID, $branches, '', 'unclosed');
        if($field == 'taskProjects') $onchange = 'getExecutions(this.value)';
        return print(html::select($field, array('' => '') + $projects, '', "class='form-control chosen' onchange='$onchange'"));
    }

    /**
     * Get executions by executions.
     *
     * @param  int $projectId
     * @access public
     * @return void
     */
    public function ajaxGetExecutions($projectID = 0)
    {
        $executions = $this->loadModel('execution')->getPairs($projectID, 'all', 'leaf|order_asc');

        if(!$projectID) $executions = array(0 => '');
        return print(html::select('executions', $executions, '', "class='form-control chosen' onchange=changeTaskButton()"));
    }

	/**
	 * Ajax get execution lang.
	 *
	 * @param  int    $projectID
	 * @access public
	 * @return string
	 */
	public function ajaxGetExecutionLang($projectID = 0)
	{
        $this->app->loadLang('execution');
        $project = $this->loadModel('project')->getByID($projectID);

        if($project->model == 'kanban')
        {
            $this->lang->feedback->execution = str_replace($this->lang->execution->common, $this->lang->project->kanban, $this->lang->feedback->execution);
        }

        return print($this->lang->feedback->execution);
	}

    /**
     * Export feedback.
     *
     * @param  string $browseType
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($browseType, $orderBy)
    {
        if($_POST)
        {
            $this->loadModel('file');
            $feedbackLang = $this->lang->feedback;

            /* Create field lists. */
            $sort   = common::appendOrder($orderBy);
            $fields = explode(',', $this->config->feedback->exportFields);
            if(!empty($this->app->user->feedback) or !empty($_COOKIE['feedbackView'])) $fields = explode(',', $this->config->feedback->frontFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = isset($feedbackLang->$fieldName) ? $feedbackLang->$fieldName : $fieldName;
                unset($fields[$key]);
            }

            /* Get feedbacks. */
            $this->session->feedbackQueryCondition = preg_replace('/SELECT.*WHERE/i', '', $this->session->feedbackQueryCondition);
            $sort = preg_replace('/id/i', 't1.id', $sort);
            $feedbacks = $this->dao->select('t1.*')
                ->from(TABLE_FEEDBACK . ' as t1')
                ->leftJoin(TABLE_USER . ' as t2')->on('t1.openedBy = t2.account')
                ->where($this->session->feedbackQueryCondition)
                ->beginIF($this->post->exportType == 'selected')->andWhere('t1.id')->in($this->cookie->checkedItem)->fi()
                ->orderBy($sort)->fetchAll('id');

            $sql = $this->dao->get();
            $this->session->set('feedbackTransferCondition', $sql);

            $productIdList = $bugIdList = $storyIdList = $todoIdList = $taskIdList = $ticketIdList = array();
            foreach($feedbacks as $feedback)
            {
                $productIdList[$feedback->product] = $feedback->product;
                if($feedback->solution == 'tobug')       $bugIdList[$feedback->result]    = $feedback->result;
                if($feedback->solution == 'tostory')     $storyIdList[$feedback->result]  = $feedback->result;
                if($feedback->solution == 'touserstory') $storyIdList[$feedback->result]  = $feedback->result;
                if($feedback->solution == 'totodo')      $todoIdList[$feedback->result]   = $feedback->result;
                if($feedback->solution == 'totask')      $taskIdList[$feedback->result]   = $feedback->result;
                if($feedback->solution == 'toticket')    $ticketIdList[$feedback->result] = $feedback->result;
            }

            /* Get users and projects. */
            $users    = $this->loadModel('user')->getPairs('noletter');
            $modules  = $this->dao->select('id,name')->from(TABLE_MODULE)->where('root')->in($productIdList)->andWhere('type')->in('feedback,story')->fetchPairs('id', 'name');
            $products = $this->dao->select('id,name')->from(TABLE_PRODUCT)->where('id')->in($productIdList)->fetchPairs('id', 'name');
            $bugs     = $this->dao->select('id,title')->from(TABLE_BUG)->where('id')->in($bugIdList)->fetchPairs('id', 'title');
            $todos    = $this->dao->select('id,name')->from(TABLE_TODO)->where('id')->in($todoIdList)->fetchPairs('id', 'name');
            $tasks    = $this->dao->select('id,name')->from(TABLE_TASK)->where('id')->in($taskIdList)->fetchPairs('id', 'name');
            $stories  = $this->dao->select('id,title')->from(TABLE_STORY)->where('id')->in($storyIdList)->fetchPairs('id', 'title');
            $tickets  = $this->dao->select('id,title')->from(TABLE_TICKET)->where('id')->in($ticketIdList)->fetchPairs('id', 'title');

            foreach($feedbacks as $feedback)
            {
                $title = '';
                if($feedback->solution == 'tobug')       $title = $bugs[$feedback->result];
                if($feedback->solution == 'tostory')     $title = $stories[$feedback->result];
                if($feedback->solution == 'touserstory') $title = $stories[$feedback->result];
                if($feedback->solution == 'totodo')      $title = $todos[$feedback->result];
                if($feedback->solution == 'totask')      $title = $tasks[$feedback->result];
                if($feedback->solution == 'toticket')    $title = $tickets[$feedback->result];

                if($feedback->mailto)
                {
                    $mailtos   = explode(',', $feedback->mailto);
                    $realnames = array();
                    foreach($mailtos as $mailto) $realnames[] = zget($users, $mailto);
                    $feedback->mailto = trim(join(',', $realnames), ',');
                }

                $feedback->product       = zget($products, $feedback->product, '') . "(#$feedback->product)";
                $feedback->module        = zget($modules, $feedback->module, '') . "(#$feedback->module)";
                $feedback->status        = $this->processStatus('feedback', $feedback);
                $feedback->type          = zget($this->lang->feedback->typeList, $feedback->type, '');
                $feedback->solution      = zget($this->lang->feedback->solutionList, $feedback->solution, '');
                $feedback->openedBy      = zget($users, $feedback->openedBy);
                $feedback->assignedTo    = zget($users, $feedback->assignedTo);
                $feedback->processedBy   = zget($users, $feedback->processedBy);
                $feedback->closedBy      = zget($users, $feedback->closedBy);
                $feedback->editedBy      = zget($users, $feedback->editedBy);
                $feedback->openedDate    = helper::isZeroDate($feedback->openedDate) ? '' : $feedback->openedDate;
                $feedback->assignedDate  = helper::isZeroDate($feedback->assignedDate) ? '' : $feedback->assignedDate;
                $feedback->processedDate = helper::isZeroDate($feedback->processedDate) ? '' : $feedback->processedDate;
                $feedback->closedDate    = helper::isZeroDate($feedback->closedDate) ? '' : $feedback->closedDate;
                $feedback->closedReason  = zget($feedbackLang->closedReasonList, $feedback->closedReason);
                $feedback->editedDate    = helper::isZeroDate($feedback->editedDate) ? '' : $feedback->editedDate;
                $feedback->title         = "\t" . $feedback->title . "\t";
                $feedback->desc          = "\t" . $feedback->desc . "\t";
                $feedback->source        = "\t" . $feedback->source . "\t";

                if($title) $feedback->solution .= "#{$feedback->result} $title";
            }

            if($this->post->fileType == 'csv')
            {
                $feedback->desc = htmlspecialchars_decode($feedback->desc);
                $feedback->desc = str_replace("<br />", "\n", $feedback->desc);
                $feedback->desc = str_replace('"', '""', $feedback->desc);
            }
            if($this->config->edition != 'open') list($fields, $feedbacks) = $this->loadModel('workflowfield')->appendDataFromFlow($fields, $feedbacks);

            $this->post->set('fields', $fields);
            $this->post->set('rows', $feedbacks);
            $this->post->set('kind', 'feedback');

            $width['openedDate']    = 20;
            $width['assignedDate']  = 20;
            $width['processedDate'] = 20;
            $width['closedDate']    = 20;
            $width['editedDate']    = 20;
            $this->post->set('width', $width);

            $this->post->set('exportFields', explode(',', $this->config->feedback->exportFields));

            $this->feedback->setListValue();
            $this->loadModel('transfer')->export('feedback');
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }

        $fileName = zget($this->lang->feedback->featureBar['admin'], $browseType, '');
        if(empty($fileName)) $fileName = zget($this->lang->feedback->statusList, $browseType, '');
        if(empty($fileName) and isset($this->lang->feedback->$browseType)) $fileName = $this->lang->feedback->$browseType;
        if($fileName) $fileName = $this->lang->feedback->common . $this->lang->dash . $fileName;

        $this->view->fileName = $fileName;
        $this->display();
    }

    /**
     * Get feedback module
     *
     * @param  int    $projectID
     * @param  bool   $isChosen
     * @param  int    $number
     * @param  int    $moduleID
     * @access public
     * @return string
     */
    public function ajaxGetModule($productID, $isChosen = true, $number = 0, $moduleID = 0)
    {
        $module = $this->loadModel('tree')->getOptionMenu($productID, 'feedback', 0, 'all');
        $chosen = $isChosen ? 'chosen' : '';
        $number = !empty($number) ? $number : '';
        $name   = $number ? "module[$number]" : 'module';
        $select =  html::select($name, empty($module) ? array('' => '') : $module, $moduleID, "class='form-control {$chosen}'");
        die($select);
    }

    /**
     * Drop menu page.
     *
     * @param  int    $productID
     * @param  string $module
     * @param  string $method
     * @param  string $extra
     * @param  string $from
     * @access public
     * @return void
     */
    public function ajaxGetDropMenu($productID, $module, $method, $extra = '')
    {
        $this->loadModel('product');
        $programProducts = array();

        $products = $this->loadModel('feedback')->getGrantProducts(false);

        $programProducts = array();
        foreach($products as $product) $programProducts[$product->program][] = $product;

        $this->view->link      = $this->product->getProductLink($module, $method, $extra);
        $this->view->productID = $productID;
        $this->view->module    = $module;
        $this->view->method    = $method;
        $this->view->extra     = $extra;
        $this->view->products  = $programProducts;
        $this->view->projectID = 0;
        $this->view->programs  = $this->loadModel('program')->getPairs(true);
        $this->view->lines     = $this->product->getLinePairs();
        $this->display();
    }

    /**
     * Product setting
     *
     * @access public
     * @return void
     */
    public function productSetting()
    {
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $data = fixer::input('post')->get();

            $productList = !empty($data->products) ? array_values($data->products) : array();

            if(empty($productList[0])) die(js::error($this->lang->feedback->productSettingSaveError));

            $this->loadModel('setting')->setItem('system.common.global.productSettingList', json_encode($productList));

            $this->feedback->productSetting();

            return print(js::reload('parent.parent'));
        }

        $feedbackProducts   = $this->feedback->getFeedbackProducts(NULL, false);
        $productSettingList = isset($this->config->global->productSettingList) ? json_decode($this->config->global->productSettingList, true) : array();

        $productPairs = $productHeadMap = array();
        foreach($feedbackProducts as $productID => $product)
        {
            if(!empty($productSettingList) and !in_array($productID, $productSettingList)) continue;
            $productPairs[$productID]   = $product->name;
            $productHeadMap[$productID] = array('feedback' => $product->feedback, 'ticket' => $product->ticket);
        }

        /* 使用键名比较计算数组的交集 */
        $intersectKey = array_intersect_key(array_flip($productSettingList), $productPairs);
        /* 使用后面数组的值替换第一个数组的值 */
        $productPairs = array_replace($intersectKey, $productPairs);

        $this->view->productPairs   = $productPairs;
        $this->view->productHeadMap = $productHeadMap;
        $this->view->products       = array("" => "") + $this->loadModel('product')->getPairs('all|noclosed', 0, '', 'all');
        $this->view->users          = $this->loadModel('user')->getPairs('noclosed|nodeleted|noletter');

        $this->display();
    }
}
