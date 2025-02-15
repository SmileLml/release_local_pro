<?php
public function getPivotFieldList($pivotID)
{
    return $this->loadExtension('zentaobiz')->getPivotFieldList($pivotID);
}

public function create($dimensionID)
{
    return $this->loadExtension('zentaobiz')->create($dimensionID);
}

public function querySave($pivotID)
{
    return $this->loadExtension('zentaobiz')->querySave($pivotID);
}

public function edit($pivotID)
{
    return $this->loadExtension('zentaobiz')->edit($pivotID);
}

public function update($pivotID)
{
    return $this->loadExtension('zentaobiz')->update($pivotID);
}

public function processNameAndDesc($data)
{
    return $this->loadExtension('zentaobiz')->processNameAndDesc($data);
}

public function getCommonColumn($fieldSettings, $langs)
{
    return $this->loadExtension('zentaobiz')->getCommonColumn($fieldSettings, $langs);
}
