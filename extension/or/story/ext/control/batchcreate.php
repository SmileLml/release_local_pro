<?php
helper::importControl('story');
class myStory extends story
{
    /**
     * Create a batch stories.
     *
     * @param  int    $productID
     * @param  int    $branch
     * @param  int    $moduleID
     * @param  int    $storyID
     * @param  int    $executionID
     * @param  int    $plan
     * @param  string $storyType requirement
     * @param  string $extra for example feedbackID=0
     * @access public
     * @return void
     */
    public function batchCreate($productID = 0, $branch = 0, $moduleID = 0, $storyID = 0, $executionID = 0, $plan = 0, $storyType = 'requirement', $extra = '')
    {
        $this->view->visibleFields = array('pri'=>'');
        parent::batchCreate($productID, $branch, $moduleID, $storyID, $executionID, $plan, $storyType, $extra);
    }
}
