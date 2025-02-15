<?php
class myDoc extends doc
{
    /**
     * Import doc to component lib.
     *
     * @param  int    $docID
     * @access public
     * @return void
     */
    public function importToComponentLib($docID)
    {
        $this->doc->importToLib($docID, 'component');
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

        return $this->send(array('result' => 'success', 'message' => $this->lang->importSuccess, 'locate' => 'reload'));
    }
}
