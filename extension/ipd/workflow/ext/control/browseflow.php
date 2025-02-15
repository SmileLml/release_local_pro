<?php
class workflow extends control
{
    public function browseFlow($mode = 'browse', $status = '', $app = '', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Build search form. */
        $this->config->workflow->search['actionURL'] = inlink('browseFlow', 'mode=bysearch');
        $this->config->workflow->search['params']['app']['values'] = array('' => '') + $this->workflow->getApps();
        $this->loadModel('search')->setSearchParams($this->config->workflow->search);

        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->session->set('workflowList', $this->app->getURI());

        $flows = $this->workflow->getList($mode, 'flow', $status, '', $app, $orderBy, $pager);
        foreach($flows as $flow)
        {
            $flow->newVersion = $this->workflow->getVersionPairs($flow);
        }

        $this->view->title      = $this->lang->workflow->browseFlow;
        $this->view->apps       = $this->workflow->getApps();
        $this->view->flows      = $flows;
        $this->view->mode       = $mode;
        $this->view->status     = $status;
        $this->view->currentApp = $app;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->display();
    }
}
