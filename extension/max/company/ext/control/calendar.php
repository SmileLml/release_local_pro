<?php
/**
 * The control file of calendar module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     calendar
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class company extends control
{
    public function calendar($parent = '', $begin = 0, $end = 0, $product = 0, $project = 0, $execution = 0, $userID = '', $showUser = 'logged', $iframe = 'no', $userType = '')
    {
        $account = '';
        if($userID)
        {
            $user    = $this->loadModel('user')->getById($userID, 'id');
            $account = $user->account;
        }

        $currentDept = $this->app->user->dept;
        $parent      = $parent === '' ? $currentDept : $parent;

        $super = common::hasPriv('company', 'alleffort');
        if(!$super)
        {
            if($parent != $currentDept)
            {
                $dept = $this->loadModel('dept')->getById($parent);
                if(strpos($dept->path, ",{$currentDept},") === false) $parent = $currentDept;
            }
        }

        if($iframe == 'yes')
        {
            $days = array();
            for($i = $begin; $i <= $end; $i += 86400) $days[] = date('Y-m-d', $i);

            $begin = date('Y-m-d', $begin);
            $end   = date('Y-m-d', $end);

            $this->app->loadLang('effort');
            $this->view->datas     = $this->company->getEffort($parent, $begin, $end, $product, $project, $execution, $account, $showUser, $userType);
            $this->view->users     = $this->loadModel('user')->getPairs('noclosed|nodeleted|noletter');
            $this->view->parent    = $parent;
            $this->view->userType  = $userType;
            $this->view->depts     = $this->loadModel('dept')->getOptionMenu($super ? 0 : $this->app->user->dept);
            $this->view->iframe    = $iframe;
            $this->view->begin     = $begin;
            $this->view->end       = $end;
            $this->view->product   = $product;
            $this->view->project   = $project;
            $this->view->execution = $execution;
            $this->view->userID    = $userID;
            $this->view->days      = $days;
            die($this->display());
        }

        if(!empty($_POST))
        {
            $data  = fixer::input('post')->setDefault('user', '')->get();
            $begin = explode('-', $data->begin);
            $begin = implode('', $begin);
            $end   = explode('-', $data->end);
            $end   = implode('', $end);
            die(js::locate(inlink('calendar', "parent={$data->dept}&begin=$begin&end=$end&product={$data->product}&project={$data->project}&execution={$data->execution}&userID={$data->user}&showUser={$data->showUser}&iframe=no&userType={$data->userType}"), 'parent'));
        }

        $begin = $begin == 0 ? strtotime('-1 week') : strtotime($begin);
        $begin = date('Y-m-d', $begin);
        $end   = $end == 0 ? strtotime('now') : strtotime($end);
        $end   = date('Y-m-d', $end);

        $this->app->loadLang('effort');
        $this->company->setMenu();
        $date = 'today';
        $this->view->date  = $date;
        $this->view->today = (int)$date == 0 ? date(DT_DATE1, time()) : substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);

        /* Get products' list.*/
        $products = $this->loadModel('product')->getPairs();
        $products = array(0 => '') + $products;
        $this->view->products = $products;

        /* Get projects' list.*/
        $projects = $this->loadModel('project')->getPairsByModel('all', 0);
        $projects = array(0 => '') + $projects;
        $this->view->projects = $projects;

        /* Get executions' list.*/
        $executions = $this->loadModel('execution')->getPairs(0, 'all', 'multiple,leaf');
        $executions = array(0 => '') + $executions;
        $this->view->executions = $executions;

        /* Get users.*/
        $users = array('' => '') + $this->loadModel('dept')->getDeptUserPairs($parent, 'id');
        $this->view->users = $users;

        $mainDepts = $this->dept->getOptionMenu($super ? 0 : $this->app->user->dept);

        /* Add all depts. */
        unset($mainDepts[0]);
        $mainDepts = array('all' => '/' . $this->lang->company->allDept) + $mainDepts;

        if(!$super)
        {
            unset($mainDepts['all']);
            if($this->app->user->dept == 0) $mainDepts = array('0' => "/");
        }

        $this->view->mainDepts = $mainDepts;
        $this->view->userID    = $userID;
        $this->view->product   = $product;
        $this->view->project   = $project;
        $this->view->execution = $execution;
        $this->view->userType  = $userType;

        $this->view->title    = $this->lang->company->common . $this->lang->colon . $this->lang->company->effort->common;
        $this->view->parent   = $parent;
        $this->view->showUser = $showUser;
        $this->view->begin    = $begin;
        $this->view->end      = $end;
        $this->view->iframe   = $iframe;
        $this->display();
    }
}
