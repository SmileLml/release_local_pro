<?php
helper::importControl('story');
class myStory extends story
{
    /**
     * Batch edit story.
     *
     * @param  int    $productID
     * @param  int    $executionID
     * @param  int    $branch
     * @param  string $storyType
     * @param  string $from
     * @access public
     * @return void
     */
    public function batchEdit($productID = 0, $executionID = 0, $branch = 0, $storyType = 'story', $from = '')
    {
        $this->loadModel('roadmap');
        $this->view->roadmaps    = $this->roadmap->getPairs($productID, $branch, 'linkRoadmap');
        $this->view->allRoadmaps = $this->roadmap->getList();
        $this->view->mailto      = $this->loadModel('user')->getPairs('pofirst|nodeleted|noclosed');

        parent::batchEdit($productID, $executionID, $branch, $storyType, $from);
    }
}
