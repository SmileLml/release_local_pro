<?php
class release extends control
{
    public function ajaxGetByProduct($productID)
    {
        $releases = array(0 => '') + $this->release->getPairsByProduct($productID);
        die(html::select('release', $releases, '', "class='form-control'"));
    }
}
