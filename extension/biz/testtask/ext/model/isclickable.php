<?php
    /**
     * Adjust the action is clickable.
     *
     * @param  string $bug
     * @param  string $action
     * @access public
     * @return void
     */
     public static function isClickable($testtask, $action)
    {
        $action = strtolower($action);
        if($action == 'start')    return $testtask->status  == 'wait';
        if($action == 'block')    return ($testtask->status == 'doing'   || $testtask->status == 'wait');
        if($action == 'activate') return ($testtask->status == 'blocked' || $testtask->status == 'done');
        if($action == 'close')    return $testtask->status != 'done';
        if($action == 'runcase' and isset($testtask->auto) and $testtask->auto == 'unit')  return false;
        if($action == 'runcase')  return isset($testtask->caseStatus) ? $testtask->caseStatus != 'wait' : $testtask->status != 'wait';
        if($action == 'autorun') return $testtask->color == 'green';
        return true;
    }