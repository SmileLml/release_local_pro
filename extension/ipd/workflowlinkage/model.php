<?php
/**
 * The model file of workflowlinkage module of ZDOO.
 *
 * @copyright   Copyright 2009-2018 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowlinkage
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowlinkageModel extends model
{
    /**
     * Process post data of a linkage.
     *
     * @access public
     * @return array
     */
    public function processPostData()
    {
        $errors  = array();
        $sources = array();
        $targets = array();

        foreach($this->post->source as $key => $field)
        {
            $operator = $this->post->operator[$key];
            $value    = $this->post->value[$key];

            if(!$field or !$operator or !$value) continue;

            if(is_array($value)) $value = implode(',', array_values(array_unique(array_filter($value))));

            $source = new stdclass();
            $source->field    = $field;
            $source->operator = $operator;
            $source->value    = $value;

            $sources[] = $source;
        }

        foreach($this->post->target as $key => $field)
        {
            $status = $this->post->status[$key];

            if(!$field or !$status) continue;

            $target = new stdclass();
            $target->field  = $field;
            $target->status = $status;

            $targets[] = $target;
        }

        if(!$sources) $errors['source'] = sprintf($this->lang->error->notempty, $this->lang->workflowlinkage->source);
        if(!$targets) $errors['target'] = sprintf($this->lang->error->notempty, $this->lang->workflowlinkage->target);

        $linkage = array('sources' => $sources, 'targets' => $targets);

        return array($linkage, $errors);
    }

    /**
     * Create a linkage.
     *
     * @param  int    $action
     * @access public
     * @return void
     */
    public function create($action)
    {
        $action   = $this->loadModel('workflowaction', 'flow')->getByID($action);
        $linkages = $action->linkages;

        list($linkage, $errors) = $this->processPostData();

        if($errors)
        {
            dao::$errors = $errors;
            return false;
        }

        if(!is_array($linkages)) $linkages = array();

        $linkages[] = $linkage;

        $this->dao->update(TABLE_WORKFLOWACTION)
            ->set('linkages')->eq(helper::jsonEncode($linkages))
            ->autoCheck()
            ->where('id')->eq($action->id)
            ->exec();

        return !dao::isError();
    }

    /**
     * Update a linkage.
     *
     * @param  int    $action
     * @param  int    $key
     * @access public
     * @return void
     */
    public function update($action, $key)
    {
        $action   = $this->loadModel('workflowaction', 'flow')->getByID($action);
        $linkages = $action->linkages;

        list($linkage, $errors) = $this->processPostData();

        if($errors)
        {
            dao::$errors = $errors;
            return false;
        }

        if(!is_array($action->linkages))
        {
            $linkages = array();
            $linkages[$key] = $linkage;
        }
        else
        {
            $linkages[$key] = $linkage;
        }

        $this->dao->update(TABLE_WORKFLOWACTION)
            ->set('linkages')->eq(helper::jsonEncode($linkages))
            ->autoCheck()
            ->where('id')->eq($action->id)
            ->exec();

        return !dao::isError();
    }

    /**
     * Delete a linkage.
     *
     * @param  int    $action
     * @param  int    $key
     * @access public
     * @return void
     */
    public function delete($action, $key)
    {
        $action   = $this->loadModel('workflowaction', 'flow')->getByID($action);
        $linkages = $action->linkages;

        if(is_array($linkages)  and isset($linkages[$key])) unset($linkages[$key]);
        if(is_object($linkages) and isset($linkages->$key))
        {
            unset($linkages->$key);
            $linkages = (array)$linkages;
        }

        /* Make sure linkages is a indexed array. */
        $linkages = array_values($linkages);

        $this->dao->update(TABLE_WORKFLOWACTION)
            ->set('linkages')->eq(helper::jsonEncode($linkages))
            ->autoCheck()
            ->where('id')->eq($action->id)
            ->exec();

        return !dao::isError();
    }
}
