<?php
class flowWorkflowdatasource extends workflowdatasourceModel
{
    /**
     * Get modules of an app.
     *
     * @param  string $app
     * @access public
     * @return array
     */
    public function getAppModules($app)
    {
        $modules = array('');
        if(isset($this->config->workflowdatasource->methods) && is_array($this->config->workflowdatasource->methods))
        {
            foreach($this->config->workflowdatasource->methods as $module => $methods)
            {
                $this->app->loadLang($module);
                $modules[$module] = isset($this->lang->$module->common) ? $this->lang->$module->common : $module;
            }
        }

        if($this->config->vision == 'lite') unset($modules['branch'], $modules['bug'], $modules['build'], $modules['productplan'], $modules['testcase'], $modules['caselib'], $modules['testtask']);

        return $modules;
    }

    /**
     * Get methods of a module in an app.
     *
     * @param  string $app
     * @param  string $module
     * @access public
     * @return array
     */
    public function getModuleMethods($app, $module)
    {
        $methods = array('');
        if(isset($this->config->workflowdatasource->methods[$module]) && is_array($this->config->workflowdatasource->methods[$module]))
        {
            foreach($this->config->workflowdatasource->methods[$module] as $method)
            {
                $methods[$method] = $method;
            }
        }
        return $methods;
    }

    /**
     * Get field pairs of a view.
     *
     * @param  string $sql
     * @access public
     * @return array
     */
    public function getViewFields($sql)
    {
        if(empty($sql)) return array('');

        $view = 'view_datasource_tmp';
        $sql  = "CREATE OR REPLACE VIEW $view AS $sql";
        $this->dbh->query($sql);

        $sqlFields = $this->dbh->query("DESC $view")->fetchAll();

        $i = 1;
        $fields = array();
        foreach($sqlFields as $field)
        {
            $fields["field{$i}"] = $field->Field;
            $i++;
        }

        $sql = "DROP VIEW $view";
        $this->dbh->query($sql);

        return $fields;
    }

}
