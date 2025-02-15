<?php
class zentaobizTree extends treeModel
{
    /**
     * delete module.
     *
     * @param mixed $moduleID
     * @param mixed $null
     * @access public
     * @return void
     */
    public function delete($moduleID, $null = null)
    {
        if(!empty($this->app->user->feedback) or $this->cookie->feedbackView)
        {
            $module = $this->getById($moduleID);
            if($module->type != 'doc') return false;
        }
        return parent::delete($moduleID);
    }

    /**
     * Get feedback tree menu.
     *
     * @param  string $userFunc
     *
     * @access public
     * @return string
     */
    public function getFeedbackTreeMenu($userFunc = '')
    {
        $menu = "<ul id='modules' class='tree' data-ride='tree' data-name='tree-feedback'>";

        /* Get module according to product. */
        $products = $this->loadModel('feedback')->getGrantProducts();

        $syncConfig = json_decode($this->config->global->syncProduct, true);
        $syncConfig = isset($syncConfig['feedback']) ? $syncConfig['feedback'] : array();
        $productNum = count($products);
        $productID  = $this->session->feedbackProduct;
        if($productID and isset($products[$productID])) $products = array($productID => $products[$productID]);

        /* Create module tree.*/
        foreach($products as $id => $product)
        {
            $feedbackProductLink = helper::createLink('feedback', $this->config->vision == 'lite' ? 'browse' : 'admin', "browseType=byProduct&param=$id");
            if($productNum >= 1) $menu .= "<li>" . html::a($feedbackProductLink, $product, '_self', "id='product$id' title=$product");
            $type = isset($syncConfig[$id]) ? 'story,feedback' : 'feedback';

            /* tree menu. */
            $tree = '';
            $treeMenu = array();
            $query = $this->dao->select('*')->from(TABLE_MODULE)
                ->where('root')->eq($id)
                ->andWhere('type')->in($type)
                ->andWhere('deleted')->eq(0)
                ->orderBy('grade desc, `order`, type')
                ->get();
            $stmt = $this->dbh->query($query);
            while($module = $stmt->fetch())
            {
                /* If is merged add story module.*/
                if($module->type == 'story' and $module->grade > $syncConfig[$id]) continue;

                /* If not manage, ignore unused modules. */
                $this->buildTree($treeMenu, $module, 'feedback', $userFunc, '');
            }
            $tree .= isset($treeMenu[0]) ? $treeMenu[0] : '';

            if($productNum >= 1) $tree = "<ul>" . $tree . "</ul>\n</li>";
            $menu .= $tree;
        }

        $menu .= '</ul>';
        return $menu;
    }

    /**
     * Get ticket tree menu.
     *
     * @param  string $userFunc
     *
     * @access public
     * @return string
     */
    public function getTicketTreeMenu($userFunc = '')
    {
        $menu = "<ul id='modules' class='tree' data-ride='tree' data-name='tree-ticket'>";

        /* Get module according to product. */
        $products = $this->loadModel('feedback')->getGrantProducts();

        $syncConfig = json_decode($this->config->global->syncProduct, true);
        $syncConfig = isset($syncConfig['ticket']) ? $syncConfig['ticket'] : array();
        $productNum = count($products);
        $productID  = $this->session->ticketProduct;
        if($productID and isset($products[$productID])) $products = array($productID => $products[$productID]);

        /* Create module tree.*/
        foreach($products as $id => $product)
        {
            $ticketProductLink = helper::createLink('ticket', 'browse', "browseType=byProduct&param=$id");
            if($productNum >= 1) $menu .= "<li>" . html::a($ticketProductLink, $product, '_self', "id='product$id' title=$product");
            $type = isset($syncConfig[$id]) ? 'story,ticket' : 'ticket';

            /* tree menu. */
            $tree = '';
            $treeMenu = array();
            $query = $this->dao->select('*')->from(TABLE_MODULE)
                ->where('root')->eq($id)
                ->andWhere('type')->in($type)
                ->andWhere('deleted')->eq(0)
                ->orderBy('grade desc, `order`, type')
                ->get();
            $stmt = $this->dbh->query($query);
            while($module = $stmt->fetch())
            {
                /* If is merged add story module.*/
                if($module->type == 'story' and $module->grade > $syncConfig[$id]) continue;

                /* If not manage, ignore unused modules. */
                $this->buildTree($treeMenu, $module, 'ticket', $userFunc, '');
            }
            $tree .= isset($treeMenu[0]) ? $treeMenu[0] : '';

            if($productNum >= 1) $tree = "<ul>" . $tree . "</ul>\n</li>";
            $menu .= $tree;
        }

        $menu .= '</ul>';
        return $menu;
    }

