<?php
/**
 * Sync story status.
 *
 * @param  int $storyID
 * @access public
 * @return void
 */
public function close($storyID)
{
    $changes = parent::close($storyID);
    if(!$changes) return false;

    $this->syncClose($storyID);
    return $changes;
}

/**
 * Sync story status.
 *
 * @access public
 * @return void
 */
public function batchClose()
{
    $allChanges = parent::batchClose();
    if(!$allChanges) return false;

    foreach($allChanges as $storyID => $changes) $this->syncClose($storyID);
    return $allChanges;
}

/**
 * Sync story status.
 *
 * @param  int $storyID
 * @access public
 * @return void
 */
public function syncClose($storyID)
{
    $story = $this->getById($storyID);
    if($story->type == 'requirement') return false;

    /* Get all linked requirements.*/
    $relations = $this->getRelation($storyID, $story->type);
    if(empty($relations)) return false;

    /* Get requirement all related stories.*/
    foreach($relations as $id => $title)
    {
        $stories = $this->getRelation($id, 'requirement');

        $storiesStatus = $this->dao->select('status')->from(TABLE_STORY)
            ->where('id')->in(array_keys($stories))
            ->fetchPairs();

        $allClosed = true;
        foreach($storiesStatus as $status)
        {
            if($status != 'closed') $allClosed = false;
        }

        if($allClosed)
        {
            $data = new stdclass();
            $data->assignedTo     = 'closed';
            $data->status         = 'closed';
            $data->lastEditedBy   = $this->app->user->account;
            $data->lastEditedDate = helper::now();
            $data->assignedDate   = helper::now();
            $data->closedDate     = helper::now();
            $data->closedBy       = $this->app->user->account;

            $this->dao->update(TABLE_STORY)->data($data)
                ->autoCheck()
                ->where('id')->eq($id)->exec();
        }
    }
}

/**
 * Print cell data
 *
 * @param  object $col
 * @param  object $story
 * @param  array  $users
 * @param  array  $branches
 * @param  array  $storyStages
 * @param  array  $modulePairs
 * @param  array  $storyTasks
 * @param  array  $storyBugs
 * @param  array  $storyCases
 * @access public
 * @return void
 */
public function printCell($col, $story, $users, $branches, $storyStages, $modulePairs = array(), $storyTasks = array(), $storyBugs = array(), $storyCases = array(), $mode = 'datatable', $storyType = 'story', $execution = '', $isShowBranch = '')
{
    if($col->id == 'SRS' && $col->show && $story->type != 'story')
    {
        $link    = helper::createLink('story', 'relation', "storyID=$story->id&storyType=$story->type");
        $storySR = $this->getStoryRelationCounts($story->id, $story->type);
        echo $storySR > 0 ? '<td class="datatable-cell c-SRS text-center">' . html::a($link, $storySR, '', 'class="iframe"') . '</td>' : '<td class="datatable-cell c-SRS text-center"></td>';
    }
    elseif($col->id == 'URS' && $col->show)
    {
        $link    = helper::createLink('story', 'relation', "storyID=$story->id&storyType=$story->type");
        $storySR = $this->getStoryRelationCounts($story->id, $story->type);
        echo $storySR > 0 ? '<td class="datatable-cell c-URS text-center">' . html::a($link, $storySR, '', 'class="iframe"') . '</td>' : '<td class="datatable-cell c-URS text-center">0</td>';
    }
    else
    {
        parent::printCell($col, $story, $users, $branches, $storyStages, $modulePairs, $storyTasks, $storyBugs, $storyCases, $mode, $storyType, $execution, $isShowBranch);
    }
}

/**
 * Import story to asset lib.
 *
 * @param  int|array|string  $storyIDList
 * @access public
 * @return bool
 */
public function importToLib($storyIDList = 0)
{
    $data = fixer::input('post')->get();
    if(empty($data->lib))
    {
        dao::$errors[] = sprintf($this->lang->error->notempty, $this->lang->story->lib);
        return false;
    }

    $stories         = $this->getByList($storyIDList);
    $importedStories = $this->dao->select('fromStory,fromVersion')->from(TABLE_STORY)
        ->where('lib')->eq($data->lib)
        ->andWhere('fromStory')->in($storyIDList)
        ->fetchGroup('fromStory');

    if(is_numeric($storyIDList) and isset($importedStories[$storyIDList]))
    {
        dao::$errors[] = $this->lang->story->isExist;
        return false;
    }

    /* Remove duplicate story. */
    foreach($stories as $story)
    {
        if(isset($importedStories[$story->id]))
        {
            foreach($importedStories[$story->id] as $improtedStory)
            {
                if($improtedStory->fromVersion == $story->version) unset($stories[$story->id]);
            }
        }
    }

    $now           = helper::now();
    $today         = helper::today();
    $hasApprovePiv = common::hasPriv('assetlib', 'approveStory') or common::hasPriv('assetlib', 'batchApproveStory');
    $this->loadModel('action');

    /* Create story to asset lib. */
    foreach($stories as $story)
    {
        $assetStory = new stdclass();
        $assetStory->title       = $story->title;
        $assetStory->keywords    = $story->keywords;
        $assetStory->pri         = $story->pri;
        $assetStory->estimate    = $story->estimate;
        $assetStory->status      = $hasApprovePiv ? 'active' : 'draft';
        $assetStory->category    = $story->category;
        $assetStory->lib         = $data->lib;
        $assetStory->fromStory   = $story->id;
        $assetStory->fromVersion = $story->version;
        $assetStory->openedBy    = $this->app->user->account;
        $assetStory->openedDate  = $now;
        if(!empty($data->assignedTo)) $assetStory->assignedTo = $data->assignedTo;
        if($hasApprovePiv)
        {
            $assetStory->assignedTo   = $this->app->user->account;
            $assetStory->approvedDate = $today;
        }

        $this->dao->insert(TABLE_STORY)->data($assetStory)->exec();
        $assetStoryID = $this->dao->lastInsertID();

        $storySpec          = new stdclass();
        $storySpec->story   = $assetStoryID;
        $storySpec->version = 1;
        $storySpec->title   = $story->title;
        $storySpec->spec    = $story->spec;
        $storySpec->verify  = $story->verify;
        $this->dao->insert(TABLE_STORYSPEC)->data($storySpec)->exec();

        if(!dao::isError()) $this->action->create('story', $assetStoryID, 'import2StoryLib');
    }

    return true;
}

/**
 * Replace story lang to requirement.
 *
 * @param  string   $type
 * @access public
 * @return void
 */
public function replaceURLang($type)
{
    if($type == 'story') unset($this->lang->story->sourceList['researchreport']);
    parent::replaceURLang($type);
}
