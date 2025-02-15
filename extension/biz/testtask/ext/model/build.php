<?php
    function buildOperateBrowseMenu($task)
    {
        $menu   = '';
        $params = "taskID=$task->id";
        $username = $this->app->user->account;
        $menu .= '<div id="action-divider">';
        $menu .= common::buildMyIconButton('testtask',   'autorun',    "tid=$task->id&username=$username", $task,'browse',  'play' ,'', 'iframe', true, "data-width='100%' data-height='800px'");
        $menu .= $this->buildMenu('testtask',   'cases',    $params, $task, 'browse', 'sitemap');
        $menu .= $this->buildMenu('testtask',   'linkCase', "$params&type=all&param=myQueryID", $task, 'browse', 'link');
        $menu .= $this->buildMenu('testreport', 'browse',   "objectID=$task->product&objectType=product&extra=$task->id", $task, 'browse', 'summary', '', '', false, '', $this->lang->testreport->common);
        $menu .= '</div>';
        $menu .= $this->buildMenu('testtask',   'view',     $params, $task, 'browse', 'list-alt', '', 'iframe', true, "data-width='90%'");
        $menu .= $this->buildMenu('testtask',   'edit',     $params, $task, 'browse');
        $clickable = $this->buildMenu('testtask', 'delete', $params, $task, 'browse', '', '', '', '', '', '', false);
        if(common::hasPriv('testtask', 'delete', $task))
        {
            $deleteURL = helper::createLink('testtask', 'delete', "taskID=$task->id&confirm=yes");
            $class = 'btn';
            if(!$clickable) $class .= ' disabled';
            $menu .= html::a("javascript:ajaxDelete(\"$deleteURL\",\"taskList\",confirmDelete)", '<i class="icon-common-delete icon-trash"></i>', '', "title='{$this->lang->testtask->delete}' class='{$class}'");
        }
        return $menu;
    }