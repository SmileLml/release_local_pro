<?php
/**
 * The control file of review module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     review
 * @version     $Id: control.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class review extends control
{
    /**
     * Review Common action.
     *
     * @param  int    $projectID
     * @access public
     * @return void
     */
    public function commonAction($projectID)
    {
        $this->app->loadLang('baseline');
        if($this->app->tab == 'project') $this->loadModel('project')->setMenu($projectID);

        $project = $this->loadModel('project')->getByID($projectID);
        $this->session->set('hasProduct', $project->hasProduct);
    }

    /**
     * Browse reviews.
     *
     * @param  int    $projectID
     * @param  string $browseType
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($projectID, $browseType = 'all', $orderBy = 't1.id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->commonAction($projectID);
        $this->loadModel('datatable');
        $this->session->set('reviewList', $this->app->getURI(true), 'project');
        $browseType = strtolower($browseType);

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        $reviewList = $this->review->getList($projectID, $browseType, $orderBy, $pager);

        $this->view->title      = $this->lang->review->browse;
        $this->view->position[] = $this->lang->review->browse;

        $this->view->reviewList     = $reviewList;
        $this->view->users          = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->pager          = $pager;
        $this->view->recTotal       = $recTotal;
        $this->view->recPerPage     = $recPerPage;
        $this->view->pageID         = $pageID;
        $this->view->orderBy        = $orderBy;
        $this->view->browseType     = $browseType;
        $this->view->products       = $this->loadModel('product')->getPairs($projectID);
        $this->view->projectID      = $projectID;
        $this->view->pendingReviews = $this->loadModel('approval')->getPendingReviews('review');
        $this->view->reviewers      = $this->review->getReviewerByIdList(array_keys($reviewList));

        $this->display();
    }

    /**
     * Assess a review.
     *
     * @param  int    $reviewID
     * @param  string $from  work|contribute
     * @param  string $type  gantt|assignedTo
     * @access public
     * @return void
     */
    public function assess($reviewID = 0, $from = '', $type = 'gantt')
    {
        $this->loadModel('stage');
        $this->loadModel('project');

        $review = $this->review->getByID($reviewID);
        $this->commonAction($review->project);

        if($_POST)
        {
            $this->review->saveResult($reviewID, 'review');

            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $reviewList = $this->session->reviewList ? $this->session->reviewList : inlink('browse', "project=$review->project");
            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $reviewList));
        }

        if($this->app->tab == 'my')
        {
            if($from == 'contribute') $this->lang->my->menu->contribute['subModule'] = 'review';
        }

        $project      = $this->loadModel('project')->getByID($review->project);
        $projectModel = $project->model == 'ipd' ? 'waterfall' : $project->model;

        $this->setViewData($review, $type);
        $this->view->title        = $this->lang->review->common;
        $this->view->review       = $review;
        $this->view->object       = $review;
        $this->view->projectID    = $review->project;
        $this->view->result       = $this->review->getResultByUser($reviewID);
        $this->view->issues       = $this->loadmodel('reviewissue')->getIssueByReview($reviewID, $review->project);
        $this->view->reviewcl     = $this->loadModel('reviewcl')->getList($review->category, 'id_desc', null, $project->model);
        $this->view->categoryList = $this->lang->reviewcl->{$projectModel . 'CategoryList'};
        $this->view->users        = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->reviewID     = $reviewID;
        $this->view->type         = $type;

        $this->display();
    }

    /**
     * Set data to review page.
     *
     * @param  object $review
     * @param  string $type
     * @access public
     * @return void
     */
    public function setViewData($review, $type = '')
    {
        if($review->category == 'PP')
        {
            $selectCustom = 0;
            $dateDetails  = 1;
            if($review->category == 'PP')
            {
                $owner        = $this->app->user->account;
                $module       = 'programplan';
                $section      = 'browse';
                $object       = 'stageCustom';
                $setting      = $this->loadModel('setting');
                $selectCustom = $setting->getItem("owner={$owner}&module={$module}&section={$section}&key={$object}");

                if(strpos($selectCustom, 'date') !== false) $dateDetails = 0;
            }

            if($type == 'assignedTo')
            {
                $this->view->plans = $this->loadModel('programplan')->getDataForGanttGroupByAssignedTo($review->project, $review->product, 0, $selectCustom, false);
            }
            else
            {
                $this->view->plans = $this->loadModel('programplan')->getDataForGantt($review->project, $review->product, 0, $selectCustom, false);
            }

            $this->view->selectCustom = $selectCustom;
            $this->view->dateDetails  = $dateDetails;
        }
        else
        {
            if($review->doc) $this->view->doc = $this->loadModel('doc')->getById($review->doc, $review->docVersion);

            if(!$review->template) return;
            $template = $this->loadModel('doc')->getByID($review->template);

            if($template->type == 'book')
            {
                $this->view->bookID = $template->id;
                $this->view->book   = $template;
            }

            $this->view->template = $template;
        }
    }

    /**
     * Create a review.
     *
     * @param  int     $projectID
     * @param  string  $object
     * @param  int     $productID
     * @param  string  $reviewRange
     * @param  string  $checkedItem
     * @access public
     * @return void
     */
    public function create($projectID = 0, $object = '', $productID = 0, $reviewRange = 'all', $checkedItem = '')
    {
        $this->commonAction($projectID);

        if($_POST)
        {
            $reviewID = $this->review->create($projectID, $reviewRange, $checkedItem);

            if(!dao::isError())
            {
                $response['result']  = 'success';
                $response['message'] = $this->lang->saveSuccess;
                $response['locate']  = inlink('browse', "project=$projectID");
                return $this->send($response);
            }

            $response['result']  = 'fail';
            $response['message'] = dao::getError();
            return $this->send($response);
        }

        $libs       = $this->loadModel('doc')->getLibsByObject('project', $projectID);
        $executions = $this->dao->select('id,path,name')->from(TABLE_EXECUTION)
            ->where('project')->eq($projectID)
            ->andWhere('deleted')->eq(0)
            ->fetchAll('id');
        foreach($libs as $libID => $lib)
        {
            $libNamePrefix = '';
            if(!empty($lib->execution))
            {
                $execution     = $executions[$lib->execution];
                $executionPath = explode(',', trim($execution->path, ','));
                array_shift($executionPath); // Remove project id.

                foreach($executionPath as $path) if(isset($executions[$path])) $libNamePrefix .= $executions[$path]->name . '/';
            }

            $libs[$libID] = $libNamePrefix . $lib->name;
        }

        if(!$this->session->hasProduct) $productID = $this->loadModel('product')->getProductIDByProject($projectID);

        $this->view->title      = $this->lang->review->create;
        $this->view->position[] = $this->lang->review->create;

        $this->view->object    = $object;
        $this->view->projectID = $projectID;
        $this->view->productID = $productID;
        $this->view->libs      = array('' => '') + $libs;
        $this->view->products  = $this->loadModel('product')->getProductPairsByProject($projectID);
        $this->view->backLink  = $this->session->reviewList ? $this->session->reviewList : inlink('browse', "project=$projectID");
        $this->display();
    }

    /**
     * Edit a review.
     *
     * @param  int  $reviewID
     * @access public
     * @return void
     */
    public function edit($reviewID)
    {
        $review = $this->review->getByID($reviewID);
        $this->commonAction($review->project);

        if($_POST)
        {
            $changes = $this->review->update($reviewID);
            $files   = $this->loadModel('file')->saveUpload('review', $reviewID);

            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                return $this->send($response);
            }

            if($changes or $this->post->comment or !empty($files))
            {
                $fileAction = !empty($files) ? $this->lang->addFiles . join(',', $files) . "\n" : '';
                $actionID = $this->loadModel('action')->create('review', $reviewID, 'Edited', $fileAction . $this->post->comment);
                $this->action->logHistory($actionID, $changes);
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = inlink('browse', "project=$review->project");
            return $this->send($response);

        }

        $this->view->title      = $this->lang->review->edit;
        $this->view->position[] = $this->lang->review->edit;
        $this->view->review     = $review;
        $this->view->project    = $this->loadModel('project')->getByID($review->project);
        $this->view->products   = $this->loadModel('product')->getPairs($review->project);
        $this->view->members    = $this->project->getTeamMemberPairs($review->project);
        $this->display();
    }

    /**
     * View a review.
     *
     * @param  int  $reviewID
     * @access public
     * @return void
     */
    public function view($reviewID)
    {
        $review = $this->review->getByID($reviewID);
        $this->commonAction($review->project);
        $audit = $this->review->getAuditByReviewID($reviewID);

        $approval = $this->loadModel('approval')->getByObject('review', $reviewID);
        $results  = $this->approval->getNodeReviewers($approval->id);

        $reviewerResult = array();
        foreach($results as $nodeList)
        {
            if(empty($nodeList['reviewers'])) continue;

            foreach($nodeList['reviewers'] as $user)
            {
                if(empty($reviewerResult[$user['account']])) $reviewerResult[$user['account']] = array();
                array_push($reviewerResult[$user['account']], $user['result']);
            }
        }

        $this->setViewData($review, 'gantt');
        $this->view->title          = $this->lang->review->view;
        $this->view->review         = $review;
        $this->view->object         = $review;
        $this->view->actions        = $this->loadModel('action')->getList('review', $reviewID);
        $this->view->approval       = $approval;
        $this->view->projectID      = $review->project;
        $this->view->users          = $this->loadModel('user')->getPairs('noletter');
        $this->view->auditOpinion   = !empty($audit->opinion) ? $audit->opinion : '';
        $this->view->pendingReviews = $this->approval->getPendingReviews('review');
        $this->view->reviewers      = $this->review->getReviewerByIdList($reviewID);
        $this->view->reviewerResult = $reviewerResult;
        $this->display();
    }

    /**
     * Delete a review.
     *
     * @param int    $reviewID
     * @param string $confirm
     *
     * @access public
     * @return void
     */
    public function delete($reviewID, $confirm = 'no')
    {
        $review = $this->review->getByID($reviewID);
        if($confirm == 'no')
        {
            return print(js::confirm($this->lang->review->confirmDelete, inlink('delete', "reviewID=$reviewID&confirm=yes")));
        }
        else
        {
            $this->review->delete(TABLE_REVIEW, $reviewID);

            $reviewIssues = $this->loadModel('reviewissue')->getIssueByReview($reviewID, $review->project, 'review', '', '');
            if(!empty($reviewIssues))
            {
                foreach($reviewIssues as $reviewIssue)
                {
                    $this->reviewissue->delete(TABLE_REVIEWISSUE, $reviewIssue->id);
                }
            }

            $locateLink = inlink('browse', "projectID={$review->project}");
            return print(js::locate($locateLink, 'parent'));
        }
    }


    /**
     * Submit a review.
     *
     * @param  int  $reviewID
     * @access public
     * @return void
     */
    public function submit($reviewID)
    {
        $review = $this->review->getByID($reviewID);

        if($_POST)
        {
            $changes = $this->review->submit($reviewID);
            if(dao::isError()) die(js::error(dao::getError()));

            $actionID = $this->loadModel('action')->create('review', $reviewID, 'Submit', $this->post->comment);
            if($changes) $this->action->logHistory($actionID, $changes);
            die(js::closeModal('parent.parent'));
        }

        $this->view->title      = $this->lang->review->submit;
        $this->view->position[] = $this->lang->review->submit;
        $this->view->review     = $review;
        $this->view->actions    = $this->loadModel('action')->getList('review', $reviewID);
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->members    = $this->loadModel('project')->getTeamMemberPairs($review->project);
        $this->display();
    }

    /**
     * Recall a review.
     *
     * @param  int 	   $reviewID
     * @access public
     * @return void
     */
    public function recall($reviewID)
    {
        $this->dao->update(TABLE_REVIEW)->set('status')->eq('draft')->where('id')->eq($reviewID)->exec();
        $this->loadModel('action')->create('review', $reviewID, 'Recall');
        $this->loadModel('approval')->cancel('review', $reviewID);

        die(js::reload('parent.parent'));
    }

    /**
     * Review report.
     *
     * @param  int  $reviewID
     * @param  int  $approvalID
     * @access public
     * @return void
     */
    public function report($reviewID, $approvalID = 0)
    {
        $this->app->loadLang('baseline');
        $review         = $this->review->getByID($reviewID);
        $approvalIDList = $this->loadModel('approval')->getApprovalIDByObjectID($reviewID, 'review');
        $approvalID     = empty($approvalID) ? end($approvalIDList) : $approvalID;
        $approvalNode   = $this->loadModel('approval')->getApprovalNodeByApprovalID($approvalID);
        $this->loadModel('project')->setMenu($review->project);

        /* Get reviewers. */
        $reviewers = array();
        foreach($approvalNode as $node)
        {
            if(!empty($node->reviewedBy) and !in_array($node->reviewedBy, $reviewers))
            {
                $reviewers[] = $node->reviewedBy;
            }
        }

        $this->view->reviewID        = $reviewID;
        $this->view->title           = $this->lang->review->submit;
        $this->view->review          = $review;
        $this->view->objectScale     = $this->review->getObjectScale($review);
        $this->view->results         = $this->review->getResultByUserList($reviewID);
        $this->view->issues          = $this->loadModel('reviewissue')->getIssueByReview($reviewID, $review->project, 'review', 'all', 'all', $approvalID);
        $this->view->users           = $this->loadModel('user')->getPairs('noclosed|noletter');
        $this->view->approvalNode    = $approvalNode;
        $this->view->reviewer        = $reviewers;
        $this->view->reviewerCount   = count($reviewers);
        $this->view->approvalIDList  = $approvalIDList;
        $this->view->approvalID      = $approvalID;
        $this->view->approval        = $this->loadModel('approval')->getByID($approvalID);
        $this->view->efforts         = $this->loadModel('effort')->getByObject('review', $reviewID, 'id', $approvalID);

        $this->display();
    }

    /**
     * Ajax get role for review.
     *
     * @param  int     $projectID
     * @param  string  $object
     * @param  string  $reviewedBy
     * @access public
     * @return void
     */
    public function ajaxGetNodes($projectID = 0, $object = '')
    {
        if(!$object) die($this->lang->noData);

        $this->loadModel('user');
        $this->commonAction($projectID);

        $users  = $this->user->getPairs('noletter|nodeleted|noclosed');
        $flowID = $this->loadModel('approvalflow')->getFlowIDByObject($projectID, $object);
        $nodes  = $this->loadModel('approval')->getNodesToConfirm($flowID);

        if(empty($nodes)) die($this->lang->noData);

        $approvalNodeWidth = common::checkNotCN() ? 'w-110px' : 'w-80px';
        $html   = "<table class='table table-form mg-0 table-bordered' style='border: 1px solid #ddd'>";
        $html  .= '<thead><tr>';
        $html  .= "<th class='text-center {$approvalNodeWidth}'>" . $this->lang->approval->node . '</th>';
        $html  .= "<th class='text-center'>" . $this->lang->approval->reviewer . '</th>';
        $html  .= "<th class='text-center'>" . $this->lang->approval->ccer . '</th>';
        $html  .= '</tr></thead><tbody>';

        foreach($nodes as $node)
        {
            $html .= '<tr>';
            $html .= '<td class="text-center">' . $node['title'] . html::hidden('id[]', $node['id']) . '</td>';
            $html .= '<td>';

            $reviewers = array();
            if(isset($node['appointees']['reviewer']))
            {
                foreach($node['appointees']['reviewer'] as $appointee) $reviewers[$appointee] = zget($users, $appointee);
            }
            if(isset($node['upLevel']['reviewer']))
            {
                foreach($node['upLevel']['reviewer'] as $upLevel) $reviewers[$upLevel] = zget($users, $upLevel);
            }
            if(isset($node['role']['reviewer']))
            {
                foreach($node['role']['reviewer'] as $roleUser) $reviewers[$roleUser] = zget($users, $roleUser);
            }

            if(in_array('reviewer', $node['types']))
            {
                $html .= html::select('reviewer[' . $node['id'] . '][]', array_diff($users, $reviewers), '', "multiple class='form-control chosen'");
                if($reviewers) $html .= "<div class='otherReviewer' style='margin-top:10px'>" . $this->lang->approval->otherReviewer . join(',', $reviewers) . '</div>';
            }
            else
            {
                $html .= html::hidden('reviewer[' . $node['id'] . '][]', '');
                if($reviewers) $html .= join(',', $reviewers);
            }

            $html .= '</td>';
            $html .= '<td>';

            $ccers = array();
            if(isset($node['appointees']['ccer']))
            {
                foreach($node['appointees']['ccer'] as $appointee) $ccers[$appointee] = zget($users, $appointee);
            }
            if(isset($node['upLevel']['ccer']))
            {
                foreach($node['upLevel']['ccer'] as $upLevel) $ccers[$upLevel] = zget($users, $upLevel);
            }
            if(isset($node['role']['ccer']))
            {
                foreach($node['role']['ccer'] as $roleUser) $ccers[$roleUser] = zget($users, $roleUser);
            }

            if(in_array('ccer', $node['types']))
            {
                $html .= html::select('ccer[' . $node['id'] . '][]', array_diff($users, $ccers), '', "multiple class='form-control chosen'");
                if($ccers) $html .= "<div class='otherCcer' style='margin-top:10px'>" . $this->lang->approval->otherCcer . join(',', $ccers) . '</div>';
            }
            else
            {
                $html .= html::hidden('ccer[' . $node['id'] . '][]', '');
                if($ccers) $html .= join(',', $ccers);
            }

            $html .= '</td>';
            $html .= '</tr>';
        }

        $html  .= '</tbody></table>';

        return print($html);
    }

    /**
     * AJAX: return reviews of a user in html select.
     *
     * @param  int    $userID
     * @param  string $id
     * @param  string $status
     * @access public
     * @return void
     */
    public function ajaxGetUserReviews($userID = '', $id = '', $status = 'all')
    {
        if($userID == '') $userID = $this->app->user->id;
        $user    = $this->loadModel('user')->getById($userID, 'id');
        $account = $user->account;

        $reviews = $this->review->getUserReviewPairs($account, 0, $status);

        if($id) die(html::select("reviews[$id]", $reviews, '', 'class="form-control"'));
        die(html::select('review', $reviews, '', 'class=form-control'));
    }

    /**
     * Set review auditer.
     *
     * @param  int     $reviewID
     * @access public
     * @return void
     */
    public function toAudit($reviewID)
    {
        $review = $this->review->getByID($reviewID);
        if($_POST)
        {
            $this->review->toAudit($reviewID);
            if(dao::isError()) die(js::error(dao::getError()));

            $this->loadModel('action')->create('review', $reviewID, 'Toaudit', $this->post->comment, $this->post->auditedBy);
            die(js::closeModal('parent.parent'));
        }

        $this->view->title      = $this->lang->review->toAudit;
        $this->view->position[] = $this->lang->review->toAudit;
        $this->view->review     = $review;
        $this->view->users      = $this->loadModel('project')->getTeamMemberPairs($review->project);
        $this->view->actions    = $this->loadModel('action')->getList('review', $reviewID);
        $this->display();
    }

    /**
     * Audit a review.
     *
     * @param  int   $reviewID
     * @access public
     * @return void
     */
    public function audit($reviewID)
    {
        $review = $this->review->getByID($reviewID);
        $this->commonAction($review->project);

        if($_POST)
        {
            $this->review->saveResult($reviewID, 'audit');

            if(dao::isError()) return $this->send(array('result' => 'fail', 'message' => dao::getError()));

            return $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse', "project=$review->project")));
        }

        $this->app->loadLang('reviewissue');
        $reviewer = explode(',', $review->reviewedBy);

        $this->setViewData($review);

        $project = $this->loadModel('project')->getByID($review->project);

        $this->view->title      = $this->lang->review->audit;
        $this->view->review     = $review;
        $this->view->object     = $review;
        $this->view->result     = $this->review->getResultByUser($reviewID, 'audit');
        $this->view->issues     = $this->loadModel('reviewissue')->getIssueByReview($reviewID, $review->project, 'audit', 'all', 'all');
        $this->view->cmcl       = $this->loadModel('cmcl')->getList('all', 'id_desc', null, $project->model);
        $this->view->typeList   = $this->lang->cmcl->typeList;
        $this->view->items      = $this->lang->cmcl->titleList;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->display();
    }
}
