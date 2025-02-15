<?php
public function getPairs($categories = '', $type = 'dept')
{
    return $this->loadExtension('oa')->getPairs($categories, $type);
}

public function getDeptManagedByMe($account)
{
    return $this->loadExtension('oa')->getDeptManagedByMe($account);
}
