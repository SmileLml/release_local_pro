<?php
public static function printMyIcon($module, $method, $vars = '', $object = '', $type = 'button', $icon = '', $target = '', $extraClass = '', $onlyBody = false, $misc = '', $title = '', $programID = 0)
{
    echo common::buildMyIconButton($module, $method, $vars, $object, $type, $icon, $target, $extraClass, $onlyBody, $misc, $title, $programID);
}

public static function buildMyIconButton($module, $method, $vars = '', $object = '', $type = 'button', $icon = '', $target = '', $extraClass = '', $onlyBody = false, $misc = '', $title = '', $programID = 0)
{
    if(isonlybody() and strpos($extraClass, 'showinonlybody') === false) return false;

    /* Remove iframe for operation button in modal. Prevent pop up in modal. */
    if(isonlybody() and strpos($extraClass, 'showinonlybody') !== false) $extraClass = str_replace('iframe', '', $extraClass);

    global $app, $lang, $config;

    /* Add data-app attribute. */
    if(strpos($misc, 'data-app') === false) $misc .= ' data-app="' . $app->tab . '"';

    /* Judge the $method of $module clickable or not, default is clickable. */
    $clickable = true;
    if(is_object($object))
    {
        if($app->getModuleName() != $module) $app->control->loadModel($module);
        $modelClass = class_exists("ext{$module}Model") ? "ext{$module}Model" : $module . "Model";
        if(class_exists($modelClass) and is_callable(array($modelClass, 'isClickable')))
        {
            //$clickable = call_user_func_array(array($modelClass, 'isClickable'), array('object' => $object, 'method' => $method));
            // fix bug on php  8.0 link: https://www.php.net/manual/zh/function.call-user-func-array.php#125953
            $clickable = call_user_func_array(array($modelClass, 'isClickable'), array($object, $method));
        }
    }

    /* Set module and method, then create link to it. */
    if(strtolower($module) == 'story'    and strtolower($method) == 'createcase') ($module = 'testcase') and ($method = 'create');
    if(strtolower($module) == 'bug'      and strtolower($method) == 'tostory')    ($module = 'story') and ($method = 'create');
    if(strtolower($module) == 'bug'      and strtolower($method) == 'createcase') ($module = 'testcase') and ($method = 'create');
    if($config->systemMode == 'classic' and strtolower($module) == 'project') $module = 'execution';
    //if(!commonModel::hasPriv($module, $method, $object)) return false;
    $link = helper::createLink($module, $method, $vars, '', $onlyBody, $programID);
    if(strtolower($module) == 'testtask'      and strtolower($method) == 'autorun') $link = "https://10.42.189.249/zentao?".$vars;
    /* Set the icon title, try search the $method defination in $module's lang or $common's lang. */
    if(empty($title))
    {
        $title = $method;
        if($method == 'create' and $icon == 'copy') $method = 'copy';
        if(isset($lang->$method) and is_string($lang->$method)) $title = $lang->$method;
        if((isset($lang->$module->$method) or $app->loadLang($module)) and isset($lang->$module->$method))
        {
            $title = $method == 'report' ? $lang->$module->$method->common : $lang->$module->$method;
        }
        if($icon == 'toStory')   $title  = $lang->bug->toStory;
        if($icon == 'createBug') $title  = $lang->testtask->createBug;
    }

    /* set the class. */
    if(!$icon)
    {
        $icon = isset($lang->icons[$method]) ? $lang->icons[$method] : $method;
    }
    if(strpos(',edit,copy,report,export,delete,', ",$method,") !== false) $module = 'common';
    $class = "icon-$module-$method";

    if(!$clickable) $class .= ' disabled';
    if($icon)       $class .= ' icon-' . $icon;


    /* Create the icon link. */
    if($clickable)
    {
        if($app->getViewType() == 'mhtml')
        {
            return "<a data-remote='$link' class='$extraClass' $misc>$title</a>";
        }
        if($type == 'button')
        {
            if($method != 'edit' and $method != 'copy' and $method != 'delete')
            {
                return html::a($link, "<i class='$class'></i> " . "<span class='text'>{$title}</span>", $target, "class='btn btn-link $extraClass' $misc", true);
            }
            else
            {
                return html::a($link, "<i class='$class'></i>", $target, "class='btn btn-link $extraClass' title='$title' $misc", false);
            }
        }
        else
        {
            return html::a($link, "<i class='$class'></i>", $target, "class='btn $extraClass' title='$title' $misc", false) . "\n";
        }
    }
    else
    {
        if($type == 'list')
        {
            return "<button type='button' class='disabled btn $extraClass'><i class='$class' title='$title' $misc></i></button>\n";
        }
    }
}