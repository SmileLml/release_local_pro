<?php
class myScreen extends control
{
    /**
     * Edit a screen.
     *
     * @param  int    $screenID 
     * @access public
     * @return void
     */
    public function edit($screenID)
    {
        if($_POST)
        {
            $screen = $this->screen->getByID($screenID);
            if($screen->builtin) $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'parent'));

            $this->screen->update($screenID);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'parent'));
        }
        $this->view->title  = $this->lang->screen->editScreen;
        $this->view->screen = $this->screen->getByID($screenID);
        $this->display();
    }
}
