<?php
/**
 * The model file of workflowlayout module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowlayout
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
class workflowlayoutModel extends model
{
    /**
     * Get list of a flow.
     *
     * @param  string $module
     * @access public
     * @return array
     */
    public function getList($module)
    {
        return $this->dao->select('*')->from(TABLE_WORKFLOWLAYOUT)
            ->where('module')->eq($module)
            ->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()
            ->fetchAll();
    }

    /**
     * Get layout fields of an action.
     *
     * @param  string $module
     * @param  string $action
     * @access public
     * @return array
     */
    public function getFields($module, $action)
    {
        return $this->dao->select('t1.field, t2.name')->from(TABLE_WORKFLOWLAYOUT)->alias('t1')
            ->leftJoin(TABLE_WORKFLOWFIELD)->alias('t2')->on('t1.module = t2.module AND t1.field = t2.field')
            ->where('t1.module')->eq($module)
            ->andWhere('t1.action')->eq($action)
            ->beginIF(!empty($this->config->vision))->andWhere('t1.vision')->eq($this->config->vision)->fi()
            ->fetchPairs();
    }

    /**
     * Check the fields count show in mobile device.
     *
     * @param  string $action
     * @access public
     * @return bool
     */
    public function checkMobileShow($action)
    {
        if($action != 'browse') return true;

        $count = 0;
        foreach($this->post->show as $field => $show)
        {
            if(!$show) continue;

            if(zget($this->post->mobileShow, $field, '')) $count++;

            if($count > 5)
            {
                dao::$errors = $this->lang->workflowlayout->error->mobileShow;
                return false;
            }
        }

        return true;
    }

    /**
     * Save layout of an action.
     *
     * @param  string $module
     * @param  string $action
     * @access public
     * @return bool
     */
    public function save($module, $action)
    {
        $result = $this->checkMobileShow($action);
        if(!$result) return $result;

        $this->saveLayout($module, $action);
        if(dao::isError()) return false;

        $this->saveSubTables($action);
        if(dao::isError()) return false;

        $this->savePrevModules($module, $action);

        return !dao::isError();
    }

    /**
     * Save layout.
     *
     * @param  string $module
     * @param  string $action
     * @access public
     * @return bool
     */
    public function saveLayout($module, $action)
    {
        $fields = $this->loadModel('workflowfield')->getList($module);

        foreach($this->post->show as $field => $show)
        {
            $defaultValue = isset($this->post->defaultValue[$field]) ? $this->post->defaultValue[$field] : '';

            if($defaultValue)
            {
                $fieldInfo = $fields[$field];
                $fieldInfo->default = $defaultValue;
                $result = $this->workflowfield->checkDefaultValue($fieldInfo);

                if(is_array($result) && $result['result'] == 'fail' && is_array($result['message'])) dao::$errors["defaultValue$field"] = $result['message']['default'];
                if(is_array($result) && $result['result'] == 'fail' && is_string($result['message'])) dao::$errors["defaultValue$field"] = $result['message'];
            }
        }

        if(!empty(dao::$errors)) return false;

        $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)
            ->where('module')->eq($module)
            ->andWhere('action')->eq($action)
            ->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()
            ->exec();

        $order  = 1;
        $layout = new stdclass();
        $layout->module = $module;
        $layout->action = $action;
        foreach($this->post->show as $field => $show)
        {
            if(!$show) continue;

            /* Check width validate. */
            if(isset($this->post->width[$field]) && filter_var($this->post->width[$field], FILTER_VALIDATE_INT) === false && $this->post->width[$field] != 'auto')
            {
                dao::$errors['width' . $field] = sprintf($this->lang->error->int[0], $this->lang->workflowlayout->width);
                return false;
            }

            $defaultValue = isset($this->post->defaultValue[$field]) ? $this->post->defaultValue[$field] : '';
            if(is_array($defaultValue)) $defaultValue = implode(',', array_values(array_unique(array_filter($defaultValue))));

            $summary = isset($this->post->summary[$field]) ? $this->post->summary[$field] : '';
            if(is_array($summary)) $summary = implode(',', $summary);

            $layout->field        = $field;
            $layout->order        = $order++;
            $layout->width        = (isset($this->post->width[$field]) && $this->post->width[$field] != 'auto' && $this->post->width[$field] != '') ? $this->post->width[$field] : 0;
            $layout->position     = isset($this->post->position[$field])   ? $this->post->position[$field]   : '';
            $layout->readonly     = isset($this->post->readonly[$field])   ? $this->post->readonly[$field]   : '0';
            $layout->mobileShow   = isset($this->post->mobileShow[$field]) ? $this->post->mobileShow[$field] : '0';
            $layout->summary      = $summary;
            $layout->defaultValue = $defaultValue;
            $layout->layoutRules  = isset($this->post->layoutRules[$field]) ? implode(',', $this->post->layoutRules[$field]) : '';
            if(!empty($this->config->vision)) $layout->vision = $this->config->vision;

            $this->dao->insert(TABLE_WORKFLOWLAYOUT)->data($layout)->autoCheck()->exec();
        }

        return !dao::isError();
    }

    /**
     * Save sub tables.
     *
     * @param  string $action
     * @access public
     * @return bool
     */
    public function saveSubTables($action)
    {
        if(!$this->post->subTables) return true;

        $data = new stdclass();
        $data->action = $action;
        foreach($this->post->subTables as $subModule => $child)
        {
            $subModule = str_replace('sub_', '', $subModule);

            $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)
                ->where('module')->eq($subModule)
                ->andWhere('action')->eq($action)
                ->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()
                ->exec();

            if(!isset($this->post->show['sub_' . $subModule])) continue;

            $order = 1;

            $data->module = $subModule;
            foreach($child['show'] as $field => $show)
            {
                if(!$show) continue;

                /* Check width validate. */
                if(isset($child['width'][$field]) && filter_var($child['width'][$field], FILTER_VALIDATE_INT) === false && $child['width'][$field] != 'auto')
                {
                    dao::$errors['subTablessub_' . $subModule . 'width' . $field] = sprintf($this->lang->error->int[0], $this->lang->workflowlayout->width);
                    return false;
                }

                $defaultValue = isset($child['defaultValue'][$field]) ? $child['defaultValue'][$field] : '';
                if(is_array($defaultValue)) $defaultValue = implode(',', array_values(array_unique(array_filter($defaultValue))));

                $summary = isset($child['summary'][$field]) ? $child['summary'][$field] : '';
                if(is_array($summary)) $summary = implode(',', $summary);

                $data->field        = $field;
                $data->order        = $order++;
                $data->width        = (isset($child['width'][$field]) && $child['width'][$field] != 'auto' && $child['width'][$field] != '') ? $child['width'][$field] : 0;
                $data->position     = '';
                $data->readonly     = isset($child['readonly'][$field])   ? $child['readonly'][$field]   : '0';
                $data->mobileShow   = isset($child['mobileShow'][$field]) ? $child['mobileShow'][$field] : '0';
                $data->summary      = $summary;
                $data->defaultValue = $defaultValue;
                $data->layoutRules  = isset($child['layoutRules'][$field]) ? implode(',', $child['layoutRules'][$field]) : '';
                if(!empty($this->config->vision)) $data->vision = $this->config->vision;

                $this->dao->insert(TABLE_WORKFLOWLAYOUT)->data($data)->autoCheck()->exec();
            }
        }

        return !dao::isError();
    }

    /**
     * Save the layout of prev modules.
     *
     * @param  string $module
     * @param  string $action
     * @access public
     * @return bool
     */
    public function savePrevModules($module, $action)
    {
        if(!$this->post->prevModules) return true;

        $data = new stdclass();
        $data->next   = $module;
        $data->action = $action;
        foreach($this->post->prevModules as $prevModule => $prev)
        {
            $this->dao->delete()->from(TABLE_WORKFLOWRELATIONLAYOUT)
                ->where('prev')->eq($prevModule)
                ->andWhere('next')->eq($module)
                ->andWhere('action')->eq($action)
                ->exec();

            $order = 1;

            $data->prev = $prevModule;
            foreach($prev['show'] as $field => $show)
            {
                if(!$show) continue;

                $data->field = $field;
                $data->order = $order++;

                $this->dao->insert(TABLE_WORKFLOWRELATIONLAYOUT)->data($data)->autoCheck()->exec();
            }
        }

        return !dao::isError();
    }

    /**
     * Save blocks.
     *
     * @param  int    $module
     * @access public
     * @return void
     */
    public function saveBlocks($module, $oldBlocks)
    {
        $blocks = array();
        foreach($this->post->blockName as $key => $blockName)
        {
            if(empty($blockName)) continue;

            $block = array();
            $block['name']     = $blockName;
            $block['showName'] = isset($this->post->showName[$key]) ? $this->post->showName[$key] : '0';

            $currentKey = $this->post->key[$key];

            $block['tabs'] = array();
            if($this->post->parent)
            {
                foreach($this->post->parent as $parentKey => $tabParent)
                {
                    if($currentKey == $tabParent && !empty($this->post->tabName[$parentKey])) $block['tabs'][$parentKey] = $this->post->tabName[$parentKey];
                }
            }

            $blocks[$key] = $block;
        }

        /* Delete fields from layout when delete their block or tab. */
        foreach($oldBlocks as $oldBlockKey => $oldBlock)
        {
            if(!isset($blocks[$oldBlockKey]))
            {
                $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)
                    ->where('module')->eq($module)
                    ->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()
                    ->andWhere('position', true)->eq("block{$oldBlockKey}")
                    ->orWhere('position')->like("block{$oldBlockKey}\_tab%")
                    ->markRight(1)
                    ->exec();
                continue;
            }

            if(!empty($oldBlock['tabs']))
            {
                foreach($oldBlock['tabs'] as $oldTabKey => $oldTab)
                {
                    if(!isset($blocks[$oldBlockKey]['tabs'][$oldTabKey]))
                    {
                        $this->dao->delete()->from(TABLE_WORKFLOWLAYOUT)
                            ->where('module')->eq($module)
                            ->beginIF(!empty($this->config->vision))->andWhere('vision')->eq($this->config->vision)->fi()
                            ->andWhere('position')->eq("block{$oldBlockKey}_tab{$oldTabKey}")
                            ->exec();
                    }
                }
            }
        }

        $this->dao->update(TABLE_WORKFLOWACTION)->set('blocks')->eq(helper::jsonEncode($blocks))->where('module')->eq($module)->andWhere('action')->eq('view')->exec();

        return !dao::isError();
    }
}
