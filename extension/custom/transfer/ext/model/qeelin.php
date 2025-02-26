<?php

/**
 * Export module data.
 *
 * @param  string $model
 * @access public
 * @return void
 */
public function export($model = '')
{
    parent::export($model);
    $rows = isset($_POST['rows']) ? $_POST['rows'] : [];
    if($model == 'bug') foreach($rows as $id => $row) if(isset($row->plan) && !$row->plan) $rows[$id]->plan = '';
    $this->post->set('rows', $rows);
}

/**
 * Get query datas.
 *
 * @param  string $model
 * @access public
 * @return void
 */
public function getQueryDatas($model = '')
{
    $queryCondition    = $this->session->{$model . 'QueryCondition'};
    $onlyCondition     = $this->session->{$model . 'OnlyCondition'};
    $transferCondition = $this->session->{$model . 'TransferCondition'};

    $modelDatas = array();

    if($transferCondition)
    {
        $selectKey = 'id';
        $stmt = $this->dbh->query($transferCondition);
        while($row = $stmt->fetch())
        {
            if($selectKey !== 't1.id' and isset($row->$model) and isset($row->id)) $row->id = $row->$model;
            $modelDatas[$row->id] = $row;
        }

        return $modelDatas;
    }

    /* Fetch the scene's cases. */
    if($model == 'testcase') $queryCondition = preg_replace("/AND\s+t[0-9]\.scene\s+=\s+'0'/i", '', $queryCondition);

    $checkedItem = $this->post->checkedItem ? $this->post->checkedItem : $this->cookie->checkedItem;
    if($onlyCondition and $queryCondition)
    {
        $table = zget($this->config->objectTables, $model);
        if(isset($this->config->$model->transfer->table)) $table = $this->config->$model->transfer->table;
        $modelDatas = $this->dao->select('*')->from($table)->alias('t1')
            ->where($queryCondition)
            ->beginIF($this->post->exportType == 'selected')->andWhere('t1.id')->in($checkedItem)->fi()
            ->fetchAll('id');
    }
    elseif($queryCondition)
    {
        $selectKey = 'id';
        if($model == 'testcase') $model = 'case';
        preg_match_all('/[`"]' . $this->config->db->prefix . $model .'[`"] AS ([\w]+) /', $queryCondition, $matches);
        if(isset($matches[1][0])) $selectKey = "{$matches[1][0]}.id";

        $stmt = $this->dbh->query($queryCondition . ($this->post->exportType == 'selected' ? " AND $selectKey IN(" . ($checkedItem ? $checkedItem : '0') . ")" : ''));
        while($row = $stmt->fetch())
        {
            if($selectKey !== 't1.id' and isset($row->$model) and isset($row->id)) $row->id = $row->$model;
            $modelDatas[$row->id] = $row;
        }
    }

    if($model == 'bug')
    {
        $users = $this->user->getPairs('noletter');
        $pattern     = '/\sonload="[^"]*"/';
        $replacement = '';
        foreach($modelDatas as $row)
        {
            $actions = $this->loadModel('action')->getList('bug', $row->id);
            $comments = array();
            foreach($actions as $action)
            {
                unset($action->history);
                if($action->action == 'commented' || (in_array($action->action, $this->config->bug->exportActionCommentType) && !empty($action->comment)))
                {
                    $action->actor = isset($users[$action->actor]) ? $users[$action->actor] : '';
                    $action = $this->loadModel('file')->processImgURL($action, 'comment');
                    $action->comment = preg_replace($pattern, $replacement, $action->comment);
                    $comments[$action->id] = $action;
                }
            }
            $row->comments = $comments;
        }
    }
    return $modelDatas;
}