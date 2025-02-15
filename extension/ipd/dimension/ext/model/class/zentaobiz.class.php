<?php
/**
 * The model file of ddimension module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2022 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chenxuan Song <1097180981@qq.com>
 * @package     dimension
 * @version     $Id: model.php 5086 2022-11-1 10:26:23Z $
 * @link        http://www.zentao.net
 */
class zentaobizDimension extends dimensionModel
{
    /**
     * Check whether a dimension have BI content.
     *
     * @param  int    $dimensionID
     * @access public
     * @return bool
     */
    public function checkBIContent($dimensionID)
    {
        $charts = $this->loadModel('chart')->getList($dimensionID);
        if(count($charts)) return true;

        $pivots = $this->loadModel('pivot')->getList($dimensionID);
        if(count($pivots)) return true;

        $screens = $this->loadModel('screen')->getList($dimensionID);
        return count($screens) > 0;
    }

    /**
     * Create a dimension.
     *
     * @access public
     * @return int
     */
    public function create()
    {
        $dimension = fixer::input('post')
            ->setDefault('createdBy', $this->app->user->account)
            ->setDefault('createdDate', helper::now())
            ->get();
        $dimension->code = str_replace(' ', '', $dimension->code);

        if(empty($dimension->code))
        {
            dao::$errors['code'] = sprintf($this->lang->error->notempty, $this->lang->dimension->code);
            return false;
        }

        if(!validater::checkCode($dimension->code))
        {
            dao::$errors['code'] = sprintf($this->lang->dimension->errorCode, $this->lang->dimension->code);
            return false;
        }

        $this->dao->insert(TABLE_DIMENSION)->data($dimension)
            ->autoCheck()
            ->batchCheck($this->config->dimension->create->requiredFields, 'notempty')
            ->checkIF(!empty($dimension->name), 'name', 'unique')
            ->checkIF(!empty($dimension->code), 'code', 'unique')
            ->exec();

        $dimensionID = $this->dao->lastInsertID();

        $this->loadModel('upgrade')->addDefaultModules4BI('chart', $dimensionID);
        $this->loadModel('upgrade')->addDefaultModules4BI('pivot', $dimensionID);

        return $dimensionID;
    }

    /**
     * Update a dimension.
     *
     * @param  int    $dimensionID
     * @access public
     * @return bool|array
     */
    public function update($dimensionID)
    {
        $oldDimension = $this->getById($dimensionID);
        $dimension    = fixer::input('post')
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', helper::now())
            ->get();
        $dimension->code = str_replace(' ', '', $dimension->code);

        if(!validater::checkCode($dimension->code))
        {
            dao::$errors['code'] = sprintf($this->lang->dimension->errorCode, $this->lang->dimension->code);
            return false;
        }

        $this->dao->update(TABLE_DIMENSION)->data($dimension)
            ->autoCheck()
            ->batchcheck($this->config->dimension->edit->requiredFields, 'notempty')
            ->checkIF(!empty($dimension->name), 'name', 'unique', "id != {$dimensionID}")
            ->checkIF(!empty($dimension->code), 'code', 'unique', "id != {$dimensionID}")
            ->where('id')->eq($dimensionID)
            ->exec();

        if(dao::isError()) return false;

        return common::createChanges($oldDimension, $dimension);
    }
}
