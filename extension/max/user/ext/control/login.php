<?php
helper::importControl('user');
class myUser extends user
{
    public function login($referer = '', $from = '', $type = 'ldap')
    {
        $ldapConfig = $this->user->getLDAPConfig();
        if(!empty($ldapConfig->turnon) && $type == 'ldap') $this->config->notMd5Pwd = true;

        return parent::login($referer, $from);
    }
}
