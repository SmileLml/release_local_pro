<?php
class admin extends control
{
    public function uploadLicense()
    {
        $configRoot = $this->app->getConfigRoot();
        if($_FILES)
        {
            $tmpName  = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            $dest     = $this->app->getTmpRoot() . "/extension/$fileName";

            $pathinfo = pathinfo($fileName);
            if($pathinfo['extension'] != 'zip') return $this->send(array('result' => 'fail', 'message' => $this->lang->admin->notZip));

            move_uploaded_file($tmpName, $dest);

            /* Extract files. */
            $extractedFile = basename($fileName, '.zip');
            $extractedPath = $this->app->getTmpRoot() . "/extension/$extractedFile";
            $this->app->loadClass('pclzip', true);
            $zip = new pclzip($dest);
            $files = $zip->listContent();
            $removePath = $files[0]['filename'];
            if($zip->extract(PCLZIP_OPT_PATH, $extractedPath, PCLZIP_OPT_REMOVE_PATH, $removePath) == 0)
            {
                unlink($dest);
                die($zip->errorInfo(true));
            }

            $classFile = $this->app->loadClass('zfile');
            $classFile->copyDir($extractedPath . '/config/', $configRoot);
            $classFile->removeDir($extractedPath);
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => 'parent'));
        }

        $fixFile = array();
        if(!is_writable($configRoot)) $fixFile[] = $configRoot;
        if(is_dir($configRoot . 'license') and !is_writable($configRoot . 'license')) $fixFile[] = $configRoot . 'license';

        $this->view->fixFile = $fixFile;
        $this->display();
    }
}
