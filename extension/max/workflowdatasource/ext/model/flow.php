<?php
public function getAppModules($app)
{
    return $this->loadExtension('flow')->getAppModules($app);
}

public function getModuleMethods($app, $module)
{
    return $this->loadExtension('flow')->getModuleMethods($app, $module);
}

public function getViewFields($sql)
{
    return $this->loadExtension('flow')->getViewFields($sql);
}
