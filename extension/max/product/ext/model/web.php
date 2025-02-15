<?php
public function setMenu($productID, $branch = 0, $module = 0, $moduleType = '', $extra = '')
{
    return $this->loadExtension('web')->setMenu($productID, $branch, $module, $moduleType, $extra);
}
