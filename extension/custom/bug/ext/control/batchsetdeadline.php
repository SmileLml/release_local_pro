<?php

class mybug extends bug
{
    public function batchSetDeadline()
    {
        if(!$this->post->bugIDList) return print(js::locate($this->session->bugList));

        $allChanges = $this->bug->batchSetDeadline();
        foreach($allChanges as $bugID => $changes)
        {
            if(empty($changes)) continue;

            $actionID = $this->action->create('bug', $bugID, 'Edited');
            $this->action->logHistory($actionID, $changes);
        }
        return print(js::locate($this->session->bugList));
    }
}