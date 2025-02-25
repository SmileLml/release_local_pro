<?php

public function copyTaskComment($copyComment)
{
    $pattern     = '/\sonload="[^"]*"/';
    $replacement = '';
    foreach($copyComment as $comment)
    {
        $action             = new stdclass();
        $action->objectType = strtolower($comment->objectType);
        $action->objectID   = $comment->objectID;
        $action->actor      = $comment->actor;
        $action->action     = $comment->action;
        $action->date       = $comment->date;
        $action->extra      = $comment->extra;
        $action->vision     = $this->config->vision;
        $action->comment    = preg_replace($pattern, $replacement, $comment->comment);
        $relation           = $this->getRelatedFields($comment->objectType, $comment->objectID, $comment->action, $comment->extra);
        $action->product    = $relation['product'];
        $action->project    = (int)$relation['project'];
        $action->execution  = (int)$relation['execution'];
        $this->dao->insert(TABLE_ACTION)->data($action)->autoCheck()->exec();
    }
}