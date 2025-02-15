<?php
helper::importControl('story');
class myStory extends story
{
    public function confirmDemandUnlink($objectID = '', $objectType = '', $extra = '')
    {
        if($_POST)
        {
            $this->loadModel('action')->create($objectType, $objectID, 'confirmedunlink', '', $extra);
            return print(js::closeModal('parent.parent', 'this', "function(){parent.parent.location.reload();}"));
        }

        $this->view->title      = $this->lang->story->confirmDemandUnlink;
        $this->view->stories    = $this->story->getByList($extra, 'requirement');
        $this->view->objectType = $objectType;

        $this->display();
    }
}
