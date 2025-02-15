<div class='panel'>
  <div class='panel-body'>
    <nav class='menu leftmenu'>
      <ul class='nav nav-primary'>
        <?php
        $currentModule = $this->app->getModuleName();
        $currentMethod = $this->app->getMethodName();
        foreach($lang->workfloweditor->moreSettings as $setting)
        {
            list($label, $moduleName, $methodName, $params) = explode('|', $setting);
            if(!commonModel::hasPriv($moduleName, $methodName)) continue;

            $class  = ($currentModule == $moduleName && $currentMethod == strtolower($methodName)) ? "class='active'" : '';
            $params = ($moduleName == 'workflow' && $methodName != 'setapproval') ? sprintf($params, $flow->id) : sprintf($params, $flow->module);
        
            echo "<li $class>" . baseHTML::a($this->createLink($moduleName, $methodName, $params), $label) . '</li>';
        }
        ?>
      </ul>
    </nav>
  </div>
</div>
