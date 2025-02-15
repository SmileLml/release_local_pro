<?php

class mycustom extends custom
{

    public function limitWorkHour()
    {
        if($_POST)
        {
            $this->loadModel('setting')->setItem("system.common.limitWorkHour", $this->post->limitWorkHour);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $this->view->title      = $this->lang->custom->projectName;
        $this->view->position[] = $this->lang->custom->common;
        $this->view->position[] = $this->view->title;
        $this->view->module     = 'task';

        $this->display();
    }
}