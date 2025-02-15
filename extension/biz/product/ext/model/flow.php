<?php
public function getProductLink($module, $method, $extra, $branch = false)
{
    return $this->loadExtension('flow')->getProductLink($module, $method, $extra, $branch);
}
