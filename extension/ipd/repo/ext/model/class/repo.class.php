<?php
class repoRepo extends repoModel
{
    /**
     * Get review.
     *
     * @param  int    $repoID
     * @param  string $entry
     * @param  string $revision
     * @access public
     * @return array
     */
    public function getReview($repoID, $entry, $revision)
    {
        $reviews = array();
        $bugs    = $this->dao->select('t1.*, t2.realname')->from(TABLE_BUG)->alias('t1')
            ->leftJoin(TABLE_USER)->alias('t2')
            ->on('t1.openedBy = t2.account')
            ->where('t1.repo')->eq($repoID)
            ->beginIF($entry)->andWhere('t1.entry')->eq($entry)->fi()
            ->andWhere('t1.v2')->eq($revision)
            ->andWhere('t1.mr')->eq(0)
            ->andWhere('t1.deleted')->eq(0)
            ->fetchAll('id');

        $comments = $this->getComments(array_keys($bugs));
        foreach($bugs as $bug)
        {
            if(common::hasPriv('bug', 'edit'))   $bug->edit   = true;
            if(common::hasPriv('bug', 'delete')) $bug->delete = true;
            $lines = explode(',', trim($bug->lines, ','));
            $line  = $lines[0];
            $reviews[$line]['bugs'][$bug->id] = $bug;

            if(isset($comments[$bug->id])) $reviews[$line]['comments'] = $comments;
        }

        return $reviews;
    }

    /**
     * Get review comments.
     *
     * @param  array  $bugIDList
     * @access public
     * @return array
     */
    public function getComments($bugIDList)
    {
        $users    = $this->dao->select('account,realname,nickname,avatar')->from(TABLE_USER)->fetchAll('account');
        $comments = $this->dao->select('*')->from(TABLE_ACTION)
            ->where('objectType')->eq('bug')
            ->andWhere('objectID')->in($bugIDList)
            ->andWhere('action')->eq('commented')
            ->fetchGroup('objectID', 'id');

        foreach($bugIDList as $bugID)
        {
            if(!isset($comments[$bugID])) continue;

            foreach($comments[$bugID] as $comment)
            {
                $comment->user = zget($users, $comment->actor);
                $comment->realname = zget($users, $comment->actor, $comment->actor, $users[$comment->actor]->realname);
                $comment->edit = $comment->actor == $this->app->user->account ? true : false;
            }
        }
        return $comments;
    }

    /**
     * Get bugs by repo.
     *
     * @param  int    $repoID
     * @param  string $browseType
     * @param  int    $executionID
     * @param  array  $bugs
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getBugsByRepo($repoID = 0, $browseType = '', $executionID = 0, $bugs = array(), $orderBy = 'id_desc', $pager = null)
    {
        /* Get execution that user can access. */
        $executions = $this->loadModel('execution')->getPairs(0, 'all', 'empty|withdelete');

        return $this->dao->select('t1.*, t2.name AS executionName, t3.name as productName')->from(TABLE_BUG)->alias('t1')
            ->leftJoin(TABLE_EXECUTION)->alias('t2')->on('t1.execution = t2.id')
            ->leftJoin(TABLE_PRODUCT)->alias('t3')->on('t1.product = t3.id')
            ->where('t1.deleted')->eq('0')
            ->beginIF($repoID)->andWhere('t1.repo')->eq($repoID)->fi()
            ->beginIF($executionID)
            ->andWhere('t1.execution')->eq($executionID)
            ->andWhere('t1.repo')->gt('0')
            ->fi()
            ->beginIF(!$this->app->user->admin)->andWhere('t1.product')->in($this->app->user->view->products)->fi()
            ->beginIF(!$this->app->user->admin)->andWhere('t1.execution')->in(array_keys($executions))->fi()
            ->beginIF($browseType == 'assigntome')->andWhere('t1.assignedTo')->eq($this->app->user->account)->fi()
            ->beginIF($browseType == 'openedbyme')->andWhere('t1.openedBy')->eq($this->app->user->account)->fi()
            ->beginIF($browseType == 'resolvedbyme')->andWhere('t1.resolvedBy')->eq($this->app->user->account)->fi()
            ->beginIF($browseType == 'assigntonull')->andWhere('t1.assignedTo')->eq('')->fi()
            ->beginIF($browseType == 'unresolved')->andWhere('t1.resolvedBy')->eq('')->fi()
            ->beginIF($browseType == 'unclosed')->andWhere('t1.status')->ne('closed')->fi()
            ->beginIF(!empty($bugs))->andWhere('t1.id')->in($bugs)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /**
     * Save bug.
     *
     * @param  int    $repoID
     * @param  string $file
     * @param  int    $v1
     * @param  int    $v2
     * @access public
     * @return array
     */
    public function saveBug($repoID, $file, $v1, $v2)
    {
        $now  = helper::now();
        $data = fixer::input('post')
            ->setIF(!$this->post->entry, 'entry', $file)
            ->add('severity', 3)
            ->add('openedBy', $this->app->user->account)
            ->add('openedDate', $now)
            ->add('openedBuild', 'trunk')
            ->add('assignedDate', $now)
            ->add('type', 'codeimprovement')
            ->add('repo', $repoID)
            ->add('lines', $this->post->begin . ',' . $this->post->end)
            ->add('v1', $v1)
            ->add('v2', $v2)
            ->remove('commentText,begin,end,uid')
            ->get();

        if(strpos($data->v1, '^') !== false) $data->v1 = substr($data->v1, 0, strlen($data->v1) -1);
        if(strpos($data->v2, '^') !== false) $data->v2 = substr($data->v2, 0, strlen($data->v2) -1);
        $data->product   = intval($data->product);
        $data->branch    = intval($data->branch);
        $data->execution = intval($data->execution);
        $data->module    = intval($data->module);
        if($data->execution)
        {
            $execution     = $this->loadModel('execution')->getByID($data->execution);
            $data->project = $execution->project;
        }

        $data->steps = $this->loadModel('file')->pasteImage($this->post->commentText, $this->post->uid);

        $this->lang->bug->title = $this->lang->repo->title;
        $this->dao->insert(TABLE_BUG)->data($data)
        ->batchCheck('title,product', 'notempty')
        ->autoCheck()
        ->exec();

        if(!dao::isError())
        {
            $bugID = $this->dao->lastInsertID();
            $this->file->updateObjectID($this->post->uid, $bugID, 'bug');
            setcookie("repoPairs[$repoID]", $data->product);

            $result = array(
                'result'     => 'success',
                'id'         => $bugID,
                'realname'   => $this->app->user->realname,
                'openedDate' => substr($now, 5, 11),
                'edit'       => true,
                'delete'     => true,
                'lines'      => $data->lines,
                'line'       => $this->post->begin,
                'steps'      => $data->steps,
                'title'      => $data->title,
                'file'       => $data->entry,
            );
            return $result;
        }

        return array('result' => 'fail', 'message' => dao::getError(true));
    }

