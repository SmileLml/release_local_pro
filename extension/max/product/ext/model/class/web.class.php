<?php
class webProduct extends productModel
{
    public function setMenu($productID, $branch = 0, $module = 0, $moduleType = '', $extra = '')
    {
        parent::setMenu($productID, $branch, $module, $moduleType, $extra);
        if($this->app->viewType == 'mhtml')
        {
            $this->lang->product->menu->all = "{$this->lang->product->all}|product|all|";
        }
    }
}
