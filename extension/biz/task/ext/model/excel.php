<?php
public function createFromImport($executionID)
{
    return $this->loadExtension('excel')->createFromImport($executionID);
}

public function processDatas4Task($taskData)
{
    return $this->loadExtension('excel')->processDatas4Task($taskData);
}
