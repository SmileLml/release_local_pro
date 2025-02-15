<?php
helper::importControl('feedback');
class myfeedback extends feedback
{
    /**
     * Export template.
     *
     * @access public
     * @return void
     */
    public function exportTemplate()
    {
        if($_POST)
        {
            $this->feedback->setListValue();
            $this->fetch('transfer', 'exportTemplate', 'model=feedback');
        }

        $this->loadModel('transfer');

        $this->display();
    }
}
