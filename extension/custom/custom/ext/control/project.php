<?php

class mycustom extends custom
{
    /**
     * Set whether the project is read-only.
     *
     * @access public
     * @return void
     */
    public function project()
    {
        if($_POST)
        {
            $this->loadModel('setting')->setItem("system.common.CRProject", $this->post->project);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'reload'));
        }

        $this->view->title      = $this->lang->custom->projectName;
        $this->view->position[] = $this->lang->custom->common;
        $this->view->position[] = $this->view->title;

        $this->display();
    }
}