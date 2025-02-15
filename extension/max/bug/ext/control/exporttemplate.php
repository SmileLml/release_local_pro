<?php
helper::importControl('bug');
class mybug extends bug
{
    public function exportTemplate($productID, $branch = 0)
    {
        $product = $this->loadModel('product')->getByID($productID);

        if($product->type == 'normal') $this->config->bug->templateFields = str_replace('branch,', '', $this->config->bug->templateFields);
        if($product->type != 'normal') $this->config->bug->listFields .= ',branch';
        if($_POST)
        {
            $product = $this->loadModel('product')->getByID($productID);
            $this->post->set('product', $product->name);
            $this->session->set('bugTransferParams', array('branch' => $branch, 'productID' => $productID));

            $this->fetch('transfer', 'exportTemplate', 'model=bug');
        }

        $this->loadModel('transfer');
        $this->display();
    }
}
