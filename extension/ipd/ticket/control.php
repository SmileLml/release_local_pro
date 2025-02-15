<?php
/**
 * The control file of ticket of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2022 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yue Ma, Zongjun Lan, Xin Zhou
 * @package     ticket
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class ticket extends control
{

    /**
     * Construct function, load model
     *
     * @access public
     * @return void
     */
    public function __construct($module = '', $method = '')
    {
        parent::__construct($module, $method);
        $this->loadModel('action');
    }

    /**
     * Common actions of ticket module.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function commonAction($ticketID)
    {
        $this->view->ticket  = $this->ticket->getByID($ticketID);
        $this->view->users   = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->actions = $this->action->getList('ticket', $ticketID);
    }

    /**
     * Browse ticket.
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
        $this->loadModel('feedback');
        $this->loadModel('tree');
        $productID = $param;

        if(!$this->session->ticketProduct) $this->session->ticketProduct = 'all';
        if($browseType == 'byModule' and $param) $productID = $this->loadModel('tree')->getByID($param)->root;
        if($this->session->ticketProduct and !$productID and $browseType != 'byProduct') $productID = $this->session->ticketProduct;
        if($browseType == 'bysearch') $productID = $this->session->ticketProduct = 'all';
        if(in_array($browseType, array('byProduct', 'byModule')))
        {
            $this->session->set('ticketBrowseType', $browseType);
            $this->session->set('ticketObjectID', $param == 'all' ? 0 : $param);
        }
        $this->feedback->setMenu($productID, 'ticket');

        $queryID = $browseType == 'bysearch' ? (int)$param : 0;
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $moduleName = $this->lang->feedback->allModule;
        $moduleID = $this->session->ticketObjectID ? $this->session->ticketObjectID : 0;

        if($this->session->ticketBrowseType == 'byModule'  and $moduleID and $this->session->ticketProduct != 'all') $moduleName = $this->loadModel('tree')->getById($moduleID)->name;
        if($this->session->ticketBrowseType == 'byProduct' and $moduleID and $this->session->ticketProduct != 'all') $moduleName = $this->loadModel('product')->getById($moduleID)->name;

        if($browseType != 'bysearch')
        {
            $tickets = $this->ticket->getList($browseType, $orderBy, $pager, $moduleID);
        }
        else
        {
            $tickets = $this->ticket->getBySearch($queryID, $orderBy, $pager);
        }
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'ticket', false);

        /* Processing tickets consumed hours. */
        $allConsumed = $this->ticket->getConsumedByTicket(array_keys($tickets));
        foreach($tickets as $ticket)
        {
            $ticket->consumed = isset($allConsumed[$ticket->id]) ? round($allConsumed[$ticket->id], 2) : 0;
        }

        $actionURL = $this->createLink('ticket', 'browse', "browseType=bysearch&param=myQueryID");
        $this->ticket->buildSearchForm($queryID, $actionURL, $productID);

        $this->session->set('ticketList', $this->app->getURI(true), 'feedback');

        $this->view->title      = $this->lang->ticket->browse;
        $this->view->products   = $this->feedback->getGrantProducts();
        $this->view->users      = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->tickets    = $tickets;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->view->browseType = $browseType;
        $this->view->moduleName = $moduleName;
        $this->view->moduleTree = $this->tree->getTicketTreeMenu(array('treeModel', 'createTicketLink'));
        $this->view->moduleID   = $moduleID;
        $this->view->productID  = $productID;
        $this->display();
    }

    /**
     * Create a ticket.
     *
     * @param  string $extras
     * @access public
     * @return void
     */
    public function create($productID = 0, $extras = '')
    {
        $this->loadModel('feedback');
        $modules = $this->loadModel('tree')->getOptionMenu($productID, 'ticket', '', 'all');

        $extras = str_replace(array(',', ' '), array('&', ''), $extras);
        parse_str($extras, $output);
        $fromType = isset($output['fromType']) ? $output['fromType'] : '';
        $fromID   = isset($output['fromID']) ? $output['fromID'] : '';

        if($_POST)
        {
            $ticketID = $this->ticket->create($extras);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $files = $this->loadModel('file')->saveUpload('ticket', $ticketID, 'create');

            if(empty($fromType)) $actionID = $this->action->create('ticket', $ticketID, 'Opened');

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'id' => $ticketID));

            $browseLink = $fromType == 'feedback' ? $this->createLink('feedback', 'adminView', "feedbackID=$fromID") : $this->createLink('ticket', 'browse');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $browseLink));
        }

        $products = $this->feedback->getGrantProducts(false);
        $productPairs = $productTicket = array();
        foreach($products as $product)
        {
            if(empty($product)) continue;
            $productPairs[$product->id]  = $product->name;
            $productTicket[$product->id] = $product->ticket;
        }

        $ticketTitle = '';
        $desc        = '';
        $customer    = '';
        $contact     = '';
        $email       = '';
        $moduleID    = '';
        $pri         = 3;
        if($fromType == 'feedback')
        {
            $feedback    = $this->feedback->getByID($fromID);
            $productID   = $feedback->product;
            $ticketTitle = $feedback->title;
            $desc        = $feedback->desc;
            $modules     = $this->loadModel('tree')->getOptionMenu($productID, 'ticket', '', 'all');
            $customer    = $feedback->source;
            $contact     = $feedback->feedbackBy;
            $email       = $feedback->notifyEmail;
            $moduleID    = $feedback->module;
            $pri         = $feedback->pri;
            $this->view->sourceFiles = $feedback->files;
        }

        $this->feedback->setMenu($productID, 'ticket');

        $this->view->title         = $this->lang->ticket->create;
        $this->view->defaultType   = 'code';
        $this->view->products      = array('' => '') + $productPairs;
        $this->view->productTicket = $productTicket;
        $this->view->users         = $this->loadModel('user')->getPairs('noclosed');
        $this->view->pri           = $pri;
        $this->view->productID     = $productID;
        $this->view->product       = $this->loadModel('product')->getByID((int)$productID);
        $this->view->ticketTitle   = $ticketTitle;
        $this->view->desc          = $desc;
        $this->view->modules       = $modules ? $modules : array('' => '/');
        $this->view->customer      = $customer;
        $this->view->contact       = $contact;
        $this->view->email         = $email;
        $this->view->fromType      = $fromType;
        $this->view->fromID        = $fromID;
        $this->view->builds        = $this->loadModel('build')->getBuildPairs((int)$productID, 'all', 'noreleased');
        $this->view->moduleID      = $moduleID;

        $this->view->notifyEmailRequired = strpos($this->config->ticket->create->requiredFields, 'notifyEmail') !== false ? true : false;

        $this->display();
    }

    /**
     * Edit a ticket.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function edit($ticketID)
    {
        $ticket = $this->ticket->getByID($ticketID);
        $ticketSources = $this->ticket->getSourceByTicket($ticketID);
        if(empty($ticketSources))
        {
            $source = new stdclass();
            $source->customer    = '';
            $source->contact     = '';
            $source->notifyEmail = '';
            $ticketSources = array(0 => $source);
        }

        $this->loadModel('feedback')->setMenu($ticket->product, 'ticket');

        if(!empty($ticket->feedback)) $feedback = $this->feedback->getById($ticket->feedback);

        if($_POST)
        {
            $changes = $this->ticket->update($ticketID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $createFiles = $this->loadModel('file')->saveUpload('ticket', $ticketID, 'create', 'createFiles', 'createLabels');
            $finishFiles = $this->loadModel('file')->saveUpload('ticket', $ticketID, 'finished', 'finishFiles', 'finishLabels');
            $fileAction  = !empty($createFiles) ? $this->lang->addFiles . join(',', $createFiles) . "\n" : '';
            $fileAction .= !empty($finishFiles) ? $this->lang->addFiles . join(',', $finishFiles) . "\n" : '';
            $actionID = $this->loadModel('action')->create('ticket', $ticketID, 'Edited', $fileAction);
            $this->action->logHistory($actionID, $changes);

            $browseLink = $this->session->ticketList ? $this->session->ticketList : $this->createLink('ticket', 'browse');
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $browseLink));
        }

        $this->view->title         = $this->lang->ticket->edit;
        $this->view->products      = $this->feedback->getGrantProducts();
        $this->view->ticketSources = array_values($ticketSources);
        $this->view->users         = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->modules       = $this->loadModel('tree')->getOptionMenu($ticket->product, 'ticket', '', 'all');
        $this->view->actions       = $this->loadModel('action')->getList('ticket', $ticketID);
        $this->view->builds        = $this->loadModel('build')->getBuildPairs($ticket->product, 'all', 'noreleased', 0, 'execution', $ticket->openedBuild);
        $this->view->ticket        = $ticket;
        $this->view->feedback      = empty($feedback) ? '' : $feedback;

        $this->view->notifyEmailRequired = strpos($this->config->ticket->edit->requiredFields, 'notifyEmail') !== false ? true : false;
        $this->display();
    }

    /**
     * View a ticket.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function view($ticketID)
    {
        $ticket = $this->ticket->getByID($ticketID);

        if(empty($ticket)) return print(js::error($this->lang->notFound) . js::locate('back'));

        $products = $this->loadModel('feedback')->getGrantProducts();
        if(!isset($products[$ticket->product])) return print(js::error($this->lang->ticket->accessDenied) . js::locate('back'));

        $this->loadModel('feedback')->setMenu($ticket->product, 'ticket');

        if(!empty($ticket->feedback)) $feedback = $this->loadModel('feedback')->getById($ticket->feedback);

        $this->view->title         = $this->lang->ticket->view;
        $this->view->products      = $products;
        $this->view->ticketSources = $this->ticket->getSourceByTicket($ticketID);
        $this->view->actions       = $this->loadModel('action')->getList('ticket', $ticketID);
        $this->view->users         = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->modules       = $this->loadModel('tree')->getOptionMenu($ticket->product, 'ticket', 0, 'all');
        $this->view->builds        = $this->loadModel('build')->getBuildPairs($ticket->product);
        $this->view->ticket        = $ticket;
        $this->view->preAndNext    = $this->loadModel('common')->getPreAndNextObject('ticket', $ticketID);
        $this->view->feedback      = empty($feedback) ? '' : $feedback;
        $this->view->stories       = $this->ticket->getStoriesByTicket($ticketID);
        $this->view->bugs          = $this->ticket->getBugsByTicket($ticketID);
        $this->view->ticket        = $ticket;
        $this->display();
    }

    /**
     * Start a ticket.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function start($ticketID)
    {
        if(!empty($_POST))
        {
            $changes = $this->ticket->start($ticketID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $actionID = $this->action->create('ticket', $ticketID, 'Started', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('ticket', 'view', "ticketID=$ticketID"), 'parent'));
        }

        $this->commonAction($ticketID);

        $this->view->assignedTo = $this->app->user->account;
        $this->view->actions    = $this->loadModel('action')->getList('ticket', $ticketID);
        $this->display();
    }

    /**
     * Update assign of ticket.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function assignTo($ticketID)
    {
        if(!empty($_POST))
        {
            $changes = $this->ticket->assign($ticketID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $actionID = $this->action->create('ticket', $ticketID, 'Assigned', $this->post->comment, $this->post->assignedTo);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('ticket', 'view', "ticketID=$ticketID"), 'parent'));
        }

        $this->commonAction($ticketID);

        $this->display();
    }

    /**
     * Finish a ticket.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function finish($ticketID)
    {
        if($_POST)
        {
            $changes = $this->ticket->finish($ticketID);
            if(dao::isError()) die(js::error(dao::getError()));

            if($changes or ($this->post->comment))
            {
                $this->loadModel('action');
                $files = $this->loadModel('file')->saveUpload('ticket', $ticketID, 'finished');
                $fileAction = !empty($files) ? $this->lang->addFiles . join(',', $files) . "\n" : '';
                $actionID = $this->action->create('ticket', $ticketID, 'Finished', $fileAction . $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('ticket', 'view', "ticketID=$ticketID"), 'parent'));
        }

        $this->commonAction($ticketID);

        $this->view->finishedBy = $this->app->user->account;
        $this->display();
    }

    /**
     * Delete ticket.
     *
     * @param  int    $ticketID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($ticketID, $confirm = 'no')
    {
        if($confirm != 'yes') return print(js::confirm($this->lang->ticket->confirmDelete, inlink('delete', "ticketID=$ticketID&confirm=yes")));

        $this->ticket->delete(TABLE_TICKET, $ticketID);

        return print(js::reload('parent'));
    }

    /**
     * Close ticket.
     *
     * @param  int    $ticketID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function close($ticketID, $confirm = 'no')
    {
        if($_POST or $confirm == 'yes')
        {
            $changes = $this->ticket->close($ticketID, $confirm);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $this->loadModel('action');

                if($confirm == 'no')
                {
                    $files = $this->loadModel('file')->saveUpload('ticket', $ticketID, 'finished');
                    $fileAction = !empty($files) ? $this->lang->addFiles . join(',', $files) . "\n" : '';
                    $actionID = $this->action->create('ticket', $ticketID, 'Closed', $fileAction . $this->post->comment, $this->post->closedReason);
                }
                else
                {
                    $actionID = $this->action->create('ticket', $ticketID, 'Closed', '', 'commented');
                }
                $this->action->logHistory($actionID, $changes);
            }

            if($confirm == 'yes') return print(js::reload('parent'));            ;
            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('ticket', 'view', "ticketID=$ticketID"), 'parent'));
        }

        $ticket = $this->ticket->getByID($ticketID);
        if($ticket->status == 'done') return print(js::confirm($this->lang->ticket->confirmClose, inlink('close', "ticketID=$ticketID&confirm=yes")));

        $ticketList = array('' => '');
        $tickets = $this->ticket->getList('all');

        if($tickets)
        {
            foreach($tickets as $key => $ticket) $ticketList[$ticket->id] = "#$ticket->id " . $ticket->title;
            if(!empty($ticketList[$ticketID])) unset($ticketList[$ticketID]);
        }

        $this->view->ticket  = $this->ticket->getByID($ticketID);
        $this->view->users   = $this->loadModel('user')->getPairs('noletter');
        $this->view->actions = $this->loadModel('action')->getList('ticket', $ticketID);
        $this->view->tickets = $ticketList;
        $this->display();
    }

    /**
     * Activate ticket.
     *
     * @param  int    $ticketID
     * @access public
     * @return void
     */
    public function activate($ticketID)
    {
        if(!empty($_POST))
        {
            $changes = $this->ticket->activate($ticketID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes)
            {
                $actionID = $this->loadModel('action')->create('ticket', $ticketID, 'Activated', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return print(js::reload('parent.parent'));
            return print(js::locate($this->createLink('ticket', 'view', "ticketID=$ticketID"), 'parent'));
        }

        $this->commonAction($ticketID);
        /* 默认指派给最后解决人，最后解决者被删除置空 */
        $finishedAccount = !empty($this->view->ticket->resolvedBy) ? $this->loadModel('user')->getById($this->view->ticket->resolvedBy) : '';
        $finishedBy = (!empty($finishedAccount) and empty($finishedAccount->deleted)) ? $finishedAccount->account : '';
        $this->view->assignedTo = $finishedBy;

        $this->display();
    }

    /**
     * Create story by ticket;
     *
     * @param  int    $product
     * @param  string $extra
     * @access public
     * @return void
     */
    public function createStory($product, $extra)
    {
        echo $this->fetch('story', 'create', "product=$product&branch=0&moduleID=0&storyID=0&executionID=0&bugID=0&planID=0&todoID=0&extra=$extra&type=story");
    }

    /**
     * Create bug by ticket;
     *
     * @param  int    $product
     * @param  string $extra
     * @access public
     * @return void
     */
    public function createBug($product, $extra)
    {
        echo $this->fetch('bug', 'create', "product=$product&branch=0&extras=$extra");
    }

    /**
     * Get ticket module
     *
     * @param  int    $projectID
     * @param  int    $isChosen
     * @param  int    $number
     * @param  int    $moduleID
     * @param  string $field
     * @access public
     * @return string
     */
    public function ajaxGetModule($productID, $isChosen = 1, $number = 0, $moduleID = 0, $field = '')
    {
        $module = $this->loadModel('tree')->getOptionMenu($productID, 'ticket', 0, 'all');
        $chosen = $isChosen ? 'chosen' : '';
        $number = !empty($number) ? $number : '';
        if(!empty($field))
        {
            $name = $field . "[" . $number . "]";
        }
        else
        {
            $name = $number ? "modules[$number]" : 'module';
        }
        $select =  html::select($name, empty($module) ? array('' => '') : $module, $moduleID, "class='form-control {$chosen}'");
        die($select);
    }

    /**
     * Batch edit ticket.
     *
     * @access public
     * @return void
     */
    public function batchEdit()
    {
        if(isset($_POST['ticketIDList']))
        {
            $tickets = $this->ticket->getByList($_POST['ticketIDList']);
            $closedTickets = array();
            foreach($tickets as $ticketID => $ticket)
            {
                if($ticket->status == 'closed')
                {
                    $closedTickets[] = $ticket->id;
                    unset($tickets[$ticketID]);
                }

            }
            if($tickets == array())
            {
                echo js::alert(sprintf($this->lang->ticket->batchEditTip, implode(',', $closedTickets)));
                return print(js::locate($this->createLink('ticket', 'browse')));
            }

            $this->view->title        = $this->lang->ticket->edit;
            $this->view->users        = $this->loadModel('user')->getPairs('noletter');
            $this->view->modules      = $this->loadModel('feedback')->getModuleList('ticket');
            $this->view->tickets      = $tickets;
            $this->view->batchEditTip = !empty($closedTickets) ? sprintf($this->lang->ticket->batchEditTip, implode(',', $closedTickets)) : '';
            $this->view->products     = $this->feedback->getGrantProducts(true, true);

            $this->display();
        }
        elseif($_POST)
        {
            $allChanges = $this->ticket->batchUpdate();
            if(dao::isError()) die(js::error(dao::getError()));
            $this->loadModel('action');
            if(!empty($allChanges))
            {
                foreach($allChanges as $ticketID => $changes)
                {
                    if(empty($changes)) continue;

                    $actionID = $this->action->create('ticket', $ticketID, 'Edited');
                    $this->action->logHistory($actionID, $changes);
                }
            }
            $browseLink = $this->createLink('ticket', 'browse', 'browseType=all');
            return print(js::locate($browseLink, 'parent'));
        }
    }

    /**
     * Batch assignTo.
     *
     * @access public
     * @return void
     */
    public function batchAssignTo()
    {
        $allChanges = $this->ticket->batchAssign();

        $this->loadModel('action');
        if(!empty($allChanges))
        {
            foreach($allChanges as $ticketID => $changes)
            {
                if(empty($changes)) continue;
                $actionID = $this->action->create('ticket', $ticketID, 'Assigned', '', $this->post->assignedTo);
                $this->action->logHistory($actionID, $changes);
            }
        }
        return print(js::reload('parent'));
    }

    /**
     * Batch finish ticket.
     *
     * @access public
     * @return void
     */
    public function batchFinish()
    {
        if(isset($_POST['ticketIDList']))
        {
            $ticketIDList = $this->ticket->getByList($_POST['ticketIDList']);
            $notFinishTicket = array();
            foreach($ticketIDList as $ticketID => $ticket)
            {
                if($ticket->status == 'done' or $ticket->status == 'closed')
                {
                    $notFinishTicket[] = $ticket->id;
                    unset($ticketIDList[$ticketID]);
                }

            }

            if($ticketIDList == array())
            {
                echo js::alert(sprintf($this->lang->ticket->batchFinishTip, implode(',', $notFinishTicket)));
                $browseLink = $this->session->ticketList ? $this->session->ticketList : $this->createlink('ticket', 'browse');
                return print(js::locate($browseLink));
            }

            $this->view->tickets         = $ticketIDList;
            $this->view->batchFinishTip  = !empty($notFinishTicket) ? sprintf($this->lang->ticket->batchFinishTip, implode(',', $notFinishTicket)) : '';
            $this->view->title           = $this->lang->ticket->batchFinish;

            $this->display();
        }
        elseif($_POST)
        {
            $data = fixer::input('post')->get();
            $allChanges = $this->ticket->batchFinish();
            if(dao::isError()) die(js::error(dao::getError()));
            if(!empty($allChanges))
            {
                foreach($allChanges as $ticketID => $changes)
                {
                    if(empty($changes)) continue;

                    $actionID = $this->loadModel('action')->create('ticket', $ticketID, 'Finished', $data->comments[$ticketID]);
                    $this->action->logHistory($actionID, $changes);
                }
            }
            $browseLink = $this->createLink('ticket', 'browse');
            return print(js::locate($browseLink, 'parent'));
        }
    }

    /**
     * Batch activated ticket.
     *
     * @access public
     * @return void
     */
    public function batchActivate()
    {
        if(isset($_POST['ticketIDList']))
        {
            $ticketIDList = $this->ticket->getByList($_POST['ticketIDList']);
            $notActivateTicket = array();
            foreach($ticketIDList as $ticketID => $ticket)
            {
                if($ticket->status == 'wait' or $ticket->status == 'doing')
                {
                    $notActivateTicket[] = $ticket->id;
                    unset($ticketIDList[$ticketID]);
                }

            }

            if($ticketIDList == array())
            {
                echo js::alert(sprintf($this->lang->ticket->batchActivateTip, implode(',', $notActivateTicket)));
                return print(js::locate($this->createLink('ticket', 'browse')));
            }

            $this->view->tickets          = $ticketIDList;
            $this->view->batchActivateTip = !empty($notActivateTicket) ? sprintf($this->lang->ticket->batchActivateTip, implode(',', $notActivateTicket)) : '';
            $this->view->users            = $this->loadModel('user')->getPairs('noclosed|noletter');
            $this->view->title            = $this->lang->ticket->batchActivate;

            $this->display();
        }
        elseif($_POST)
        {
            $data = fixer::input('post')->get();
            $allChanges = $this->ticket->batchActivate();
            if(dao::isError()) die(js::error(dao::getError()));
            if(!empty($allChanges))
            {
                foreach($allChanges as $ticketID => $changes)
                {
                    if(empty($changes)) continue;

                    $actionID = $this->loadModel('action')->create('ticket', $ticketID, 'Activated', $data->comments[$ticketID]);
                    $this->action->logHistory($actionID, $changes);
                }
            }
            $browseLink = $this->createLink('ticket', 'browse');
            return print(js::locate($browseLink, 'parent'));
        }
    }

    /**
     * Sync product module.
     *
     * @param  int    $productID
     * @param  string $parent
     * @access public
     * @return void
     */
    public function syncProduct($productID = 0, $parent = '')
    {
        echo $this->fetch('feedback', 'syncProduct', "productID=$productID&module=ticket&parent=$parent");
    }

    /**
     * Export ticket.
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
            $this->loadModel('transfer');
            $sort = common::appendOrder($orderBy);
            $sort = str_replace('id', 't1.id', $sort);

            /* Define the common fields. */
            $commonFields = array(
                'group_concat(t2.customer) as customer',
                'group_concat(t2.contact) as contact',
                'group_concat(t2.notifyEmail) as notifyEmail',
            );

            /* Replace id and desc with their corresponding aliases. */
            $selectFields = array_merge($_POST['exportFields'], $commonFields);
            $selectFields = str_replace(array('id', 'desc'), array('t1.id', '`desc`'), $selectFields);
            $selectFields = implode(',', $selectFields);

            /* Get tickets. */
            $this->session->ticketQueryCondition = preg_replace('/SELECT.*WHERE/i', '', $this->session->ticketQueryCondition);
            $sql = $this->dao->select("$selectFields")
                ->from(TABLE_TICKET)->alias('t1')
                ->leftJoin(TABLE_TICKETSOURCE)->alias('t2')->on('t1.id=t2.ticketId')
                ->where($this->session->ticketQueryCondition)
                ->beginIF($this->post->exportType == 'selected')->andWhere('t1.id')->in($this->cookie->checkedItem)->fi()
                ->groupBy('t1.id')
                ->orderBy($sort)
                ->get();

            $this->session->set('ticketTransferCondition', $sql);

            $this->ticket->setListValue();

            $this->transfer->export('ticket');

            $this->fetch('file', 'export2' . $_POST['fileType'], $_POST);
        }

        $fileName = zget($this->lang->ticket->featureBar['browse'], $browseType, '');
        if(empty($fileName)) $fileName = zget($this->lang->ticket->statusList, $browseType, '');
        if(empty($fileName) and isset($this->lang->ticket->$browseType)) $fileName = $this->lang->ticket->$browseType;
        if($fileName) $fileName = $this->lang->ticket->common . $this->lang->dash . $fileName;

        $this->view->fileName        = $fileName;
        $this->view->allExportFields = $this->config->ticket->exportFields;
        $this->view->customExport    = true;
        $this->display();
    }
}
