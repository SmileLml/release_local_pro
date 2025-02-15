<?php
/**
 * The control file of cm module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     cm
 * @version     $Id: control.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class cm extends control
{
    public function commonAction($projectID)
    {
        $this->app->loadLang('baseline');
        $this->loadModel('project')->setMenu($projectID);
    }

    /**
     * Browse cm.
     *
     * @param  int    $projectID
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($projectID = 0, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->commonAction($projectID);

        $this->app->loadLang('review');
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $this->view->title     = $this->lang->cm->browse;
        $this->view->projectID = $projectID;
        $this->view->pager     = $pager;
        $this->view->orderBy   = $orderBy;
        $this->view->users     = $this->loadModel('user')->getPairs('noletter|nodeleted');
        $this->view->baselines = $this->cm->getList($projectID, $orderBy, $pager);
        $this->display();
    }

    /**
     * Cm report.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function report($projectID)
    {
        $this->commonAction($projectID);

        $this->app->loadLang('reviewissue');
        $this->app->loadLang('review');
        $this->app->loadLang('project');

        $this->view->title  = $this->lang->cm->report;
        $this->view->report = $this->cm->getReportInfo($projectID);
        $this->view->users  = $this->loadModel('user')->getPairs('noclosed|noletter');

        $this->display();
    }

    /**
     * Create a cm.
     *
     * @param  int    $projectID
     * @param  int    $reviewID
     * @access public
     * @return void
     */
    public function create($projectID, $reviewID = 0)
    {
        $this->commonAction($projectID);

        $review = $this->loadModel('review')->getByID($reviewID);
        if($_POST)
        {
            $baselineID = $this->cm->create($projectID);

            if(dao::isError())
            {
                $result['result']  = 'fail';
                $result['message'] = dao::getError();
                return $this->send($result);
            }

            $this->loadModel('action')->create('cm', $baselineID, 'Opened', '');

            $result['result']  = 'success';
            $result['message'] = $this->lang->saveSuccess;
            $result['locate']  = inlink('browse', "project=$projectID");
            return $this->send($result);
        }

        $this->view->title     = $this->lang->cm->create;
        $this->view->review    = $review;
        $this->view->projectID = $projectID;

        $this->display();
    }

    /**
     * Edit a baseline.
     *
     * @param  int    $baselineID
     * @access public
     * @return void
     */
    public function edit($baselineID)
    {
        $baseline = $this->cm->getByID($baselineID);
        $this->commonAction($baseline->project);

        if($_POST)
        {
            $changes = $this->cm->update($baselineID);
            if(!empty($changes))
            {
                $actionID = $this->loadModel('action')->create('cm', $baselineID, 'Edited');
                $this->action->logHistory($actionID, $changes);
            }

            if(dao::isError())
            {
                $result['result']  = 'fail';
                $result['message'] = dao::getError();
                return $this->send($result);
            }

            $result['result']  = 'success';
            $result['message'] = $this->lang->saveSuccess;
            $result['locate']  = inlink('view', "baselineID=$baselineID");
            return $this->send($result);
        }

        $this->view->title     = $this->lang->cm->view;
        $this->view->baseline  = $baseline;
        $this->view->projectID = $baseline->project;

        $this->display();
    }

    /**
     * View a baseline.
     *
     * @param  int    $baselineID
     * @access public
     * @return void
     */
    public function view($baselineID)
    {
        $baseline = $this->cm->getByID($baselineID);
        $this->commonAction($baseline->project);

        $this->setViewData($baseline);

        $selectCustom = 0;
        $dateDetails  = 1;
        if($baseline->category == 'PP')
        {
            $owner        = $this->app->user->account;
            $module       = 'programplan';
            $section      = 'browse';
            $object       = 'stageCustom';
            $setting      = $this->loadModel('setting');
            $selectCustom = $setting->getItem("owner={$owner}&module={$module}&section={$section}&key={$object}");

            if(strpos($selectCustom, 'date') !== false) $dateDetails = 0;
        }

        $this->view->title        = $this->lang->cm->view;
        $this->view->baseline     = $baseline;
        $this->view->object       = $baseline;
        $this->view->actions      = $this->loadModel('action')->getList('cm', $baselineID);
        $this->view->users        = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->projectID    = $baseline->project;
        $this->view->selectCustom = $selectCustom;
        $this->view->dateDetails  = $dateDetails;
        $this->view->ganttType    = 'gantt';
        $this->view->productID    = $baseline->product;
        $this->display();
    }

    /**
     * Set data to view.
     *
     * @param  int    $baseline
     * @access public
     * @return void
     */
    public function setViewData($baseline)
    {
        $this->loadModel('review');
        if($baseline->category == 'PP')
        {
            $this->view->plans = $this->loadModel('programplan')->getDataForGantt($baseline->project, $baseline->product, $baseline->id, '', false);
        }
        else
        {
            if($baseline->doc)
            {
                $doc = $this->loadModel('doc')->getById($baseline->doc, $baseline->docVersion);
                if($doc->contentType == 'markdown') $doc->content = commonModel::processMarkdown($doc->content);

                $this->view->doc = $doc;
            }

            if(!$baseline->template) return;
            $template = $this->loadModel('doc')->getByID($baseline->template);

            if($template->type == 'book')
            {
                $this->view->bookID = $template->id;
                $this->view->book   = $template;
            }

            $this->view->template = $template;
        }
    }

    /**
     * Delete a baseline.
     *
     * @param  int    $baselineID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($baselineID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            echo js::confirm($this->lang->cm->confirmDelete, $this->createLink('cm', 'delete', "baselineID=$baselineID&confirm=yes"), '');
            exit;
        }
        else
        {
            $this->cm->delete(TABLE_OBJECT, $baselineID);
            die(js::reload('parent.parent'));
        }
    }

    /**
     * Ajax get reviews by project id.
     *
     * @param  string $category
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function ajaxGetReviews($category = '', $projectID = '')
    {
        $reviews = $this->dao->select('t2.id, t1.title, t2.version')->from(TABLE_REVIEW)->alias('t1')
            ->leftJoin(TABLE_OBJECT)->alias('t2')->on('t1.object=t2.id')
            ->where('t2.category')->eq($category)
            ->andWhere('t1.status')->eq('done')
            ->beginIF($projectID)->andWhere('t1.project')->eq($projectID)->fi()
            ->fetchAll();
        $pairs = array('' => '');
        foreach($reviews as $review) $pairs[$review->id] = $review->title . '-' . $review->version;

        die(html::select('from', $pairs, '', "class='form-control chosen' onchange=getProduct(this.value)"));
    }

    public function ajaxGetProduct($objectID)
    {
        $productID = $this->dao->findByID($objectID)->from(TABLE_OBJECT)->fetch('product');
        die($productID);
    }
}
