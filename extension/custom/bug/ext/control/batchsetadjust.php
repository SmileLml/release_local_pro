<?php

class mybug extends bug
{
    public function batchSetAdjust($productID = 0, $branchID = 0)
    {
        if(!$this->post->bugIDList) return print(js::locate($this->session->bugList, 'parent'));

        if($this->post->adjustProject)
        {
            $allChanges = $this->bug->batchSetAdjust($productID, $branchID);

            foreach($allChanges as $bugID => $changes)
            {
                if(empty($changes)) continue;

                $actionID = $this->action->create('bug', $bugID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }
            return print(js::locate($this->session->bugList, 'parent'));
        }
        else
        {
            return print(js::alert($this->lang->bug->project . $this->lang->bug->noempty) . js::locate($this->session->bugList));
        }
    }
}