<?php
helper::importControl('tree');
class mytree extends tree
{
    public function browse($rootID, $viewType, $currentModuleID = 0, $branch = 0, $from = '')
    {
        if((!empty($this->app->user->feedback) or $this->cookie->feedbackView) and $viewType != 'doc') die();
        if($this->app->tab == 'feedback') $this->lang->feedback->menu->browse['subModule'] = 'tree';

        return parent::browse($rootID, $viewType, $currentModuleID, $branch, $from);
    }
}
