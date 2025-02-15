<?php
public function getList($programID = 0, $status = 'all', $limit = 0, $line = 0, $shadow = 0, $fields = '*')
{
    $products = $this->dao->select('t1.*')->from(TABLE_PRODUCT)->alias('t1')
        ->leftJoin(TABLE_PROGRAM)->alias('t2')->on('t1.program = t2.id')
        ->where('t1.deleted')->eq(0)
        ->beginIF($shadow !== 'all')->andWhere('t1.shadow')->eq((int)$shadow)->fi()
        ->beginIF($programID)->andWhere('t1.program')->eq($programID)->fi()
        ->beginIF($line > 0)->andWhere('t1.line')->eq($line)->fi()
        ->beginIF(!$this->app->user->admin and strpos($status, 'feedback') === false)->andWhere('t1.id')->in($this->app->user->view->products)->fi()
        ->beginIF(strpos($status, 'noclosed') === false && !in_array($status, array('all', 'mine', 'involved', 'review', 'feedback'), true))->andWhere('t1.status')->in($status)->fi()
        ->beginIF(strpos($status, 'noclosed') !== false)->andWhere('t1.status')->ne('closed')->fi()
        ->beginIF(strpos($status, 'feedback') !== false)
        ->andWhere("CONCAT(',', t1.vision, ',')")->like("%,rnd,%")
        ->fi()
        ->beginIF($status == 'mine')
        ->andWhere("FIND_IN_SET('{$this->app->user->account}', t1.PO)")
        ->fi()
        ->orderBy('t2.order_asc, t1.line_desc, t1.order_asc')
        ->beginIF($limit > 0)->limit($limit)->fi()
        ->fetchAll('id');

    return $products;
}

/**
 * Get product stats.
 *
 * @param string $orderBy
 * @param objec  $pager
 * @param string $status
 * @param int    $line
 * @param string $storyType
 * @param int    $programID
 * @param int    $param
 * @param int    $shadow
 * @access public
 * @return array
 */
