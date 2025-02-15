<?php
/**
 * The control file of domain of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2010 QingDao Nature Easy Soft Network Technology Co,LTD (www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Jiangxiu Peng <pengjiangxiu@cnezsoft.com>
 * @package     domain
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class domain extends control
{
    /**
     * Browse domian page.
     *
     * @param  string   $browseType
     * @param  string   $param
     * @param  string   $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($browseType = 'all', $param = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $browseType = strtolower($browseType);

        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $domainList = $this->domain->getList($browseType, $param, $orderBy, $pager);

        /* Build the search form. */
        $actionURL = $this->createLink('domain', 'browse', "browseType=bySearch&queryID=myQueryID");
        $this->config->domain->search['actionURL'] = $actionURL;
        $this->config->domain->search['queryID']   = $param;
        $this->config->domain->search['onMenuBar'] = 'no';
        $this->loadModel('search')->setSearchParams($this->config->domain->search);

        $this->view->title      = $this->lang->domain->common;
        $this->view->pager      = $pager;
        $this->view->param      = $param;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|nodeleted');
        $this->view->orderBy    = $orderBy;
        $this->view->browseType = $browseType;
        $this->view->domainList = $domainList;

        $this->view->position[] = $this->lang->domain->common;

        $this->display();
    }

    /**
     * Create domain.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        if($_POST)
        {
            $createID = $this->domain->create();
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $this->loadModel('action')->create('domain', $createID, 'created');

            if(isonlybody()) return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse')));
        }

        $this->view->title = $this->lang->domain->create;
        $this->view->position[] = html::a($this->createLink('domain', 'browse'), $this->lang->domain->common);
        $this->view->position[] = $this->lang->domain->create;

        $this->view->users = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');
        $this->display();
    }

    /**
     * Edit one domain.
     *
     * @param  int $id
     * @access public
     * @return void
     */
    public function edit($id)
    {
        if($_POST)
        {
            $changes = $this->domain->update($id);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            if($changes)
            {
                $actionID = $this->loadModel('action')->create('domain', $id, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }

            if(isonlybody()) return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse')));
        }

        $this->view->title      = $this->lang->domain->edit;
        $this->view->domain     = $this->domain->getById($id);
        $this->view->position[] = html::a($this->createLink('domain', 'browse'), $this->lang->domain->common);
        $this->view->position[] = $this->lang->domain->edit;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter|nodeleted|noclosed');

        $this->display();
    }

    public function view($id)
    {
        $this->view->title   = $this->lang->domain->view;
        $this->view->domain  = $this->domain->getById($id);
        $this->view->users   = $this->loadModel('user')->getPairs('noletter|nodeleted');
        $this->view->actions = $this->loadModel('action')->getList('domain', $id);

        $this->view->position[] = html::a($this->createLink('domain', 'browse'), $this->lang->domain->common);
        $this->view->position[] = $this->lang->domain->view;
        $this->display();
    }

    /**
     * Delete domain item.
     *
     * @param  int     $id
     * @access public
     * @return void
     */
    public function delete($id)
    {
        $this->domain->delete(TABLE_DOMAIN, $id);

        /* if ajax request, send result. */
        if($this->server->ajax)
        {
            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
            }
            else
            {
                $response['result']  = 'success';
                $response['message'] = '';
            }
            return $this->send($response);
        }

        if(isOnlyBody()) die(js::reload('parent.parent'));
        die(js::reload('parent'));
    }
}
