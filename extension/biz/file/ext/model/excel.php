<?php
public function excludeHtml($content, $extra = '')
{
    return $this->loadExtension('qeelin')->excludeHtml($content, $extra);
}

public function setAreaStyle($excelSheet, $style, $area)
{
    return $this->loadExtension('qeelin')->setAreaStyle($excelSheet, $style, $area);
}

public function getRowsFromExcel($file)
{
    return $this->loadExtension('qeelin')->getRowsFromExcel($file);
}

public function insertImgToXlsx($imgList, $path)
{
    return $this->loadExtension('qeelin')->insertImgToXlsx($imgList, $path);
}
