<?php
/**
 * The control file of approval module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     approval
 * @version     $Id: control.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class approval extends control
{
    /**
     * Ajax generate nodes.
     *
     * @param int $flowID
     * @param int $version
     * @access public
     * @return void
     */
    public function ajaxGenNodes($flowID, $version = 0)
    {
    }

    /**
     * Display progress.
     *
     * @param int   $approvalID
     * @access public
     * @return void
     */
    public function progress($approvalID)
    {
        $this->app->loadLang('approvalflow');
        $approval = $this->approval->getByID($approvalID);
        if(!$approval) return;

        /* Get approval times and approval time. */
        $approvals     = $this->approval->getListByObject($approval->objectType, $approval->objectID);
        $approvalCount = count($approvals);
        $firstApproval = array_pop($approvals);
        $approvalTime  = 0;
        if(!helper::isZeroDate($firstApproval->createdDate))
        {
            $endTime = time();
            if($approval->status == 'done')
            {
                $lastReviewDate = $this->approval->getLastReviewDate($approvalID);
                $endTime        = empty($lastReviewDate) ? strtotime($lastReviewDate) : strtotime($firstApproval->createdDate);
            }

            $approvalTime = $endTime - strtotime($firstApproval->createdDate);
            $approvalTime = round($approvalTime / 3600, 2);
            if($approvalTime < 1) $approvalTime = 0;
        }

        $nodes     = json_decode($approval->nodes);
        $reviewers = $this->approval->getNodeReviewers($approvalID);

        $nodes = $this->approval->orderBranchNodes($nodes, $reviewers);

        $noticeLang = $this->lang->approval->notice;

        $approvalNotice = '';
        if($approvalCount > 1) $approvalNotice .= sprintf($noticeLang->times, $approvalCount);

        if($approvalTime)
        {
            $days =($approvalTime > 24) ? floor($approvalTime / 24) : 0;
            $hour = $approvalTime % 24;

            $processedApprovalTime = $hour . $noticeLang->hour;
            if($days > 0) $processedApprovalTime = $days . $noticeLang->day . $processedApprovalTime;

            $processedTime = sprintf($noticeLang->approvalTime, $processedApprovalTime);

            if($approvalNotice) $approvalNotice .= $this->lang->comma;
            $approvalNotice .= $processedTime;
        }

        if($approvalNotice) $approvalNotice = "<span class='approvalNotice'>({$approvalNotice})</span>";

        $this->view->title         = $this->lang->approval->progress . $approvalNotice;
        $this->view->approval      = $approval;
        $this->view->approvalCount = $approvalCount;
        $this->view->approvalTime  = $approvalTime;
        $this->view->nodes         = $nodes;
        $this->view->reviewers     = $reviewers;
        $this->view->users         = $this->loadModel('user')->getPairs('noletter');
        $this->display();
    }
}
