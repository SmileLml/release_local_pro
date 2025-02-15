<?php
helper::import('../../control.php');
class myRepo extends repo
{
    /**
     * Show diff.
     *
     * @param  int    $repoID
     * @param  int    $objectID
     * @param  string $entry
     * @param  string $oldRevision
     * @param  string $newRevision
     * @param  string $showBug
     * @param  string $encoding
     * @param  bool   $isBranchOrTag
     * @access public
     * @return void
     */
    public function diff($repoID = 0, $objectID = 0, $entry = '', $oldRevision = '', $newRevision = '', $showBug = 'false', $encoding = '', $isBranchOrTag = false)
    {
        $this->repo->setBackSession('diff', true);

        $this->view->isBranchOrTag = $isBranchOrTag;
        parent::diff($repoID, $objectID, $entry, $oldRevision, $newRevision, $showBug, $encoding, $isBranchOrTag);
    }
}
