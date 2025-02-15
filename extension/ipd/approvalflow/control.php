<?php
/**
 * The control file of approvalflow module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     approvalflow
 * @version     $Id: control.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class approvalflow extends control
{
    /**
     * Design flow.
     *
     * @param int $flowID
     * @access public
     * @return void
     */
    public function design($flowID = 0)
    {
        $flow = $this->approvalflow->getByID($flowID);

        if(!empty($_POST))
        {
            $this->approvalflow->updateNodes($flow);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "type=$flow->type")));
        }

        $this->view->flow  = $flow;
        $this->view->users = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->depts = $this->loadModel('dept')->getPairs();
        $this->view->roles = $this->approvalflow->getRolePairs();
        $this->view->title = $flow->name . '-' . $this->lang->approvalflow->common;

        $this->display();
    }

    /**
     * Browse flows.
     *
     * @param string $type
     * @param string $orderBy
     * @param int    $recTotal
     * @param int    $recPerPage
     * @param int    $pageID
     *
     * @access public
     * @return void
     */
    public function browse($type = 'project', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        if($type == 'project' and !helper::hasFeature('waterfall') and !helper::hasFeature('waterfallplus')) $type = 'workflow';

        $uri = $this->app->getURI(true);
        $this->session->set('flowList', $uri, $this->app->tab);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $flows = $this->approvalflow->getList($type, $orderBy, $pager);

        $this->view->type   = $type;
        $this->view->flows  = $flows;
        $this->view->title  = $this->lang->approvalflow->common;
        $this->view->users  = $this->loadModel('user')->getPairs('noletter');
        $this->view->module = 'approvalflow';
        $this->view->pager  = $pager;
        $this->display();
    }

    /**
     * Create flow.
     *
     * @param string $type
     * @access public
     * @return void
     */
    public function create($type = 'project')
    {
        if($_POST)
        {
            $flowID = $this->approvalflow->create();

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();

                return print $this->send($response);
            }

            $this->loadModel('action')->create('approvalflow', $flowID, 'Opened');

            $response['result']  = 'success';
            $response['locate']  = $this->session->flowList;
            $response['message'] = $this->lang->saveSuccess;

            return print $this->send($response);
        }

        $this->view->type  = $type;
        $this->view->title = $this->lang->approvalflow->common;
        $this->view->users = $this->loadModel('user')->getPairs('noletter');

        $this->display();
    }

    /**
     * View flow.
     *
     * @param  int    $flowID
     * @access public
     * @return void
     */
    public function view($flowID)
    {
        $flow = $this->approvalflow->getByID($flowID);

        $this->view->title   = $this->lang->approvalflow->common . $this->lang->colon . $flow->name;
        $this->view->actions = $this->loadModel('action')->getList('approvalflow', $flowID);
        $this->view->users   = $this->loadModel('user')->getPairs('noletter|pofirst|nodeleted');
        $this->view->flow    = $flow;

        $this->display();
    }

    /**
     * Edit flow.
     *
     * @param  int    $flowID
     * @access public
     * @return void
     */
    public function edit($flowID)
    {
        $flow = $this->approvalflow->getByID($flowID);

        if($_POST)
        {
            if(empty($flow))
            {
                if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'fail', 'message' => '404 Not found'));
                die(js::error($this->lang->notFound));
            }

            $changes = $this->approvalflow->update($flowID);
            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $actionID = $this->loadModel('action')->create('approvalflow', $flowID, 'Edited');
            $this->action->logHistory($actionID, $changes);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inLink('view', "flowID=$flowID")));
        }

        $this->view->title = $this->lang->approvalflow->common . $this->lang->colon . $this->lang->approvalflow->edit;
        $this->view->flow  = $flow;

        $this->display();
    }

    /**
     * Delete flow.
     *
     * @param  int    $flowID
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function delete($flowID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->approvalflow->confirmDelete, inLink('delete', "flowID=$flowID&confirm=yes")));
        }
        else
        {
            $this->dao->update(TABLE_APPROVALFLOW)->set('deleted')->eq(1)->where('id')->eq($flowID)->exec();
            if(defined('RUN_MODE') && RUN_MODE == 'api') return $this->send(array('status' => 'success'));
            die(js::locate($this->session->flowList, 'parent'));
        }
    }

    /**
     * Approval flow role list.
     *
     * @access public
     * @return void
     */
    public function role()
    {
        $this->view->title    = $this->lang->approvalflow->role->browse;
        $this->view->roleList = $this->approvalflow->getRoleList();
        $this->view->users    = $this->loadModel('user')->getPairs('nodeleted|noclosed|noletter');
        $this->view->module   = 'approvalflow';

        $this->display();
    }

    /**
     * Create a role.
     *
     * @access public
     * @return void
     */
    public function createRole()
    {
        if($_POST)
        {
            $roleID = $this->approvalflow->createRole();
            if(dao::isError())
            {
                return print($this->send(array('result' => 'fail', 'message' => dao::getError())));
            }

            return print($this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'parent')));
        }

        $this->view->users = $this->loadModel('user')->getPairs('nodeleted|noclosed');
        $this->display();
    }

    /**
     * Edit a role.
     *
     * @param  int    $roleID
     * @access public
     * @return void
     */
    public function editRole($roleID = 0)
    {
        if($_POST)
        {
            $this->approvalflow->editRole($roleID);
            if(dao::isError())
            {
                return print($this->send(array('result' => 'fail', 'message' => dao::getError())));
            }

            return print($this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'parent')));
        }

        $this->view->role  = $this->dao->select('*')->from(TABLE_APPROVALROLE)->where('id')->eq($roleID)->fetch();
        $this->view->users = $this->loadModel('user')->getPairs('nodeleted|noclosed');
        $this->display();
    }

    /**
     * Delete role.
     *
     * @param  int    $roleID
     * @param  string $confirm yes|no
     * @access public
     * @return void
     */
    public function deleteRole($roleID = 0, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->approvalflow->confirmDelete, inLink('deleterole', "roleID=$roleID&confirm=yes")));
        }
        else
        {
            $this->dao->update(TABLE_APPROVALROLE)->set('deleted')->eq('1')->where('id')->eq($roleID)->exec();
            die(js::locate(inlink('role'), 'parent'));
        }
    }
}
