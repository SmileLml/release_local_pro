<?php
helper::importControl('story');
class mystory extends story
{
    public function export($productID, $orderBy, $executionID = 0, $browseType = '', $storyType = 'story')
    {
        if($storyType == 'requirement') $this->story->replaceUserRequirementLang();
        parent::export($productID, $orderBy, $executionID, $browseType, $storyType);
    }
}
