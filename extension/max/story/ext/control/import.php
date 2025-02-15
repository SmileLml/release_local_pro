<?php
helper::importControl('story');
class mystory extends story
{
    public function import($productID, $branch = 0, $storyType = 'story', $projectID = 0)
    {
        if($storyType == 'requirement') $this->story->replaceUserRequirementLang();

        $url = inlink('showImport', "productID=$productID&branch=$branch&storyType=$storyType&projectID=$projectID");
        $this->session->set('showImportURL', $url);
        echo $this->fetch('transfer', 'import', "model=story");
    }
}
