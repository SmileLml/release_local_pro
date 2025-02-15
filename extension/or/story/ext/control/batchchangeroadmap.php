<?php
helper::importControl('story');
class myStory extends story
{
    /**
     * Batch change the roadmap of story.
     *
     * @param  int    $roadmapID
     * @param  string $confirm
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function batchChangeRoadmap($roadmapID)
    {
        if(empty($_POST['storyIdList']) and empty($storyIdList)) return print(js::locate($this->session->storyList, 'parent'));
        $storyIdList = array_unique($this->post->storyIdList);
        $storyIdList = array_combine(array_values($storyIdList), array_values($storyIdList));

        $stories     = $this->story->getByList($storyIdList, 'requirement');
        $showConfirm = false;
        $storyStatus = array('active' => 0);
        foreach($stories as $story)
        {
            $status = $story->status;
            if(!isset($storyStatus[$status])) $storyStatus[$status] = 0;
            $storyStatus[$status] ++;
            if($status != 'active')
            {
                $showConfirm = true;
                unset($storyIdList[$story->id]);
            }
        }

        $allChanges  = $this->story->batchChangeRoadmap($storyIdList, $roadmapID);
        if(dao::isError()) return print(js::error(dao::getError()));
        foreach($allChanges as $storyID => $changes)
        {
            $actionID = $this->action->create('story', $storyID, 'Edited');
            $this->action->logHistory($actionID, $changes);
        }
        if(!dao::isError()) $this->loadModel('score')->create('ajax', 'batchOther');

        if($showConfirm)
        {
            $activeCount = $storyStatus['active'];
            unset($storyStatus['active']);
            $statusTips = array();
            foreach($storyStatus as $status => $statusCount) $statusTips[] = sprintf($this->lang->story->statusCount, $statusCount, zget($this->lang->story->statusList, $status));
            echo js::alert(sprintf($this->lang->story->changeRoadmapTip, $activeCount, implode('、', $statusTips)));
            echo js::locate($this->session->storyList, 'parent');
        }

        echo js::locate($this->session->storyList, 'parent');
    }
}
