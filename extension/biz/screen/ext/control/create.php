<?php
class myScreen extends control
{
    /**
     * Create screen.
     *
     * @param  int    $dimensionID
     * @access public
     * @return void
     */
    public function create($dimensionID)
    {
        if($_POST)
        {
            $screenID = $this->screen->create($dimensionID);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));

            $_GET['onlybody'] = 'no';
            return print(js::locate($this->createLink('screen', 'design', "screenID=$screenID"), 'parent.parent'));
        }
        $this->view->title       = $this->lang->screen->create;
        $this->view->dimensionID = $dimensionID;
        $this->display();
    }
}
