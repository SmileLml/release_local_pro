<?php
/**
 * Adjust the action clickable.
 *
 * @param  object $story
 * @param  string $action
 * @access public
 * @return void
 */
public static function isClickable($story, $action)
{
    global $app, $config;
    $action = strtolower($action);

    if($action == 'recall')     return strpos('reviewing,changing', $story->status) !== false;
    if($action == 'close')      return $story->status != 'closed';
    if($action == 'activate')   return $story->status == 'closed';
    if($action == 'assignto')   return $story->status != 'closed';
    if($action == 'batchcreate' and $story->parent > 0) return false;
    if($action == 'batchcreate' and !empty($story->twins)) return false;
    if($action == 'batchcreate' and $story->type == 'requirement' and $story->status != 'closed') return strpos('draft,reviewing,changing', $story->status) === false;
    if($action == 'submitreview' and strpos('draft,changing', $story->status) === false) return false;

    static $shadowProducts = array();
    static $taskGroups     = array();
    static $hasShadow      = true;
    if($hasShadow and empty($shadowProducts[$story->product]))
    {
        global $dbh;
        $stmt = $dbh->query('SELECT id FROM ' . TABLE_PRODUCT . " WHERE shadow = 1")->fetchAll();
        if(empty($stmt)) $hasShadow = false;
        foreach($stmt as $row) $shadowProducts[$row->id] = $row->id;
    }

    if($hasShadow and empty($taskGroups[$story->id])) $taskGroups[$story->id] = $app->dbQuery('SELECT id FROM ' . TABLE_TASK . " WHERE story = $story->id")->fetch();

    if($story->parent < 0 and strpos($config->story->list->actionsOpratedParentStory, ",$action,") === false) return false;

    if($action == 'batchcreate')
    {
        if($config->vision == 'lite' and ($story->status == 'active' and in_array($story->stage, array('wait', 'projected')))) return true;

        if($story->status != 'active' or !empty($story->plan)) return false;
        if(isset($shadowProducts[$story->product]) && (!empty($taskGroups[$story->id]) or $story->stage != 'projected')) return false;
        if(!isset($shadowProducts[$story->product]) && $story->stage != 'wait') return false;
    }

    $story->reviewer  = isset($story->reviewer)  ? $story->reviewer  : array();
    $story->notReview = isset($story->notReview) ? $story->notReview : array();
    $isSuperReviewer = strpos(',' . trim(zget($config->story, 'superReviewers', ''), ',') . ',', ',' . $app->user->account . ',');

    if($action == 'change')     return (($isSuperReviewer !== false or count($story->reviewer) == 0 or count($story->notReview) == 0) and ($story->type == 'story' ? $story->status == 'active' : ($story->status == 'launched' or $story->status == 'developing')));
    if($action == 'review')     return (($isSuperReviewer !== false or in_array($app->user->account, $story->notReview)) and $story->status == 'reviewing');

    return true;
}

/**
 * Build operate menu.
 *
 * @param  object $story
 * @param  string $type
 * @param  object $execution
 * @param  string $storyType story|requirement
 * @access public
 * @return string
 */
public function buildOperateMenu($story, $type = 'view', $execution = '', $storyType = 'story')
{
    $this->lang->story->changeTip = $storyType == 'story' ? $this->lang->story->changeTip : $this->lang->story->URChangeTip;

    if($story->type != 'requirement' and !empty($story->confirmeObject))
    {
        $method = $story->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
        return $this->buildMenu('story', $method, "objectID=$story->id&object=story&extra={$story->confirmeObject['id']}", $story, 'view', 'search', '', 'iframe', true);
    }

    return parent::buildOperateMenu($story, $type, $execution, $storyType);
}

/**
 * Get affect objects.
 *
 * @param  mixed  $objects
 * @param  string $objectType
 * @param  string $object
 * @access public
 * @return void
 */
public function getAffectObject($objects = '', $objectType = '', $objectInfo = '')
{
    if(empty($objects) and empty($objectInfo)) return $objects;
    if(empty($objects) and $objectInfo) $objects = array($objectInfo->id => $objectInfo);

    $objectIDList = array();
    $storyIDList  = array();
    foreach($objects as $object)
    {
        if(!empty($object->children)) $this->getAffectObject($object->children, $objectType);
        if($this->app->rawModule == 'testtask') $object->id = $object->case;

        $objectIDList[] = $object->id;
        $storyIDList[]  = $objectType == 'story' ? $object->id : $object->story;
        $object->confirmeObject = array();
        $object->URs            = '';
    }

    /* 根据需求ID查找父需求。*/
    $parentStories = $this->dao->select('id,parent')->from(TABLE_STORY)
        ->where('deleted')->eq(0)
        ->andWhere('id')->in($storyIDList)
        ->andWhere('parent')->gt(0)
        ->fetchPairs('id', 'parent');

    /* 获取需要确认的用户需求id。 */
    $URs   = $this->getStoryRelationByIds($storyIDList, 'story');
    $URIds = implode(',', $URs);

    if($parentStories)
    {
        $parentURs = $this->getStoryRelationByIds($parentStories, 'story');
        $URIds .= ',' . implode(',', $parentURs);

        foreach($parentStories as $storyID => $parentStoryID)
        {
            $URs[$storyID]  = isset($URs[$storyID]) ? $URs[$storyID] : '';
            $URs[$storyID] .= isset($parentURs[$parentStoryID]) ? ',' . $parentURs[$parentStoryID] : '';
        }
    }

    if(!$URIds) return $objectInfo ? $objectInfo : $objects;

    /* 查询最近一次撤回/移除操作。 */
    $lastActions = $this->dao->select('*')->from(TABLE_ACTION)
        ->where('objectType')->eq('story')
        ->andWhere('objectID')->in($URIds)
        ->andWhere('action')->in('retractclosed,unlinkedfromroadmap')
        ->orderBy('id_asc')
        ->fetchAll('objectID');

    /* 获取已经确认过的对象。*/
    $confirmedActions = $this->dao->select('*')->from(TABLE_ACTION)
        ->where('objectType')->eq($objectType)
        ->andWhere('objectID')->in($objectIDList)
        ->andWhere('action')->in('confirmedretract,confirmedunlink')
        ->orderBy('id_asc')
        ->fetchAll('objectID');

    /* 将确认信息插入到objects中并且过滤掉已经确认的用户需求。*/
    foreach($objects as $objectID => $object)
    {
        if($this->app->rawModule == 'testtask') $object->id = $object->case;
        $objectID = $object->id;
        $storyID  = $objectType == 'story' ? $object->id : $object->story;
        $object->URs = isset($URs[$storyID]) ? $URs[$storyID] : '';
        if(!$object->URs) continue;

        $objectURs = explode(',', $object->URs);
        $URAction  = $objectAction = array();

        foreach($objectURs as $URID)
        {
            if(!isset($lastActions[$URID])) continue;
            if($object->openedDate > $lastActions[$URID]->date) continue;

            if(empty($URAction)) $URAction = $lastActions[$URID];
            if($URAction->date < $lastActions[$URID]->date) $URAction = $lastActions[$URID];

            if(!isset($confirmedActions[$objectID])) continue;

            $objectAction = $confirmedActions[$objectID];
            if($objectAction and $objectAction->date > $URAction->date) $URAction = array();
        }

        if($URAction)
        {
            $actionType = $URAction->action == 'retractclosed' ? 'confirmedretract' : 'confirmedunlink';
            $object->confirmeObject = array('id' => $URAction->objectID, 'type' => $actionType);
        }
    }

    if($objectInfo) return $objects[$objectInfo->id];
    return $objects;
}
