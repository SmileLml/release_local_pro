<?php
class charter extends control
{
    /**
     * Browse.
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
    public function browse($browseType = 'all', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->session->set('charterList', $this->app->getURI(true));
        setcookie('browseType', $browseType);

        $queryID   = ($browseType == 'bysearch') ? (int)$param : 0;

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $charters = $this->charter->getList($browseType, $queryID, $orderBy, $pager);

        $this->view->title      = $this->lang->charter->browse;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->view->charters   = $charters;
        $this->view->browseType = $browseType;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');

        $this->display();
    }

    /**
     * Create.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        if($_POST)
        {
            $charterID = $this->charter->create();

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            $this->loadModel('action')->create('charter', $charterID, 'created');
            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = inlink('browse');

            $this->send($response);
        }

        $this->view->title          = $this->lang->charter->create;
        $this->view->budgetUnitList = $this->loadModel('project')->getBudgetUnitList();
        $this->view->users          = $this->loadModel('user')->getPairs('noletter|noclosed');
        $this->view->products       = array(0 => '') + $this->loadModel('product')->getPairs('noclosed');
        $this->view->roadmaps       = array(0 => '');
        $this->display();
    }

    /**
     * Edit.
     *
     * @access public
     * @return void
     */
    public function edit($charterID = 0)
    {
        $charter = $this->charter->getByID($charterID);

        if($_POST)
        {
            $changes = $this->charter->update($charterID);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            if($changes)
            {
                $actionID = $this->loadModel('action')->create('charter', $charterID, 'edited');
                $this->action->logHistory($actionID, $changes);
                foreach($changes as $change)
                {
                    if($change['field'] == 'roadmap')
                    {
                       $this->loadModel('roadmap')->setStatus($change['old'], 'wait');
                       $this->loadModel('roadmap')->setStatus($change['new'], 'launching');
                       break;
                    }
                }
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = inlink('browse');

            $this->send($response);
        }

        $this->view->title          = $this->lang->charter->edit;
        $this->view->budgetUnitList = $this->loadModel('project')->getBudgetUnitList();
        $this->view->users          = $this->loadModel('user')->getPairs('noletter|noclosed');
        $this->view->products       = array(0 => '') + $this->loadModel('product')->getPairs('noclosed');
        $this->view->roadmaps       = array(0 => '') + $this->loadModel('roadmap')->getPairs($charter->product, '', 'nolaunching');
        $this->view->charter        = $charter;

        $this->display();
    }

    /**
     * Delete a charter.
     *
     * @param  int    $charterID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($charterID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            echo js::confirm($this->lang->charter->confirmDelete, $this->createLink('charter', 'delete', "charter=$charterID&confirm=yes"), '');
            exit;
        }
        else
        {
            $charter = $this->charter->getByID($charterID);
            $this->charter->delete(TABLE_CHARTER, $charterID);

            if(dao::isError()) return print(js::error(dao::getError()));
            $roadmap = $this->loadModel('roadmap')->getByID($charter->roadmap);
            if($roadmap and $roadmap->status != 'wait')
            {
                $this->dao->update(TABLE_ROADMAP)->set('status')->eq('wait')->where('id')->eq($charter->roadmap)->exec();
                $this->loadModel('action')->create('roadmap', $charter->roadmap, 'changedbycharter', '', $charterID);
            }

            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));

            $locateLink = $this->createLink('charter', 'browse');
            die(js::locate($locateLink, 'parent.parent'));
        }
    }

    /**
     * Close a charter.
     *
     * @param  int    $charterID
     *
     * @access public
     * @return void
     */
    public function close($charterID = 0)
    {
        $charter = $this->charter->getByID($charterID);

        if(!empty($_POST))
        {
            $changes = $this->charter->close($charterID);

            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes || $this->post->comment != '')
            {
                $actionID = $this->loadModel('action')->create('charter', $charterID, 'closed', $this->post->comment, $charter->status);
                $this->action->logHistory($actionID, $changes);
            }

            return print(js::closeModal('parent.parent'));
        }

        $this->view->title   = $this->lang->close;
        $this->view->users   = $this->loadModel('user')->getPairs('nodeleted');
        $this->view->actions = $this->loadModel('action')->getList('charter', $charterID);
        $this->view->charter = $charter;
        $this->display();
    }

