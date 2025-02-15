<?php
/**
 * Get logs.
 *
 * @param  object $repo
 * @param  string $entry
 * @param  string $revision
 * @param  string $type
 * @param  object $pager
 * @param  string $begin
 * @param  string $end
 * @access public
 * @return array
 */
public function getCommits($repo, $entry, $revision = 'HEAD', $type = 'dir', $pager = null, $begin = '', $end = '')
{
    if($repo->SCM == 'Gitlab') return $this->loadModel('gitlab')->getCommits($repo, $entry, $revision, $type, $pager, $begin, $end);

    $entry = ltrim($entry, '/');
    $entry = (isset($repo->prefix) ? $repo->prefix : '') . (empty($entry) ? '' : '/' . $entry);

    $repoID       = isset($repo->id) ? $repo->id : '';
    $revisionTime = $this->dao->select('time')->from(TABLE_REPOHISTORY)->alias('t1')
        ->leftJoin(TABLE_REPOBRANCH)->alias('t2')->on('t1.id=t2.revision')
        ->where('t1.repo')->eq($repoID)
        ->beginIF($revision != 'HEAD')->andWhere('t1.revision')->eq($revision)->fi()
        ->beginIF($this->cookie->repoBranch)->andWhere('t2.branch')->eq($this->cookie->repoBranch)->fi()
        ->orderBy('time desc')
        ->limit(1)
        ->fetch('time');

    $historyIdList = array();
    if($entry != '/' and !empty($entry))
    {
        $historyIdList = $this->dao->select('DISTINCT t2.id,t2.`time`')->from(TABLE_REPOFILES)->alias('t1')
            ->leftJoin(TABLE_REPOHISTORY)->alias('t2')->on('t1.revision=t2.id')
            ->leftJoin(TABLE_REPOBRANCH)->alias('t3')->on('t2.id=t3.revision')
            ->where('1=1')
            ->beginIF($begin)->andWhere('t2.`time`')->ge($begin)->fi()
            ->beginIF($end)->andWhere('t2.`time`')->le($end)->fi()
            ->andWhere('t1.repo')->eq($repo->id)
            ->andWhere('t2.`time`')->le($revisionTime)
            ->andWhere('left(t2.`comment`, 12)')->ne('Merge branch')
            ->beginIF($this->cookie->repoBranch)->andWhere('t3.branch')->eq($this->cookie->repoBranch)->fi()
            ->beginIF($type == 'dir')
            ->andWhere('t1.path', true)->like(rtrim($entry, '/') . "%")
            ->orWhere('t1.parent')->eq(rtrim($entry, '/'))
            ->markRight(1)
            ->fi()
            ->beginIF($type == 'file')->andWhere('t1.path')->eq("$entry")->fi()
            ->orderBy('t2.`time` desc')
            ->page($pager, 't2.id')
            ->fetchPairs('id', 'id');
    }

    $comments = $this->dao->select('DISTINCT t1.*')->from(TABLE_REPOHISTORY)->alias('t1')
        ->leftJoin(TABLE_REPOBRANCH)->alias('t2')->on('t1.id=t2.revision')
        ->where('t1.repo')->eq($repoID)
        ->beginIF($revisionTime)->andWhere('t1.`time`')->le($revisionTime)->fi()
        ->beginIF($begin)->andWhere('t1.`time`')->ge($begin)->fi()
        ->beginIF($end)->andWhere('t1.`time`')->le($end)->fi()
        ->andWhere('left(t1.`comment`, 12)')->ne('Merge branch')
        ->beginIF($this->cookie->repoBranch)->andWhere('t2.branch')->eq($this->cookie->repoBranch)->fi()
        ->beginIF($entry != '/' and !empty($entry))->andWhere('t1.id')->in($historyIdList)->fi()
        ->orderBy('time desc');
    if($entry == '/' or empty($entry))$comments->page($pager, 't1.id');
    $comments = $comments->fetchAll('revision');

    $designNames = $this->dao->select("commit, name")->from(TABLE_DESIGN)->where('deleted')->eq(0)->fetchPairs();
    $designIds   = $this->dao->select("commit, id")->from(TABLE_DESIGN)->where('deleted')->eq(0)->fetchPairs();
    foreach($comments as $repoComment)
    {
        $repoComment->designName      = zget($designNames, $repoComment->revision, '');
        $repoComment->designID        = zget($designIds, $repoComment->revision, '');
        $repoComment->originalComment = $repoComment->comment;
        $repoComment->comment         = $this->replaceCommentLink($repoComment->comment);
    }
    return $comments;
}
