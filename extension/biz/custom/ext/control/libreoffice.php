<?php
class custom extends control
{
    public function libreOffice()
    {
        if($_POST)
        {
            $data = fixer::input('post')->get();
            if($data->libreOfficeTurnon)
            {
                if($data->convertType == 'libreoffice')
                {
                    if(!file_exists($data->sofficePath)) die(js::alert($this->lang->custom->errorSofficePath));
                    exec("{$data->sofficePath} --version 2>&1", $out, $result);
                    if($result != 0) die(js::alert(sprintf($this->lang->custom->errorRunSoffice, join($out))));
                    $data->collaboraPath = '';
                }
                else
                {
                    if($this->config->requestType != 'PATH_INFO') die(js::alert($this->lang->custom->cannotUseCollabora));
                    $collaboraDiscovery = $this->loadModel('file')->getCollaboraDiscovery($data->collaboraPath);
                    if(empty($collaboraDiscovery)) die(js::alert($this->lang->custom->errorRunCollabora));
                    $data->sofficePath = '';
                }
            }
            $this->loadModel('setting')->setItems('system.file', $data);

            if(isonlybody()) die(js::reload('parent.parent'));
            die(js::reload('parent'));
        }

        $this->lang->admin->menu->system['subModule'] = 'custom';

        $this->view->title = $this->lang->custom->libreOffice;
        $this->view->position[] = $this->lang->custom->libreOffice;

        $this->app->loadConfig('file');
        $this->display();
    }
}
