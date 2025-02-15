<?php
class myProduct extends product
{
    /**
     * Export product.
     *
     * @param  string $status
     * @param  string $orderBy
     * @access public
     * @return void
     */
    public function export($status, $orderBy)
    {
        if($_POST)
        {
            $productLang   = $this->lang->product;
            $productConfig = $this->config->product;

            /* Create field lists. */
            $fields = $this->post->exportFields ? $this->post->exportFields : explode(',', $productConfig->list->exportFields);
            foreach($fields as $key => $fieldName)
            {
                $fieldName = trim($fieldName);
                $fields[$fieldName] = zget($productLang, $fieldName);
                unset($fields[$key]);
            }

            $lastProgram  = $lastLine = '';
            $users        = $this->user->getPairs('noletter');
            $productStats = $this->product->getStats($orderBy, null, $status);
            foreach($productStats as $i => $product)
            {
                if(!empty($product->PMT))
                {
                    $pmt = trim($product->PMT, ',');
                    if(strpos($pmt, ',') !== false)
                    {
                        $pmt  = explode(',', $pmt);
                        $pmts = '';
                        foreach($pmt as $account)
                        {
                            $pmts .= zget($users, $account, $account);
                            $pmts .= ',';
                        }
                        $product->PMT = trim($pmts, ',');
                    }
                    else
                    {
                        $product->PMT  = zget($users, $product->PMT, '');
                    }
                }
                $product->type = zget($productLang, 'typeList')[$product->type];
                $product->PO   = zget($users, $product->PO);

                if($this->post->exportType == 'selected')
                {
                    $checkedItem = $this->cookie->checkedItem;
                    if(strpos(",$checkedItem,", ",{$product->id},") === false) unset($productStats[$i]);
                }
            }
            if($this->config->edition != 'open') list($fields, $productStats) = $this->loadModel('workflowfield')->appendDataFromFlow($fields, $productStats);

            if(isset($rowspan)) $this->post->set('rowspan', $rowspan);
            $this->post->set('fields', $fields);
            $this->post->set('rows', $productStats);
            $this->post->set('kind', $this->lang->productCommon);
            $this->fetch('file', 'export2' . $this->post->fileType, $_POST);
        }
        $this->display();
    }
}
