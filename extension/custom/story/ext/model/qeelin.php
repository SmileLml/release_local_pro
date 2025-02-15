<?php
/**
 * Product module story page add assignment function.
 *
 * @param  object    $story
 * @param  array     $users
 * @access public
 * @return string
 */
public function printAssignedHtml($story, $users, $print = true)
{
    
    $btnTextClass   = '';
    $btnClass       = '';
    $assignedToText = zget($users, $story->assignedTo);

    if(empty($story->assignedTo))
    {
        $btnClass       = $btnTextClass = 'assigned-none';
        $assignedToText = $this->lang->task->noAssigned;
        if(isset($story->assignedToChange) && !$story->assignedToChange)
        {
            if(!$print) return '';
            print('');
        }
    }

    if($story->assignedTo == $this->app->user->account) $btnClass = $btnTextClass = 'assigned-current';
    if(!empty($story->assignedTo) and $story->assignedTo != $this->app->user->account) $btnClass = $btnTextClass = 'assigned-other';

    $btnClass    .= $story->assignedTo == 'closed' ? ' disabled' : '';
    $btnClass    .= ' iframe btn btn-icon-left btn-sm';
    $assignToLink = helper::createLink('story', 'assignTo', "storyID=$story->id&kanbanGroup=default&from=&storyType=$story->type", '', true);
    $assignToHtml = html::a($assignToLink, "<i class='icon icon-hand-right'></i> <span>{$assignedToText}</span>", '', "class='$btnClass' data-toggle='modal'");
    $assignToHtml = !common::hasPriv($story->type, 'assignTo', $story) || (isset($story->assignedToChange) && !$story->assignedToChange) ? "<span style='padding-left: 21px' class='$btnTextClass'>{$assignedToText}</span>" : $assignToHtml;
    
    if(!$print) return $assignToHtml;
    print($assignToHtml);
}