    /**
     * Get group tree.
     *
     * @param  int    $dimensionID
     * @param  string $type        chart|report|dashboard|dataview
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return string
     */
    public function getGroupTree($dimensionID = 0, $type = 'chart', $orderBy = 'id_desc', $pager = null)
    {
        $tab    = $this->app->tab;
        $menu   = "<ul id='modules' class='tree' data-ride='tree' data-name='tree-group'>";

        /* tree menu. */
        $query = $this->dao->select('*')->from(TABLE_MODULE)
            ->where('root')->eq($dimensionID)
            ->andWhere('type')->eq($type)
            ->andWhere('deleted')->eq(0)
            ->orderBy('grade desc, `order`')
            ->get();
        $treeMenu = array();

        if($type == 'dataview')
        {
            $stmt           = $this->dbh->query($query);
            $dataviewGroups = $this->dao->select('*')->from(TABLE_DATAVIEW)->where('deleted')->eq(0)->fetchGroup('group');

            while($module = $stmt->fetch())
            {
                $dataviews = isset($dataviewGroups[$module->id]) ? $dataviewGroups[$module->id] : array();
                foreach($dataviews as $dataview)
                {
                    $group = $dataview->group;
                    $link  = html::a(helper::createLink('dataview', 'browse', "type=view&table=$dataview->id"), $dataview->name);

                    if(!isset($treeMenu[$group])) $treeMenu[$group] = "";
                    $treeMenu[$group] .= "<li class='dataview-{$dataview->id}' title='{$dataview->name}'>$link\n</li>";
                }
            }
        }

        $stmt = $this->dbh->query($query);
        while($module = $stmt->fetch())
        {
            $methodList = array('report' => 'browseReport', 'chart' => 'browse', 'dashboard' => 'browse', 'pivot' => 'browse');
            $linkHtml   = $type == 'dataview' ? "<a>{$module->name}</a>" : html::a(helper::createLink($type, $methodList[$type], "dimensionID=$dimensionID&group={$module->id}&orderBy={$orderBy}&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}"), $module->name, '_self', "id='module{$module->id}' title='{$module->name}'");
            $title      = "title='{$module->name}'";

            if(isset($treeMenu[$module->id]) and !empty($treeMenu[$module->id]))
            {
                if(!isset($treeMenu[$module->parent])) $treeMenu[$module->parent] = '';
                $treeMenu[$module->parent] .= "<li class='closed' $title>$linkHtml";
                $treeMenu[$module->parent] .= "<ul>" . $treeMenu[$module->id] . "</ul>\n";
            }
            else
            {
                if(!isset($treeMenu[$module->parent])) $treeMenu[$module->parent] = "";
                $treeMenu[$module->parent] .= "<li $title>$linkHtml\n";
            }
            $treeMenu[$module->parent] .= "</li>\n";

        }
        $menu .= isset($treeMenu[0]) ? $treeMenu[0] : '';

        $menu .= '</ul>';
        return $menu;
    }

    /**
     * Get full chart group tree.
     *
     * @param  int    $dimensionID
     * @param  int    $groupID
     * @param  string $type
     * @access public
     * @return array
     */
    public function getGroupStructure($dimensionID = 0, $groupID = 0, $type = 'chart')
    {
        $query = $this->dao->select('*')->from(TABLE_MODULE)
            ->where('root')->eq($dimensionID)
            ->beginIF(!empty($groupID))->andWhere('path')->like("%,$dimensionID,%")->fi()
            ->andWhere('type')->eq($type)
            ->andWhere('deleted')->eq(0)
            ->orderBy('grade desc, `order`')
            ->get();
        return $this->loadModel('tree')->getDataStructure($this->dbh->query($query), $type);
    }

