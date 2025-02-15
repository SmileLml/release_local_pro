<?php
/**
 * The model file of review module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     model
 * @version     $Id: control.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class reviewModel extends model
{
    /**
     * Get review list.
     *
     * @param  int    $projectID
     * @param  string $browseType
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return void
     */
    public function getList($projectID = 0, $browseType = '', $orderBy = '', $pager = null)
    {
        if($browseType == 'wait')
        {
            $pendingList = $this->loadModel('approval')->getPendingReviews('review');
            $reviews     = $this->getByList($projectID, $pendingList, $orderBy, $pager);
        }
        else
        {
            $reviews = $this->dao->select('t1.*, t2.version, t2.category, t2.product')->from(TABLE_REVIEW)->alias('t1')
                ->leftJoin(TABLE_OBJECT)->alias('t2')
                ->on('t1.object=t2.id')
                ->where('t1.deleted')->eq(0)
                ->beginIF($projectID)->andWhere('t1.project')->eq($projectID)->fi()
                ->beginIF($browseType == 'reviewing')->andWhere('t1.status')->eq('reviewing')->fi()
                ->beginIF($browseType == 'done')->andWhere('t1.status')->eq('done')->fi()
                ->beginIF($browseType == 'reviewedbyme')
                ->andWhere("CONCAT(',', t1.reviewedBy, ',')")->like("%,{$this->app->user->account},%")
                ->fi()
                ->beginIF($browseType == 'createdbyme')
                ->andWhere('t1.createdBy')->eq($this->app->user->account)
                ->fi()
                ->orderBy($orderBy)
                ->page($pager)
                ->fetchAll('id');
        }

        $approvals = $this->dao->select('max(approval) as approval,objectID')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq('review')
            ->andWhere('objectID')->in(array_keys($reviews))
            ->groupBy('objectID')
            ->fetchAll('objectID');

        foreach($reviews as $id => $review)
        {
            $reviews[$id]->approval = isset($approvals[$id]) ? $approvals[$id]->approval : 0;
        }

        return $reviews;
    }

    /**
     * Get by list.
     *
     * @param  int    $projectID
     * @param  array  $idList
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return void
     */
    public function getByList($projectID = 0, $idList = array(), $orderBy = 'id_desc', $pager = null)
    {
        $reviews = $this->dao->select('t1.*, t2.version, t2.category, t2.product')->from(TABLE_REVIEW)->alias('t1')
            ->leftJoin(TABLE_OBJECT)->alias('t2')
            ->on('t1.object=t2.id')
            ->where('t1.deleted')->eq(0)
            ->andWhere('t1.id')->in($idList)->fi()
            ->beginIF($projectID)->andWhere('t1.project')->eq($projectID)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        $approvals = $this->dao->select('max(approval) as approval,objectID')->from(TABLE_APPROVALOBJECT)
            ->where('objectType')->eq('review')
            ->andWhere('objectID')->in(array_keys($reviews))
            ->groupBy('objectID')
            ->fetchAll('objectID');

        foreach($reviews as $id => $review)
        {
            $reviews[$id]->approval = isset($approvals[$id]) ? $approvals[$id]->approval : 0;
        }

        return $reviews;
    }

    /**
     * Get review pairs.
     *
     * @param  int    $projectID
     * @param  int    $productID
     * @param  bool   $withVersion true|false
     * @access public
     * @return void
     */
    public function getPairs($projectID, $productID, $withVersion = false)
    {
        $reviews = $this->dao->select('t1.id, t1.title, t2.version')->from(TABLE_REVIEW)->alias('t1')
            ->leftJoin(TABLE_OBJECT)->alias('t2')
            ->on('t1.object=t2.id')
            ->where('t1.deleted')->eq(0)
            ->beginIF($projectID)->andWhere('t1.project')->eq($projectID)->fi()
            ->beginIF($productID)->andWhere('t2.product')->eq($productID)->fi()
            ->orderBy('t1.id asc')
            ->fetchAll();

        $pairs = array();
        foreach($reviews as $id => $review) $pairs[$review->id] = $withVersion ? $review->title . '-' . $review->version : $review->title;

        return $pairs;
    }

    /**
     * Get review by id.
     *
     * @param  int    $reviewID
     * @access public
     * @return void
     */
    public function getByID($reviewID)
    {
        $review =  $this->dao->select('t1.*, t2.id as objectID, t2.version, t2.category, t2.project, t2.product, t2.data, t2.range')->from(TABLE_REVIEW)->alias('t1')
            ->leftJoin(TABLE_OBJECT)->alias('t2')
            ->on('t1.object=t2.id')
            ->where('t1.id')->eq((int)$reviewID)
            ->fetch();

        $pointLatestReviews = array();
        if($this->config->edition == 'ipd') $pointLatestReviews = $this->getPointLatestReviews($review->project);

        $review->latestReview = !empty($pointLatestReviews[$review->category]) ? $pointLatestReviews[$review->category]->id : $reviewID;

        if(!empty($review)) $review->files = $this->loadModel('file')->getByObject('review', $review->id);
        return $review;
    }

    /**
     * Get user review.
     *
     * @param  string $browseType
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return void
     */
    public function getUserReviews($browseType, $orderBy, $pager = null)
    {
        return $this->dao->select('t1.*, t2.version, t2.category, t2.product')->from(TABLE_REVIEW)->alias('t1')
            ->leftJoin(TABLE_OBJECT)->alias('t2')
            ->on('t1.object=t2.id')
            ->where('t1.deleted')->eq(0)
            ->beginIF($browseType == 'reviewing')->andWhere('t1.status')->eq('reviewing')->fi()
            ->beginIF($browseType == 'done')->andWhere('t1.status')->eq('done')->fi()
            ->beginIF($browseType == 'needreview')
            ->andWhere('t1.status')->in('wait,reviewing')
            ->andWhere("CONCAT(',', t1.reviewedBy, ',')")->like("%,{$this->app->user->account},%")
            ->fi()
            ->beginIF($browseType == 'reviewedbyme')
            ->andWhere('t1.status')->ne('wait')
            ->andWhere("CONCAT(',', t1.reviewedBy, ',')")->like("%,{$this->app->user->account},%")
            ->fi()
            ->beginIF($browseType == 'createdbyme')
            ->andWhere('t1.createdBy')->eq($this->app->user->account)
            ->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();
    }

    /**
     * Get review result by user.
     *
     * @param  int    $reviewID
     * @param  string $type
     * @access public
     * @return void
     */
    public function getResultByUser($reviewID, $type = 'review')
    {
        $result = $this->dao->select('*')->from(TABLE_REVIEWRESULT)
            ->where('review')->eq($reviewID)
            ->andWhere('reviewer')->eq($this->app->user->account)
            ->andWhere('type')->eq($type)
            ->fetch();

        $result = $this->loadModel('file')->replaceImgURL($result, 'opinion');
        return $result;
    }

    /**
     * Get review result by user list.
     *
     * @param  int    $reviewID
     * @access public
     * @return void
     */
    public function getResultByUserList($reviewID, $type = 'review')
    {
        $results = $this->dao->select('*')->from(TABLE_REVIEWRESULT)
            ->where('review')->eq($reviewID)
            ->andWhere('type')->eq($type)
            ->fetchAll('reviewer');

        foreach($results as $user => $result)
        {
            $result = $this->loadModel('file')->replaceImgURL($result, 'opinion');
            $results[$user] = $result;
        }

        return $results;
    }

    /**
     * Get review pairs of a user.
     *
     * @param  string $account
     * @param  int    $limit
     * @param  string $status all|draft|wait|reviewing|pass|fail|auditing|done
     * @param  array  $skipProjectIDList
     * @access public
     * @return array
     */
    public function getUserReviewPairs($account, $limit = 0, $status = 'all', $skipProjectIDList = array())
    {
        $stmt = $this->dao->select('t1.id, t1.title, t2.name as project')
            ->from(TABLE_REVIEW)->alias('t1')
            ->leftjoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
            ->where("CONCAT(',', t1.reviewedBy, ',')")->like("%,{$account},%")
            ->andWhere('t1.deleted')->eq(0)
            ->beginIF($status != 'all')->andWhere('t1.status')->in($status)->fi()
            ->beginIF(!empty($skipProjectIDList))->andWhere('t1.project')->notin($skipProjectIDList)->fi()
            ->beginIF($limit)->limit($limit)->fi()
            ->query();

        $reviews = array();
        while($review = $stmt->fetch())
        {
            $reviews[$review->id] = $review->project . ' / ' . $review->title;
        }
        return $reviews;
    }

    /**
     * Get book id.
     *
     * @param  object $review
     * @access public
     * @return void
     */
    public function getBookID($review)
    {
        return $this->dao->select('id')->from(TABLE_DOC)
            ->where('product')->eq($review->product)
            ->andWhere('templateType')->eq($review->category)
            ->andWhere('lib')->ne('')->fetch('id');
    }

    /**
     * Get object scale.
     *
     * @param  object $review
     * @access public
     * @return void
     */
    public function getObjectScale($review)
    {
        $productID   = $review->product;
        $objectScale = $this->dao->select('sum(estimate) as objectScale')->from(TABLE_STORY)
            ->where('product')->eq($productID)
            ->andWhere('type')->eq('requirement')
            ->andWhere('deleted')->eq(0)
            ->fetch('objectScale');

        return $objectScale;
    }

    /**
     * Create a review.
     *
     * @param  int    $projectID
     * @param  string $reviewRange
     * @param  string $checkedItem
     * @access public
     * @return void
     */
    public function create($projectID = 0, $reviewRange = 'all', $checkedItem = '')
    {
        $today = helper::today();
        $data  = fixer::input('post')
            ->setDefault('template', 0)
            ->setDefault('doc', 0)
            ->remove('comment,uid,reviewer,ccer,doclib')
            ->get();

        foreach(explode(',', $this->config->review->create->requiredFields) as $requiredField)
        {
            if(!isset($data->$requiredField) or strlen(trim($data->$requiredField)) == 0)
            {
                $fieldName = $requiredField;
                if(isset($this->lang->review->$requiredField)) $fieldName = $this->lang->review->$requiredField;
                dao::$errors[] = sprintf($this->lang->error->notempty, $fieldName);
                return false;
            }
        }

        $objectData = $this->getDataByObject($projectID, $data->object, $data->product, $reviewRange, $checkedItem);

        $object = new stdclass();
        $object->project    = $projectID;
        $object->product    = $data->product;
        $object->title      = zget($this->lang->baseline->objectList, $data->object);
        $object->category   = $data->object;
        $object->version    = $this->loadModel('reviewsetting')->getVersionName($data->object);
        $object->type       = 'reviewed';
        $object->range      = $checkedItem ? $checkedItem : $reviewRange;
        $object->storyEst   = isset($objectData['storyEst']) ? $objectData['storyEst'] : 0;
        $object->taskEst    = isset($objectData['taskEst']) ? $objectData['taskEst'] : 0;
        $object->testEst    = isset($objectData['testEst']) ? $objectData['testEst'] : 0;
        $object->requestEst = isset($objectData['requestEst']) ? $objectData['requestEst'] : 0;
        $object->devEst     = isset($objectData['devEst']) ? $objectData['devEst'] : 0;
        $object->designEst  = isset($objectData['designEst']) ? $objectData['designEst'] : 0;

        unset($objectData['storyEst']);
        unset($objectData['testEst']);
        unset($objectData['requestEst']);
        unset($objectData['devEst']);
        unset($objectData['designEst']);

        $object->data        = json_encode($objectData);
        $object->createdBy   = $this->app->user->account;
        $object->createdDate = $today;

        $this->dao->insert(TABLE_OBJECT)->data($object)->batchCheck('product', 'notempty')->exec();
        if(dao::isError()) return false;

        $objectID = $this->dao->lastInsertID();

        $docID      = 0;
        $docVersion = 0;
        if(is_array($data->doc))
        {
            $docs = $this->loadModel('doc')->getByIdList($data->doc);
            foreach($docs as $doc)
            {
                $docIDList[]      = $doc->id;
                $docVersionList[] = $doc->docVersion ? $doc->docVersion : 0;
            }
            $docID      = implode(',', $docIDList);
            $docVersion = implode(',', $docVersionList);
        }
        else
        {
            $doc = $this->loadModel('doc')->getByID($data->doc);
            if(!empty($doc))
            {
                $docID      = $doc->id;
                $docVersion = $doc->version;
            }
        }

        $review = new stdclass();
        $review->title       = $data->title;
        $review->project     = $projectID;
        $review->object      = $objectID;
        $review->template    = $data->template;
        $review->doc         = $docID;
        $review->docVersion  = $docVersion;
        $review->status      = 'wait';
        $review->createdBy   = $this->app->user->account;
        $review->createdDate = $today;
        $review->deadline    = $data->deadline;
        if(!empty($data->begin)) $review->begin = $data->begin;

        $this->dao->insert(TABLE_REVIEW)->data($review)
            ->autoCheck()
            ->batchCheck($this->config->review->create->requiredFields, 'notempty')
            ->exec();

        $reviewID = $this->dao->lastInsertID();
        $this->loadModel('file')->saveUpload('review', $reviewID);

        $reviewers = $this->post->reviewer ? $this->post->reviewer : array();
        $ccers     = $this->post->ccer     ? $this->post->ccer     : array();
        $idList    = $this->post->id       ? $this->post->id       : array();

        if($reviewID) $this->loadModel('action')->create('review', $reviewID, 'Opened', $this->post->comment);

        $result = $this->loadModel('approval')->createApprovalObject($projectID, $reviewID, 'review', $reviewers, $ccers, $idList, $this->post->object);
        if(!empty($result['result'])) $this->dao->update(TABLE_REVIEW)->set('result')->eq($result['result'])->set('status')->eq($result['result'])->where('id')->eq($reviewID)->exec();

        if(!dao::isError()) return $reviewID;

        return false;
    }

    /**
     * Edit a review.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function update($reviewID)
    {
        $oldReview = $this->getByID($reviewID);
        $today = helper::today();
        $data  = fixer::input('post')
            ->setDefault('template', 0)
            ->setDefault('doc', 0)
            ->setDefault('deleteFiles', array())
            ->remove('comment,uid,filed')
            ->get();

        $object = new stdclass();
        $object->product = $data->product;
        $object->title   = $data->title;
        $object->end     = $data->deadline;

        $this->dao->update(TABLE_OBJECT)->data($object)->where('id')->eq($oldReview->objectID)->exec();

        $review = new stdclass();
        $review->title          = $data->title;
        $review->deadline       = $data->deadline;
        $review->lastEditedBy   = $this->app->user->account;
        $review->lastEditedDate = date('Y-m-d');

        $this->dao->update(TABLE_REVIEW)->data($review, 'deleteFiles')
            ->autoCheck()
            ->batchCheck($this->config->review->create->requiredFields, 'notempty')
            ->where('id')->eq($reviewID)
            ->exec();

        $review->product = $object->product;
        $date = $this->loadModel('file')->processImgURL($review, $this->config->review->editor->edit['id'], $this->post->uid);
        $this->file->processFile4Object('review', $oldReview, $data);
        if(!dao::isError()) return common::createChanges($oldReview, $data);

        return false;
    }

    /**
     * Submit a review.
     *
     * @param  int    $reviewID
     * @access public
     * @return void
     */
    public function submit($reviewID)
    {
        $oldReview = $this->getByID($reviewID);
        $today     = helper::today();
        $review    = fixer::input('post')
            ->add('status', 'wait')
            ->setIF($oldReview->status == 'fail', 'status', 'reviewing')
            ->remove('comment,uid,reviewer,ccer,id')
            ->get();

        if($oldReview->doc)
        {
            $doc = $this->loadModel('doc')->getByID($oldReview->doc);
            if($oldReview->docVersion != $doc->version) $review->docVersion = $doc->version;
        }

        $this->dao->update(TABLE_REVIEW)->data($review)->where('id')->eq($reviewID)->exec();

        if(!dao::isError())
        {
            $changes   = common::createChanges($oldReview, $review);
            $review    = $this->getByID($reviewID);
            $reviewers = $this->post->reviewer ? $this->post->reviewer : array();
            $ccers     = $this->post->ccer     ? $this->post->ccer     : array();
            $idList    = $this->post->id       ? $this->post->id       : array();

            $result = $this->loadModel('approval')->createApprovalObject($review->project, $reviewID, 'review', $reviewers, $ccers, $idList, $review->category);
            if(!empty($result['result'])) $this->dao->update(TABLE_REVIEW)->set('result')->eq($result['result'])->set('status')->eq($result['result'])->where('id')->eq($reviewID)->exec();

            return $changes;
        }
        return false;
    }

    /**
     * Set review to audit.
     *
     * @param  int    $reviewID
     * @access public
     * @return void
     */
    public function toAudit($reviewID)
    {
        $auditedBy = $this->post->auditedBy;
        if(!$auditedBy) die(js::alert($this->lang->review->auditedByEmpty));

        $this->dao->update(TABLE_REVIEW)
            ->set('auditedBy')->eq($auditedBy)
            ->set('status')->eq('auditing')
            ->where('id')->eq($reviewID)->exec();

        return !dao::isError();
    }

    /**
     * Save review result.
     *
     * @param  int    $reviewID
     * @param  string $type
     * @access public
     * @return void
     */
    public function saveResult($reviewID, $type = 'review')
    {
        $this->loadModel('approval');

        $review  = $this->getById($reviewID);
        $account = $this->app->user->account;
        $today   = helper::today();
        $data    = fixer::input('post')
            ->setDefault('reviewer', $this->app->user->account)
            ->setIF(is_numeric($this->post->consumed), 'consumed', (float)$this->post->consumed)
            ->stripTags($this->config->review->editor->assess['id'], $this->config->allowedTags)
            ->get();

        $result = new stdclass();
        $result->id          = $reviewID;
        $result->result      = $data->result;
        $result->opinion     = $data->opinion;
        $result->createdDate = $data->createdDate ? $data->createdDate : helper::today();
        $result->consumed    = $data->consumed;

        $result = $this->loadModel('file')->processImgURL($result, $this->config->review->editor->assess['id'], $this->post->uid);

        /* Log first for approval send message. */
        $action   = $type == 'review' ? 'Reviewed' : 'Audited';
        $actionID = $this->loadModel('action')->create('review', $reviewID, $action, $this->post->opinion, ucfirst($result->result));

        if($type == 'review')
        {
            $reviewers = explode(',', $review->reviewedBy);
            if(array_search($account, $reviewers) === false) $reviewers[] = $account;
            $reviewers = implode(',', $reviewers);

            $this->dao->update(TABLE_REVIEW)
                ->set('lastReviewedBy')->eq($this->app->user->account)
                ->set('lastReviewedDate')->eq($today)
                ->set('reviewedBy')->eq($reviewers)
                ->where('id')->eq($reviewID)
                ->exec();

            if($data->result == 'pass')
            {
                $allNodesPassed = $this->approval->pass('review', $result, $result->consumed);
                if($allNodesPassed)
                {
                    $this->dao->update(TABLE_REVIEW)->set('result')->eq('pass')->set('status')->eq('pass')->where('id')->eq($reviewID)->exec();
                }
                else
                {
                    if($review->status == 'wait') $this->dao->update(TABLE_REVIEW)->set('status')->eq('reviewing')->where('id')->eq($reviewID)->exec();
                }
            }

            if($data->result == 'fail')
            {
                $this->approval->reject('review', $result, $result->consumed);
                $this->dao->update(TABLE_REVIEW)->set('result')->eq('fail')->set('status')->eq('fail')->where('id')->eq($reviewID)->exec();
            }

            /* Save effort. */
            $approvalID = $this->dao->select('approval')->from(TABLE_APPROVALOBJECT)->where('objectType')->eq('review')->andWhere('objectID')->eq($reviewID)->orderBy('id_desc')->limit(1)->fetch('approval');

            /* Save file. */
            $approvalNodeID = $this->dao->select('*')->from(TABLE_APPROVALNODE)
                ->where('approval')->eq($approvalID)
                ->andWhere('type')->eq('review')
                ->andWhere('result')->eq($result->result)
                ->andWhere('reviewedBy')->eq($this->app->user->account)
                ->orderBy('id_desc')
                ->fetch('id');
            if($approvalNodeID) $this->loadModel('file')->saveUpload('approvalnode', $approvalNodeID);

            $this->loadModel('effort')->create('review', $reviewID, $result->consumed, $review->title, $approvalID, $result->createdDate);
        }
        else
        {
            $audit  = new stdclass();
            $audit->auditResult     = $result->result;
            $audit->auditedBy       = $this->app->user->account;
            $audit->lastAuditedBy   = $this->app->user->account;
            $audit->lastAuditedDate = helper::today();

            if($result->result == 'pass') $audit->status = 'done';

            if($result->result == 'fail')
            {
                $audit->status    = 'wait';
                $audit->result    = '';
                $audit->auditedBy = '';

                $this->loadModel('approval')->restart('review', $reviewID);
            }

            if($result->result == 'needfix')
            {
                $audit->status    = 'pass';
                $audit->auditedBy = '';
            }

            $this->dao->update(TABLE_REVIEW)->data($audit)->where('id')->eq($reviewID)->exec();
            $this->loadModel('effort')->create('review', $reviewID, $result->consumed, $this->lang->review->audit . $review->title, '', $result->createdDate);
        }

        if(dao::isError())
        {
            $this->dao->delete()->from(TABLE_ACTION)->where('id')->eq($actionID)->exec();
            return false;
        }


        /* Save review issues. */
        $issueResult = isset($data->issueResult) ? $data->issueResult : array();
        if(empty($issueResult)) return true;

        $checkListPairs = $type == 'review' ? $this->loadModel('reviewcl')->getByList(array_keys($issueResult)) : $this->loadModel('cmcl')->getByList(array_keys($issueResult));

        $approval = $this->loadModel('approval')->getApprovalIDByObjectID($reviewID);
        $currentApprovalID = end($approval);

        foreach($issueResult as $id => $result)
        {
            if($result != 0) continue;

            $issue = new stdclass();
            $issue->title       = zget($checkListPairs, $id, $data->issueOpinion[$id]);
            $issue->type        = $type;
            $issue->review      = $reviewID;
            $issue->listID      = $id;
            $issue->status      = 'active';
            $issue->opinion     = $data->issueOpinion[$id];
            $issue->createdBy   = $this->app->user->account;
            $issue->createdDate = helper::today();
            $issue->opinionDate = isset($data->opinionDate[$id]) ? $data->opinionDate[$id] : '';
            $issue->approval    = $currentApprovalID ? $currentApprovalID : 0;

            $this->dao->insert(TABLE_REVIEWISSUE)->data($issue)->autoCheck()->exec();
            $issueID = $this->dao->lastInsertID();
            $this->loadModel('action')->create('reviewissue', $issueID, 'opened', $issue->opinion);
        }
    }

    /**
     * get audit by reviewID
     *
     * @param  int $reviewID
     * @access public
     * @return $audit
     */
    public function getAuditByReviewID($reviewID)
    {
        $audit = $this->dao->select('*')->from(TABLE_REVIEWRESULT)->where('review')->eq($reviewID)->andWhere('type')->eq('audit')->fetch();
        $audit = $this->loadModel('file')->replaceImgURL($audit, 'opinion');

        return $audit;
    }

    /**
     * Get object data.
     *
     * @param  int     $projectID
     * @param  string  $objectType
     * @param  int     $productID
     * @param  string  $reviewRange
     * @param  string  $checkedItem
     * @access public
     * @return void
     */
    public function getDataByObject($projectID, $objectType, $productID, $reviewRange, $checkedItem)
    {
        $data = array();
        if($objectType == 'PP')  $data = $this->getDataFromPP($projectID, $objectType, $productID);
        if($objectType == 'SRS') $data = $this->getDataFromStory($projectID, $objectType, $productID, $reviewRange, $checkedItem);
        if($objectType == 'HLDS' || $objectType == 'DDS' || $objectType == 'DBDS' || $objectType == 'ADS') $data = $this->getDataFromDesign($projectID, $objectType, $productID, $reviewRange, $checkedItem);
        if($objectType == 'ITTC' || $objectType == 'STTC') $data = $this->getDataFromCase($projectID, $objectType, $productID, $reviewRange, $checkedItem);

        return $data;
    }

    /**
     * Get data from story.
     *
     * @param  int     $projectID
     * @param  string  $objectType
     * @param  int     $productID
     * @param  string  $reviewRange
     * @param  string  $checkedItem
     * @access public
     * @return void
     */
    public function getDataFromStory($projectID, $objectType, $productID, $reviewRange, $checkedItem)
    {
        $data = array();

        $stories     = $this->loadModel('story')->getExecutionStories($projectID, $productID);
        $storyIdList = array_keys($stories);

        $stories = $this->dao->select('t1.module, t1.estimate, t2.*')->from(TABLE_STORY)->alias('t1')
            ->leftJoin(TABLE_STORYSPEC)->alias('t2')->on('t1.id=t2.story and t1.version=t2.version')
            ->where('t1.id')->in($storyIdList)
            ->andWhere('t1.deleted')->eq(0)
            ->beginIF($reviewRange != 'all')->andWhere('t1.id')->in($checkedItem)->fi()
            ->fetchAll('story');
        $storyEst = $this->dao->select('sum(estimate) as storyEst')->from(TABLE_STORY)
            ->where('id')->in($storyIdList)
            ->andWhere('deleted')->eq(0)
            ->beginIF($reviewRange != 'all')->andWhere('id')->in($checkedItem)->fi()
            ->fetch('storyEst');

        $data['story']    = $stories;
        $data['storyEst'] = $storyEst;
        return $data;
    }

    /**
     * Get data from design.
     *
     * @param  int     $projectID
     * @param  string  $objectType
     * @param  int     $productID
     * @param  string  $reviewRange
     * @param  string  $checkedItem
     * @access public
     * @return void
     */
    public function getDataFromDesign($projectID, $objectType, $productID, $reviewRange, $checkedItem)
    {
        $data = array();
        $designs = $this->dao->select('t2.*')->from(TABLE_DESIGN)->alias('t1')
            ->leftJoin(TABLE_DESIGNSPEC)->alias('t2')
            ->on('t1.id=t2.design and t1.version=t2.version')
            ->where('(t1.product')->eq($productID)
            ->orWhere('t1.product')->eq(0)
            ->markRight(1)
            ->andWhere('t1.type')->eq($objectType)
            ->andWhere('t1.project')->eq($projectID)
            ->andWhere('t1.deleted')->eq(0)
            ->beginIF($reviewRange != 'all')->andWhere('t1.id')->in($checkedItem)->fi()
            ->orderBy('version_desc')
            ->fetchAll('design');

        $data['design'] = $designs;
        return $data;
    }

    /**
     * Get data from case.
     *
     * @param  int     $projectID
     * @param  string  $objectType
     * @param  int     $productID
     * @param  string  $reviewRange
     * @param  string  $checkedItem
     * @access public
     * @return void
     */
    public function getDataFromCase($projectID, $objectType, $productID, $reviewRange, $checkedItem)
    {
        $data  = array();
        $stage = $objectType == 'ITTC' ? 'intergrate' : 'system';
        $cases = $this->dao->select('t1.id as caseID, t1.module, t1.title, t2.*')->from(TABLE_CASE)->alias('t1')
            ->leftJoin(TABLE_CASESTEP)->alias('t2')
            ->on('t1.id=t2.case')
            ->where('t1.product')->eq($productID)
            ->andWhere('t1.stage')->like("%$stage%")
            ->andWhere('t1.deleted')->eq(0)
            ->beginIF($reviewRange != 'all')->andWhere('t1.id')->in($checkedItem)->fi()
            ->fetchAll('caseID');

        $data['case'] = $cases;
        return $data;
    }

    /**
     * Get data from project plan.
     *
     * @param  int    $projectID
     * @param  string $objectType
     * @param  int    $productID
     * @access public
     * @return void
     */
    public function getDataFromPP($projectID, $objectType, $productID)
    {
        $data   = array();
        $stages = $this->dao->select('t1.*')->from(TABLE_PROJECTSPEC)->alias('t1')
            ->leftJoin(TABLE_PROJECT)->alias('t2')
            ->on('t1.project=t2.id and t1.version=t2.version')
            ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')
            ->on('t2.id=t3.project')
            ->where('t2.deleted')->eq(0)
            ->andWhere('t2.project')->eq($projectID)
            ->andWhere('t3.product')->eq($productID)
            ->fetchAll('project');

        $data['stage'] = $stages;

        $projects = $this->dao->select('t1.id')->from(TABLE_PROJECT)->alias('t1')
            ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t2')
            ->on('t1.id=t2.project')
            ->where('t1.project')->eq($projectID)
            ->andWhere('t1.type')->eq('stage')
            ->andWhere('t2.product')->eq($productID)
            ->fetchPairs();

        $tasks = $this->dao->select('t1.*, t2.estimate, t2.type')->from(TABLE_TASKSPEC)->alias('t1')
            ->leftJoin(TABLE_TASK)->alias('t2')
            ->on('t1.task=t2.id and t1.version=t2.version')
            ->where('t2.deleted')->eq(0)
            ->andWhere('t2.status')->ne('cancel')
            ->andWhere('t2.parent')->le(0)
            ->andWhere('t2.project')->in($projects)
            ->fetchAll('task');

        /* Sum estimate by type.*/
        $taskEst = $requestEst = $testEst = $devEst = $designEst = 0;
        foreach($tasks as $task)
        {
            $taskEst += $task->estimate;
            if($task->type == 'request') $requestEst += $task->estimate;
            if($task->type == 'devel')   $devEst     += $task->estimate;
            if($task->type == 'test')    $testEst    += $task->estimate;
            if($task->type == 'design')  $designEst  += $task->estimate;
        }

        $data['task']        = $tasks;
        $data['taskEst']     = $taskEst;
        $data['requestEst']  = $requestEst;
        $data['devEst']      = $devEst;
        $data['testEst']     = $testEst;
        $data['designEst']   = $designEst;
        return $data;
    }

    /**
     * Get reviewer by object.
     *
     * @param  int    $projectID
     * @param  string $object
     * @access public
     * @return void
     */
    public function getReviewerByObject($projectID, $object = '')
    {
        $this->app->loadConfig('reviewsetting');
        $roleList = isset($this->config->reviewsetting->reviewer->$object) ? $this->config->reviewsetting->reviewer->$object : array();
        if(empty($roleList)) return array();

        $users = $this->dao->select('t1.account, t1.realname')->from(TABLE_USER)->alias('t1')
            ->leftJoin(TABLE_TEAM)->alias('t2')->on('t1.account=t2.account')
            ->where('t1.deleted')->eq(0)
            ->andWhere('t1.role')->in($roleList)
            ->andWhere('t2.type')->eq('project')
            ->andWhere('t2.root')->eq($projectID)
            ->fetchPairs();

        return !empty($users) ? $users : array('' => '');
    }

    /**
     * Judge button if can clickable.
     *
     * @param  object $review
     * @param  string $action
     * @access public
     * @return void
     */
    public static function isClickable($review, $action)
    {
        global $app, $config;
        $action = strtolower($action);
        if(!empty($review->reviewStatus)) $review->status = $review->reviewStatus;

        if($action == 'edit')    return $review->status == 'wait' || $review->status == 'draft' || $review->status == 'fail' || $app->user->admin;
        if($action == 'assess')  return ($review->status == 'wait' || $review->status == 'reviewing');
        if($action == 'submit')
        {
            $isLatestReview = ($config->edition == 'ipd' && isset($review->latestReview) && $review->latestReview != $review->id) ? false : true;
            return (($review->status == 'draft' || ($review->status == 'fail' && $review->result == 'fail')) && $isLatestReview);
        }
        if($action == 'recall')  return $review->status == 'wait';
        if($action == 'toaudit') return $review->status == 'pass' and !$review->auditedBy;
        if($action == 'audit')   return $review->status == 'auditing' and $review->auditedBy == $app->user->account;
        if($action == 'report')  return $review->result;
        if($action == 'createbaseline')  return $review->status == 'done';
        return true;
    }

    /**
     * Print datatable cell.
     *
     * @param  object $col
     * @param  object $review
     * @param  array  $users
     * @param  array  $products
     * @param  array  $pendingReviews
     * @param  array  $reviewers
     * @access public
     * @return void
     */
    public function printCell($col, $review, $users, $products, $pendingReviews, $reviewers = array())
    {
        $canView = common::hasPriv('review', 'view');
        $canBatchAction = false;

        $reviewList = inlink('view', "reviewID=$review->id");
        $account    = $this->app->user->account;
        $id = $col->id;
        if($col->show)
        {
            $class = "c-$id";
            $title = '';
            if($id == 'id') $class .= ' cell-id';
            if($id == 'status')
            {
                $class .= ' status-' . $review->status;
            }
            if($id == 'result')
            {
                $class .= ' status-' . $review->result;
            }
            if($id == 'title')
            {
                $class .= ' text-left';
                $title  = "title='{$review->title}'";
            }
            if($id == 'reviewedBy')
            {
                $reviewed = '';
                $reviewedBy = explode(',', $review->reviewedBy);
                foreach($reviewedBy as $account)
                {
                    $account = trim($account);
                    if(empty($account)) continue;
                    $reviewed .= zget($users, $account) . " &nbsp;";
                }
                $title = "title='{$reviewed}'";
            }
            if($id == 'reviewer')
            {
                $reviewer = '';
                foreach($reviewers[$review->id] as $account)
                {
                    $account = trim($account);
                    if(empty($account)) continue;
                    $reviewer .= zget($users, $account) . " &nbsp;";
                }
                $title = "title='{$reviewer}'";
            }
            if($id == 'product')
            {
                $title = 'title=' . zget($products, $review->product);
            }
            if($id == 'category')
            {
                $title = 'title=' . zget($this->lang->baseline->objectList, $review->category);
            }

            echo "<td class='" . $class . "' $title>";
            switch($id)
            {
            case 'id':
                if($canBatchAction)
                {
                    echo html::checkbox('reviewIDList', array($review->id => '')) . html::a(helper::createLink('review', 'view', "reviewID=$review->id"), sprintf('%03d', $review->id));
                }
                else
                {
                    printf('%03d', $review->id);
                }
                break;
            case 'title':
                echo html::a(helper::createLink('review', 'view', "reviewID=$review->id"), $review->title);
                break;
            case 'product':
                echo zget($products, $review->product);
                break;
            case 'category':
                echo zget($this->lang->baseline->objectList, $review->category);
                break;
            case 'version':
                echo $review->version;
                break;
            case 'status':
                echo zget($this->lang->review->statusList, $review->status);
                break;
            case 'reviewedBy':
                echo $reviewed;
                break;
            case 'reviewer':
                echo $reviewer;
                break;
            case 'createdBy':
                echo zget($users, $review->createdBy);
                break;
            case 'createdDate':
                echo helper::isZeroDate($review->createdDate) ? '' : $review->createdDate;
                break;
            case 'deadline':
                echo helper::isZeroDate($review->deadline) ? '' : $review->deadline;
                break;
            case 'lastReviewedDate':
                echo helper::isZeroDate($review->lastReviewedDate) ? '' : $review->lastReviewedDate;
                break;
            case 'lastAuditedDate':
                echo helper::isZeroDate($review->lastAuditedDate) ? '' : $review->lastAuditedDate;
                break;
            case 'result':
                if($review->status == 'reviewing') break;
                echo zget($this->lang->review->resultList, $review->result);
                break;
            case 'auditResult':
                echo zget($this->lang->review->auditResultList, $review->auditResult);
                break;
            case 'actions':
                $leftActionAccess   = common::hasPriv('review', 'submit') or common::hasPriv('review', 'recall') or common::hasPriv('review', 'assess') or common::hasPriv('review', 'progress') or common::hasPriv('review', 'report');
                $middleActionAccess = common::hasPriv('review', 'toAudit') or common::hasPriv('review', 'audit');
                $rightActionAccess  = common::hasPriv('review', 'create') or common::hasPriv('review', 'edit') or common::hasPriv('review', 'delete');
                $params  = "reviewID=$review->id";

                common::printIcon('review', 'submit', $params, $review, 'list', 'play', '', 'iframe', true, '', $this->lang->review->submit);
                common::printIcon('review', 'recall', $params, $review, 'list', 'back', 'hiddenwin', '', '', '', $this->lang->review->recall);
                if(isset($pendingReviews[$review->id]))
                {
                    common::printIcon('review', 'assess', $params, $review, 'list', 'glasses');
                }
                else
                {
                    common::printIcon('review', 'assess', $params, $review, 'list', 'glasses', '', '', false, '', '', 0, false);
                }

                $review->approval = isset($review->approval) ? $review->approval : 0;
                common::printIcon('approval', 'progress', "approvalID=$review->approval", $review, 'list', 'list-alt', '', 'iframe', 1);
                common::printIcon('review', 'report',  $params, $review, 'list', 'bar-chart', '');
                if(($leftActionAccess and $middleActionAccess) or ($leftActionAccess and $rightActionAccess and !$middleActionAccess)) echo '<div class="dividing-line"></div>';
                common::printIcon('review', 'toAudit', $params, $review, 'list', 'hand-right', '', 'iframe', true);
                common::printIcon('review', 'audit',   $params, $review, 'list', 'search');

                if($rightActionAccess and $middleActionAccess) echo '<div class="dividing-line"></div>';
                if($review->status == 'done')
                {
                    common::printIcon('cm', 'create', "project=$review->project&" . $params, '', 'list', 'flag', '', '', false, '', $this->lang->review->createBaseline);
                }
                else
                {
                    common::printIcon('cm', 'create', "project=$review->project&" . $params, '', 'list', 'flag', '', '', false, '', '', 0, false);
                }
                common::printIcon('review', 'edit', $params, $review, 'list');
                common::printIcon('review', 'delete', $params, $review, 'list', 'trash', 'hiddenwin');
            }
            echo '</td>';
        }
    }

    /**
     * Get reviewer by review id list.
     *
     * @param  int|array    $reviewIdList
     * @access public
     * @return array
     */
    public function getReviewerByIdList($reviewIdList)
    {
        $reviewerGroup = $this->dao->select("id,objectID,nodes")->from(TABLE_APPROVAL)
            ->where('objectType')->eq('review')
            ->andWhere('objectID')->in($reviewIdList)
            ->andWhere('deleted')->eq(0)
            ->orderBy('id_desc')
            ->fetchGroup('objectID', 'id');

        $reviewers = array();
        foreach($reviewerGroup as $reviewID => $reviewList)
        {
            $reviewers[$reviewID] = isset($reviewers[$reviewID]) ? $reviewers[$reviewID] : array();

            $latestNode = current($reviewList);
            $nodes      = json_decode($latestNode->nodes);
            foreach($nodes as $reviewerList)
            {
                $approverList = isset($reviewerList->reviewers) ? $reviewerList->reviewers : array();
                if(empty($approverList)) continue;

                foreach($approverList as $users) $reviewers[$reviewID] = array_unique(array_merge($reviewers[$reviewID], $users->users));
            }
        }

        return $reviewers;
    }
}
