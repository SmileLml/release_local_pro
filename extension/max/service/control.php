<?php
/**
 * The control file of service of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     service
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class service extends control
{
    /**
     * Index
     *
     * @access public
     * @return void
     */
    public function index()
    {
        $this->locate(inlink('show'));
    }

    /**
     * Create service.
     *
     * @param  int    $parent
     * @param  string $type
     * @access public
     * @return void
     */
    public function create($parent = 0, $type = 'service')
    {
        if($_POST)
        {
            $serviceID = $this->service->create($parent);
            if(dao::isError()) return print(js::error(dao::getError()));
            $this->loadModel('action')->create('service', $serviceID, 'Created');

            $isonlybody = isonlybody();
            $target     = $isonlybody ? 'parent.parent' : 'parent';
            if($isonlybody) return print(js::reload($target));

            $service   = $this->service->getById($serviceID);
            list($top) = explode(',', trim($service->path, ','));
            return print(js::locate($this->createLink('service', 'manage', "serviceID=$top"), $target));
        }

        $this->view->title = $this->lang->service->create;
        $this->view->position[] = html::a(inlink('manage'), $this->lang->service->common);
        $this->view->position[] = $this->lang->service->create;

        $this->view->users  = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $this->view->depts  = $this->loadModel('dept')->getOptionMenu();
        $this->view->hosts  = $this->loadModel('host')->getPairs();
        $this->view->type   = $type;
        $this->view->parent = $parent ? $this->service->getById($parent) : 0;

        $this->view->hostGroup = $this->loadModel('tree')->getOptionMenu(0, 'host');
        $this->display();
    }

    /**
     * Edit service.
     *
     * @param  int    $serviceID
     * @access public
     * @return void
     */
    public function edit($serviceID)
    {
        if($_POST)
        {
            $changes = $this->service->update($serviceID);
            if(dao::isError()) return print(js::error(dao::getError()));
            if($changes)
            {
                $actionID = $this->loadModel('action')->create('service', $serviceID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }

            $isonlybody = isonlybody();
            $target     = $isonlybody ? 'parent.parent' : 'parent';
            if($isonlybody) return print(js::locate($this->createLink('service', 'view', "serviceID=$serviceID"), 'parent'));

            $service   = $this->service->getById($serviceID);
            list($top) = explode(',', trim($service->path, ','));
            return print(js::locate($this->createLink('service', 'view', "serviceID=$serviceID"), $target));
        }

        $service = $this->service->getById($serviceID);
        if($service->port == 0) $service->port = '';

        $this->view->title = $this->lang->service->edit;
        $this->view->position[] = html::a(inlink('manage'), $this->lang->service->common);
        $this->view->position[] = $this->lang->service->edit;

        $this->view->service = $service;
        $this->view->users   = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed', "{$this->view->service->devel},{$this->view->service->qa},{$this->view->service->ops}");
        $this->view->depts   = $this->loadModel('dept')->getOptionMenu();
        $this->view->hosts   = $this->loadModel('host')->getPairs();
        $this->view->externalList = $this->lang->service->externalList;
        $this->display();
    }

    /**
     * View service
     *
     * @param  int    $serviceID
     * @access public
     * @return void
     */
    public function view($serviceID)
    {
        $serviceID = (int)$serviceID;
        $service   = $this->service->getById($serviceID);

        if(empty($service)) return print(js::error($this->lang->notFound) . js::locate($this->createLink('service', 'manage')));
        if($service->port == 0)     $service->port = '';
        if($service->external == 0) $service->external = '';

        $this->view->title = $this->lang->service->view;
        $this->view->position[] = html::a(inlink('manage'), $this->lang->service->common);
        $this->view->position[] = $this->lang->service->view;

        $this->view->service = $service;
        $this->view->users   = $this->loadModel('user')->getPairs('noletter|nodeleted');
        $this->view->depts   = $this->loadModel('dept')->getOptionMenu();
        $this->view->hosts   = $this->loadModel('host')->getPairs();
        $this->view->actions = $this->loadModel('action')->getList('service', $serviceID);
        $this->display();
    }

    /**
     * Delete service.
     *
     * @param  int    $serviceID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($serviceID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            return print(js::confirm($this->lang->service->confirmDelete, inlink('delete', "serviceID=$serviceID&confirm=yes"), ''));
        }

        $service = $this->service->getById($serviceID);
        $this->service->delete(TABLE_SERVICE, $serviceID);

        if($service->grade == 1) return print(js::locate($this->createLink('service', 'manage'), 'parent'));
        return print(js::reload('parent'));
    }

    /**
     * Manage service.
     *
     * @param  int    $serviceID
     * @access public
     * @return void
     */
    public function manage($serviceID = 0)
    {
        $this->view->title = $this->lang->service->manage;
        $this->view->position[] = html::a(inlink('manage'), $this->lang->service->common);
        $this->view->position[] = $this->lang->service->manage;

        $this->view->topServices = array(0 => $this->lang->service->all) + $this->service->getTopServicePairs();
        $this->view->serviceID   = $serviceID;
        $this->view->tree        = $this->service->getTree($serviceID);
        $this->display();
    }


    public function browse($browseType = 'all', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $browseType = strtolower($browseType);

        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);
        $serviceList = $this->service->getList($browseType, $param, $orderBy, $pager);
        foreach($serviceList as $service)
        {
            if($service->port == 0) $service->port = '';
        }

        $this->view->title      = $this->lang->service->common;
        $this->view->pager      = $pager;
        $this->view->param      = $param;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|nodeleted');
        $this->view->orderBy    = $orderBy;
        $this->view->browseType = $browseType;
        $this->view->serviceList = $serviceList;
        $this->view->hosts  = $this->loadModel('host')->getPairs();

        $this->view->position[] = $this->lang->service->common;

        $this->view->topServices = array(0 => $this->lang->service->all) + $this->service->getTopServicePairs();
        $this->display();
    }

}
