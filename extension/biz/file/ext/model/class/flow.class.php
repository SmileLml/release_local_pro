<?php
class flowFile extends fileModel
{
    /**
     * Print files.
     *
     * @param  object $files
     * @param  bool   $print
     * @access public
     * @return string|void
     */
    public function printFiles($files, $print = true)
    {
        if(empty($files)) return false;

        $filesHtml  = '';
        foreach($files as $file)
        {
            $fileName   = rtrim($file->title, '.' . $file->extension) . '.' . $file->extension;
            $filesHtml .= "<li class='file file-{$file->extension}'>" . html::a(helper::createLink('file', 'download', "fileID=$file->id&mouse=left"), $fileName, "target='_blank'") . '</li>';
        }
        if($print)
        {
            echo "<ul class='article-files clearfix'>" . $filesHtml . '</ul>';
        }
        else
        {
            return "<ul class='article-files clearfix'>" . $filesHtml . '</ul>';
        }
    }

    public function parseExcel($fields = array(), $sheetIndex = 0)
    {
        $file = $this->session->importFile;

        $phpExcel  = $this->app->loadClass('phpexcel');
        $phpReader = new PHPExcel_Reader_Excel2007();
        if(!$phpReader->canRead($file)) $phpReader = new PHPExcel_Reader_Excel5();

        $phpExcel     = $phpReader->load($file);
        $currentSheet = $phpExcel->getSheet($sheetIndex);
        $allRows      = $currentSheet->getHighestRow();
        $allColumns   = $currentSheet->getHighestColumn();
        /* In php, 'A'++  === 'B', 'Z'++ === 'AA', 'a'++ === 'b', 'z'++ === 'aa'. */
        $allColumns++;
        $currentColumn = 'A';
        $columnKey = array();
        while($currentColumn != $allColumns)
        {
            $title = $currentSheet->getCell($currentColumn . '1')->getValue();
            $field = array_search($title, $fields);
            $columnKey[$currentColumn] = $field ? $field : '';
            $currentColumn++;
        }

        $dataList   = array();
        $dateFields = isset($this->config->excel->dateFields) ? $this->config->excel->dateFields : array();

        for($currentRow = 2; $currentRow <= $allRows; $currentRow++)
        {
            $currentColumn = 'A';
            $data          = new stdclass();
            while($currentColumn != $allColumns)
            {
                $cell      = $currentSheet->getCell($currentColumn . $currentRow);
                $cellValue = $cell->getValue();
                $cellValue = trim($cellValue);
                if(empty($columnKey[$currentColumn]))
                {
                    $currentColumn++;
                    continue;
                }
                $field = $columnKey[$currentColumn];
                $currentColumn++;

                if($cell->getDataType() == PHPExcel_Cell_DataType::TYPE_NUMERIC)
                {
                    $cellstyleformat = $cell->getStyle($cell->getCoordinate())->getNumberFormat();

                    $formatcode = $cellstyleformat->getFormatCode();

                    if(preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $formatcode))
                    {
                        $cellValue = gmdate("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($cellValue));
                        if(strpos($cellValue, '00:00:00') === 11) $cellValue = substr($cellValue, 0, 10);
                    }
                    else
                    {
                        $cellValue = PHPExcel_Style_NumberFormat::toFormattedString($cellValue, $formatcode);
                    }
                }

                $data->$field = empty($cellValue) ? '' : $cellValue;
            }
            foreach(array_keys($fields) as $key) if(!isset($data->$key)) $data->$key = '';

            $dataList[] = $data;
        }

        foreach($dataList as $key => $data)
        {
            $emptyData = true;
            foreach($data as $field => $value)
            {
                if($value)
                {
                    $emptyData = false;
                    break;
                }
            }
            if($emptyData) unset($dataList[$key]);
        }

        $dataList = $this->processImportData($dataList);
        return $dataList;
    }
}