    /**
     * review
     *
     * @param int     $charterID
     * @param string  $confim
     * @access public
     * @return void
     */
    public function activate($charterID = 0, $confim = 'no')
    {
        if($confim == 'no')
        {
            echo js::confirm($this->lang->charter->confirmActivate, inlink('activate', "charterID=$charterID&confirm=yes"), '');
            exit;
        }
        else
        {
            $charter = $this->charter->getByID($charterID);

            $changes = $this->charter->activate($charterID);

            if(dao::isError()) return print(js::error(dao::getError()));

            if($changes || $this->post->comment != '')
            {
                $actionID = $this->loadModel('action')->create('charter', $charterID, 'activated', $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            return print(js::reload('parent.parent'));
        }
    }

    /**
     * review
     *
     * @param  int    $charterID
     *
     * @access public
     * @return void
     */
    public function review($charterID = 0)
    {
        $charter = $this->charter->getByID($charterID);
        $launchedCharter = $this->fetchPairsByRoadmap($charter->roadmap, 'launched');

        if(!empty($_POST))
        {
            $reviewedResult = !empty($_POST['reviewedResult']) ? $_POST['reviewedResult'] : $charter->reviewedResult;
            $changes = $this->charter->review($charterID);

            if(dao::isError()) return print(js::error(dao::getError()));

            $actionID = $this->loadModel('action')->create('charter', $charterID, 'reviewbycharter', '', ucfirst($reviewedResult));
            $this->action->logHistory($actionID, $changes);

            $this->executeHooks($charterID);
            return print(js::reload('parent.parent'));
        }
        $charter->meetingDate = helper::isZeroDate($charter->meetingDate) ? '' : date('Y-m-d', strtotime($charter->meetingDate));

        $this->view->title           = $this->lang->charter->review;
        $this->view->budgetUnitList  = $this->loadModel('project')->getBudgetUnitList();
        $this->view->launchedCharter = $launchedCharter;
        $this->view->users           = $this->loadModel('user')->getPairs('noletter|noclosed');
        $this->view->products        = array(0 => '') + $this->loadModel('product')->getPairs();
        $this->view->charter         = $charter;
        $this->view->actions         = $this->loadModel('action')->getList('charter', $charterID);
        $this->display();
    }

    /**
     * 根据立项关联的路标以及立项状态查询相关立项。
     * Fetch charter by roadmap and status.
     *
     * @param  int    $roadmapID
     * @param  string $status
     * @access public
     * @return void
     */
    public function fetchPairsByRoadmap($roadmapID = 0, $status = '')
    {
        return  $this->dao->select('*')->from(TABLE_CHARTER)
            ->where('deleted')->eq(0)
            ->andWhere('roadmap')->eq($roadmapID)
            ->beginIF($status)->andWhere('status')->eq($status)
            ->fetch();
    }

    /**
     * view.
     *
     * @access public
     * @return void
     */
    public function view($charterID = 0)
    {
        $charter = $this->charter->getByID($charterID);
        if(!$charter) return print(js::error($this->lang->notFound) . js::locate($this->createLink('charter', 'browse')));

        $this->view->title          = $this->lang->charter->view;
        $this->view->budgetUnitList = $this->loadModel('project')->getBudgetUnitList();
        $this->view->users          = $this->loadModel('user')->getPairs('noletter');
        $this->view->products       = array(0 => '') + $this->loadModel('product')->getPairs();
        $this->view->roadmaps       = array(0 => '') + $this->loadModel('roadmap')->getPairs($charter->product);
        $this->view->charter        = $charter;
        $this->view->stories        = $this->roadmap->getRoadmapStories($charter->roadmap);
        $this->view->actions        = $this->loadModel('action')->getList('charter', $charterID);
        $this->view->modules        = $this->loadModel('tree')->getOptionMenu($charter->product, $viewType = 'story', 0, 'all');

        $this->display();
    }

    /**
     * Build the upload form.
     *
     * @param  string $filesName
     * @param  string $buttonName
     * @param  string $labelsName
     * @access public
     * @return void
     */
    public function buildFileForm($filesName = "files", $buttonName = '', $labelsName = "labels")
    {
        $this->loadModel('file');
        if(!file_exists($this->file->savePath))
        {
            printf($this->lang->file->errorNotExists, $this->file->savePath);
            return false;
        }
        elseif(!is_writable($this->file->savePath))
        {
            printf($this->lang->file->errorCanNotWrite, $this->file->savePath, $this->file->savePath);
            return false;
        }

        $this->view->filesName  = $filesName;
        $this->view->labelsName = $labelsName;
        $this->view->buttonName = $buttonName ? $buttonName : $this->lang->file->addFile;
        $this->display();
    }

    /**
     * Load roadmap stories.
     *
     * @param  int    $roadmapID
     * @access public
     * @return void
     */
    public function loadRoadmapStories($roadmapID = 0)
    {
        $this->loadModel('story');
        $stories = $this->loadModel('roadmap')->getRoadmapStories($roadmapID);
        $roadmap = $this->roadmap->getByID($roadmapID);

        $this->view->stories = $stories;
        $this->view->modules = !empty($roadmapID) ? $this->loadModel('tree')->getOptionMenu($roadmap->product, $viewType = 'story', 0, 'all') : array();
        $this->display();
    }

    /**
     * Ajax get roadmaps by product.
     *
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function ajaxGetRoadmaps($productID = 0)
    {
        $options = $this->loadModel('roadmap')->getPairs($productID, '', 'nolaunching');

        return print(html::select('roadmap', $options, '', "class='from-control chosen' onchange='changeLink(this.value)'"));
    }

    /**
     * Ajax get charter info by id.
     *
     * @param  int    $charterID
     * @access public
     * @return void
     */
    public function ajaxGetCharterInfo($charterID = 0)
    {
        $charter = $this->charter->getByID($charterID);
        $charter->productName = $this->dao->select('name')->from(TABLE_PRODUCT)->where('id')->eq($charter->product)->fetch('name');
        $charter->roadmapName = $this->dao->select('name')->from(TABLE_ROADMAP)->where('id')->eq($charter->roadmap)->fetch('name');

        return print(json_encode($charter));
    }
}
