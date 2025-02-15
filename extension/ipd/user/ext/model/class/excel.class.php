<?php
/**
 * The model file of excel module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yue Ma <mayue@easycorp.ltd>
 * @package     excel
 * @link        https://www.zentao.net
 */
class excelUser extends userModel
{
    /**
     * Set list value for export excel.
     *
     * @access public
     * @return void
     */
    public function setListValue()
    {
        $roleList   = $this->lang->user->roleList;
        $genderList = $this->lang->user->genderList;
        $typeList   = $this->lang->user->typeList;

        $depts = $this->loadModel('dept')->getOptionMenu();
        foreach($depts as $id => $dept) $depts[$id] = "$dept(#$id)";

        $this->post->set('deptList',   $depts);
        $this->post->set('roleList',   join(',', $roleList));
        $this->post->set('typeList',   join(',', $typeList));
        $this->post->set('genderList', join(',', $genderList));
        $this->post->set('listStyle', $this->config->user->export->listFields);
        $this->post->set('extraNum', 0);
    }

    /**
     * Create from excel.
     *
     * @access public
     * @return void
     */
    public function createFromImport()
    {
        if(empty($_POST['verifyPassword']) or $this->post->verifyPassword != md5($this->app->user->password . $this->session->rand)) helper::end(js::alert($this->lang->user->error->verifyPassword));

        $users    = fixer::input('post')->get();
        $data     = array();
        $accounts = array();
        for($i = 1; $i <= $this->config->file->maxImport; $i++)
        {
            $users->account[$i] = empty($users->account[$i]) ? '' : trim($users->account[$i]);
            if($users->account[$i] != '')
            {
                if(strtolower($users->account[$i]) == 'guest') helper::end(js::error(sprintf($this->lang->user->error->reserved, $i)));
                $account = $this->dao->select('account')->from(TABLE_USER)->where('account')->eq($users->account[$i])->fetch();
                if($account) helper::end(js::error(sprintf($this->lang->user->error->accountDupl, $i)));
                if(in_array($users->account[$i], $accounts)) helper::end(js::error(sprintf($this->lang->user->error->accountDupl, $i)));
                if(!validater::checkAccount($users->account[$i])) helper::end(js::error(sprintf($this->lang->user->error->account, $i)));
                if($users->realname[$i] == '') helper::end(js::error(sprintf($this->lang->user->error->realname, $i)));
                if(empty($users->visions[$i])) helper::end(js::error(sprintf($this->lang->user->error->visions, $i)));
                if($users->email[$i] and !validater::checkEmail($users->email[$i])) helper::end(js::error(sprintf($this->lang->user->error->mail, $i)));
                $users->password[$i] = (isset($prev['password']) and $users->ditto[$i] == 'on' and !$this->post->password[$i]) ? $prev['password'] : $this->post->password[$i];
                if(!validater::checkReg($users->password[$i], '|(.){6,}|')) helper::end(js::error(sprintf($this->lang->user->error->password, $i)));
                $role    = $users->role[$i] == 'ditto' ? (isset($prev['role']) ? $prev['role'] : '') : $users->role[$i];
                $visions = in_array('ditto', $users->visions[$i]) ? (isset($prev['visions']) ? $prev['visions'] : array()) : $users->visions[$i];
                if(isset($users->type[$i])) $users->type[$i] = $users->type[$i] == 'ditto' ? (isset($prev['type']) ? $prev['type'] : '') : $users->type[$i];
                $userGroup = isset($users->group[$i]) ? $users->group[$i] : array();

                /* Check weak and common weak password. */
                if(isset($this->config->safe->mode) and $this->computePasswordStrength($users->password[$i]) < $this->config->safe->mode) helper::end(js::error(sprintf($this->lang->user->error->weakPassword, $i)));
                if(!empty($this->config->safe->changeWeak))
                {
                    if(!isset($this->config->safe->weak)) $this->app->loadConfig('admin');
                    if(strpos(",{$this->config->safe->weak},", ",{$users->password[$i]},") !== false) helper::end(js::error(sprintf($this->lang->user->error->dangerPassword, $i, $this->config->safe->weak)));
                }

                $data[$i] = new stdclass();
                $data[$i]->dept     = $users->dept[$i] == 'ditto' ? (isset($prev['dept']) ? $prev['dept'] : 0) : $users->dept[$i];
                $data[$i]->account  = $users->account[$i];
                $data[$i]->type     = empty($users->type[$i]) ? 'inside' : $users->type[$i];
                $data[$i]->realname = $users->realname[$i];
                $data[$i]->role     = $role;
                $data[$i]->group    = in_array('ditto', $userGroup) ? (isset($prev['group']) ? $prev['group'] : '') : $userGroup;
                $data[$i]->email    = $users->email[$i];
                $data[$i]->gender   = $users->gender[$i];
                $data[$i]->password = md5(trim($users->password[$i]));
                $data[$i]->join     = empty($users->join[$i]) ? '0000-00-00' : ($users->join[$i]);
                $data[$i]->qq       = $users->qq[$i];
                $data[$i]->weixin   = $users->weixin[$i];
                $data[$i]->mobile   = $users->mobile[$i];
                $data[$i]->phone    = $users->phone[$i];
                $data[$i]->address  = $users->address[$i];
                $data[$i]->visions  = join(',', $visions);

                /* Check required fields. */
                foreach(explode(',', $this->config->user->create->requiredFields) as $field)
                {
                    $field = trim($field);
                    if(empty($field)) continue;

                    if(!isset($data[$i]->$field)) continue;
                    if(!empty($data[$i]->$field)) continue;

                    helper::end(js::error(sprintf($this->lang->error->notempty, $this->lang->user->$field)));
                }

                /* Change for append field, such as feedback. */
                if(!empty($this->config->user->batchAppendFields))
                {
                    $appendFields = explode(',', $this->config->user->batchAppendFields);
                    foreach($appendFields as $appendField)
                    {
                        if(empty($appendField)) continue;
                        if(!isset($users->$appendField)) continue;
                        $fieldList = $users->$appendField;
                        $data[$i]->$appendField = $fieldList[$i];
                    }
                }

                $accounts[$i]     = $data[$i]->account;
                $prev['dept']     = $data[$i]->dept;
                $prev['role']     = $data[$i]->role;
                $prev['group']    = $data[$i]->group;
                $prev['type']     = $data[$i]->type;
                $prev['visions']  = $visions;
                $prev['password'] = $users->password[$i];
            }
        }

        $this->loadModel('mail');
        $userIDList = array();
        foreach($data as $user)
        {
            $userGroups = $user->group;
            unset($user->group);
            $this->dao->insert(TABLE_USER)->data($user)->autoCheck()
                ->checkIF($user->email  != '', 'email',  'email')
                ->checkIF($user->phone  != '', 'phone',  'phone')
                ->checkIF($user->mobile != '', 'mobile', 'mobile')
                ->exec();

            /* Fix bug #2941 */
            $userID       = $this->dao->lastInsertID();
            $userIDList[] = $userID;
            $this->loadModel('action')->create('user', $userID, 'Created');

            if(dao::isError())
            {
                echo js::error(dao::getError());
                helper::end(js::reload('parent'));
            }
            else
            {
                if(is_array($userGroups))
                {
                    foreach($userGroups as $group)
                    {
                        $groups = new stdClass();
                        $groups->account = $user->account;
                        $groups->group   = $group;
                        $this->dao->insert(TABLE_USERGROUP)->data($groups)->exec();
                    }
                }

                $this->computeUserView($user->account);
                if($this->config->mail->mta == 'sendcloud' and !empty($user->email)) $this->mail->syncSendCloud('sync', $user->email, $user->realname);
            }
        }

        return $userIDList;
    }
}
