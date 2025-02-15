<?php
class zentaobizCommon extends commonModel
{
    public function setCompany()
    {
        if(function_exists('ioncube_license_properties') and !isset($_SESSION['bizIoncubeProperties']))
        {
            $properties = ioncube_license_properties();

            if($properties)
            {
                $ioncubeProperties = new stdclass();
                foreach($properties as $key => $property) $ioncubeProperties->$key = $property['value'];

                $user = $this->dao->select("COUNT('*') as count")->from(TABLE_USER)
                    ->where('deleted')->eq(0)
                    ->andWhere('vision')->like("%{$this->config->vision}%")
                    ->fetch();
                if(isset($properties['user']) and $properties['user']['value'] < $user->count) $ioncubeProperties->userLimited = true;
                $this->session->set('bizIoncubeProperties', $ioncubeProperties);
            }
        }
    }
}