    /**
     * Update bug.
     *
     * @param  int    $bugID
     * @param  string $title
     * @access public
     * @return string
     */
    public function updateBug($bugID, $title)
    {
        $this->dao->update(TABLE_BUG)->set('title')->eq($title)->where('id')->eq($bugID)->exec();
        return $title;
    }

    /**
     * Update comment.
     *
     * @param  int    $commentID
     * @param  string $comment
     * @access public
     * @return string
     */
    public function updateComment($commentID, $comment)
    {
        $this->dao->update(TABLE_ACTION)->set('comment')->eq($comment)->where('id')->eq($commentID)->exec();
        return $comment;
    }

    /**
     * Delete comment.
     *
     * @param  int    $commentID
     * @access public
     * @return void
     */
    public function deleteComment($commentID)
    {
        return $this->dao->delete()->from(TABLE_ACTION)->where('id')->eq($commentID)->exec();
    }

    /**
     * Get last review info.
     *
     * @param  string $entry
     * @access public
     * @return object
     */
    public function getLastReviewInfo($entry)
    {
        return $this->dao->select('*')->from(TABLE_BUG)->where('entry')->eq($entry)->orderby('id_desc')->fetch();
    }

    /**
     * Get linked object ids by comment.
     *
     * @param  int    $comment
     * @access public
     * @return array
     */
    public function getLinkedObjects($comment)
    {
        $rules   = $this->processRules();
        $stories = array();
        $tasks   = array();
        $bugs    = array();

        $storyReg = '/' . $rules['storyReg'] . '/i';
        $taskReg  = '/' . $rules['taskReg'] . '/i';
        $bugReg   = '/' . $rules['bugReg'] . '/i';

        if(preg_match_all($taskReg, $comment, $matches))
        {
            foreach($matches[3] as $i => $idList)
            {
                $links = $matches[2][$i] . ' ' . $matches[4][$i];
                preg_match_all('/\d+/', $idList, $idMatches);
            }
            $tasks = $idMatches[0];
        }
        if(preg_match_all($bugReg, $comment, $matches))
        {
            foreach($matches[3] as $i => $idList)
            {
                $links = $matches[2][$i] . ' ' . $matches[4][$i];
                preg_match_all('/\d+/', $idList, $idMatches);
            }
            $bugs = $idMatches[0];
        }
        if(preg_match_all($storyReg, $comment, $matches))
        {
            foreach($matches[3] as $i => $idList)
            {
                $links = $matches[2][$i] . ' ' . $matches[4][$i];
                preg_match_all('/\d+/', $idList, $idMatches);
            }
            $stories = $idMatches[0];
        }
        return array('stories' => $stories, 'tasks' => $tasks, 'bugs' => $bugs);
    }

    /**
     * Get diff file tree.
     *
     * @param  object $diffs
     * @access public
     * @return void
     */
    public function getDiffFileTree($diffs = null)
    {
        $files = array();
        foreach($diffs as $diff) $files[] = $diff->fileName;

        return $this->buildFileTree($files);
    }
}
