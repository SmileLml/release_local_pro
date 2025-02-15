<?php
class feedbackBug extends bugModel
{
    public function create($from = '', $extras = '')
    {
        if($this->post->feedback || $this->post->ticket) $fileIDPairs = $this->loadModel('file')->copyObjectFiles('bug');
        $result = parent::create($from, $extras);
        if($result)
        {
            $bugID      = $result['id'];

            /* If bug is from feedback, record action for feedback and add files to bug from feedback. */
            if($this->post->feedback)
            {
                $feedbackID = $this->post->feedback;
                $objectID   = $feedbackID;
                $objectType = 'feedback';

                $feedback = new stdclass();
                $feedback->status        = 'commenting';
                $feedback->result        = $bugID;
                $feedback->processedBy   = $this->app->user->account;
                $feedback->processedDate = helper::now();
                $feedback->solution      = 'tobug';

                $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($feedbackID)->exec();

                $actionID = $this->loadModel('action')->create('feedback', $feedbackID, 'ToBug', '', $bugID);
            }

            /* If story is from feedback, record action for feedback and add files to story from feedback. */
            if($this->post->ticket)
            {
                $ticketID   = $this->post->ticket;
                $objectID   = $ticketID;
                $objectType = 'ticket';

                $ticket = new stdClass();
                $ticket->ticketId   = $ticketID;
                $ticket->objectId   = $bugID;
                $ticket->objectType = 'bug';

                $this->dao->insert(TABLE_TICKETRELATION)->data($ticket)->exec();

                $actionID = $this->loadModel('action')->create('ticket', $ticketID, 'ToBug', '', $bugID);
            }

            if(isset($objectID) && !empty($fileIDPairs))
            {
                if(!empty($fileIDPairs)) $this->dao->update(TABLE_FILE)->set('objectID')->eq($bugID)->where('id')->in($fileIDPairs)->exec();
            }

            return $result;
        }
        return false;
    }

    public function getById($bugID, $setImgSize = false)
    {
        $bug = parent::getById($bugID, $setImgSize);

        if(!empty($bug->feedback))
        {
            $feedback = $this->loadModel('feedback')->getById($bug->feedback);
            $bug->feedbackTitle = $feedback->title;
        }

        return $bug;
    }

    public function getBugs($productID, $executions, $branch, $browseType, $moduleID, $queryID, $sort, $pager, $projectID)
    {
        $browseType = ($browseType == 'bymodule' and $this->session->bugBrowseType and $this->session->bugBrowseType != 'bysearch') ? $this->session->bugBrowseType : $browseType;

        if(strpos($sort, 'pri_') !== false) $sort = str_replace('pri_', 'priOrder_', $sort);
        if(strpos($sort, 'severity_') !== false) $sort = str_replace('severity_', 'severityOrder_', $sort);

        if($browseType == 'feedback')
        {
            /* Set modules and browse type. */
            $modules = $moduleID ? $this->loadModel('tree')->getAllChildId($moduleID) : '0';

            $bugs = $this->dao->select("*, IF(`pri` = 0, {$this->config->maxPriValue}, `pri`) as priOrder, IF(`severity` = 0, {$this->config->maxPriValue}, `severity`) as severityOrder")->from(TABLE_BUG)
                ->where('execution')->in(array_keys($executions))
                ->andWhere('product')->eq($productID)
                ->beginIF($branch)->andWhere('branch')->in($branch)->fi()
                ->beginIF($modules)->andWhere('module')->in($modules)->fi()
                ->beginIF($projectID)->andWhere('project')->eq($projectID)->fi()
                ->andWhere('feedback')->ne('0')
                ->andWhere('deleted')->eq(0)
                ->orderBy($sort)->page($pager)->fetchAll();

            return $this->checkDelayedBugs($bugs);
        }
        return parent::getBugs($productID, $executions, $branch, $browseType, $moduleID, $queryID, $sort, $pager, $projectID);
    }
}
