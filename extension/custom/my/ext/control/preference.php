<?php
class my extends control
{
    public function preference(string $showTip = 'true')
    {
        $this->loadModel('setting');

        if($_POST)
        {
            $keyList = array('URSR', 'programLink', 'productLink', 'projectLink', 'executionLink');
            foreach($_POST as $key => $value)
            {
                if(!in_array($key, $keyList)) continue;
                if($key != 'URSR' && !isset($this->lang->my->{$key . 'List'}[$value])) continue;
                if($key == 'URSR') $value = (int)$value;
                $this->setting->setItem("{$this->app->user->account}.common.$key", $value);
            }

            $this->setting->setItem("{$this->app->user->account}.common.preferenceSetted", 1);
            if(isOnlybody()) return print(js::closeModal('parent.parent'));

            return print(js::locate($this->createLink('my', 'index'), 'parent'));
        }

        $this->view->title      = $this->lang->my->common . $this->lang->hyphen . $this->lang->my->preference;
        $this->view->showTip    = $showTip;

        $this->view->URSRList         = $this->loadModel('custom')->getURSRPairs();
        $this->view->URSR             = $this->setting->getURSR();
        $this->view->programLink      = isset($this->config->programLink)   ? $this->config->programLink   : 'program-browse';
        $this->view->productLink      = isset($this->config->productLink)   ? $this->config->productLink   : 'product-all';
        $this->view->projectLink      = isset($this->config->projectLink)   ? $this->config->projectLink   : 'project-browse';
        $this->view->executionLink    = isset($this->config->executionLink) ? $this->config->executionLink : 'execution-task';
        $this->view->preferenceSetted = isset($this->config->preferenceSetted) ? true : false;

        $this->display();
    }
}
