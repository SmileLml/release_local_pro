<?php
/**
 * Run.
 *
 * @access public
 * @return void
 */
public function run()
{
    $this->setRepos();
    if(empty($this->repos)) return false;

    $this->loadModel('compile');
    /* Get commit triggerType jobs by repoIdList */
    $commentJobs  = $this->loadModel('job')->getListByTriggerType('commit', array_keys($this->repos));
    $commentGroup = array();
    foreach($commentJobs as $job) $commentGroup[$job->repo][$job->id] = $job;

    /* Get tag triggerType jobs by repoIdList */
    $tagJobs  = $this->job->getListByTriggerType('tag', array_keys($this->repos));
    $tagGroup = array();
    foreach($tagJobs as $job) $tagGroup[$job->repo][$job->id] = $job;

    $_COOKIE['repoBranch'] = '';
    foreach($this->repos as $repoID => $repo)
    {
        $this->printLog("begin repo {$repo->name}");
        if(!$this->setRepo($repo)) return false;

        $this->printLog("get this repo logs.");
        $lastInDB = $this->repo->getLatestCommit($repoID);
        /* Ignore unsynced repo. */
        if(empty($lastInDB))
        {
            $this->printLog("Please init repo {$repo->name}");
            continue;
        }

        $version = $lastInDB->commit;
        $logs    = $this->repo->getUnsyncedCommits($repo);
        $objects = array();
        if(!empty($logs))
        {
            $this->printLog("get " . count($logs) . " logs");
            $this->printLog('begin parsing logs');

            foreach($logs as $log)
            {
                $this->printLog("parsing log {$log->revision}");

                $this->printLog("comment is\n----------\n" . trim($log->msg) . "\n----------");
                $objects = $this->parseComment($log->msg);
                if($objects)
                {
                    $this->printLog('extract' .
                        ' story:' . join(' ', $objects['stories']) .
                        ' task:' . join(' ', $objects['tasks']) .
                        ' design:' . join(' ', $objects['designs']) .
                        ' bug:'  . join(',', $objects['bugs']));
                    $this->saveAction2PMS($objects, $log, $this->repoRoot, $repo->encoding, 'svn');
                }
                else
                {
                    $this->printLog('no objects found' . "\n");
                }

                /* Create compile by comment. */
                $jobs = zget($commentGroup, $repoID, array());
                foreach($jobs as $job)
                {
                    foreach(explode(',', $job->comment) as $comment)
                    {
                        if(strpos($log->msg, $comment) !== false) $this->compile->createByJob($job->id);
                    }
                }

                $version = $this->repo->saveOneCommit($repoID, $log, $version);
                $this->linkCommit($objects['designs'], $repoID, $log);
            }
            $this->repo->updateCommitCount($repoID, $lastInDB->commit + count($logs));
            $this->dao->update(TABLE_REPO)->set('lastSync')->eq(helper::now())->where('id')->eq($repoID)->exec();

            $this->printLog("\n\nrepo #" . $repo->id . ': ' . $repo->path . " finished");
        }

        /* Create compile by tag. */
        $jobs = zget($tagGroup, $repoID, array());
        foreach($jobs as $job)
        {
            $dirs    = $this->getRepoTags($repo, $job->svnDir);
            $isNew   = empty($job->lastTag) ? true : false;
            $lastTag = '';
            foreach($dirs as $dir)
            {
                if(!$isNew and $dir == $job->lastTag)
                {
                    $isNew = true;
                    continue;
                }
                if(!$isNew) continue;

                $lastTag = $dir;
                $tag     = rtrim($repo->path , '/') . '/' . trim($job->svnDir, '/') . '/' . $lastTag;
                $this->compile->createByJob($job->id, $tag, 'tag');
            }
            if($lastTag) $this->dao->update(TABLE_JOB)->set('lastTag')->eq($lastTag)->where('id')->eq($job->id)->exec();
        }
    }
}

/**
 * Parse the comment of svn, extract object id list from it.
 *
 * @param  string    $comment
 * @access public
 * @return array
 */
public function parseComment($comment)
{
    $objects = $this->repo->parseComment($comment);

    $designs = array();
    $commonReg = "(?:\s){0,}(?:#|:|：){0,}([0-9, ]{1,})";
    $designReg = '/design'. $commonReg . '/i';

    if(preg_match_all($designReg, $comment, $result))$designs = join(' ', $result[1]);
    if($designs) $designs = array_unique(explode(' ', str_replace(',', ' ', $designs)));

    $objects['designs'] = $designs;
    return $objects;
}

/**
 * Save action to pms.
 *
 * @param  array    $objects
 * @param  object   $log
 * @param  string   $repoRoot
 * @param  string   $encodings
 * @param  string   $scm
 * @access public
 * @return void
 */
public function saveAction2PMS($objects, $log, $repoRoot = '', $encodings = 'utf-8', $scm = 'svn')
{
    $this->repo->saveAction2PMS($objects, $log, $repoRoot, $encodings, $scm);
    $action = new stdclass();
    $action->actor   = $log->author;
    $action->action  = 'svncommited';
    $action->date    = $log->date;
    $action->comment = htmlspecialchars($this->repo->iconvComment($log->msg, $encodings));
    $action->extra   = $log->revision;

    $changes = $this->repo->createActionChanges($log, $repoRoot);

    if($objects['designs'])
    {
        foreach($objects['designs'] as $designID)
        {
            $designID = (int)$designID;

            $action->objectType = 'design';
            $action->objectID   = $designID;

            $this->repo->saveRecord($action, $changes);
        }
    }
}

/**
 * Code Association of design through annotations.
 *
 * @param  array    $designs
 * @param  int      $repoID
 * @param  object   $log
 * @access public
 * @return void
 */
public function linkCommit($designs, $repoID, $log)
{
    foreach($designs as $designID)
    {
        if(empty($designID)) continue;
        $this->dao->delete()->from(TABLE_RELATION)->where('AType')->eq('design')->andWhere('AID')->eq($designID)->andWhere('BType')->eq('commit')->andWhere('relation')->eq('completedin')->exec();
        $this->dao->delete()->from(TABLE_RELATION)->where('AType')->eq('commit')->andWhere('BID')->eq($designID)->andWhere('BType')->eq('design')->andWhere('relation')->eq('completedfrom')->exec();

        $revisionID = $this->dao->select('id')->from(TABLE_REPOHISTORY)->where('repo')->eq($repoID)->andWhere('revision')->eq($log->revision)->fetch('id');
        $program    = $this->dao->select('id,project,product')->from(TABLE_DESIGN)->where('id')->eq($designID)->fetch();

        $data = new stdclass();
        $data->program  = $program->project;
        $data->product  = $program->product;
        $data->AType    = 'design';
        $data->AID      = $designID;
        $data->BType    = 'commit';
        $data->BID      = $revisionID;
        $data->relation = 'completedin';
        $data->extra    = $repoID;

        $this->dao->replace(TABLE_RELATION)->data($data)->autoCheck()->exec();

        $data->AType    = 'commit';
        $data->AID      = $revisionID;
        $data->BType    = 'design';
        $data->BID      = $designID;
        $data->relation = 'completedfrom';

        $this->dao->replace(TABLE_RELATION)->data($data)->autoCheck()->exec();
    }
}
