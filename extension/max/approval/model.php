<?php
/**
 * The model file of approval module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     approval
 * @version     $Id: model.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class approvalModel extends model
{
    /**
     * Construct.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadModel('approvalflow');
    }

    /**
     * Get approval by id.
     *
     * @param  int    $id
     * @access public
     * @return object
     */
    public function getByID($id)
    {
        return $this->dao->select('*')->from(TABLE_APPROVAL)->where('id')->eq($id)->fetch();
    }

    /**
     * Get pending review list.
     *
     * @param  string $objectType
     * @access public
     * @return array
     */
    public function getPendingReviews($objectType)
    {
        $pendingList = $this->dao->select('t2.objectID')->from(TABLE_APPROVALNODE)->alias('t1')
            ->leftJoin(TABLE_APPROVALOBJECT)->alias('t2')
            ->on('t2.approval = t1.approval')
            ->where('t2.objectType')->eq($objectType)
            ->andWhere('t1.account')->eq($this->app->user->account)
            ->andWhere('t1.status')->eq('doing')
            ->andWhere('t1.type')->eq('review')
            ->fetchPairs('objectID');

        unset($pendingList['']);

        return $pendingList;
    }

    /**
     * Get nodes to confirm.
     *
     * @param int $flowID
     * @param int $version
     * @access public
     * @return void
     */
    public function getNodesToConfirm($flowID, $version = 0)
    {
        $flow  = $this->approvalflow->getByID($flowID, $version);

        $nodes = !empty($flow->nodes) ? json_decode($flow->nodes) : array();

        $nodesToConfirm = $this->approvalflow->searchNodesToConfirm($nodes);
        return $nodesToConfirm;
    }

    /**
     * Get front node list.
     *
     * @param  string $objectType
     * @param  int    $object
     * @access public
     * @return void
     */
    public function getFrontNodeList($objectType, $object)
    {
        $approvalObject = $this->dao->select('*')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($object->id)
            ->orderBy('id_desc')
            ->fetch();

        $nodes = $this->dao->select('*')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalObject->approval)
            ->orderBy('id_asc')
            ->fetchGroup('node', 'id');

        /* Group by code|type. */
        foreach($nodes as $code => $nodeGroup)
        {
            foreach($nodeGroup as $id => $node)
            {
                unset($nodes[$code][$node->id]);
                $nodes[$code][$node->type][] = $node;

                if($node->type != 'review') continue;

                $nodes[$code][$node->type]['users'][$node->account]['result'] = $node->result;
                $nodes[$code][$node->type]['users'][$node->account]['status'] = $node->status;
            }
        }

        /* Compute status and result. */
        foreach($nodes as $code => $nodeGroup)
        {
            foreach($nodeGroup as $type => $node)
            {
                if(isset($node['users']))
                {
                    foreach($node['users'] as $account => $resultAndStatus)
                    {
                        if($resultAndStatus['status'] == 'doing')
                        {
                            $nodes[$code][$type]['status'] = 'doing';
                            $nodes[$code][$type]['result'] = '';
                            break;
                        }
                        else
                        {
                            if($resultAndStatus['status'] == 'wait')
                            {
                                $nodes[$code][$type]['status'] = 'wait';
                                $nodes[$code][$type]['result'] = '';
                                break;
                            }
                            elseif($resultAndStatus['result'] == 'fail')
                            {
                                $nodes[$code][$type]['status'] = 'done';
                                $nodes[$code][$type]['result'] = 'fail';
                                break;
                            }
                            else
                            {
                                $nodes[$code][$type]['status'] = 'done';
                                $nodes[$code][$type]['result'] = 'pass';
                            }
                        }
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * Get approval object by id.
     *
     * @param  int    $approvalID
     * @access public
     * @return void
     */
    public function getApprovalObjectByID($approvalID)
    {
        return $this->dao->select('*')->from(TABLE_APPROVALOBJECT)->where('approval')->eq($approvalID)->fetch();
    }

    /**
     * Get approval id by object id.
     *
     * @param  int    $objectID
     * @param  string $objectType
     * @access public
     * @return array
     */
    public function getApprovalIDByObjectID($objectID, $objectType = 'review')
    {
        return $this->dao->select('approval')->from(TABLE_APPROVALOBJECT)->alias('t1')
            ->leftJoin(TABLE_APPROVAL)->alias('t2')->on('t1.approval=t2.id')
            ->where('t1.objectType')->eq($objectType)
            ->andWhere('t1.objectID')->eq($objectID)
            ->andWhere('t2.status')->eq('done')
            ->orderBy('t1.id asc')
            ->fetchPairs();
    }

    /**
     * Get last reviewDate.
     *
     * @param  int    $approvalID
     * @access public
     * @return string
     */
    public function getLastReviewDate($approvalID)
    {
        return $this->dao->select('reviewedDate')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('reviewedDate')->notZeroDatetime()
            ->orderBy('reviewedDate_desc')
            ->limit(1)
            ->fetch('reviewedDate');
    }

    /**
     * Get node by id.
     *
     * @param  int    $nodeID
     * @access public
     * @return void
     */
    public function getNodeByID($nodeID)
    {
        return $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('id')->eq($nodeID)->fetch();
    }

    /**
     * Get approval by object.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @access public
     * @return void
     */
    public function getByObject($objectType, $objectID)
    {
        return $this->dao->select('*')->from(TABLE_APPROVAL)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($objectID)
            ->orderBy('id_desc')
            ->fetch();
    }

    /**
     * Get list by object.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @access public
     * @return array
     */
    public function getListByObject($objectType, $objectID)
    {
        return $this->dao->select('*')->from(TABLE_APPROVAL)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($objectID)
            ->orderBy('id_desc')
            ->fetchAll('id');
    }

    /**
     * Get node reviewers.
     *
     * @param  int    $approvalID
     * @access public
     * @return void
     */
    public function getNodeReviewers($approvalID)
    {
        $nodeGroups = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->orderBy('id')->fetchGroup('node', 'id');
        $nodeIdList = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->orderBy('id')->fetchPairs('id', 'id');
        $nodeMap    = array();
        $this->loadModel('file');

        /* Get node files by nodeID .*/
        $nodeFileGroups = array();
        $nodeFiles      = $this->file->getByObject('approvalNode', $nodeIdList);
        foreach($nodeFiles as $nodeFile) $nodeFileGroups[$nodeFile->objectID][$nodeFile->id] = $nodeFile;

        foreach($nodeGroups as $nodeID => $nodes)
        {
            $nodeMap[$nodeID] = array('reviewers' => array(), 'ccs' => array(), 'doing' => array(), 'status' => 'wait', 'result' => '');

            $nodeStatus = 'wait';
            $nodeResult = '';
            $isIgnore   = true;
            foreach($nodes as $node)
            {
                if($node->opinion) $node = $this->file->replaceImgURL($node, 'opinion');
                if($node->result == 'ignore' and $node->status == 'doing') $node->status = 'done';
                if($node->type == 'review') $nodeMap[$nodeID]['reviewers'][$node->id] = array('account' => $node->account, 'status' => $node->status, 'result' => $node->result, 'reviewedDate' => helper::isZeroDate($node->reviewedDate) ? '' : $node->reviewedDate, 'opinion' => $node->opinion, 'files' => isset($nodeFileGroups[$node->id]) ? $nodeFileGroups[$node->id] : array());
                if($node->type == 'cc')     $nodeMap[$nodeID]['ccs'][]       = $node->account;

                if($node->status == 'doing')
                {
                    $isIgnore   = false;
                    $nodeStatus = 'doing';
                    $nodeResult = '';
                    $nodeMap[$node->node]['doing'][] = $node->account;
                }
                elseif($node->status == 'done' and $nodeStatus != 'doing')
                {
                    if($node->result != 'ignore') $isIgnore = false;

                    $nodeStatus = 'done';
                    if(!$isIgnore and $nodeResult != 'fail')     $nodeResult = 'success';
                    if(!$isIgnore and $node->result == 'fail')   $nodeResult = 'fail';
                    if($isIgnore  and $node->result == 'ignore') $nodeResult = 'ignore';
                }
            }

            $nodeMap[$nodeID]['status'] = $nodeStatus;
            $nodeMap[$nodeID]['result'] = $nodeResult;
        }

        /* Sort reviewers. Set status of done before doing. Set mine to head in the doing list. */
        foreach($nodeMap as $nodeID => $maps)
        {
            if(empty($maps['doing'])) continue;

            $reviewers = $maps['reviewers'];

            $ordered   = array();
            $mine      = array();
            $doings    = array();
            foreach($reviewers as $reviewer)
            {
                if($reviewer['status'] != 'doing')
                {
                    $ordered[] = $reviewer;
                    continue;
                }

                if(empty($mine) and $reviewer['account'] == $this->app->user->account)
                {
                    $mine = $reviewer;
                    continue;
                }

                $doings[] = $reviewer;
            }

            if($mine) $ordered[] = $mine;
            if($doings)
            {
                foreach($doings as $reviewer) $ordered[] = $reviewer;
            }

            $nodeMap[$nodeID]['reviewers'] = $ordered;
        }

        return $nodeMap;
    }

    /**
     * Get current reviewers.
     *
     * @param  int    $approvalID
     * @access public
     * @return array
     */
    public function getCurrentReviewers($approvalID)
    {
        return $this->dao->select('account')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->andWhere('status')->eq('doing')->fetchPairs();
    }

    /**
     * Create approval.
     *
     * @param int    $flowID
     * @param array  $reviewers
     * @param int    $version
     * @param string $objectType
     * @param int    $objectID
     * @access private
     * @return void
     */
    private function create($flowID, $reviewers, $version = 0, $objectType = '', $objectID = 0)
    {
        $flow  = $this->approvalflow->getByID($flowID, $version);
        $nodes = json_decode($flow->nodes);

        $upLevel = '';
        /* If I am a manager, use it firstly. */
        $parent = $this->dao->select('parent')->from(TABLE_DEPT)->where('manager')->eq($this->app->user->account)->andWhere('parent')->ne(0)->orderBy('grade')->fetch('parent');
        if($parent) $upLevel = $this->dao->select('manager')->from(TABLE_DEPT)->where('id')->eq($parent)->fetch('manager');

        /* If I am not a manager, use the manager of my dept. */
        if(!$upLevel) $upLevel = $this->dao->select('manager')->from(TABLE_DEPT)->where('id')->eq($this->app->user->dept)->fetch('manager');

        /* Get users of all roles. */
        $roles = $this->dao->select('id,users')->from(TABLE_APPROVALROLE)->where('deleted')->eq(0)->fetchPairs();
        foreach($roles as $id => $users)
        {
            $roles[$id] = explode(',', trim($users, ','));
        }

        /* Generate nodes. */
        $this->approvalflow->genNodes($nodes, array('reviewers' => $reviewers, 'upLevel' => $upLevel, 'roles' => $roles));

        if(dao::isError()) return false;

        /* Insert nodes. */
        $approval = new stdclass();
        $approval->flow        = $flowID;
        $approval->objectType  = $objectType;
        $approval->objectID    = $objectID;
        $approval->version     = $flow->version;
        $approval->createdBy   = $this->app->user->account;
        $approval->createdDate = helper::now();
        $approval->nodes       = json_encode($nodes);

        $this->dao->insert(TABLE_APPROVAL)->data($approval)->exec();
        $approvalID = $this->dao->lastInsertID();

        $this->insertNodes($approvalID, $nodes, array());

        return $approvalID;
    }

    /**
     * Create approval object for a object.
     *
     * @param  int    $root
     * @param  int    $objectID
     * @param  string $objectType
     * @param  array  $reviewers
     * @param  array  $ccers
     * @param  array  $nodeIdList
     * @param  string $type
     * @access public
     * @return string If finished, return pass|fail.
     */
    public function createApprovalObject($root = 0, $objectID = 0, $objectType = 0, $reviewers = array(), $ccers = array(), $nodeIdList = array(), $type = '')
    {
        /* Create approval. */
        if($objectType == 'review')
        {
            $approvalflowObject = $this->dao->select('*')->from(TABLE_APPROVALFLOWOBJECT)->where('root')->eq($root)->andWhere('objectType')->eq($type)->fetch();
            $flowID = $approvalflowObject ? $approvalflowObject->flow : $this->dao->select('*')->from(TABLE_APPROVALFLOW)->where('code')->eq('simple')->fetch()->id;
        }
        else
        {
            $approvalflowObject = $this->dao->select('*')->from(TABLE_APPROVALFLOWOBJECT)->where('root')->eq($root)->andWhere('objectType')->eq($objectType)->fetch();
            if(!$approvalflowObject) return false;
            $flowID = $approvalflowObject->flow;
        }

        $approvalUsers = array();
        foreach($nodeIdList as $id)
        {
            $approvalUsers[$id] = array('reviewers' => array(), 'ccs' => array());

            if(isset($reviewers[$id]))
            {
                foreach($reviewers[$id] as $reviewer)
                {
                    if($reviewer) $approvalUsers[$id]['reviewers'][] = $reviewer;
                }
            }

            if(isset($ccers[$id]))
            {
                foreach($ccers[$id] as $ccer)
                {
                    if($ccer) $approvalUsers[$id]['ccs'][] = $ccer;
                }
            }
        }

        /* If submit a new approval, set old approval status as done. */
        $oldApproval = $this->getByObject($objectType, $objectID);
        if($oldApproval)
        {
            $this->dao->update(TABLE_APPROVAL)->set('status')->eq('done')->where('id')->eq($oldApproval->id)->exec();
            $this->dao->update(TABLE_APPROVALNODE)->set('status')->eq('done')->where('approval')->eq($oldApproval->id)->exec();
        }

        $approvalID = $this->create($flowID, $approvalUsers, 0, $objectType, $objectID);

        $data = new stdclass();
        $data->approval   = $approvalID;
        $data->objectType = $objectType;
        $data->objectID   = $objectID;
        $this->dao->insert(TABLE_APPROVALOBJECT)->data($data)->exec();

        /* Run approval. */
        $this->next($approvalID, '', 'submit');

        /* If the flow is finished, change status of approval. */
        $doing = $this->dao->select('*')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->eq('doing')
            ->fetchAll();

        if(empty($doing))
        {
            $reject = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->andWhere('result')->eq('fail')->fetchAll();

            $result = empty($reject) ? 'pass' : 'fail';
            $this->finish($approvalID, $result, 'submit');

            return array('result' => $result, 'approvalID' => $approvalID);
        }

        return array('result' => '', 'approvalID' => $approvalID);
    }

    /**
     * Insert node.
     *
     * @param int    $approvalID
     * @param object $node
     * @param array  $prevs
     * @access private
     * @return void
     */
    private function insertNode($approvalID, $node, $prevs)
    {
        $newPrev = '';
        if(isset($node->reviewers) and !empty($node->reviewers))
        {
            foreach($node->reviewers as $reviewer)
            {
                foreach($reviewer->users as $user)
                {
                    $data = new stdclass();
                    $data->approval     = $approvalID;
                    $data->node         = $node->id;
                    $data->title        = $node->title;
                    $data->type         = 'review';
                    $data->prev         = implode(',', $prevs);
                    $data->account      = $user;
                    $data->multipleType = $node->multiple;
                    $data->reviewType   = isset($node->reviewType) ? $node->reviewType : 'manual';

                    $this->dao->insert(TABLE_APPROVALNODE)->data($data)->exec();
                    $newPrev = $node->id;
                }
            }
        }

        if(isset($node->ccs) and !empty($node->ccs))
        {
            foreach($node->ccs as $cc)
            {
                foreach($cc->users as $user)
                {
                    $data = new stdclass();
                    $data->approval     = $approvalID;
                    $data->node         = $node->id;
                    $data->title        = $node->title;
                    $data->type         = 'cc';
                    $data->prev         = implode(',', $prevs);
                    $data->account      = $user;
                    $data->multipleType = $node->multiple;
                    $data->reviewType   = isset($node->reviewType) ? $node->reviewType : 'manual';

                    $this->dao->insert(TABLE_APPROVALNODE)->data($data)->exec();
                    $newPrev = $node->id;
                }
            }
        }

        return $newPrev;
    }

    /**
     * Insert nodes.
     *
     * @param  int   $approvalID
     * @param  array $nodes
     * @param  array $prevs
     * @access public
     * @return array
     */
    public function insertNodes($approvalID, $nodes, $prevs)
    {
        foreach($nodes as $node)
        {
            if($node->type == 'branch')
            {
                $nextPrevs = array();
                foreach($node->branches as $branch)
                {
                    $newPrevs = $this->insertNodes($approvalID, $branch->nodes, $prevs);
                    foreach($newPrevs as $newPrev)
                    {
                        if($newPrev) $nextPrevs[] = $newPrev;
                    }
                }
                if(!empty($nextPrevs)) $prevs = $nextPrevs;
            }
            else
            {
                $newPrev = $this->insertNode($approvalID, $node, $prevs);
                if($newPrev) $prevs = array($newPrev);
            }
        }

        return $prevs;
    }

    /**
     * Next viewer node.
     *
     * @param int    $approvalID
     * @param string $prev
     * @access private
     * @return void
     */
    private function next($approvalID, $prev, $action = 'submit')
    {
        /* Get next nodes. */
        if(!$prev)
        {
            $nodes = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->andWhere('prev')->eq('')->fetchAll();
        }
        else
        {
            $nodes = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->andWhere('prev')->like("%$prev%")->fetchAll();
        }
        if(empty($nodes)) return;

        $nodeMap = array();
        foreach($nodes as $index => $node)
        {
            if($prev)
            {
                $undone = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->andWhere('node')->in($node->prev)->andWhere('status')->ne('done')->fetchAll();
                if(!empty($undone)) continue;   // Only all prevs are done, the node can flow.
            }

            if(!isset($nodeMap[$node->node])) $nodeMap[$node->node] = array('review' => array(), 'cc' => array());
            $nodeMap[$node->node][$node->type][] = $node->account;
            $nodeMap[$node->node]['reviewType']  = $node->reviewType;
        }

        foreach($nodeMap as $nodeID => $users)
        {
            if(empty($users['review']))
            {
                $this->cc($approvalID, $nodeID, $users['cc'], $action);
                $this->next($approvalID, $nodeID, $action);
            }
            else if($users['reviewType'] == 'pass') // Auto pass
            {
                $this->dao->update(TABLE_APPROVALNODE)
                    ->set('status')->eq('done')
                    ->set('result')->eq('pass')
                    ->where('approval')->eq($approvalID)
                    ->andWhere('node')->eq($nodeID)
                    ->exec();
                $this->cc($approvalID, $nodeID, $users['cc'], $action);
                $this->next($approvalID, $nodeID, 'review');
            }
            else if($users['reviewType'] == 'reject') // Auto reject
            {
                $this->dao->update(TABLE_APPROVALNODE)
                    ->set('status')->eq('done')
                    ->set('result')->eq('fail')
                    ->where('approval')->eq($approvalID)
                    ->andWhere('node')->eq($nodeID)
                    ->andWhere('type')->eq('review')
                    ->exec();
                $this->dao->update(TABLE_APPROVALNODE)
                    ->set('status')->eq('done')
                    ->set('result')->eq('ignore')
                    ->where('approval')->eq($approvalID)
                    ->andWhere('status')->eq('wait')
                    ->exec();

                $approval = $this->getByID($approvalID);
                $this->sendMessage(array($approval->createdBy), $approvalID, $nodeID, 'review');
            }
            else
            {
                $this->review($approvalID, $nodeID, $users['review'], $action);
                if(!empty($users['cc'])) $this->sendMessage($users['cc'], $approvalID, $nodeID, $action, true);
            }
        }
    }

    /**
     * review
     *
     * @param int    $approvalID
     * @param string $nodeID
     * @param array  $reviewers
     * @access private
     * @return void
     */
    private function review($approvalID, $nodeID, $reviewers, $action)
    {
        $this->dao->update(TABLE_APPROVALNODE)->set('status')->eq('doing')->where('approval')->eq($approvalID)->andWhere('node')->eq($nodeID)->exec();
        $this->sendMessage($reviewers, $approvalID, $nodeID, $action);
    }

    /**
     * cc
     *
     * @param int    $approvalID
     * @param string $nodeID
     * @param array  $ccs
     * @access private
     * @return void
     */
    private function cc($approvalID, $nodeID, $ccs, $action)
    {
        $this->dao->update(TABLE_APPROVALNODE)->set('status')->eq('done')->where('approval')->eq($approvalID)->andWhere('node')->eq($nodeID)->exec();
        $this->sendMessage($ccs, $approvalID, $nodeID, $action, true);
    }

    /**
     * Send message.
     *
     * @param  array  $sendList
     * @param  int    $approvalID
     * @param  int    $nodeID
     * @param  string $method
     * @param  string $isCC
     * @access private
     * @return void
     */
    private function sendMessage($sendList, $approvalID, $nodeID, $method, $isCC = false)
    {
        $approval = $this->dao->select('t1.objectType, t1.result, t1.objectID, t2.type')->from(TABLE_APPROVAL)->alias('t1')->leftJoin(TABLE_APPROVALFLOW)->alias('t2')->on('t1.flow = t2.id')->where('t1.id')->eq($approvalID)->fetch();
        $node     = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->andWhere('node')->eq($nodeID)->fetch();

        $objectType = $approval->objectType;
        $objectID   = $approval->objectID;

        if(empty($objectID) or empty($objectType)) return;

        $this->loadModel('mail');
        $this->loadModel('message');

        /* Message config. */
        $messageSetting = $this->config->message->setting;
        if(is_string($messageSetting)) $messageSetting = json_decode($messageSetting, true);

        if($approval->type == 'workflow')
        {
            $flow   = $this->dao->select('name, `table`')->from(TABLE_WORKFLOW)->where('module')->eq($objectType)->fetch();
            $object = $this->dao->select('*')->from($flow->table)->where('id')->eq($objectID)->fetch();
        }
        else
        {
            $suffix = '';
            $model  = zget($this->config->approval->objectModels, $objectType);
            $object = $this->loadModel($model)->getByID($objectID);
            if($objectType == 'review') $suffix = empty($object->project) ? '' : ' - ' . $this->loadModel('project')->getById($object->project)->name;
        }

        if(isset($messageSetting['message']))
        {
            $actions = $messageSetting['message']['setting'];
            if($this->app->moduleName == 'flow' and isset($actions[$objectType]) and in_array($method, $actions[$objectType]))
            {
                $this->saveNotice($sendList, $objectType, $objectID, $object, $method == 'cancel' ? 'cancel' : $node->node, $isCC);
            }
        }

        if(isset($messageSetting['webhook']))
        {
            $actions = $messageSetting['webhook']['setting'];
            if($this->app->moduleName == 'flow' and isset($actions[$objectType]) and in_array($method, $actions[$objectType]))
            {
                $this->sendWebHook($sendList, $objectType, $objectID, $object, $method);
            }
        }

        if(isset($messageSetting['sms']))
        {
            $actions = $messageSetting['sms']['setting'];
            if($this->app->moduleName == 'flow' and isset($actions[$objectType]) and in_array($method, $actions[$objectType]))
            {
                $this->sendSMS($sendList, $objectType, $objectID, $object);
            }
        }

        if(isset($messageSetting['xuanxuan']))
        {
            $actions = $messageSetting['xuanxuan']['setting'];
            if($this->app->moduleName == 'flow' and isset($actions[$objectType]) and in_array($method, $actions[$objectType]))
            {
                $this->sendXuanxuan($sendList, $objectType, $objectID, $object, $method);
            }
        }

        if(isset($messageSetting['mail']))
        {
            $actions = $messageSetting['mail']['setting'];
            if($this->app->moduleName == 'flow' and !isset($actions[$objectType])) return false;
            if($this->app->moduleName != 'flow' and !isset($actions['waterfail'])) return false;

            if($this->app->moduleName == 'flow' && !in_array($method, $actions[$objectType])) return false;
            if($this->app->moduleName != 'flow' && !in_array($method, $actions['waterfail'])) return false;
        }

        /* Load module and get vars. */
        $this->loadModel('action');
        $users      = $this->loadModel('user')->getPairs('noletter');
        $actions    = $this->action->getList($objectType, $objectID);
        $nameFields = $this->config->action->objectNameFields[$objectType];
        $title      = zget($object, $nameFields, '');
        $subject    = strtoupper($objectType) . ' #' . $object->id . ($approval->type == 'workflow' ? ' - ' . $flow->name : ' ' . $title . $suffix);
        $domain     = zget($this->config->mail, 'domain', common::getSysURL());

        foreach($actions as $action)
        {
            $action->appendLink = '';
            if(strpos($action->extra, ':') !== false)
            {
                list($extra, $id) = explode(':', $action->extra);
                $action->extra    = $extra;
                if($title) $action->appendLink = html::a($domain . helper::createLink($action->objectType, 'view', "id=$id", 'html'), "#$id " . $title);
            }
        }

        if(is_array($sendList)) $sendList = implode(',', $sendList);

        /* Get mail content. */
        $modulePath = $this->app->getModulePath($appName = '', 'approval');
        $oldcwd     = getcwd();
        $viewFile   = $modulePath . 'view/sendmail.html.php';
        chdir($modulePath . 'view');
        if(file_exists($modulePath . 'ext/view/sendmail.html.php'))
        {
            $viewFile = $modulePath . 'ext/view/sendmail.html.php';
            chdir($modulePath . 'ext/view');
        }
        ob_start();
        include $viewFile;
        foreach(glob($modulePath . 'ext/view/sendmail.*.html.hook.php') as $hookFile) include $hookFile;
        $mailContent = ob_get_contents();
        ob_end_clean();
        chdir($oldcwd);

        $this->mail->send($sendList, $subject, $mailContent);

        if($this->mail->isError()) error_log(join("\n", $this->mail->getError()));
    }

    /**
     * Send notice.
     *
     * @param  array  $sendList
     * @param  string $objectType
     * @param  int    $objectID
     * @param  object $object
     * @param  string $node
     * @param  bool   $isCC
     * @access public
     * @return void
     */
    public function saveNotice($sendList, $objectType, $objectID, $object, $node, $isCC)
    {
        $this->loadModel('action');
        $actor      = $this->app->user->account;
        $user       = $this->loadModel('user')->getById($actor);
        $nameFields = $this->config->action->objectNameFields[$objectType];
        $title      = zget($object, $nameFields, '');
        $url        = helper::createLink($objectType, 'view', "id=$objectID");

        if($node == 'start')
        {
            $data = $user->realname . $this->lang->approval->start . ':' . html::a($url, "[#{$objectID}::{$title}]");
        }
        else if($node == 'end')
        {
            $data = $this->lang->approval->end . ':' . html::a($url, "[#{$objectID}::{$title}]");
        }
        else if($node == 'cancel')
        {
            $data = $user->realname . $this->lang->approval->cancel . ':' . html::a($url, "[#{$objectID}::{$title}]");
        }
        else
        {
            $data = $this->lang->action->objectTypes[$objectType] . $this->lang->approval->common . ':' . html::a($url, "[#{$objectID}::{$title}]");
        }

        if($isCC) $data = "[{$this->lang->approval->cc}]" . $data;

        $toList = ',' . implode(',', $sendList) . ',';
        $notify = new stdclass();
        $notify->objectType  = 'message';
        $notify->action      = '0';
        $notify->toList      = str_replace(",{$actor},", '', ",$toList,");;
        $notify->data        = $data;
        $notify->status      = 'wait';
        $notify->createdBy   = $this->app->user->account;
        $notify->createdDate = helper::now();

        $this->dao->insert(TABLE_NOTIFY)->data($notify)->exec();
    }

    /**
     * Send sms.
     *
     * @param  array  $sendList
     * @param  string $objectType
     * @param  int    $objectID
     * @param  object $object
     * @access public
     * @return void
     */
    public function sendSMS($sendList, $objectType, $objectID, $object)
    {
        $accounts  = $this->dao->select('mobile')->from(TABLE_USER)->where('account')->in($sendList)->andWhere('deleted')->eq(0)->fetchAll();
        $mobiles   = array();
        $delimiter = isset($this->app->config->sms->delimiter) ? $this->app->config->sms->delimiter : ',';
        foreach($accounts as $account)
        {
            if($account->mobile) $mobiles[$account->mobile] = $account->mobile;
        }

        $nameFields = $this->config->action->objectNameFields[$objectType];
        $mobiles    = join($delimiter, $mobiles);
        $content    = zget($object, $nameFields, '');
        $this->loadModel('sms')->sendContent($mobiles, $content);
    }

    /**
     * Send webhook.
     *
     * @param  array  $sendList
     * @param  string $objectType
     * @param  int    $objectID
     * @param  object $object
     * @param  string $method
     * @access public
     * @return void
     */
    public function sendWebHook($sendList, $objectType, $objectID, $object, $method = '')
    {
        static $webhooks = array();
        $this->loadModel('webhook');
        if(!$webhooks) $webhooks = $this->webhook->getList();
        if(!$webhooks) return true;

        static $users = array();
        if(empty($users)) $users = $this->loadModel('user')->getList();

        $nameFields = $this->config->action->objectNameFields[$objectType];
        $title      = zget($object, $nameFields, '');
        $host       = empty($webhook->domain) ? common::getSysURL() : $webhook->domain;
        $viewLink   = helper::createLink($objectType, 'view', "id=$objectID", 'html');
        $text       = "[#{$objectID}::{$title}](" . $host . $viewLink . ")";

        $method = 'approval' . strtolower($method);
        if($method and isset($this->lang->action->label->$method))
        {
            $objectTypeName = $objectType == 'requirement' ? $this->lang->action->objectTypes['requirement'] : $this->lang->action->objectTypes[$objectType];
            $text           = $this->app->user->realname . $this->lang->action->label->$method . $objectTypeName . ' ' . $text;
        }

        foreach($users as $user)
        {
            if(in_array($user->account, $sendList))
            {
                $mobile = $user->mobile;
                $email  = $user->email;
                foreach($webhooks as $id => $webhook)
                {
                    if($webhook->type == 'dinggroup' or $webhook->type == 'dinguser')
                    {
                        $data = $this->webhook->getDingdingData($title, $text, $webhook->type == 'dinguser' ? '' : $mobile);
                    }
                    elseif($webhook->type == 'bearychat')
                    {
                        $data = $this->webhook->getBearychatData($text, $mobile, $email, $objectType, $objectID);
                    }
                    elseif($webhook->type == 'wechatgroup' or $webhook->type == 'wechatuser')
                    {
                        $data = $this->webhook->getWeixinData($title, $text, $mobile);
                    }
                    elseif($webhook->type == 'feishuuser' or $webhook->type == 'feishugroup')
                    {
                        $data = $this->webhook->getFeishuData($title, $text);
                    }
                    else
                    {
                        $data = new stdclass();
                        $data->text = $text;
                    }

                    $postData = json_encode($data);
                    if(!$postData) continue;

                    if($webhook->sendType == 'async')
                    {
                        $this->webhook->saveData($id, '0', $postData);
                        continue;
                    }

                    $result = $this->webhook->fetchHook($webhook, $postData, 0, $user->account);
                    if(!empty($result)) $this->webhook->saveLog($webhook, '0', $postData, $result);
                }
            }
        }
    }

    /**
     * Send xuanxuan.
     *
     * @param  array  $sendList
     * @param  string $objectType
     * @param  int    $objectID
     * @param  object $object
     * @access public
     * @return void
     */
    public function sendXuanxuan($sendList, $objectType, $objectID, $object, $actionType)
    {
        $nameFields = $this->config->action->objectNameFields[$objectType];
        $title      = zget($object, $nameFields, '');

        $target = $this->dao->select('id')->from(TABLE_USER)
            ->where('account')->in($sendList)
            ->andWhere('account')->ne($this->app->user->account)->fi()
            ->fetchAll('id');

        $target = array_keys($target);
        $server = $this->loadModel('im')->getServer('zentao');
        $url    = $server . helper::createLink($objectType, 'view', "id=$objectID", 'html');

        $subcontent = new stdclass();
        $subcontent->action     = $actionType;
        $subcontent->object     = $objectID;
        $subcontent->objectName = $title;
        $subcontent->objectType = $objectType;
        $subcontent->actor      = $this->app->user->id;
        $subcontent->actorName  = $this->app->user->realname;
        $subcontent->name       = $title;
        $subcontent->id         = sprintf('%03d', $object->id);
        $subcontent->count      = 1;
        $subcontent->parentType = $objectType;

        $contentData = new stdclass();
        $contentData->title       = $title;
        $contentData->subtitle    = '';
        $contentData->contentType = "zentao-$objectType-$actionType";
        $contentData->parentType  = $subcontent->parentType;
        $contentData->content     = json_encode($subcontent);
        $contentData->actions     = array();
        $contentData->url         = "xxc:openInApp/zentao-integrated/" . urlencode($url);
        $contentData->extra       = '';

        $content   = json_encode($contentData);
        $avatarUrl = $server . $this->app->getWebRoot() . 'favicon.ico';
        $this->im->messageCreateNotify($target, $title, $subtitle = '', $content, $contentType = 'object', $url, $actions = array(), $sender = array('id' => 'zentao', 'realname' => $this->lang->message->sender, 'name' => $this->lang->message->sender, 'avatar' => $avatarUrl));
    }

    /**
     * Pass approval.
     *
     * @param  string $objectType
     * @param  object $object
     * @param  string $extra
     * @access public
     * @return bool
     */
    public function pass($objectType, $object, $extra = '')
    {
        $approvalID = $this->dao->select('approval')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($object->id)
            ->orderBy('id_desc')
            ->fetch('approval');

        $data = new stdclass();
        $data->status       = 'done';
        $data->result       = 'pass';
        $data->date         = $object->createdDate;
        $data->opinion      = $object->opinion;
        $data->extra        = $extra;
        $data->reviewedBy   = $this->app->user->account;
        $data->reviewedDate = helper::now();

        $nodes = $this->dao->select('*')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->eq('doing')
            ->andWhere('account')->eq($this->app->user->account)
            ->fetchAll();

        foreach($nodes as $node)
        {
            $this->dao->update(TABLE_APPROVALNODE)->data($data)->where('id')->eq($node->id)->exec();
            if($node->multipleType == 'or')
            {
                $this->dao->update(TABLE_APPROVALNODE)->set('status')->eq('done')->set('result')->eq('ignore')
                    ->where('approval')->eq($approvalID)
                    ->andWhere('node')->eq($node->node)
                    ->andWhere('status')->eq('doing')
                    ->exec();
            }
            else
            {
                $undone = $this->dao->select('*')->from(TABLE_APPROVALNODE)
                    ->where('approval')->eq($approvalID)
                    ->andWhere('node')->eq($node->node)
                    ->andWhere('type')->eq('review')
                    ->andWhere('status')->ne('done')
                    ->fetchAll();
                if(!empty($undone)) continue;
            }

            $ccList = $this->dao->select('account')->from(TABLE_APPROVALNODE)
                ->where('approval')->eq($approvalID)
                ->andWhere('node')->eq($node->node)
                ->andWhere('type')->eq('cc')
                ->fetchAll('account');

            /* All reviewer is passed, run cc. */
            if($ccList) $this->cc($approvalID, $node->node, array_keys($ccList), 'review');

            $this->next($approvalID, $node->node, 'review');
        }

        /* If the flow is finished, change status of approval. */
        $doing = $this->dao->select('*')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->eq('doing')
            ->fetchAll();

        if(empty($doing))
        {
            $reject = $this->dao->select('*')->from(TABLE_APPROVALNODE)
                ->where('approval')->eq($approvalID)
                ->andWhere('result')->eq('fail')
                ->fetchAll();

            return $this->finish($approvalID, empty($reject) ? 'pass' : 'fail', 'review');
        }
    }

    /**
     * Reject approval.
     *
     * @param  string $objectType
     * @param  object $object
     * @param  string $extra
     * @access public
     * @return bool
     */
    public function reject($objectType = '', $object = null, $extra = '')
    {
        $approvalID = $this->dao->select('approval')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($object->id)
            ->orderBy('id_desc')
            ->fetch('approval');

        $data = new stdclass();
        $data->status       = 'done';
        $data->result       = 'fail';
        $data->date         = $object->createdDate;
        $data->opinion      = $object->opinion;
        $data->extra        = $extra;
        $data->reviewedBy   = $this->app->user->account;
        $data->reviewedDate = helper::now();

        $nodeID = $this->dao->select('id')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->orderBy('id_desc')
            ->fetch('id');

        $this->dao->update(TABLE_APPROVALNODE)->data($data)
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->eq('doing')
            ->andWhere('account')->eq($this->app->user->account)
            ->exec();

        $endNodes = $this->dao->select('*')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('node')->eq('end')
            ->fetchAll();

        if($endNodes)
        {
            $ccList = array();
            foreach($endNodes as $endNode) $ccList[] = $endNode->account;
            $this->cc($approvalID, $endNode->node, $ccList, 'review');
        }

        $this->dao->update(TABLE_APPROVALNODE)
            ->set('status')->eq('done')
            ->set('result')->eq('ignore')
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->ne('done')
            ->exec();

        return $this->finish($approvalID, 'fail', 'review');
    }

    /**
     * Finish approval.
     *
     * @param int    $approvalID
     * @param string $result
     * @param string $method
     * @access public
     * @return bool
     */
    public function finish($approvalID, $result, $method)
    {
        $this->dao->update(TABLE_APPROVAL)
            ->set('status')->eq('done')
            ->set('result')->eq($result)
            ->where('id')->eq($approvalID)
            ->exec();

        $approval = $this->getByID($approvalID);
        $lastNode = $this->dao->select('node')->from(TABLE_APPROVALNODE)->where('approval')->eq($approvalID)->orderBy('id_desc')->fetch('node');
        $this->sendMessage(array($approval->createdBy), $approvalID, $lastNode, $method);

        $startNodes = $this->dao->select('account')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('node')->eq('start')
            ->fetchPairs('account');

        if($startNodes) $this->sendMessage($startNodes, $approvalID, $lastNode, $method, true);

        if($lastNode == 'end')
        {
            $ccList = $this->dao->select('account')->from(TABLE_APPROVALNODE)
                ->where('approval')->eq($approvalID)
                ->andWhere('node')->eq('end')
                ->andWhere('type')->eq('cc')
                ->fetchPairs('account');

            $this->sendMessage($ccList, $approvalID, $lastNode, $method, true);
        }

        return true;
    }

    /**
     * Restart a approval flow.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @access public
     * @return void
     */
    public function restart($objectType, $objectID)
    {
        $approval = $this->dao->select('*')->from(TABLE_APPROVAL)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($objectID)
            ->orderBy('id_desc')
            ->fetch();

        $approval->status = 'doing';
        $oldApprovalID    = $approval->id;
        unset($approval->id);

        $this->dao->insert(TABLE_APPROVAL)->data($approval)->exec();
        $approvalID = $this->dao->lastInsertID();

        $approvalObject = new stdclass();
        $approvalObject->approval   = $approvalID;
        $approvalObject->objectType = $objectType;
        $approvalObject->objectID   = $objectID;

        $this->dao->insert(TABLE_APPROVALOBJECT)->data($approvalObject)->exec();

        $nodes = $this->dao->select('*')->from(TABLE_APPROVALNODE)->where('approval')->eq($oldApprovalID)->fetchAll('id');
        foreach($nodes as $node)
        {
            unset($node->id);

            $node->status       = 'wait';
            $node->result       = '';
            $node->date         = '';
            $node->opinion      = '';
            $node->reviewedBy   = '';
            $node->reviewedDate = '';
            $node->approval     = $approvalID;

            $this->dao->insert(TABLE_APPROVALNODE)->data($node)->exec();
        }

        $this->next($approvalID, '', 'submit');
    }

    /**
     * Cancel all approval nodes of an object.
     *
     * @param  string $objectType
     * @param  int    $objectID
     * @access public
     * @return bool
     */
    public function cancel($objectType, $objectID)
    {
        $this->app->loadLang('approvalflow');

        $approvalID = $this->dao->select('approval')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq($objectType)
            ->andWhere('objectID')->eq($objectID)
            ->orderBy('id_desc')
            ->limit(1)
            ->fetch('approval');

        $reviewers = $this->dao->select('account, node')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->eq('doing')
            ->andWhere('type')->eq('review')
            ->fetchAll('account');

        $ccers = $this->dao->select('account, node')->from(TABLE_APPROVALNODE)
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->eq('doing')
            ->andWhere('type')->eq('cc')
            ->fetchAll('account');

        $this->dao->update(TABLE_APPROVALNODE)
            ->set('status')->eq('done')
            ->set('result')->eq('ignore')
            ->where('approval')->eq($approvalID)
            ->andWhere('status')->ne('done')
            ->exec();

        $approval = $this->getByID($approvalID);
        if($reviewers) $this->sendMessage(array_keys($reviewers), $approvalID, zget(reset($reviewers), 'node'), 'cancel');
        if($ccers)     $this->sendMessage(array_keys($ccers),     $approvalID, zget(reset($ccers), 'node'),     'cancel', true);
        return !dao::isError();
    }

    /**
     * Build review desc.
     *
     * @param  object $node
     * @param  array  $extra
     * @access public
     * @return string
     */
    public function buildReviewDesc($node, $extra = array())
    {
        if(empty($node->id)) return '';

        $approval  = zget($extra, 'approval', '');
        $users     = zget($extra, 'users', array());
        $reviewers = zget($extra, 'reviewers', array());
        $allReviewers = zget($extra, 'allReviewers', array());
        $reviewDesc   = '';

        if($node->type == 'branch' and isset($node->branches))
        {
            foreach($node->branches as $childNode)
            {
                foreach($childNode->nodes as $branchChildNode) $reviewDesc .= $this->buildReviewDesc($branchChildNode, array('users' => $users, 'allReviewers' => $allReviewers, 'reviewers' => zget($allReviewers, $branchChildNode->id, array())));
            }
            return $reviewDesc;
        }

        if($node->type == 'start')
        {
            $reviewDesc  = "<li class='start' data-id='{$node->id}'><div><span class='timeline-text'>";
            $reviewDesc .= str_replace(array('$actor', '$date'), array(zget($users, $approval->createdBy), helper::isZeroDate($approval->createdDate) ? '' : substr($approval->createdDate, 0, 16)), $this->lang->approval->reviewDesc->start);
            $reviewDesc .= "</span></div></li>";
            return $reviewDesc;
        }

        $hasTitle    = !empty($node->title);
        $reviewClass = $hasTitle ? 'reviewer' : 'node';
        $reviewDesc  = '';

        $nodeStatus = 'wait';
        if(empty($reviewers) and isset($node->agentType) and $node->agentType == 'pass') $nodeStatus = 'pass';
        if(!empty($reviewers)) $nodeStatus = empty($reviewers['result']) ? $reviewers['status'] : $reviewers['result'];
        if($nodeStatus == 'success') $nodeStatus = 'pass';
        if($nodeStatus == 'ignore') return;

        if($hasTitle)
        {
            $reviewDesc .= "<li class='node title text-muted $nodeStatus' data-id='{$node->id}' data-status='{$nodeStatus}'><div><span class='timeline-text'>";
            $reviewDesc .= $node->title;
            if($node->multiple == 'or') $reviewDesc .= $this->lang->approval->notice->orSign;
            $reviewDesc .= "</span></div></li>";
        }

        if(empty($reviewers) and isset($node->agentType) and $node->agentType == 'pass')
        {
            $reviewDesc .= "<li class='$reviewClass $nodeStatus' data-id='{$node->id}' data-status='{$nodeStatus}'><div><span class='timeline-text'>";
            $reviewDesc .= $this->lang->approval->reviewDesc->pass4NoReviewer;
            $reviewDesc .= "</span></div></li>";
        }
        elseif(empty($reviewers['reviewers']) and !empty($reviewers['ccs']))
        {
            $ccList = '';
            foreach($reviewers['ccs'] as $cc) $ccList .= zget($users, $cc) . ' ';
            $reviewDesc .= "<li class='$reviewClass $nodeStatus' data-id='{$node->id}' data-status='{$nodeStatus}'><div><span class='timeline-text'>";
            $reviewDesc .= str_replace('$actor', trim($ccList), $this->lang->approval->reviewDesc->cc);
            $reviewDesc .= "</span></div></li>";
        }
        elseif(!empty($reviewers['reviewers']))
        {
            foreach($reviewers['reviewers'] as $reviewInfo)
            {
                $reviewStatus = !empty($reviewInfo['result']) ? $reviewInfo['result'] : $reviewInfo['status'];
                if(!isset($this->lang->approval->reviewDesc->$reviewStatus)) continue;

                if(!empty($reviewInfo['result']) and isset($node->multiple) and $node->multiple == 'or')
                {
                    /* Only show reviewed account in or node. */
                    if($reviewInfo['result'] != 'ignore')
                    {
                        $reviewDesc .= "<li class='$reviewClass $reviewStatus' data-id='{$node->id}' data-status='{$reviewStatus}'}><div><span class='timeline-text'>";
                        $reviewDesc .= str_replace(array('$actor', '$date'), array(zget($users, $reviewInfo['account']), helper::isZeroDate($reviewInfo['reviewedDate']) ? '' : substr($reviewInfo['reviewedDate'], 0, 16)), $this->lang->approval->reviewDesc->{$reviewStatus});
                        $reviewDesc .= "</span>";
                        if(!empty($reviewInfo['opinion'])) $reviewDesc .= "<div>{$reviewInfo['opinion']}</div>";
                        $reviewDesc .= "</div></li>";
                        break;
                    }
                }
                else
                {
                    if($reviewStatus == 'ignore') continue;

                    $account = $reviewInfo['account'];
                    $reviewDesc .= "<li class='$reviewClass $reviewStatus' data-id='{$node->id}' data-status='{$reviewStatus}'}><div><span class='timeline-text'>";
                    if(empty($account))
                    {
                        $autoReviewDesc = '';
                        if($node->reviewType == 'reject') $autoReviewDesc = $this->lang->approval->reviewDesc->autoReject;
                        if($node->reviewType == 'pass')   $autoReviewDesc = $this->lang->approval->reviewDesc->autoPass;
                        if(!empty($reviewInfo['result']) and $node->reviewType == 'reject') $autoReviewDesc = $this->lang->approval->reviewDesc->autoRejected;
                        if(!empty($reviewInfo['result']) and $node->reviewType == 'pass')   $autoReviewDesc = $this->lang->approval->reviewDesc->autoPassed;
                        $reviewDesc .= $autoReviewDesc;
                    }
                    else
                    {
                        $reviewDesc .= str_replace(array('$actor', '$date'), array(zget($users, $reviewInfo['account']), helper::isZeroDate($reviewInfo['reviewedDate']) ? '' : substr($reviewInfo['reviewedDate'], 0, 16)), $this->lang->approval->reviewDesc->{$reviewStatus});
                    }
                    $reviewDesc .= "</span>";
                    if(!empty($reviewInfo['opinion'])) $reviewDesc .= "<div class='opinion'>{$reviewInfo['opinion']}</div>";
                    $reviewDesc .= "</div></li>";
                }

                $reviewDesc .= $this->printReviewerFiles($reviewInfo['files']);
            }
        }
        return $reviewDesc;
    }

    /**
     * Print reviewer files.
     *
     * @param mixed $files
     * @access private
     * @return void
     */
    private function printReviewerFiles($files)
    {
        if(!$files) return '';
        $showDelete = true;
        $showEdit   = true;
        $reviewDesc = include '../view/printfiles.html.php';

        return $reviewDesc;
    }

    /**
     * Order branch nodes. order rule is done, doing, wait.
     *
     * @param  array    $nodes
     * @param  array    $reviewers
     * @access public
     * @return array
     */
    public function orderBranchNodes($nodes, $reviewers)
    {
        foreach($nodes as $i => $node)
        {
            if($node->type != 'branch') continue;
            if(count((array)$node->branches) <= 1) continue;

            $doneNodes  = array();
            $doingNodes = array();
            $waitNodes  = array();
            foreach($node->branches as $branckKey => $branchNodes)
            {
                $nodeStatus = 'wait';
                foreach($branchNodes->nodes as $branchNode)
                {
                    if($nodeStatus == 'doing') break;
                    if(!isset($reviewers[$branchNode->id]))
                    {
                        if(isset($branchNode->agentType) and $branchNode->agentType == 'pass') $nodeStatus = 'done';
                        continue;
                    }

                    if($reviewers[$branchNode->id]['status'] == 'doing') $nodeStatus = 'doing';
                    if($reviewers[$branchNode->id]['status'] == 'done')  $nodeStatus = 'done';
                    if($reviewers[$branchNode->id]['status'] == 'wait' and $nodeStatus == 'done') $nodeStatus = 'doing';
                }

                if($nodeStatus == 'done')  $doneNodes[]  = $branchNodes;
                if($nodeStatus == 'doing') $doingNodes[] = $branchNodes;
                if($nodeStatus == 'wait')  $waitNodes[]  = $branchNodes;
            }

            $sortedNodes = array();
            foreach($doneNodes as $branchNodes)  $sortedNodes[] = $branchNodes;
            foreach($doingNodes as $branchNodes) $sortedNodes[] = $branchNodes;
            foreach($waitNodes as $branchNodes)  $sortedNodes[] = $branchNodes;

            $node->branches = $sortedNodes;
        }

        return $nodes;
    }

    /*
     * Get approval node by approval id.
     *
     * @param  int    $approvalID
     * @param  string $type
     * @access public
     */
    public function getApprovalNodeByApprovalID($approvalID, $type = 'review')
    {
        return $this->dao->select('t1.id,t1.approval,t1.type,t1.result,t1.date,t1.opinion,t1.extra,t1.reviewedBy,t1.reviewedDate')->from(TABLE_APPROVALNODE)->alias('t1')
            ->where('type')->eq($type)
            ->andWhere('approval')->eq($approvalID)
            ->andWhere('result')->in('fail,pass')
            ->orderBy('reviewedDate asc')
            ->fetchAll();
    }
}
