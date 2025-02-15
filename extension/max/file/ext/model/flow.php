<?php
public function printFiles($files, $print = true)
{
    return $this->loadExtension('flow')->printFiles($files, $print);
}

public function parseExcel($fields = array(), $sheetIndex = 0)
{
    return $this->loadExtension('flow')->parseExcel($fields, $sheetIndex);
}
