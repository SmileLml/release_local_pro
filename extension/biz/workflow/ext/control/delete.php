<?php
class workflow extends control
{
    /**
     * Delete a flow or table.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function delete($id)
    {
        $this->workflow->delete($id);
        if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
        return $this->send(array('result' => 'success', 'message' => $this->lang->deleteSuccess, 'locate' => 'reload'));
    }
}
