<?php
public function create($projectID = 0, $productID = 0, $parentID = 0)
{
    return $this->loadExtension('zentaoipd')->create($projectID, $productID, $parentID);
}

public function update($planID = 0, $projectID = 0)
{
    return $this->loadExtension('zentaoipd')->update($planID, $projectID);
}
