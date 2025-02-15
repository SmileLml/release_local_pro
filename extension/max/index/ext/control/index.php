<?php
helper::importControl('index');
class myindex extends index
{
    /**
     * The index page of whole zentao system.
     *
     * @param  string $open
     * @access public
     * @return void
     */
    public function index($open = '')
    {
        return parent::index($open);
    }
}
