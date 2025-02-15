<?php
helper::importControl('bug');
class mybug extends bug
{
    public function export($productID, $orderBy, $browseType = '', $executionID = 0)
    {
        parent::export($productID, $orderBy, $browseType, $executionID);
    }
}
