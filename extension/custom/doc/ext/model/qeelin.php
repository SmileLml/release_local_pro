<?php
/**
 * Set doc menu by type.
 *
 * @param  string $type
 * @param  int    $objectID
 * @param  int    $libID
 * @param  int    $appendLib
 * @access public
 * @return array
 */
public function setMenuByType($type, $objectID, $libID, $appendLib = 0)
{
    if(empty($type))
    {
        $doclib   = $this->getLibById($libID);
        $type     = $doclib->type == 'execution' ? 'project' : $doclib->type;
        $objectID = $type == 'custom' ? 0 : $doclib->$type;
    }
    if($this->app->tab == 'doc' and $type == 'execution') $type = 'project';

    $objectDropdown = '';
    $appendObject   = $objectID;
    if(in_array($type, array('project', 'product', 'execution')))
    {
        $table  = $this->config->objectTables[$type];
        if($type == 'product') $object = $this->dao->select('id,name,status,deleted')->from($table)->where('id')->eq($objectID)->fetch();
        else $object = $this->dao->select('id,name,status,deleted,project')->from($table)->where('id')->eq($objectID)->fetch();

        if(empty($object))
        {
            $param = ($type == 'project' and $this->config->vision == 'lite') ? 'model=kanban' : '';
            $methodName = ($type == 'project' and $this->config->vision != 'lite') ? 'createGuide' : 'create';
            return print(js::locate(helper::createLink($type, $methodName, $param)));
        }

        $objects  = $this->getOrderedObjects($type, 'merge', $objectID);
        $objectID = $this->loadModel($type)->saveState($objectID, $objects);
        $libs     = $this->getLibsByObject($type, $objectID, '', $appendLib);
        if(($libID == 0 or !isset($libs[$libID])) and !empty($libs)) $libID = reset($libs)->id;

        $objectTitle = zget($objects, $objectID, '');
        if($this->app->tab != 'doc' and isset($libs[$libID]))
        {
            $objectTitle    = zget($libs[$libID], 'name', '');
            $objectDropdown = "<div id='sidebarHeader'><div class='title' title='{$objectTitle}'>{$objectTitle}</div></div>";
        }
        else
        {
            $objectDropdown = $this->select($type, $objectTitle, $appendObject);
        }
    }
    else
    {
        $libs = $this->getLibsByObject($type, 0, '', $appendLib);
        if(($libID == 0 or !isset($libs[$libID])) and !empty($libs)) $libID = reset($libs)->id;
        if(isset($libs[$libID]))
        {
            $objectTitle    = zget($libs[$libID], 'name', '');
            $objectDropdown = "<div id='sidebarHeader'><div class='title' title='{$objectTitle}'>{$objectTitle}</div></div>";
        }

        $object     = new stdclass();
        $object->id = 0;
    }

    $tab  = strpos(',my,doc,product,project,execution,', ",{$this->app->tab},") !== false ? $this->app->tab : 'doc';
    if($type == 'mine')   $type = 'my';
    if($type == 'custom')   $type = 'team';
    if($tab == 'doc' and !common::hasPriv('doc', $type . 'Space')) return print(js::locate(helper::createLink('user', 'deny', "module=doc&method={$type}Space")));
    if($tab != 'doc' and method_exists($type . 'Model', 'setMenu'))
    {
        $this->loadModel($type)->setMenu($objectID);
    }
    elseif($tab == 'doc' and isset($this->lang->doc->menu->{$type}['alias']))
    {
        $this->lang->doc->menu->{$type}['alias'] .= ',' . $this->app->rawMethod;
    }

    return array($libs, $libID, $object, $objectID, $objectDropdown);
}