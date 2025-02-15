<?php
/**
 * The model file of approvalflow module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     approvalflow
 * @version     $Id: model.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class approvalflowModel extends model
{
    /**
     * My info
     *
     * @var array
     * @access private
     */
    private $submitter = array();

    /**
     * Get flow by id.
     *
     * @param  int $flowID
     * @param  int $version
     * @access public
     * @return void
     */
    public function getByID($flowID, $version = 0)
    {
        $flow = $this->dao->select('*')->from(TABLE_APPROVALFLOW)->where('id')->eq($flowID)->fetch();
        if(!$flow) return false;

        $spec = $this->dao->select('*')->from(TABLE_APPROVALFLOWSPEC)
            ->where('flow')->eq($flow->id)
            ->andWhere('version')->eq($version == 0 ? $flow->version : $version)
            ->fetch();
        $flow->version = $spec->version;
        $flow->nodes   = $spec->nodes;

        return $flow;
    }

    /**
     * Get flows.
     *
     * @param  string $type
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getList($type = 'all', $orderBy = 'id_desc', $pager = null)
    {
        $flows = $this->dao->select("*, date_format(createdDate,'%Y-%m-%d %H:%i:%s') as createdDate")->from(TABLE_APPROVALFLOW)
            ->where('deleted')->eq(0)
            ->beginIF($type != 'all')->andWhere('type')->eq($type)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();

        return $flows;
    }

    /**
     * Get flow pairs.
     *
     * @param  string $type
     * @access public
     * @return void
     */
    public function getPairs($type = 'all')
    {
        $flows = $this->getList($type);

        if(empty($flows)) return array();

        $pairs = array();
        foreach($flows as $flow) $pairs[$flow->id] = $flow->name;

        return $pairs;
    }

    /**
     * Get approval flow id by object.
     *
     * @param  int    $rootID
     * @param  string $objectType
     * @access public
     * @return void
     */
    public function getFlowIDByObject($rootID = 0, $objectType = '')
    {
        if(!$objectType) return 0;

        $this->app->loadLang('baseline');
        if($this->config->systemMode == 'PLM') $this->lang->baseline->objectList = array_merge($this->lang->baseline->objectList, $this->lang->baseline->ipd->pointList);
        $baselineObjects = $this->lang->baseline->objectList;

        $flowID = $this->dao->select('flow')->from(TABLE_APPROVALFLOWOBJECT)
            ->where('root')->eq($rootID)
            ->andWhere('objectType')->eq($objectType)
            ->fetch('flow');

        /* Baseline object list default simple flow. */
        if(in_array($objectType, array_keys($baselineObjects)) and !$flowID) $flowID = $this->dao->select('id')->from(TABLE_APPROVALFLOW)->where('code')->eq('simple')->fetch('id');

        return $flowID ? $flowID : 0;
    }

    /**
     * Gen nodes for approval flow.
     *
     * @param object $nodes
     * @param array  $my
     * @param array  $users
     * @access public
     * @return void
     */
    public function genNodes(&$nodes, $users = array())
    {
        if($users)
        {
            $reviewers = $users['reviewers'];
            $upLevel   = $users['upLevel'];
        }

        foreach($nodes as $node)
        {
            if($node->type == 'branch')
            {
                $isContinue = true; // Need execute other branch ?
                foreach($node->branches as $index => $branch)
                {
                    if($this->checkCondition($branch->conditions) and $isContinue)
                    {
                        $this->genNodes($branch->nodes, $users);
                        $node->branches[$index] = $branch;
                        if($node->branchType != 'parallel')
                        {
                            $isContinue = false;
                        }
                    }
                    else
                    {
                        unset($node->branches[$index]);
                    }
                }

                if($isContinue)
                {
                    $this->genNodes($node->default->nodes, $users);
                    $node->branches[] = $node->default;
                }
                unset($node->default);
            }
            else
            {
                $node->id       = in_array($node->type, array('start', 'end')) ? $node->type : $node->id;
                $node->title    = isset($node->title) ? $node->title : $this->lang->approvalflow->nodeTypeList[$node->type];
                $node->multiple = isset($node->multiple) ? $node->multiple : 'and';
                if($node->type == 'approval' and isset($node->reviewType) and $node->reviewType != 'manual')
                {
                    $reviewer = new stdclass();
                    $reviewer->users = array('');
                    $node->reviewers = array($reviewer);
                }
                else if(isset($node->reviewers) and !empty($node->reviewers))
                {
                    foreach($node->reviewers as $index => $reviewer)
                    {
                        if($reviewer->type == 'select')
                        {
                            if(!isset($reviewers[$node->id]) or !isset($reviewers[$node->id]['reviewers']))
                            {
                                dao::$errors[] = $this->lang->approvalflow->errorList['needReivewer'];
                                return false;
                            }

                            $node->reviewers[$index]->users = isset($reviewers) ? $reviewers[$node->id]['reviewers'] : array();
                        }
                        else if($reviewer->type == 'role')
                        {
                            $node->reviewers[$index]->users = array();
                            foreach($reviewer->roles as $role) $node->reviewers[$index]->users = array_merge($node->reviewers[$index]->users, $users['roles'][$role]);
                        }
                        else if($reviewer->type == 'upLevel')
                        {
                            $node->reviewers[$index]->users = $upLevel ? array($upLevel) : array();
                        }

                        /* If reviewers is empty, use agent. */
                        if(empty($node->reviewers[$index]->users) && isset($node->agentType))
                        {
                            switch($node->agentType)
                            {
                            case 'appointee':
                                $node->reviewers[$index]->users = array($node->agentUser);
                                break;
                            case 'admin':
                                $admins = explode(',', trim($this->app->company->admins, ','));
                                $node->reviewers[$index]->users = array($admins[0]);
                                break;
                            default:
                                $node->reviewers[$index]->users = array();
                                break;
                            }
                        }
                    }
                }

                if(isset($node->ccs) and !empty($node->ccs))
                {
                    foreach($node->ccs as $index => $cc)
                    {
                        if($cc->type == 'select')
                        {
                            if(!isset($reviewers[$node->id]) or !isset($reviewers[$node->id]['ccs']))
                            {
                                dao::$errors[] = $this->lang->approvalflow->errorList['needCcer'];
                                return false;
                            }
                            $node->ccs[$index]->users = isset($reviewers) ? $reviewers[$node->id]['ccs'] : array();
                        }
                        else if($cc->type == 'role')
                        {
                            $node->ccs[$index]->users = array();
                            foreach($cc->roles as $role) $node->ccs[$index]->users = array_merge($node->ccs[$index]->users, $users['roles'][$role]);
                        }
                        else if($cc->type == 'upLevel')
                        {
                            $node->ccs[$index]->users = $upLevel ? array($upLevel) : array();
                        }
                    }
                }
            }
        }
    }

    /**
     * Get role list.
     *
     * @access public
     * @return array
     */
    public function getRoleList()
    {
        return $this->dao->select('*')->from(TABLE_APPROVALROLE)->where('deleted')->eq('0')->fetchAll('id');
    }

    /**
     * Get role pairs.
     *
     * @access public
     * @return array
     */
    public function getRolePairs()
    {
        return $this->dao->select('id,name')->from(TABLE_APPROVALROLE)->where('deleted')->eq('0')->fetchPairs();
    }

    /**
     * Create flow.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        $data = new stdclass();
        $data->name        = $this->post->name;
        $data->type        = $this->post->type;
        $data->desc        = $this->post->desc;
        $data->createdBy   = $this->app->user->account;
        $data->createdDate = helper::now();

        $this->dao->insert(TABLE_APPROVALFLOW)->data($data)
            ->autoCheck()
            ->batchCheck($this->config->approvalflow->create->requiredFields, 'notempty')
            ->exec();

        if(!dao::isError())
        {
            $flowID = $this->dao->lastInsertID();

            $spec = new stdclass();
            $spec->flow        = $flowID;
            $spec->version     = 1;
            $spec->nodes       = '[{"type":"start","ccs":[]},{"id":"3ewcj92p55e","type":"approval","reviewType":"manual","reviewers":[{"type":"select"}]},{"type":"end","ccs":[]}]';
            $spec->createdBy   = $this->app->user->account;
            $spec->createdDate = helper::now();

            $this->dao->insert(TABLE_APPROVALFLOWSPEC)->data($spec)->exec();

            return $flowID;
        }

        return false;
    }

    /**
     * Update flow.
     *
     * @param  int    $flowID
     * @access public
     * @return array
     */
    public function update($flowID)
    {
        $oldFlow = $this->getByID($flowID);
        $flow    = fixer::input('post')->get();

        $this->dao->update(TABLE_APPROVALFLOW)->data($flow)
            ->where('id')->eq($flowID)
            ->batchCheck($this->config->approvalflow->edit->requiredFields, 'notempty')
            ->exec();

        return common::createChanges($oldFlow, $flow);
    }

    /**
     * Update nodes
     *
     * @param object $flow
     * @access public
     * @return void
     */
    public function updateNodes($flow)
    {
        $version = $flow->version + 1;

        $data = new stdclass();
        $data->flow        = $flow->id;
        $data->version     = $version;
        $data->nodes       = $this->post->nodes;
        $data->createdBy   = $this->app->user->account;
        $data->createdDate = helper::now();

        $this->dao->insert(TABLE_APPROVALFLOWSPEC)->data($data)->exec();

        $this->dao->update(TABLE_APPROVALFLOW)
            ->set('version')->eq($version)
            ->where('id')->eq($flow->id)
            ->exec();
    }

    /**
     * Create a approval role.
     *
     * @access public
     * @return void
     */
    public function createRole()
    {
        $data = fixer::input('post')
            ->join('users', ',')
            ->get();
        if($data->users) $data->users = ',' . trim($data->users, ',') . ',';

        $this->lang->approvalrole = new stdclass();
        $this->lang->approvalrole->name = $this->lang->approvalflow->name;
        $this->dao->insert(TABLE_APPROVALROLE)->data($data)->batchCheck('name', 'notempty')->exec();

        return $this->dao->lastInsertID();
    }

    /**
     * Edit a approval role.
     *
     * @access public
     * @return void
     */
    public function editRole($roleID)
    {
        $data = fixer::input('post')
            ->join('users', ',')
            ->get();
        if($data->users) $data->users = ',' . trim($data->users, ',') . ',';

        $this->lang->approvalrole = new stdclass();
        $this->lang->approvalrole->name = $this->lang->approvalflow->name;
        $this->dao->update(TABLE_APPROVALROLE)
            ->data($data)
            ->batchCheck('name', 'notempty')
            ->where('id')->eq($roleID)
            ->exec();

        return !dao::isError();
    }

    /**
     * Check condition
     *
     * @param  array $conditions
     * @access public
     * @return bool
     */
    public function checkCondition($conditions)
    {
        if(empty($conditions)) return true;

        if(empty($this->submitter))
        {
            /* Depts. */
            $path = '';
            if($this->app->user->dept) $path = $this->dao->select('path')->from(TABLE_DEPT)->where('id')->eq($this->app->user->dept)->fetch('path');
            $depts = explode(',', trim($path, ','));

            /* Roles. */
            $roles = $this->dao->select('id')->from(TABLE_APPROVALROLE)->where('users')->like('%,' . $this->app->user->account . ',%')->fetchAll('id');
            $roles = array_keys($roles);

            $this->submitter['account'] = $this->app->user->account;
            $this->submitter['depts']   = $depts;
            $this->submitter['roles']   = $roles;
        }

        foreach($conditions as $condition)
        {
            if($condition->type == 'user')
            {
                if($condition->selectType == 'account')
                {
                    if(in_array($this->app->user->account, $condition->users)) return true;
                }
                else if($condition->selectType == 'dept')
                {
                    foreach($this->submitter['depts'] as $dept)
                    {
                        if(in_array($dept, $condition->depts)) return true;
                    }
                }
                else if($condition->selectType == 'role')
                {
                    foreach($this->submitter['roles'] as $role)
                    {
                        if(in_array($role, $condition->roles)) return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Search nodes to confirm.
     *
     * @param array  $nodes
     * @access public
     * @return array
     */
    public function searchNodesToConfirm($nodes)
    {
        $upLevel = '';
        /* If I am a moderators, use it firstly. */
        $parent = $this->dao->select('parent')->from(TABLE_DEPT)->where('manager')->eq($this->app->user->account)->andWhere('parent')->ne(0)->orderBy('grade')->fetch('parent');
        if($parent) $upLevel = $this->dao->select('manager')->from(TABLE_DEPT)->where('id')->eq($parent)->fetch('manager');

        /* If I am not a manager, use the manager of my dept. */
        if(!$upLevel) $upLevel = $this->dao->select('manager')->from(TABLE_DEPT)->where('id')->eq($this->app->user->dept)->fetch('manager');

        /* Get users of all roles. */
        $roles = $this->dao->select('id,users')->from(TABLE_APPROVALROLE)->where('deleted')->eq(0)->fetchPairs();
        foreach($roles as $id => $users) $roles[$id] = explode(',', trim($users, ','));

        $results = array();
        foreach($nodes as $node)
        {
            if($node->type == 'branch')
            {
                $exeDefault = true; // Need execute default branch ?
                foreach($node->branches as $branch)
                {
                    if(!$this->checkCondition($branch->conditions)) continue;

                    $results = array_merge($results, $this->searchNodesToConfirm($branch->nodes));
                    if($node->branchType != 'parallel')
                    {
                        $exeDefault = false;
                        break;
                    }
                }
                if($exeDefault) $results = array_merge($results, $this->searchNodesToConfirm($node->default->nodes));
            }
            else
            {
                $result = array('types' => array());
                if(in_array($node->type, array('start', 'end')))
                {
                    $result['id']    = $node->type;
                    $result['title'] = $this->lang->approvalflow->nodeTypeList[$node->type];
                }
                else
                {
                    $result['id']    = $node->id;
                    $result['title'] = isset($node->title) ? $node->title : $this->lang->approvalflow->nodeTypeList[$node->type];
                }

                if(isset($node->reviewers) and !empty($node->reviewers))
                {
                    foreach($node->reviewers as $reviewer)
                    {
                        if(!isset($reviewer->type)) continue;
                        if($reviewer->type == 'select')    $result['types'][] = 'reviewer';
                        if($reviewer->type == 'appointee') $result['appointees']['reviewer'] = array_values($reviewer->users);
                        if($reviewer->type == 'upLevel')   $result['upLevel']['reviewer'][] = $upLevel ? $upLevel : '';
                        if($reviewer->type == 'role')
                        {
                            if(!isset($result['role']['reviewer'])) $result['role']['reviewer'] = array();
                            foreach($reviewer->roles as $role) $result['role']['reviewer'] = array_merge($result['role']['reviewer'], zget($roles, $role, array()));
                        }
                    }
                }
                if(isset($node->ccs) and !empty($node->ccs))
                {
                    foreach($node->ccs as $cc)
                    {
                        if(!isset($cc->type)) continue;
                        if($cc->type == 'select')    $result['types'][] = 'ccer';
                        if($cc->type == 'appointee') $result['appointees']['ccer'] = array_values($cc->users);
                        if($cc->type == 'upLevel')   $result['upLevel']['ccer'][] = $upLevel ? $upLevel : '';
                        if($cc->type == 'role')
                        {
                            if(!isset($result['role']['ccer'])) $result['role']['ccer'] = array();
                            foreach($cc->roles as $role) $result['role']['ccer'] = array_merge($result['role']['ccer'], zget($roles, $role, array()));
                        }
                    }
                }

                if(count($result['types']) >= 1 or isset($result['appointees']) or isset($result['upLevel']) or isset($result['role'])) $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Adjust the action is clickable.
     *
     * @param  object  $flow
     * @param  string  $action
     *
     * @access public
     * @return bool
     */
    public static function isClickable($issue, $action)
    {
        $action = strtolower($action);

        if($action == 'delete') return !$issue->code;
        if($action == 'edit')   return !$issue->code;

        return true;
    }
}
