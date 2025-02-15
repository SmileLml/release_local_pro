<?php
helper::importControl('story');
class myStory extends story
{
    public function confirmDemandRetract($objectID = '', $objectType = '', $extra = '')
    {
        if($_POST)
        {
            $this->loadModel('action')->create($objectType, $objectID, 'confirmedRetract', '', $extra);
            return print(js::closeModal('parent.parent', 'this', "function(){parent.parent.location.reload();}"));
        }

        $this->view->title      = $this->lang->story->confirmDemandRetract;
        $this->view->stories    = $this->story->getByList($extra, 'requirement');
        $this->view->objectType = $objectType;

        $this->display();
    }
}
