<?php
/**
 * The model file of cm module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     cm
 * @version     $Id: model.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class cmModel extends model
{
    /**
     * Get base line list.
     *
     * @param  int    $projectID
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getList($projectID, $orderBy, $pager = null)
    {
        return $this->dao->select('*')->from(TABLE_OBJECT)
            ->where('project')->eq($projectID)
            ->andWhere('type')->eq('taged')
            ->andWhere('deleted')->eq(0)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();
    }

    /**
     * Get a base line.
     *
     * @param  int    $baselineID
     * @access public
     * @return object
     */
    public function getByID($baselineID)
    {
        return $this->dao->select('t1.*, t2.template, t2.doc, t2.docVersion')->from(TABLE_OBJECT)->alias('t1')
            ->leftJoin(TABLE_REVIEW)->alias('t2')
            ->on('t1.from = t2.object')
            ->where('t1.id')->eq($baselineID)
            ->fetch();
    }

    /**
     * Get base line pairs.
     *
     * @param  int    $projectID
     * @param  int    $productID
     * @access public
     * @return array
     */
    public function getPairsForGantt($projectID, $productID)
    {
        return $this->dao->select('id, version')->from(TABLE_OBJECT)
            ->where('project')->eq($projectID)
            ->andWhere('product')->eq($productID)
            ->andWhere('type')->eq('taged')
            ->andWhere('category')->eq('PP')
            ->andWhere('deleted')->eq(0)
            ->fetchPairs();
    }

    /**
     * Create a base line.
     *
     * @param  int    $projectID
     * @access public
     * @return int
     */
    public function create($projectID)
    {
        $baseline = fixer::input('post')
            ->add('range', 'all')
            ->add('type', 'taged')
            ->add('project', $projectID)
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', helper::today())
            ->setDefault('product', 0)
            ->get();

        $range = $this->dao->findByID($baseline->from)->from(TABLE_OBJECT)->fetch('range');
        $data  = $this->getDataByObject($projectID, $baseline->product, $baseline->category, $range);
        $baseline->storyEst   = isset($data['storyEst']) ? $data['storyEst'] : 0;
        $baseline->taskEst    = isset($data['taskEst']) ? $data['taskEst'] : 0;
        $baseline->testEst    = isset($data['testEst']) ? $data['testEst'] : 0;
        $baseline->requestEst = isset($data['requestEst']) ? $data['requestEst'] : 0;
        $baseline->devEst     = isset($data['devEst']) ? $data['devEst'] : 0;
        $baseline->designEst  = isset($data['designEst']) ? $data['designEst'] : 0;
        unset($data['totalEstimate']);
        unset($data['testEst']);
        unset($data['requestEst']);
        unset($data['devEst']);
        unset($data['designEst']);
        $baseline->range = $range;
        $baseline->data  = json_encode($data);

        foreach(explode(',', $this->config->cm->create->requiredFields) as $field) $this->lang->object->$field = $this->lang->cm->$field;

        $this->dao->insert(TABLE_OBJECT)->data($baseline)
            ->autoCheck()
            ->batchCheck($this->config->cm->create->requiredFields, 'notempty')
            ->exec();

        if(!dao::isError()) return $this->dao->lastInsertID();
    }

    /**
     * Update a base line.
     *
     * @param  int    $baselineID
     * @access public
     * @return array
     */
    public function update($baselineID)
    {
        $oldBaseline = $this->getByID($baselineID);
        $baseline = fixer::input('post')->get();

        $range           = $this->dao->findByID($baseline->from)->from(TABLE_OBJECT)->fetch('range');
        $data            = $this->getDataByObject($oldBaseline->project, $baseline->product, $baseline->category, $range);
        $baseline->data  = json_encode($data);
        $baseline->range = $range;

        foreach(explode(',', $this->config->cm->edit->requiredFields) as $field) $this->lang->object->$field = $this->lang->cm->$field;
        $this->dao->update(TABLE_OBJECT)->data($baseline)->autoCheck()->batchCheck($this->config->cm->edit->requiredFields, 'notempty')->where('id')->eq($baselineID)->exec();

        unset($baseline->data);
        unset($baseline->range);
        return common::createChanges($oldBaseline, $baseline);
    }

    /**
     * Get data info by object.
     *
     * @param  int    $projectID
     * @param  int    $productID
     * @param  string $objectType
     * @param  int    $range
     * @access public
     * @return array
     */
    public function getDataByObject($projectID, $productID, $objectType, $range)
    {
        $data = array();
        $this->loadModel('review');
        $checkedItem = $range;
        if($objectType == 'PP') $data = $this->review->getDataFromPP($projectID, $objectType, $productID);
        if($objectType == 'SRS' || $objectType == 'URS')  $data = $this->review->getDataFromStory($projectID, $objectType, $productID, $range, $checkedItem);
        if(in_array($objectType, array('HLDS', 'DDS', 'DBDS', 'ADS'))) $data = $this->review->getDataFromDesign($projectID, $objectType, $productID, $range, $checkedItem);
        if($objectType == 'ITTC' || $objectType == 'STTC') $data = $this->review->getDataFromCase($projectID, $objectType, $productID, $range, $checkedItem);

        return $data;
    }

    /**
     * Get report data.
     *
     * @param  int    $projectID
     * @param  string $type
     * @param  string $orderBy
     * @access public
     * @return array
     */
    public function getReportInfo($projectID, $type = 'audit', $orderBy = 't2.id')
    {
        $project = $this->loadModel('project')->getByID($projectID);

        $issue = $this->loadModel('reviewissue')->getProjectIssue($projectID, $type, $orderBy);

        $audit = $this->dao->select('*')->from(TABLE_OBJECT)
            ->where('project')->eq($projectID)
            ->andWhere('type')->eq('taged')
            ->andWhere('deleted')->eq(0)
            ->fetchAll();

        $auditItem = $this->lang->cm->auditItem;
        $auditList = array();
        foreach($audit as $item)
        {
            if(isset($auditItem[$item->category])) 
            {
                $category = $auditItem[$item->category];
                $auditList[$category][] = $item;
            }
            else
            {
                $auditList['custom'][] = $item;
            }
        }

        return array('project' => $project, 'audit' => $auditList, 'issue' => $issue);
    }
}
