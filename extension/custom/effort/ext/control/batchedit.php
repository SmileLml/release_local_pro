<?php

class myeffort extends effort
{
    /**
     * Batch edit a effort.
     *
     * @param  int    $effortID
     * @access public
     * @return void
     */
    public function batchEdit($from = 'browse', $userID = '')
    {
        if($userID == '') $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;
        if(!empty($_POST) and $from == 'batchEdit')
        {
            $this->effort->batchUpdate();
            if(dao::isError()) die(js::error(dao::getError()));

            $effortType = isset($_SESSION['effortType']) ? $_SESSION['effortType'] : 'today';

            $url = $this->session->effortList ? $this->session->effortList : $this->createLink('my', 'effort', "type=$effortType");
            die(js::locate($url, 'parent'));
        }

        if(empty($_POST['effortIDList'])) $this->post->set('effortIDList', array());
        /* Judge a private effort or not, If private, die. */
        $efforts = $this->effort->getAccountEffort($_POST['effortIDList'], $account);
        if(dao::isError())
        {
            echo js::alert(dao::getError());
            print(js::locate('back'));die;
        }
        if(isset($efforts['typeList']))
        {
            $typeList = $efforts['typeList'];
            unset($efforts['typeList']);
            $typeList['custom']   = '';
            $this->view->typeList = $typeList;
        }

        $this->view->title          = $this->lang->my->common . $this->lang->colon . $this->lang->effort->batchEdit;
        $this->view->position[]     = $this->lang->effort->batchEdit;
        $this->view->products       = $this->loadModel('product')->getPairs();
        $this->view->shadowProducts = $this->loadModel('product')->getPairs('', 0, '', 1);
        $this->view->executions     = $this->loadModel('execution')->getPairs(0, 'all', 'leaf' . (isset($this->config->CRProject) && empty($this->config->CRProject)) ? '|projectclosefilter' : '');
        $this->view->efforts        = $efforts;
        $this->display();
    }
}