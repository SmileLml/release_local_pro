<?php
class mytestcase extends testcase
{
    /**
     * Edit a case.
     *
     * @param  int   $caseID
     * @access public
     * @return void
     */
    public function editapi($caseID, $comment = false)
    {
        $this->loadModel('story');

        if(!empty($_POST))
        {
            $changes = array();
            $files   = array();
            if($comment == false)
            {
                $changes = $this->testcase->updateapi($caseID);
                if(dao::isError()) die(js::error(dao::getError()));
            }

        }
        $this->display();
    }
}