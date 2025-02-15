<?php
helper::importControl('story');
class mystory extends story
{
    public function showImport($productID, $branch = 0, $type = 'story', $projectID = 0, $pagerID = 1, $maxImport = 0, $insert = '')
    {
        $this->loadModel('transfer');
        $this->loadModel('productplan');
        $this->loadModel('product')->setMenu($productID, $branch);
        $this->session->set('storyTransferParams', array('productID' => $productID, 'branch' => $branch));

        $product = $this->product->getById($productID);

        if($product->type == 'normal') $this->session->set('storyTemplateFields', str_ireplace('branch,', '', $this->session->storyTemplateFields));
        if($this->story->checkForceReview()) $this->session->set('storyTemplateFields', str_ireplace('needReview,', '', $this->session->storyTemplateFields));

        if($type == 'requirement')
        {
            $this->session->set('storyType', 'requirement');
            $this->story->replaceUserRequirementLang();
        }
        else
        {
            $this->session->set('storyType', 'story');
        }

        if($_POST)
        {
            $this->story->createFromImport($productID, $branch, $type, $projectID);
            if(dao::isError()) return print(js::error(dao::getError()));

            if($this->post->isEndPage)
            {
                if($projectID)
                {
                    return print(js::locate($this->createLink('projectstory', 'story', "projectID=$projectID&productID=$productID&branch=0&browseType=&param=0&storyType=$type"), 'parent'));
                }
                else
                {
                    return print(js::locate($this->createLink('product','browse', "productID=$productID&branch=$branch&browseType=unclosed&param=0&storyType=$type"), 'parent'));
                }
            }
            else
            {
                return print(js::locate(inlink('showImport', "productID=$productID&branch=$branch&type=$type&projectID=$projectID&pagerID=" . ($this->post->pagerID + 1) . "&maxImport=$maxImport&insert=" . zget($_POST, 'insert', '')), 'parent'));
            }
        }

        $stories = $this->transfer->readExcel('story', $pagerID, $insert);

        if($projectID)
        {
            $project = $this->dao->findById((int)$projectID)->from(TABLE_PROJECT)->fetch();
            if($project->type == 'project')
            {
                $this->loadModel('project')->setMenu($projectID);
                $this->app->rawModule = 'projectstory';
                $this->lang->navGroup->story = 'project';
                $this->lang->product->menu = $this->lang->{$project->model}->menu;
            }

            if(!$project->hasProduct) unset($stories->fields['branch']);
        }

        if($type == 'requirement') unset($stories->fields['plan']);
        if($product->type == 'normal') unset($stories->fields['branch']);

        $params = "productID=$productID&branch=$branch";
        if($type == 'requirement') $params .= "&browseType=unclosed&param=0&storyType=requirement";

        $this->view->title       = $this->lang->story->common . $this->lang->colon . $this->lang->story->showImport;
        $this->view->datas       = $stories;
        $this->view->productID   = $productID;
        $this->view->branch      = $branch;
        $this->view->type        = $type;
        $this->view->forceReview = $this->story->checkForceReview();
        $this->view->backLink    = $this->createLink('product', 'browse', $params);

        $this->display();
    }
}
