<?php
public function getBuildPairs($products, $branch = 'all', $params = 'noterminate,nodone', $objectID = 0, $objectType = 'execution', $buildIdList = '', $replace = true)
{
    return $this->loadExtension('zentaomax')->getBuildPairs($products, $branch, $params, $objectID, $objectType, $buildIdList, $replace);
}
