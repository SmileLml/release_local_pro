<?php
class myBug extends bug
{
    /**
     * AJAX: Get identify.
     *
     * @param  int    $productID
     * @param  int    $projectID
     * @param  int    $identify
     * @access public
     * @return void
     */
    public function ajaxGetIdentify($productID, $projectID, $identify = 0)
    {
        $reviews = $this->loadModel('review')->getPairs($projectID, $productID, true);
        die(html::select('identify', array(0 => '') + $reviews, $identify, "class='form-control chosen'"));
    }
}
