<?php
class myStory extends story
{
    /**
     * Ajax get design.
     *
     * @param  int    $storyID
     * @param  int    $designID
     * @param  int    $executionID
     * @access public
     * @return string
     */
    public function ajaxGetDesign($storyID, $designID = 0, $executionID = 0)
    {
        if($executionID) $execution = $this->loadModel('execution')->getByID($executionID);
        $designs = $this->dao->select('id, name')->from(TABLE_DESIGN)
            ->where('deleted')->eq(0)
            ->beginIF($storyID)->andWhere('story')->eq($storyID)->fi()
            ->beginIF(!empty($execution->project))->andwhere('project')->eq($execution->project)->fi()
            ->fetchPairs();

        return print(html::select('design', array(0 => '') + $designs, $designID, "class='form-control chosen'"));
    }
}