public function getStats($orderBy = 'order_asc', $pager = null, $status = 'noclosed', $line = 0, $storyType = 'story', $programID = 0, $param = 0, $shadow = 0)
{
    $products = $status == 'bySearch' ? $this->getListBySearch($param) : $this->getList($programID, $status, $limit = 0, $line, $shadow, 'id');
    if(empty($products)) return array();

    $productKeys = array_keys($products);
    if($orderBy == 'program_asc')
    {
        $products = $this->dao->select('t1.id as id, t1.name, t1.program, t1.line, t1.PO, t1.status, t1.type, t1.PMT, 0 as draftStories, 0 as activeStories, 0 as launchedStories, 0 as developingStories, 0 as waitedRoadmaps, 0 as launchedRoadmaps, t1.plans, t1.releases, t1.unresolvedBugs')->from(TABLE_PRODUCT)->alias('t1')
            ->leftJoin(TABLE_PROGRAM)->alias('t2')->on('t1.program = t2.id')
            ->where('t1.id')->in($productKeys)
            ->orderBy('t2.order_asc, t1.line_desc, t1.order_asc')
            ->page($pager)
            ->fetchAll('id');
    }
    else
    {
        $products = $this->dao->select('id, name, program, line, status, type, PO, PMT, 0 as draftStories, 0 as activeStories, 0 as launchedStories, 0 as developingStories, 0 as waitedRoadmaps, 0 as launchedRoadmaps, plans, releases, unresolvedBugs')->from(TABLE_PRODUCT)
            ->where('id')->in($productKeys)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /* Recalculate productKeys after paging. */
    $productKeys = array_keys($products);

    $linePairs = $this->getLinePairs();
    foreach($products as $product) $product->lineName = zget($linePairs, $product->line, '');

    $this->app->loadLang('project');
    if(empty($programID))
    {
        $programKeys = array(0 => 0);
        foreach($products as $product) $programKeys[] = $product->program;
        $programs = $this->dao->select('id,name,PM')->from(TABLE_PROGRAM)
            ->where('id')->in(array_unique($programKeys))
            ->fetchAll('id');

        foreach($products as $product)
        {
            $product->programName = (!empty($product->line) && empty($product->program)) ? $this->lang->project->future : (isset($programs[$product->program]) ? $programs[$product->program]->name : '');
            $product->programPM   = isset($programs[$product->program]) ? $programs[$product->program]->PM : '';
        }
    }

    $stories = $this->dao->select('id,product,status')->from(TABLE_STORY)
        ->where('deleted')->eq(0)
        ->andWhere('status')->in('draft,active,launched,developing')
        ->andWhere('type')->eq('requirement')
        ->andWhere('product')->in($productKeys)
        ->fetchAll('id');

    foreach($stories as $story)
    {
        if($story->status == 'draft')
        {
            $products[$story->product]->draftStories++;
        }
        else if($story->status == 'active')
        {
            $products[$story->product]->activeStories++;
        }
        else if($story->status == 'launched')
        {
            $products[$story->product]->launchedStories++;
        }
        else if($story->status == 'developing')
        {
            $products[$story->product]->developingStories++;
        }
    }

    $roadmaps = $this->dao->select('id, product, status')->from(TABLE_ROADMAP)
        ->where('deleted')->eq(0)
        ->andWhere('status')->in('wait,launched')
        ->andWhere('product')->in($productKeys)
        ->fetchAll('id');
    foreach($roadmaps as $roadmap)
    {
        if($roadmap->status == 'wait')
        {
            $products[$roadmap->product]->waitedRoadmaps++;
        }
        else if($roadmap->status == 'launched')
        {
            $products[$roadmap->product]->launchedRoadmaps++;
        }
    }

    return $products;
}

/**
 * Batch update products.
 *
 * @access public
 * @return array
 */
public function batchUpdate()
{
    $products    = array();
    $allChanges  = array();
    $data        = fixer::input('post')->get();
    $oldProducts = $this->getByIdList($this->post->productIDList);
    $nameList    = array();

    $extendFields = $this->getFlowExtendFields();
    foreach($data->productIDList as $productID)
    {
        $productName = $data->names[$productID];

        $productID = (int)$productID;
        $products[$productID] = new stdClass();
        if(in_array($this->config->systemMode, array('ALM', 'PLM')) and isset($data->programs[$productID])) $products[$productID]->program = (int)$data->programs[$productID];
        if(in_array($this->config->systemMode, array('ALM', 'PLM')) and isset($data->lines[$productID]))    $products[$productID]->line    = (int)$data->lines[$productID];
        $products[$productID]->name    = $productName;
        $products[$productID]->PO      = isset($data->PO[$productID]) ? $data->PO[$productID] : $oldProducts[$productID]->PO;
        $products[$productID]->QD      = isset($data->QDs[$productID]) ? $data->QDs[$productID] : $oldProducts[$productID]->QD;
        $products[$productID]->RD      = isset($data->RDs[$productID]) ? $data->RDs[$productID] : $oldProducts[$productID]->RD;
        $products[$productID]->PMT     = empty($data->PMTs[$productID]) ? '' : implode(',', $data->PMTs[$productID]);
        $products[$productID]->type    = $data->types[$productID];
        $products[$productID]->desc    = strip_tags($this->post->descs[$productID], $this->config->allowedTags);
        $products[$productID]->acl     = $data->acls[$productID];
        $products[$productID]->id      = $productID;

        foreach($extendFields as $extendField)
        {
            $products[$productID]->{$extendField->field} = $this->post->{$extendField->field}[$productID];
            if(is_array($products[$productID]->{$extendField->field})) $products[$productID]->{$extendField->field} = join(',', $products[$productID]->{$extendField->field});

            $products[$productID]->{$extendField->field} = htmlSpecialString($products[$productID]->{$extendField->field});
        }
    }
    if(dao::isError()) return print(js::error(dao::getError()));

    $unlinkProducts = array();
    $linkProducts   = array();
    $this->lang->error->unique = $this->lang->error->repeat;
    foreach($products as $productID => $product)
    {
        $oldProduct = $oldProducts[$productID];
        if(in_array($this->config->systemMode, array('ALM', 'PLM'))) $programID  = !isset($product->program) ? $oldProduct->program : (empty($product->program) ? 0 : $product->program);

        $this->dao->update(TABLE_PRODUCT)
            ->data($product)
            ->autoCheck()
            ->batchCheck($this->config->product->edit->requiredFields , 'notempty')
            ->checkIF((!empty($product->name) and in_array($this->config->systemMode, array('ALM', 'PLM'))), 'name', 'unique', "id != $productID and `program` = $programID and `deleted` = '0'")
            ->checkFlow()
            ->where('id')->eq($productID)
            ->exec();
        if(dao::isError()) return print(js::error('product#' . $productID . dao::getError(true)));

        /* When acl is open, white list set empty. When acl is private,update user view. */
        if($product->acl == 'open') $this->loadModel('personnel')->updateWhitelist(array(), 'product', $productID);
        if($product->acl != 'open') $this->loadModel('user')->updateUserView($productID, 'product');
        if($product->type == 'normal' and $oldProduct->type != 'normal') $unlinkProducts[] = $productID;
        if($product->type != 'normal' and $oldProduct->type == 'normal') $linkProducts[] = $productID;

        $allChanges[$productID] = common::createChanges($oldProduct, $product);
    }

    if(!empty($unlinkProducts)) $this->loadModel('branch')->unlinkBranch4Project($unlinkProducts);
    if(!empty($linkProducts)) $this->loadModel('branch')->linkBranch4Project($linkProducts);

    return $allChanges;
}

/**
 * Get product by pool.
 *
 * @param  mixed  $poolID
 * @access public
 * @return void
 */
public function getProductByPool($poolID)
{
    $pool = $this->loadModel('demandpool')->getById($poolID);
    return $this->dao->select('*')->from(TABLE_PRODUCT)->where('id')->in($pool->products)->fetchPairs('id', 'name');
}
