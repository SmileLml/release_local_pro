<?php
/**
 * The model file of service module of ZenTaoCMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     service
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class serviceModel extends model
{
    /**
     * Get by id.
     *
     * @param  int    $serviceID
     * @access public
     * @return object
     */
    public function getById($serviceID)
    {
        $service = $this->dao->select('*')->from(TABLE_SERVICE)->where('id')->eq($serviceID)->fetch();
        if(!$service) return false;
        $service = $this->loadModel('file')->replaceImgURL($service, 'desc');
        return $service;
    }

    public function getByIdList($idList)
    {
        return $this->dao->select('*')->from(TABLE_SERVICE)->where('id')->in($idList)->fetchAll('id');
    }

    /**
     * Get top services.
     *
     * @param  string $type
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getTopServices($type = 'all', $orderBy = '', $pager = null)
    {
        $account = $this->app->user->account;
        return $this->dao->select('*')->from(TABLE_SERVICE)
            ->where('deleted')->eq(0)
            ->andWhere('grade')->eq(1)
            ->beginIF($type == 'openedbyme')->andWhere('createdBy')->eq($account)->fi()
            ->beginIF($type == 'ownerbyme')->andWhere("(`devel` = '$account' OR `qa` = '$account' OR `ops` = '$account')")->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /**
     * Get top service pairs.
     *
     * @access public
     * @return array
     */
    public function getTopServicePairs()
    {
        return $this->dao->select('*')->from(TABLE_SERVICE)
            ->where('deleted')->eq(0)
            ->andWhere('grade')->eq(1)
            ->orderBy('id_desc')
            ->fetchPairs('id', 'name');
    }

    /**
     * Create service.
     *
     * @param  int    $parent
     * @access public
     * @return bool/int
     */
    public function create($parent = 0)
    {
        $data = fixer::input('post')
            ->add('parent', $parent)
            ->add('grade', 1)
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', helper::now())
            ->stripTags($this->config->service->editor->create['id'], $this->config->allowedTags)
            ->join('hosts', ',')
            ->remove('hostGroup')
            ->get();

        $data = $this->loadModel('file')->processImgURL($data, $this->config->service->editor->create['id'], $this->post->uid);
        if($parent)
        {
            $parentService = $this->getById($parent);
            if($parentService->type == 'component') die(js::alert(sprintf($this->lang->service->errorParent, $this->lang->service->typeList[$data->type])));
            if($parentService) $data->grade = $parentService->grade + 1;
        }

        $this->dao->insert(TABLE_SERVICE)->data($data, 'uid')->autoCheck()
            ->batchCheck($this->config->service->create->requiredFields, 'notempty')
            ->exec();
        if(!dao::isError())
        {
            $serviceID = $this->dao->lastInsertId();

            $path = ",$serviceID,";
            if($parent and $parentService) $path = $parentService->path . "{$serviceID},";
            $this->dao->update(TABLE_SERVICE)->set('path')->eq($path)->where('id')->eq($serviceID)->exec();

            $this->file->updateObjectID($this->post->uid, $serviceID, 'service');

            return $serviceID;
        }
        return false;
    }

    /**
     * Update service.
     *
     * @param  int    $serviceID
     * @access public
     * @return bool/array
     */
    public function update($serviceID)
    {
        $oldService = $this->getById($serviceID);
        $service    = fixer::input('post')
            ->setDefault('port', 0)
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', helper::now())
            ->stripTags($this->config->service->editor->edit['id'], $this->config->allowedTags)
            ->join('hosts', ',')
            ->get();

        if($oldService->type == 'service' and $service->type == 'component')
        {
            $childrenCount = $this->dao->select('count(*) as count')->from(TABLE_SERVICE)->where('path')->like("{$oldService->path}%")->andWhere('path')->ne($oldService->path)->fetch('count');
            if($childrenCount) die(js::alert($this->lang->service->errorType));
        }

        $service = $this->loadModel('file')->processImgURL($service, $this->config->service->editor->edit['id'], $this->post->uid);

        $this->dao->update(TABLE_SERVICE)->data($service, 'uid')
            ->batchCheck($this->config->service->edit->requiredFields, 'notempty')
            ->where('id')->eq($serviceID)
            ->exec();
        if(!dao::isError())
        {
            $this->file->updateObjectID($this->post->uid, $serviceID, 'service');
            return common::createChanges($oldService, $service);
        }
        return false;
    }

    public function updateVersion()
    {
        $data = fixer::input('post')->get();
        if(empty($data->version)) $data->version = array();
        $oldServices = $this->getByIdList(array_keys($data->version));

        $this->loadModel('action');
        foreach($data->version as $serviceID => $version)
        {
            if(empty($oldServices[$serviceID])) continue;

            $service = new stdclass();
            $service->version     = $version;
            $service->softName    = $data->softName[$serviceID];
            $service->softVersion = $data->softVersion[$serviceID];

            $this->dao->update(TABLE_SERVICE)->data($service)->where('id')->eq($serviceID)->exec();

            if(!dao::isError())
            {
                $changes = common::createChanges($oldServices[$serviceID], $service);
                if($changes)
                {
                    $actionID = $this->action->create('service', $serviceID, 'Edited');
                    $this->action->logHistory($actionID, $changes);
                }
            }
        }
    }

    public function getPairsByHost($hostIdList, $type = 'service', $append = '')
    {
        if(is_string($hostIdList)) $hostIdList = explode(',', $hostIdList);
        if(is_array($append)) $append = ',' . join(',', $append) . ',';
        $optionMenu  = $this->getOptionMenu($type);
        $stmt = $this->dao->select('*')->from(TABLE_SERVICE)->where('id')->in(array_keys($optionMenu))->query();
        while($service = $stmt->fetch())
        {
            $serviceHosts = ',' . trim($service->hosts, ',') . ',';
            foreach($hostIdList as $hostID)
            {
                if(empty($hostID)) continue;
                if(strpos($serviceHosts, ",$hostID,") === false and strpos($append, ",{$service->id},") === false) unset($optionMenu[$service->id]);
            }
        }

        return $optionMenu;
    }

    public function getOptionMenu($type = 'service')
    {
        $topServices = $this->dao->select('*')->from(TABLE_SERVICE)->where('grade')->eq(1)->andWhere('deleted')->eq(0)->fetchAll('id');

        $optionMenu  = array();
        foreach($topServices as $topServiceID => $topService)
        {
            $stmt = $this->dao->select('*')->from(TABLE_SERVICE)->where('deleted')->eq(0)
                ->beginIF($type != 'all')->andWhere('type')->eq($type)->fi()
                ->andWhere('path')->like(",{$topServiceID},%")
                ->orderBy('grade desc, `order`, id')
                ->query();
            while($service = $stmt->fetch())
            {
                $optionMenu[$service->parent][$service->id] = $type == 'component' ? $topService->name . ' / ' . $service->name : $service->name;
                if(isset($optionMenu[$service->id]))
                {
                    foreach($optionMenu[$service->id] as $subServiceID => $subService)$optionMenu[$service->parent][$subServiceID] = $service->name . ' / ' . $subService;
                    unset($optionMenu[$service->id]);
                }
            }
        }

        if(!isset($optionMenu[0]))
        {
            foreach($optionMenu as $parentID => $subMenu)
            {
                $optionMenu += $subMenu;
                unset($optionMenu[$parentID]);
            }
        }
        elseif(isset($optionMenu[0]))
        {
            $optionMenu = $optionMenu[0];
        }

        return array('0' => '') + $optionMenu;
    }

    /**
     * Get tree.
     *
     * @param  int    $serviceID
     * @access public
     * @return array
     */
    public function getTree($serviceID = 0)
    {
        $topServices = $this->dao->select('count(*) as count')->from(TABLE_SERVICE)->where('deleted')->eq(0)->andWhere('grade')->eq(1)->fetch('count');
        if($topServices == 0) return '';

        $stmt = $this->dao->select('*')->from(TABLE_SERVICE)
            ->where('deleted')->eq(0)
            ->beginIF($serviceID)->andWhere('path')->like(",{$serviceID},%")->fi()
            ->orderBy('grade_desc,id_asc')
            ->query();

        $tree = array();
        while($service = $stmt->fetch())
        {
            if(!isset($tree[$service->parent])) $tree[$service->parent] = '';
            $tree[$service->parent] .= "<li data-type='{$service->type}' data-service='{$service->id}' data-parentid='{$service->parent}' data-color='{$service->color}'>" . htmlspecialchars($service->name);
            if(isset($tree[$service->id]))
            {
                $tree[$service->parent] .= "<ul>{$tree[$service->id]}</ul>";
                unset($tree[$service->id]);
            }
            $tree[$service->parent] .= "</li>";
        }

        if($tree)
        {
            ksort($tree);
            $tree = reset($tree);
            $tree = "<ul class='treemap-data hide'>{$tree}</ul>";
        }
        return $tree ? $tree : '';
    }

    /**
     * Get service list.
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
                    $this->session->set('serviceQuery', $query->sql);
                    $this->session->set('serviceForm', $query->form);
                }
                else
                {
                    $this->session->set('serviceQuery', ' 1 = 1');
                }
            }
            else
            {
                if($this->session->serviceQuery == false) $this->session->set('serviceQuery', ' 1 = 1');
            }
            $query = $this->session->serviceQuery;
        }

		$serviceArray = $this->dao->select('*')->from(TABLE_SERVICE)
        	->where('deleted')->eq('0')
        	->andWhere('type')->eq('service')
        	->beginIF($query)->andWhere($query)->fi()
        	->orderBy($orderBy)
        	->page($pager)
        	->fetchAll();
        return $serviceArray;
    }
}
