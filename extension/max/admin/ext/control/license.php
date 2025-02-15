<?php
class admin extends control
{
    public function license()
    {
        $ioncubeProperties = array();
        if(function_exists('ioncube_license_properties'))
        {
            $properties = ioncube_license_properties();

            if($properties)
            {
                foreach($properties as $key => $property) $ioncubeProperties[$key] = $property['value'];

                /* Update k8s max nodes for devops. */
                if($this->config->inQuickon && $this->config->CNE->api->host && $this->config->CNE->api->token)
                {
                    $params = array(
                        'expired_time' => strtotime($properties['expireDate']) * 1000,
                        'limit_nodes'  => (int)$properties['k8s_max_nodes'],
                        'type'         => $this->config->edition
                    );

                    common::http($this->config->CNE->api->host . '/api/cne/system/license/sync', $params, array(), array("X-Auth-Token: {$this->config->CNE->api->token}"), 'json');
                }
            }
        }

        $this->view->title      = $this->lang->admin->license;
        $this->view->position[] = $this->lang->admin->license;

        $this->view->ioncubeProperties = $ioncubeProperties;
        $this->display();
    }
}
