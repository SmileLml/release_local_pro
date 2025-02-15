<?php
class myStory extends story
{
    /**
     * Tasks of a story.
     *
     * @param  int    $storyID
     * @param  string    $storyType
     * @access public
     * @return void
     */
    public function relation($storyID, $storyType = '')
    {
        $selectFields = array('id', 'pri', 'title', 'plan', 'openedBy', 'assignedTo', 'estimate', 'status');
        $relation     = $this->story->getStoryRelation($storyID, $storyType, $selectFields);
        if($storyType == 'story') $this->lang->story->title = str_replace($this->lang->SRCommon, $this->lang->URCommon, $this->lang->story->title);

        $this->view->relation  = $relation;
        $this->view->users     = $this->user->getPairs('noletter');
        $this->view->storyType = $storyType;
        $this->display();
    }
}
