<?php
public function checkBIContent($dimensionID)
{
    return $this->loadExtension('zentaobiz')->checkBIContent($dimensionID);
}

public function create()
{
    return $this->loadExtension('zentaobiz')->create();
}

public function update($dimensionID)
{
    return $this->loadExtension('zentaobiz')->update($dimensionID);
}
