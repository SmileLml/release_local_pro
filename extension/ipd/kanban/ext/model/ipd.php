<?php
/**
 * Get Kanban cards menus by execution id.
 *
 * @param  int    $executionID
 * @param  array  $objects
 * @param  string $objecType story|bug|task
 * @access public
 * @return array
 */
public function getKanbanCardMenu($executionID, $objects, $objecType)
{
    $this->app->loadLang('execution');
    $methodName = $this->app->rawMethod;
    $menus      = parent::getKanbanCardMenu($executionID, $objects, $objecType);
    $this->loadModel('story');

    switch ($objecType)
    {
        case 'story':
            if(!isset($this->story)) $this->loadModel('story');
            $objects = $this->story->getAffectObject($objects, $objecType);

            foreach($objects as $story)
            {
                $menu = array();
                if(empty($story->confirmeObject)) continue;

                $method = $story->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
                $menu[] = array('label' => $this->lang->story->$method, 'icon' => 'search', 'url' => helper::createLink('story', $method, "objectID=$story->id&object=story&extra={$story->confirmeObject['id']}", '', true), 'size' => '95%');
                $menus[$story->id] = $menu;
            }
            break;
        case 'bug':
            if(!isset($this->bug)) $this->loadModel('bug');
            $objects = $this->story->getAffectObject($objects, $objecType);

            foreach($objects as $bug)
            {
                $menu = array();
                if(empty($bug->confirmeObject)) continue;

                $method = $bug->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
                $menu[] = array('label' => $this->lang->bug->$method, 'icon' => 'search', 'url' => helper::createLink('bug', $method, "objectID=$bug->id&object=bug&extra={$bug->confirmeObject['id']}", '', true), 'size' => '95%');
                $menus[$bug->id] = $menu;
            }
            break;
        case 'task':
            if(!isset($this->task)) $this->loadModel('task');
            $objects = $this->story->getAffectObject($objects, $objecType);

            foreach($objects as $task)
            {
                $menu = array();
                if(empty($task->confirmeObject)) continue;

                $method = $task->confirmeObject['type'] == 'confirmedretract' ? 'confirmDemandRetract' : 'confirmDemandUnlink';
                $menu[] = array('label' => $this->lang->task->$method, 'icon' => 'search', 'url' => helper::createLink('task', $method, "objectID=$task->id&object=task&extra={$task->confirmeObject['id']}", '', true), 'size' => '95%');
                $menus[$task->id] = $menu;
            }

            break;
    }

    return $menus;
}

