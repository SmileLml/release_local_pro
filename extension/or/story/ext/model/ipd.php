<?php
/**
 * Update the story order of roadmap.
 *
 * @param  int    $storyID
 * @param  string $oldRoadmapIDList
 * @param  string $roadmapList
 * @access public
 * @return void
 */
public function updateStoryOrderOfRoadmap($storyID, $roadmapList = '', $oldRoadmapIDList = '')
{
    $roadmapList    = $roadmapList ? explode(',', $roadmapList) : array();
    $oldRoadmapIDList = $oldRoadmapIDList ? explode(',', $oldRoadmapIDList) : array();

    /* Get the ids to be inserted and deleted by comparing roadmap ids. */
    $roadmapsTobeInsert = array_diff($roadmapList, $oldRoadmapIDList);
    $roadmapsTobeDelete = array_diff($oldRoadmapIDList, $roadmapList);

    /* Delete old story sort of roadmap. */
    if(!empty($roadmapsTobeDelete)) $this->dao->delete()->from(TABLE_ROADMAPSTORY)->where('story')->eq($storyID)->andWhere('roadmap')->in($roadmapsTobeDelete)->exec();

    if(!empty($roadmapsTobeInsert))
    {
        /* Get last story order of roadmap list. */
        $maxOrders = $this->dao->select('roadmap, max(`order`) as `order`')->from(TABLE_ROADMAPSTORY)->where('roadmap')->in($roadmapsTobeInsert)->groupBy('roadmap')->fetchPairs();

        foreach($roadmapsTobeInsert as $roadmapID)
        {
            /* Set story order in new roadmap. */
            $data = new stdClass();
            $data->roadmap = $roadmapID;
            $data->story   = $storyID;
            $data->order   = zget($maxOrders, $roadmapID, 0) + 1;

            $this->dao->replace(TABLE_ROADMAPSTORY)->data($data)->exec();
        }
    }
}

/**
 * Update the story order according to the roadmap.
 *
 * @param  int    $roadmapID
 * @param  array  $sortIDList
 * @param  string $orderBy
 * @param  int    $pageID
 * @param  int    $recPerPage
 * @access public
 * @return void
 */
public function sortStoriesOfRoadmap($roadmapID, $sortIDList, $orderBy = 'id_desc', $pageID = 1, $recPerPage = 100)
{
    /* Append id for secend sort. */
    $orderBy = common::appendOrder($orderBy);

    /* Get all stories by roadmap. */
    $stories     = $this->loadModel('roadmap')->getRoadmapStories($roadmapID, 'all', $orderBy);
    $storyIDList = array_keys($stories);

    /* Calculate how many numbers there are before the sort list and after the sort list. */
    $frontStoryCount   = $recPerPage * ($pageID - 1);
    $behindStoryCount  = $recPerPage * $pageID;
    $frontStoryIDList  = array_slice($storyIDList, 0, $frontStoryCount);
    $behindStoryIDList = array_slice($storyIDList, $behindStoryCount, count($storyIDList) - $behindStoryCount);

    /* Merge to get a new sort list. */
    $newSortIDList = array_merge($frontStoryIDList, $sortIDList, $behindStoryIDList);
    if(strpos($orderBy, 'order_desc') !== false) $newSortIDList = array_reverse($newSortIDList);

    /* Loop update the story order of roadmap. */
    $order = 1;
    foreach($newSortIDList as $storyID)
    {
        $this->dao->update(TABLE_ROADMAPSTORY)->set('`order`')->eq($order)->where('story')->eq($storyID)->andWhere('roadmap')->eq($roadmapID)->exec();
        $order++;
    }
}

/**
 * Batch change the roadmap of story.
 *
 * @param  array  $storyIdList
 * @param  int    $roadmapID
 * @access public
 * @return void
 */
public function batchChangeRoadmap($storyIdList, $roadmapID)
{
    return $this->loadExtension('ipd')->batchChangeRoadmap($storyIdList, $roadmapID);
}

/**
 * Batch update stories.
 *
 * @access public
 * @return void
 */
public function batchUpdate()
{
    return $this->loadExtension('ipd')->batchUpdate();
}

/**
 * Update stories.
 *
 * @param  int    $storyID
 * @access public
 * @return void
 */
public function update($storyID)
{
    return $this->loadExtension('ipd')->update($storyID);
}

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
    static $hasShadow      = true;
    if($hasShadow and empty($shadowProducts[$story->product]))
    {
        global $dbh;
        $stmt = $dbh->query('SELECT id FROM ' . TABLE_PRODUCT . " WHERE shadow = 1")->fetchAll();
        if(empty($stmt)) $hasShadow = false;
        foreach($stmt as $row) $shadowProducts[$row->id] = $row->id;
    }

    if($story->parent < 0 and strpos($config->story->list->actionsOpratedParentStory, ",$action,") === false) return false;

    if($action == 'batchcreate' and $config->vision == 'lite' and ($story->status == 'active' and ($story->stage == 'wait' or $story->stage == 'projected'))) return true;
    /* Adjust code, hide split entry. */
    if($action == 'batchcreate' and ($story->status != 'active' or (isset($shadowProducts[$story->product])) or (!isset($shadowProducts[$story->product]) && $story->stage != 'wait') or !empty($story->plan))) return false;

    $story->reviewer  = isset($story->reviewer)  ? $story->reviewer  : array();
    $story->notReview = isset($story->notReview) ? $story->notReview : array();
    $isSuperReviewer  = strpos(',' . trim(zget($config->story, 'superReviewers', ''), ',') . ',', ',' . $app->user->account . ',');

    if($action == 'change')     return (($isSuperReviewer !== false or count($story->reviewer) == 0 or count($story->notReview) == 0) and ($story->status == 'active' or $story->status == 'launched' or $story->status == 'developing'));
    if($action == 'review')     return (($isSuperReviewer !== false or in_array($app->user->account, $story->notReview)) and $story->status == 'reviewing');

    return true;
}
