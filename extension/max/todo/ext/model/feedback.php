<?php
public function create($date = '', $account = '') 
{
    return $this->loadExtension('feedback')->create($date, $account);
}