    /**
     * Get group pairs.
     *
     * @param  int    $dimensionID
     * @param  int    $parentGroup
     * @param  int    $grade
     * @param  string $type
     * @access public
     * @return array
     */
    public function getGroupPairs($dimensionID = 0, $parentGroup = 0, $grade = 2, $type = 'chart')
    {
        $groups = $this->dao->select('id,name,grade,parent')->from(TABLE_MODULE)
            ->where('root')->eq($dimensionID)
            ->beginIF(!empty($parentGroup))->andWhere('root')->eq($dimensionID)->fi()
            ->andWhere('type')->eq($type)
            ->andWhere('deleted')->eq(0)
            ->orderBy('order')
            ->fetchGroup('grade', 'id');

        $groupPairs = array();
        if(!empty($groups[1]))
        {
            foreach($groups[1] as $parentGroup)
            {
                if($grade == 1) $groupPairs[$parentGroup->id] = $parentGroup->name;
                if($grade == 2 and !empty($groups[2]))
                {
                    foreach($groups[2] as $childGroup)
                    {
                        if($parentGroup->id == $childGroup->parent) $groupPairs[$childGroup->id] = '/' . $parentGroup->name . '/' . $childGroup->name;
                    }
                }
            }
        }

        return $groupPairs;
    }

    /**
     * Get practice tree menu.
     *
     * @param  string $userFunc
     * @param  string $type     browse|view
     * @access public
     * @return string
     */
    public function getPracticeTreeMenu($userFunc = '', $type = 'browse')
    {
        $practices = $type == 'view' ? $this->dao->select('module,id,title')->from(TABLE_PRACTICE)->fetchGroup('module') : array();
        $menu      = "<ul id='modules' class='tree' data-ride='tree' data-name='tree-ticket'>";

        $tree = '';
        $treeMenu = array();
        $query = $this->dao->select('*')->from(TABLE_MODULE)
            ->where('type')->eq('practice')
            ->andWhere('deleted')->eq(0)
            ->orderBy('grade_desc, id_asc')
            ->get();
        $stmt = $this->dbh->query($query);
        while($module = $stmt->fetch())
        {
            $linkHtml = $userFunc ? call_user_func($userFunc, 'practice', $module) : "<a id='module{$module->id}' title='{$module->name}' >{$module->name}</a>";

            if(isset($treeMenu[$module->id]) and !empty($treeMenu[$module->id]))
            {
                if(!isset($treeMenu[$module->parent])) $treeMenu[$module->parent] = '';
                $treeMenu[$module->parent] .= "<li class='closed'>{$linkHtml}";
                $treeMenu[$module->parent] .= "<ul>" . $treeMenu[$module->id] . "</ul>\n";
            }
            else
            {
                if(!isset($treeMenu[$module->parent])) $treeMenu[$module->parent] = "";

                if(!empty($practices) && isset($practices[$module->id]))
                {
                    $treeMenu[$module->parent] .= "<li>{$linkHtml}";
                    $treeMenu[$module->parent] .= "<ul>\n";

                    foreach($practices[$module->id] as $practice)
                    {
                        $practiceLink = html::a(helper::createLink('traincourse', 'practiceview', "id={$practice->id}"), $practice->title, '_self', "id='practice{$practice->id}' title='{$practice->title}'");
                        $treeMenu[$module->parent] .= "<li style='display: flex;'><i class='icon icon-file-text-alt' style='padding-top: 5px;'></i>{$practiceLink}</li>\n";
                    }
                    $treeMenu[$module->parent] .= "</ul>\n";
                }
                else
                {
                    $treeMenu[$module->parent] .= "<li>{$linkHtml}\n";
                }
            }
            $treeMenu[$module->parent] .= "</li>\n";
        }

        $tree .= isset($treeMenu[0]) ? $treeMenu[0] : '';
        $menu .= $tree;
        $menu .= '</ul>';
        return $menu;
    }
}
