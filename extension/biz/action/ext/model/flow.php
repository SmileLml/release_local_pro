<?php
/**
 * __construct 
 * 
 * @access public
 * @return void
 */
public function __construct($appName = '')
{
    parent::__construct($appName);
if(defined('IN_USE') or (defined('RUN_MODE') and RUN_MODE == 'api')) $this->loadExtension('flow')->loadCustomLang();
}

/**
 * Get deleted objects.
 * 
 * @param  string    $type all|hidden 
 * @param  string    $orderBy 
 * @param  object    $pager 
 * @access public
 * @return array
 */
public function getTrashes($objectType, $type, $orderBy, $pager)
{
    return $this->loadExtension('flow')->getTrashes($objectType, $type, $orderBy, $pager);
}

/**
 * Transform the actions for display.
 * 
 * @param  int    $actions 
 * @access public
 * @return void
 */
public function transformActions($actions)
{
    return $this->loadExtension('flow')->transformActions($actions);
}
