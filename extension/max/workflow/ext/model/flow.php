<?php
/**
 * Get Apps.
 *
 * @param  string $exclude
 * @param  bool   $splitProject
 * @access public
 * @return array
 */
public function getApps($exclude = 'admin', $splitProject = true)
{
    return $this->loadExtension('flow')->getApps($exclude, $splitProject);
}

/**
 * Get menus of an app.
 *
 * @param  string $app
 * @param  string $exclude
 * @access public
 * @return array
 */
public function getAppMenus($app, $exclude = '')
{
    return $this->loadExtension('flow')->getAppMenus($app, $exclude);
}

/**
 * Get build in modules.
 * This function is used to check if the code of an user defined module is exist.
 *
 * @param  string $root
 * @access public
 * @return array
 */
public function getBuildinModules($root = '', $rootType = '')
{
    return $this->loadExtension('flow')->getBuildinModules($root, $rootType);
}

/**
 * Get all used apps of flow.
 *
 * @access public
 * @return array
 */
public function getFlowApps()
{
    return $this->loadExtension('flow')->getFlowApps();
}

public function create()
{
    return $this->loadExtension('flow')->create();
}

public function update($id = 0)
{
    if($this->post->navigator == 'primary')
    {
        $flow = $this->getById($id);
        $_POST['app'] = $flow->module;
    }

    return parent::update($id);
}

public function release($id = 0)
{
    if($this->post->navigator == 'primary')
    {
        $flow = $this->getById($id);
        $_POST['app'] = $flow->module;
    }

    return parent::release($id);
}

public function checkFieldAndLayout($module)
{
    $this->app->loadLang('workflowfield');
    $this->app->loadLang('entry');
    return parent::checkFieldAndLayout($module);
}

public function getList($mode = 'browse', $type = 'flow', $status = 'normal', $parent = '', $app = '', $orderBy = 'id_desc', $pager = null)
{
    return $this->loadExtension('flow')->getList($mode, $type, $status, $parent, $app, $orderBy, $pager);
}

public function sortModuleMenu($app, $module, $position, $positionModule, $buildInModules = array())
{
    return $this->loadExtension('flow')->sortModuleMenu($app, $module, $position, $positionModule, $buildInModules);
}

public function disableApproval($module)
{
    return $this->loadExtension('flow')->disableApproval($module);
}

public function delete($id, $null = null)
{
    return $this->loadExtension('flow')->delete($id, $null);
}

public function saveField($flow, $field, $oldFields)
{
    return $this->loadExtension('flow')->saveField($flow, $field, $oldFields);
}
