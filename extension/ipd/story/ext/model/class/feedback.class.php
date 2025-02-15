<?php
class feedbackStory extends storyModel
{
    /**
     * Create story from feedback.
     *
     * @param  int    $executionID
     * @param  int    $bugID
     * @param  string $from
     * @access public
     * @return array|bool
     */
    public function create($executionID = 0, $bugID = 0, $from = '', $extra = '')
    {
        $type = $this->post->type;
        if($this->post->feedback || $this->post->ticket)
        {
            $fileIDPairs = $this->loadModel('file')->copyObjectFiles($type);
            if(isset($_POST['deleteFiles'])) unset($_POST['deleteFiles']);
        }
        $result = parent::create($executionID, $bugID, $from, $extra);
        if($result === false) return false;

        $storyID          = $result['id'];
        $twinsStoryIdList = zget($result, 'ids', '');

        /* If story is from feedback, record action for feedback and add files to story from feedback. */
        if($this->post->feedback)
        {
            $feedbackID = $this->post->feedback;
            $objectID   = $feedbackID;
            $objectType = 'feedback';

            $feedback = new stdclass();
            $feedback->result   = $storyID;
            $feedback->solution = $type == 'story' ? 'tostory' : 'touserstory';

            if($this->config->vision != 'or')
            {
                $feedback->status        = 'commenting';
                $feedback->processedBy   = $this->app->user->account;
                $feedback->processedDate = helper::now();
            }

            $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($feedbackID)->exec();

            $product = $this->loadModel('product')->getById($this->post->product);
            if($product->shadow)
            {
                $project   = $this->loadModel('project')->getByShadowProduct($this->post->product);
                $projectID = $project->id;
                if(!$this->session->project) $this->session->set('project', $projectID);
                if(!$project->multiple) $projectID = $this->loadModel('execution')->getNoMultipleID($project->id);
                $this->linkStory($projectID, $this->post->product, $storyID);
            }

            $this->loadModel('action')->create('feedback', $feedbackID, $type == 'story' ? 'ToStory' : 'ToUserStory', '', $storyID);

            /* Record the action of twins story. */
            if(!empty($twinsStoryIdList) and $type == 'story')
            {
                foreach($twinsStoryIdList as $twinsStoryID)
                {
                    if($twinsStoryID == $storyID) continue;
                    $this->loadModel('action')->create('feedback', $feedbackID, 'ToStory', '', $twinsStoryID);
                }
            }
        }

        /* If story is from feedback, record action for feedback and add files to story from feedback. */
        if($this->post->ticket)
        {
            $ticketID   = $this->post->ticket;
            $objectID   = $ticketID;
            $objectType = 'ticket';

            $ticket = new stdClass();
            $ticket->ticketId   = $ticketID;
            $ticket->objectId   = $storyID;
            $ticket->objectType = 'story';

            $this->dao->insert(TABLE_TICKETRELATION)->data($ticket)->exec();

            $this->loadModel('action')->create('ticket', $ticketID, 'ToStory', '', $storyID);

            /* Record the action and relation of twins story. */
            if(!empty($twinsStoryIdList))
            {
                foreach($twinsStoryIdList as $twinsStoryID)
                {
                    if($twinsStoryID == $storyID) continue;

                    $ticket->objectId = $twinsStoryID;
                    $this->dao->insert(TABLE_TICKETRELATION)->data($ticket)->exec();

                    $this->loadModel('action')->create('ticket', $ticketID, 'ToStory', '', $twinsStoryID);
                }
            }
        }

        if($this->post->feedback || $this->post->ticket)
        {
            if(isset($objectID) && !empty($fileIDPairs))
            {
                if(!empty($fileIDPairs)) $this->dao->update(TABLE_FILE)->set('objectID')->eq($storyID)->where('id')->in($fileIDPairs)->exec();
                $storyFiles = $this->dao->select('id')->from(TABLE_FILE)->where('objectType')->eq($type)->andWhere('objectID')->eq($storyID)->andWhere('deleted')->eq('0')->fetchPairs();
                $this->dao->update(TABLE_STORYSPEC)->set('files')->eq(join(',', $storyFiles))->where('story')->eq($storyID)->andWhere('version')->eq(1)->exec();
            }
        }

        return $result;
    }

    /**
     * Get story by id.
     *
     * @param  int    $storyID
     * @param  int    $version
     * @param  int    $setImgSize
     * @access public
     * @return object
     */
    public function getById($storyID, $version = 0, $setImgSize = false)
    {
        $story = parent::getById($storyID, $version, $setImgSize);

        if(!empty($story->feedback))
        {
            $feedback = $this->loadModel('feedback')->getById($story->feedback);
            $story->feedbackTitle = $feedback->title;
        }

        return $story;
    }
}
