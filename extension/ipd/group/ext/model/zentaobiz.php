<?php
public function create()
{
    return $this->loadExtension('zentaobiz')->create();
}

public function getPairs($projectID = 0)
{
    return $this->loadExtension('zentaobiz')->getPairs($projectID);
}

public function checkMenuModule($menu, $moduleName)
{
    return $this->loadExtension('zentaobiz')->checkMenuModule($menu, $moduleName);
}

public function getPrivManagerPairs($type, $parent = '')
{
    return $this->loadExtension('zentaobiz')->getPrivManagerPairs($type, $parent);
}
