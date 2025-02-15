<?php
/**
 * Get review point by project.
 *
 * @param  int    $projectID
 * @access public
 * @return void
 */
public function getReviewPointByProject($projectID = 0)
{
    $this->loadModel('baseline');
    $this->loadModel('stage');

    $ipdReviewPoint = $this->config->stage->ipdReviewPoint;
    $ipdStageOrder  = $this->config->stage->ipdStageOrder;
    $pointList      = $this->lang->baseline->ipd->pointList;
    unset($pointList['other']);
    unset($pointList['']);

    foreach($pointList as $point => $title) $pointList[$point] = array('disabled' => true, 'message' => '');

    $project = $this->loadModel('project')->getByID($projectID);
    if($project->model != 'ipd') return;

    $stages = $this->dao->select('*')->from(TABLE_EXECUTION)
        ->where('project')->eq($projectID)
        ->andWhere('deleted')->eq(0)
        ->andWhere('type')->eq('stage')
        ->andWhere('parent')->eq($projectID)
        ->fetchPairs('attribute', 'status');

    if(empty($stages)) return $pointList;

    $reviews = $this->dao->select('t1.*, t2.category as category')->from(TABLE_REVIEW)->alias('t1')
        ->leftJoin(TABLE_OBJECT)->alias('t2')->on('t1.object = t2.id')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t1.project')->eq($projectID)
        ->andWhere('t2.category')->in(array_keys($pointList))
        ->orderBy('id_asc')
        ->fetchPairs('category', 'status');

    $canStart = true;
    $prePoint = '';
    foreach($ipdStageOrder as $stage)
    {
        if(!isset($stages[$stage])) continue;

        foreach($ipdReviewPoint->$stage as $point)
        {
            if($stages[$stage] == 'wait')
            {
                $pointList[$point]['disabled'] = true;
                $pointList[$point]['message']  = $this->lang->review->stageNotStartTip;
                $prePoint = $point;
                continue;
            }

            if($prePoint and (!isset($reviews[$prePoint]) or $reviews[$prePoint] != 'pass'))
            {
                $pointList[$point]['disabled'] = true;
                $pointList[$point]['message']  = $this->lang->review->prePointNotPassTip;
                $prePoint = $point;
                continue;
            }

            $pointList[$point]['disabled'] = false;
            preg_match_all('/TR[1-6]/', $point, $TRPoints);
            if(isset($reviews[$point]) && (empty($TRPoints[0]) || (!empty($TRPoints[0]) && in_array($reviews[$point], array('pass', 'wait', 'reviewing'))))) $pointList[$point]['disabled'] = true;

            $prePoint = $point;
        }
    }

    return $pointList;
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
    $project = $this->loadModel('project')->getByID($projectID);

    $today = helper::today();
    $data  = fixer::input('post')
        ->setDefault('template', 0)
        ->setDefault('doc', 0)
        ->remove('comment,uid,reviewer,ccer,doclib')
        ->get();

    if($project->model == 'ipd' and $data->point != 'other')
    {
        $this->config->review->create->requiredFields = str_replace('product,', '', $this->config->review->create->requiredFields);
    }

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

    $object = $this->dao->select('*')->from(TABLE_OBJECT)
        ->where('project')->eq($projectID)
        ->andWhere('deleted')->eq('0')
        ->andWhere('category')->eq($data->point)
        ->fetch();

    if($object)
    {
        $this->dao->update(TABLE_OBJECT)
            ->set('title')->eq($data->title)
            ->set('end')->eq($data->deadline)
            ->set('product')->eq($data->product)
            ->where('id')->eq($object->id)
            ->exec();
        $objectID = $object->id;
    }
    else
    {
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

        $this->dao->insert(TABLE_OBJECT)->data($object)->exec();
        if(dao::isError()) return false;

        $objectID = $this->dao->lastInsertID();
    }

    $docID      = 0;
    $docVersion = 0;
    if(is_array($data->doc))
    {
        $docs = $this->loadModel('doc')->getByIdList($data->doc);
        foreach($docs as $doc)
        {
            $docIDList[]      = $doc->docID;
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
 * Get stage by review.
 *
 * @param  int    $reviewID
 * @access public
 * @return object
 */
public function getStageByReview($reviewID = 0)
{
    $object = $this->dao->select('t1.*')->from(TABLE_OBJECT)->alias('t1')
        ->leftJoin(TABLE_REVIEW)->alias('t2')->on('t2.object=t1.id')
        ->where('t2.id')->eq($reviewID)
        ->fetch();

    $stageType = '';
    foreach($this->config->review->ipdReviewPoint as $type => $point)
    {
        if(in_array($object->category, $point))
        {
            $stageType = $type;
            break;
        }
    }

    return $this->dao->select('*')->from(TABLE_EXECUTION)
        ->where('project')->eq($object->project)
        ->andWhere('type')->eq('stage')
        ->andWhere('attribute')->eq($stageType)
        ->fetch();
}

/**
 * In ipd project, create default review points after create a stage.
 *
 * @param  int    $projectID
 * @param  int    $productID
 * @param  string $attribute
 * @access public
 * @return void
 */
public function createDefaultPoint($projectID, $productID, $attribute)
{
    if($attribute == 'launch') return;

    $this->app->loadConfig('stage');

    foreach($this->config->stage->ipdReviewPoint->$attribute as $category)
    {
        $object = new stdclass();
        $object->project     = $projectID;
        $object->product     = 0;
        $object->title       = $this->lang->review->reviewPoint->titleList[$category];
        $object->category    = $category;
        $object->type        = 'reviewed';
        $object->range       = 'all';
        $object->version     = '';
        $object->createdBy   = $this->app->user->account;
        $object->createdDate = helper::today();

        $this->dao->insert(TABLE_OBJECT)->data($object)->exec();
    }
}

/**
 * Print datatable cell.
 *
 * @param  object $col
 * @param  object $review
 * @param  array  $users
 * @param  array  $products
 * @param  array  $pendingReviews
 * @param  object $project
 * @access public
 * @return void
 */
public function printCell($col, $review, $users, $products, $pendingReviews, $project = null, $reviewers = array())
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
            echo zget($products, $review->product, '');
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
            $params = "reviewID=$review->id";
            $isIPD  = !empty($project) ? $project->model == 'ipd' : false;

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
            $progressClass = $review->approval ? '' : "disabled";
            common::printIcon('approval', 'progress', "approvalID=$review->approval", $review, 'list', 'list-alt', '', "iframe $progressClass", 1);
            common::printIcon('review', 'report',  $params, $review, 'list', 'bar-chart', '');

            if(!$isIPD)
            {
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
            }

            common::printIcon('review', 'edit', $params, $review, 'list');
            if(!$isIPD) common::printIcon('review', 'delete', $params, $review, 'list', 'trash', 'hiddenwin');
        }
        echo '</td>';

    }
}

public function updateReviewDate($objectID, $type)
{
    if($type == 'point')
    {
        $end = $_POST['startDate'];
        $this->dao->update(TABLE_OBJECT)->set('end')->eq($end)->where('id')->eq($objectID)->exec();
    }
}

/**
 * Get latest reviews for project's review points.
 *
 * @param  int    $projectID
 * @access public
 * @return array
 */
public function getPointLatestReviews($projectID)
{
    $this->app->loadLang('baseline');
    $pointList = $this->lang->baseline->ipd->pointList;
    unset($pointList['other']);
    unset($pointList['']);

    $reviews = $this->dao->select('t1.*, t2.category as category')->from(TABLE_REVIEW)->alias('t1')
        ->leftJoin(TABLE_OBJECT)->alias('t2')->on('t1.object = t2.id')
        ->where('t1.deleted')->eq(0)
        ->andWhere('t1.project')->eq($projectID)
        ->andWhere('t2.category')->in(array_keys($pointList))
        ->orderBy('id_asc')
        ->fetchAll('category');

    return $reviews;
}
