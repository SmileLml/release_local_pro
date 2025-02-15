<?php
helper::importControl('story');
class mystory extends story
{
    public function exportTemplate($productID, $branch = 0, $storyType = 'story')
    {
        if($storyType == 'requirement')
        {
            $this->app->loadLang('file');
            $this->story->replaceUserRequirementLang();
        }

        if($_POST)
        {
            $product = $this->loadModel('product')->getById($productID);
            if($product->shadow)            $this->config->story->templateFields = str_replace(array('product,', 'branch,'), array('', ''), $this->config->story->templateFields);
            if($storyType == 'requirement') $this->config->story->templateFields = str_replace('plan,', '', $this->config->story->templateFields);

            $this->post->set('product', $product->name);
            $this->fetch('transfer', 'exportTemplate', 'model=story&params=productID='. $productID);
        }
        $this->loadModel('transfer');

        $this->display();
    }
}
