<?php
class repoTest
{

    public function __construct()
    {
        global $tester;
        $this->objectModel = $tester->loadModel('repo');
    }

    /**
     * Test get linked objects.
     *
     * @param  string $comment
     * @param  string $type
     * @access public
     * @return int|array
     */
    public function getLinkedObjectsTest($comment, $type = '')
    {
        $objects = $this->objectModel->getLinkedObjects($comment);

        if($type == 'count')
        {
            return count($objects['stories']) + count($objects['tasks']) + count($objects['bugs']);
        }
        elseif($type)
        {
            $objects = zget($objects, $type);
        }

        return $objects;
    }

    /**
     * Test get branches and tags.
     *
     * @param  int    $repoID
     * @param  string $oldRevision
     * @param  string $newRevision
     * @access public
     * @return string
     */
    public function getBranchesAndTagsTest($repoID, $oldRevision = '0', $newRevision = 'HEAD')
    {
        $result = $this->objectModel->getBranchesAndTags($repoID, $oldRevision, $newRevision);

        if(isset($result->sourceHtml)) return 'success';
        return 'fail';
    }
}
