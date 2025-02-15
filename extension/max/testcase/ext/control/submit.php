<?php
class myTestcase extends testcase
{
    public function submit($productID = 0)
    {
        if($_POST)
        {
            $reviewRange = $this->post->range;
            $object      = $this->post->object;
            $product     = $this->loadModel('product')->getByID($productID);
            $programID   = !empty($product->project) ? $product->project : $this->session->project;
            $checkedItem = $reviewRange == 'all' ? '' : $this->cookie->checkedItem;

            die(js::locate($this->createLink('review', 'create', "program={$programID}&object=$object&productID=$productID&reviewRange=$reviewRange&checkedItem={$checkedItem}"), 'parent.parent'));
        }

        $this->display();
    }
}
