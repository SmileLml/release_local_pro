<?php

public function getPairsCustom($type = false)
{

    $this->app->loadConfig('user');
    unset($this->config->user->moreLink);

    $users = $this->dao->select("id, account, slack, CONCAT(realname,IF(deleted='1','(deleted)', '')) AS realname")->from(TABLE_USER)
        ->where('type')->eq('inside')->fi()
        ->beginIF($this->config->vision and !in_array($this->app->rawModule, array('kanban', 'feedback')))->andWhere("CONCAT(',', visions, ',')")->like("%,{$this->config->vision},%")->fi()
        ->orderBy('account')
        ->fetchAll();

    $userPairs = array();
    $userPairs[''] = '';
    foreach($users as $user)
    {
        if(!$type) $userPairs[$user->account] = $user->realname;
        else $userPairs[$user->account] = ['realname' => str_replace('(deleted)', '[' . $this->lang->user->dimission . ']', $user->realname), 'slack' => $user->slack];
    }
    return $userPairs;
}

public function getPairsByAccount($accounts = array())
{
    $users = $this->dao->select("id, account, slack, CONCAT(realname,IF(deleted='1','(deleted)', '')) AS realname")->from(TABLE_USER)
        ->where('type')->eq('inside')->fi()
        ->andWhere('account')->in($accounts)
        ->andWhere("CONCAT(',', visions, ',')")->like("%,{$this->config->vision},%")->fi()
        ->orderBy('account')
        ->fetchAll();

    $userPairs = array();
    $userPairs[''] = '';
    foreach($users as $user)
    {
        $userPairs[$user->account] = ['realname' => str_replace('(deleted)', '[' . $this->lang->user->dimission . ']', $user->realname), 'slack' => $user->slack];
    }

    return $userPairs;
}