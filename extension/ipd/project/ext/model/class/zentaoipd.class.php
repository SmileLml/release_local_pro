<?php
class zentaoipdProject extends projectModel
{
    /**
     * Create a project.
     *
     * @access public
     * @return int|bool
     */
    public function create()
    {
        $isLinkStory = $this->post->isLinkStory;
        $charter     = $this->post->charter;
        if(isset($_POST['isLinkStory']))unset($_POST['isLinkStory']);

        $projectID = parent::create();
        if($projectID && isset($isLinkStory) && $isLinkStory[0] == 'checked')
        {
            $product     = $this->dao->select('product')->from(TABLE_PROJECTPRODUCT)->where('project')->eq($projectID)->fetch('product');
            $project     = $this->loadModel('project')->getByID($projectID);
            $executionID = $projectID;
            if(empty($project->multiple)) $executionID = $this->loadModel('execution')->getNoMultipleID($projectID);
            $stories = $this->dao->select('t1.story')->from(TABLE_ROADMAPSTORY)->alias('t1')
                ->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story= t2.id')
                ->leftJoin(TABLE_CHARTER)->alias('t3')->on('t3.roadmap= t1.roadmap')
                ->where('t3.id')->eq((int)$charter)
                ->andWhere('t2.type')->eq('requirement')
                ->fetchPairs();
            $products = array_fill_keys($stories, $product);
            if(count($stories)) $this->execution->linkStory($executionID, $stories, $products, '', array(), 'requirement');
        }
        return $projectID;
    }
}
