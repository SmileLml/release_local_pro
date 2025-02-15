<?php
/**
 * The model file of domian module of ZenTaoCMS.
 *
 * @copyright   Copyright 2009-2010 QingDao Nature Easy Soft Network Technology Co,LTD (www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Jiangxiu Peng <pengjiangxiu@cnezsoft.com>
 * @package     domain
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class domainModel extends model
{
    /**
     * Get domain by id.
     *
     * @param  int    $id
     * @access public
     * @return object
     */
    public function getByID($id)
    {
        return $this->dao->select('*')->from(TABLE_DOMAIN)->where('id')->eq($id)->fetch();
    }

    /**
     * Get domain list.
     *
     * @param  string $browseType
     * @param  int    $param
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getList($browseType = 'all', $param = 0, $orderBy = 'id_desc', $pager = null)
    {
        $query = '';
        if($browseType == 'bysearch')
        {
            if($param)
            {
                $query = $this->loadModel('search')->getQuery($param);
                if($query)
                {
                    $this->session->set('domainQuery', $query->sql);
                    $this->session->set('domainForm', $query->form);
                }
                else
                {
                    $this->session->set('domainQuery', ' 1 = 1');
                }
            }
            else
            {
                if($this->session->domainQuery == false) $this->session->set('domainQuery', ' 1 = 1');
            }
            $query = $this->session->domainQuery;
        }

        $domainArray = $this->dao->select('*')->from(TABLE_DOMAIN)
            ->where('deleted')->eq('0')
            ->beginIF($query)->andWhere($query)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();

        return $domainArray;
    }


    /**
     * Create.
     *
     * @access public
     * @return int
     */
    public function create()
    {
        $now  = helper::now();
        $domain = fixer::input('post')
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $now)
            ->get();

        $this->dao->insert(TABLE_DOMAIN)->data($domain)->autoCheck()
            ->batchCheck($this->config->domain->create->requiredFields, 'notempty')
            ->exec();

        if(dao::isError()) return false;
        return $this->dao->lastInsertID();
    }

    /**
     * Update
     *
     * @param  int    $id
     * @access public
     * @return array
     */
    public function update($id)
    {
        $oldItem = $this->getById($id);
        $now     = helper::now();
        $newItem = fixer::input('post')
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', $now)
            ->get();

        $this->dao->update(TABLE_DOMAIN)->data($newItem)->autoCheck()
            ->batchCheck($this->config->domain->edit->requiredFields, 'notempty')
            ->where('id')->eq($id)
            ->exec();

        if(dao::isError()) return false;
        return common::createChanges($oldItem, $newItem);
    }
}
