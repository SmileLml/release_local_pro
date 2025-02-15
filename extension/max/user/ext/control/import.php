<?php
helper::importControl('user');
class myuser extends user
{
    public function import()
    {
        if($_FILES)
        {
            $file = $this->loadModel('file')->getUpload('file');
            $file = $file[0];
            $extension = $file['extension'];

            $fileName = $this->file->savePath . $this->file->getSaveName($file['pathname']);
            move_uploaded_file($file['tmpname'], $fileName);

            $phpExcel  = $this->app->loadClass('phpexcel');
            $phpReader = new PHPExcel_Reader_Excel2007();
            if(!$phpReader->canRead($fileName))
            {
                $phpReader = new PHPExcel_Reader_Excel5();
                if(!$phpReader->canRead($fileName))die(js::alert($this->lang->excel->canNotRead));
            }
            $this->session->set('fileImportFileName', $fileName);
            $this->session->set('fileImportExtension', $extension);
            die(js::locate(inlink('showImport'), 'parent.parent'));
        }
        $this->display();
    }
}
