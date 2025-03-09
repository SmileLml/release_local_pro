<?php
helper::importControl('product');
class myProduct extends product
{
    public function collect($product)
    {
        $browseLink = $this->session->productList ? $this->session->productList : inlink('all');

        if(!$this->app->user->admin and strpos(",{$this->app->user->view->products},", ",$product,") === false and $product != 0)
        {
            return print(js::error($this->lang->product->accessDenied) . js::locate($browseLink));
        }

        $product = $this->product->getByID($product);

        if(strpos(",{$product->favorites},", ",{$this->app->user->account},") !== false)
        {
            $product->favorites = str_replace(",{$this->app->user->account},", ',', $product->favorites);
        }
        else
        {
            $product->favorites = rtrim($product->favorites, ',') . ",{$this->app->user->account},";
        }
        $this->dao->update(TABLE_PRODUCT)->set('favorites')->eq($product->favorites)->where('id')->eq($product->id)->exec();
        return print(js::locate($browseLink));
    }
}
