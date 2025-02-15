<?php
helper::importControl('repo');
class myRepo extends repo
{
    /**
     * Show review.
     *
     * @param  int    $repoID
     * @param  string $bugList
     * @access public
     * @return void
     */
    public function ajaxGetBugs($repoID, $bugList)
    {
        $this->loadModel('bug');
        $bugIDList = explode(',', $bugList);

        $modules  = $this->loadModel('tree')->getAllModulePairs('bug');
        $bugs     = $this->repo->getBugsByRepo($repoID, 'all', 0, $bugIDList);
        $comments = $this->repo->getComments($bugIDList);
        $accounts = array();

        foreach($bugs as $bug)
        {
            $bug->files      = array();
            $bug->actions    = array();
            $bug->toCases    = array();
            $bug->moduleName = zget($modules, $bug->module, '');

            $accounts[] = $bug->openedBy;
        }

        $this->view->bugs       = $bugs;
        $this->view->bugIDList  = $bugIDList;
        $this->view->comments   = $comments;
        $this->view->users      = $this->loadModel('user')->getListByAccounts($accounts, 'account');
        $this->view->commentUrl = $this->repo->createLink('addComment');
        $this->display();
    }
}
