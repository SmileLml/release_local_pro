<?php
helper::importControl('user');
class myUser extends user
{
    public function importLDAP($type = 'all', $param = 0, $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->loadModel('ldap');

        $type    = strtolower($type);
        $queryID = $type == 'bysearch' ? (int)$param : 0;

        if($this->config->edition != 'open')
        {
            if(function_exists('ioncube_license_properties')) $properties = ioncube_license_properties();
            $userCount = 0;
            $maxUsers  = false;
            if(!empty($properties['user']) and $this->config->vision == 'rnd')
            {
                $userCount = $this->dao->select("COUNT('*') as count")->from(TABLE_USER)->where('deleted')->eq(0)->andWhere('visions')->like('%rnd%')->fetch('count');
                $maxUsers  = $properties['user']['value'] <= $userCount;
            }
            elseif(!empty($properties['lite']) and $this->config->vision == 'lite')
            {
                $userCount = $this->dao->select("COUNT('*') as count")->from(TABLE_USER)->where('deleted')->eq(0)->andWhere('visions')->like('%lite%')->fetch('count');
                $maxUsers  = $properties['lite']['value'] <= $userCount;
            }

            if($maxUsers)
            {
                echo js::alert($this->lang->user->error->userLimit);
                die(js::locate('back'));
            }

            if($_POST)
            {
                foreach($this->post->add as $i => $add)
                {
                    if(!$maxUsers)
                    {
                        $userCount++;
                        if($this->config->vision == 'rnd'  and isset($properties['user']) and $properties['user']['value'] <= $userCount) $maxUsers = true;
                        if($this->config->vision == 'lite' and isset($properties['lite']) and $properties['lite']['value'] <= $userCount) $maxUsers = true;
                    }
                    else
                    {
                        unset($_POST['add'][$i]);
                    }
                }
            }
        }

        if($_POST)
        {
            $error = $this->user->importLDAP($type, $queryID);
            if(!empty($error))
            {
                if(isset($error->repeat)) echo js::alert(sprintf($this->lang->user->error->repeat, join(',', $error->repeat)));
                if(isset($error->ill))    echo js::alert(sprintf($this->lang->user->error->illaccount, join(',', $error->ill)));
                die(js::reload('parent'));
            }
            die(js::locate($this->createLink('company', 'browse'), 'parent'));
        }

        if(!extension_loaded('ldap'))
        {
            $this->app->loadLang('ldap');
            echo js::alert($this->lang->ldap->noldap->header);
            die(js::locate($this->createLink('ldap', 'set')));
        }

        $this->lang->user->menu      = $this->lang->company->menu;
        $this->lang->user->menuOrder = $this->lang->company->menuOrder;

        $users = $this->user->getLDAPUser($type, $queryID);
        if($users == 'off')
        {
            echo js::alert($this->lang->user->notice->ldapoff);
            die(js::locate($this->createLink('ldap', 'index')));
        }

        $ldapError = '';
        if(is_array($users) && isset($users['result']) && $users['result'] == 'fail')
        {
            $ldapError = $users['message'];
            $users = array();
        }

        $recTotal = count($users);
        if($this->cookie->pagerUserImportldap) $recPerPage = $this->cookie->pagerUserImportldap;

        $users = array_chunk($users, $recPerPage);
        $this->app->loadClass('pager', $static = true);

        $actionURL = $this->createLink('user', 'importLDAP', "type=bysearch&param=myQuery");
        $this->ldap->buildSearchForm($queryID, $actionURL);


        $this->view->title        = $this->lang->user->importLDAP;
        $this->view->type         = $type;
        $this->view->pager        = pager::init($recTotal, $recPerPage, $pageID);
        $this->view->depts        = $this->loadModel('dept')->getOptionMenu() + array('ditto' => $this->lang->user->ditto);
        $this->view->groups       = $this->loadModel('group')->getPairs() + array('ditto' => $this->lang->user->ditto);
        $this->view->roles        = $this->lang->user->roleList + array('ditto' => $this->lang->user->ditto);
        $this->view->genders      = array('' => '') + $this->lang->user->genderList + array('ditto' => $this->lang->user->ditto);
        $this->view->users        = empty($users) ? $users : $users[$pageID - 1];
        $this->view->localUsers   = array('' => '') + $this->user->getUserWithoutLDAP();
        $this->view->defaultGroup = empty($this->app->config->ldap->group) ? '' : $this->app->config->ldap->group;
        $this->view->ldapError    = $ldapError;
        $this->display();
    }
}
