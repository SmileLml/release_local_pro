<?php
class charterModel extends model
{
    public function getByID($charterID = 0)
    {
        $charter = $this->dao->findByID($charterID)->from(TABLE_CHARTER)->fetch();
        if(!$charter) return false;

        $charter->files = $this->loadModel('file')->getByObject('charter', $charterID);
        $charter->charterFiles = $charter->charterFiles ? json_decode($charter->charterFiles , true) : array();

        return $charter;
    }

    /**
     * review
     *
     * @param  int    $charterID

     * @access public
     * @return void
     */
    public function review($charterID)
    {
        $oldCharter = $this->getByID($charterID);

        if (empty($_POST['reviewedBy']) || empty($_POST['reviewedResult']))
        {
            if (empty($_POST['reviewedBy']))
            {
                dao::$errors['reviewedBy'] = sprintf($this->lang->error->notempty, $this->lang->charter->reviewer);
            }
            if (empty($_POST['reviewedResult']) and $oldCharter->status == 'wait')
            {
                dao::$errors['reviewedResult'] = sprintf($this->lang->error->notempty, $this->lang->charter->review);
            }
        }
        if(dao::isError()) return false;

        $now   = helper::now();
        $today = helper::today();

        $charter = fixer::input('post')
            ->setDefault('status', $oldCharter->status)
            ->setDefault('reviewedResult', $oldCharter->reviewedResult)
            ->setIF(!isset($_POST['check']), 'check', 0)
            ->setIF(!isset($_POST['check']), 'meetingDate', '')
            ->setIF(!isset($_POST['check']), 'meetingLocation', '')
            ->setIF(!isset($_POST['check']), 'meetingMinutes', '')
            ->stripTags($this->config->charter->editor->review['id'], $this->config->allowedTags)
            ->join('reviewedBy', ',')
            ->remove('uid,files,labels')
            ->get();

        $charter->status     = $charter->reviewedResult == 'launched' ? 'launched' : 'failed';
        $charter->reviewedBy = implode(',', $_POST['reviewedBy']);

        $roadmap = new stdclass();
        $roadmap->status = $charter->reviewedResult == 'failed' ? 'reject' : 'launched';

        $charter = $this->loadModel('file')->processImgURL($charter, $this->config->charter->editor->create['id'], $this->post->uid);
        $this->dao->update(TABLE_CHARTER)->data($charter)->autoCheck()->checkFlow()->where('id')->eq($charterID)->exec();
        $this->dao->update(TABLE_ROADMAP)->data($roadmap)->where('id')->eq($oldCharter->roadmap)->exec();

        if(!dao::isError())
        {
            if($charter->reviewedResult == 'launched' and $oldCharter->product)
            {
                $product = $this->loadModel('product')->getByID($oldCharter->product);
                if($product->status == 'wait')
                {
                    $this->dao->update(TABLE_PRODUCT)
                        ->set('status')->eq('normal')
                        ->set('vision')->eq($product->vision . ',rnd')
                        ->where('id')->eq($oldCharter->product)
                        ->exec();
                    $this->loadModel('action')->create('product', $oldCharter->product, 'changedbycharter', '', $charterID);
                }

                $roadmapStories = $this->loadModel('roadmap')->getRoadmapStories($oldCharter->roadmap);
                $demands        = $this->loadModel('demand')->getList(0, 'all');
                foreach($roadmapStories as $story)
                {
                    if($story->status == 'launched') continue;
                    $this->dao->update(TABLE_STORY)
                        ->set('status')->eq('launched')
                        ->set('vision')->eq($story->vision . ',rnd')
                        ->where('id')->eq($story->id)
                        ->exec();

                    $this->loadModel('action')->create('story', $story->id, 'changedbycharter', '', $charterID);
                    if($story->demand)
                    {
                        $this->dao->update(TABLE_DEMAND)->set('status')->eq('launched')->where('id')->eq($story->demand)->exec();
                        if(!empty($demands[$story->demand]->feedback))
                        {
                            $feedback = new stdclass();
                            $feedback->status        = 'commenting';
                            $feedback->processedBy   = $this->app->user->account;
                            $feedback->processedDate = helper::now();

                            $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($demands[$story->demand]->feedback)->exec();
                        }
                    }

                    if($story->feedback)
                    {
                        $feedback = new stdclass();
                        $feedback->status        = 'commenting';
                        $feedback->processedBy   = $this->app->user->account;
                        $feedback->processedDate = helper::now();

                        $this->dao->update(TABLE_FEEDBACK)->data($feedback)->where('id')->eq($story->feedback)->exec();
                    }
                }
            }

            $this->loadModel('score')->create('charter', 'confirmCharter', $oldCharter);
            return common::createChanges($oldCharter, $charter);
        }

        return false;
    }

    /**
     * Update a charter.
     *
     * @access int $charterID
     * @access public
     * @return void
     */
    public function update($charterID)
    {
        $oldCharter = $this->getByID($charterID);
        $charter = fixer::input('post')
            ->setDefault('deleteFiles', array())
            ->setDefault('roadmap', 0)
            ->remove('uid,files,labels')
            ->stripTags($this->config->charter->editor->edit['id'], $this->config->allowedTags)
            ->get();

        $this->app->loadLang('project');
        if(!empty($charter->budget))
        {
            if(!is_numeric($charter->budget))
            {
                dao::$errors['budget'] = sprintf($this->lang->project->budgetNumber);
                return false;
            }
            else if(is_numeric($charter->budget) and ($charter->budget < 0))
            {
                dao::$errors['budget'] = sprintf($this->lang->project->budgetGe0);
                return false;
            }
            else
            {
                $charter->budget = round((float)$this->post->budget, 2);
            }
        }

        $this->dao->update(TABLE_CHARTER)->data($charter, 'deleteFiles')->autoCheck()
            ->batchCheck($this->config->charter->edit->requiredFields, 'notempty')
            ->where('id')->eq($charterID)
            ->exec();

        if(!dao::isError())
        {
            if(!empty($charter->deleteFiles))
            {
                $deleteFiles = $charter->deleteFiles;

                $this->dao->delete()->from(TABLE_FILE)->where('id')->in($deleteFiles)->exec();
                foreach($deleteFiles as $fileID)
                {
                    $this->loadModel('file')->unlinkFile($oldCharter->files[$fileID]);
                    if($oldCharter->charterFiles)
                    {
                        foreach($oldCharter->charterFiles as $type => $file)
                        {
                            if($file['id'] == $fileID) unset($oldCharter->charterFiles[$type]);
                        }
                    }
                }
            }

            $files = $this->saveUpload('charter', $charterID);
            $files = array_merge((array)$oldCharter->charterFiles, $files);

            $this->dao->update(TABLE_CHARTER)->set('charterFiles')->eq(json_encode($files))->where('id')->eq($charterID)->exec();
            return common::createChanges($oldCharter, $charter);
        }

        return false;
    }

    /**
     * Get charter list.
     *
     * @param  int    $browseType
     * @param  int    $queryID
     * @param  int    $orderBy
     * @param  int    $pager
     * @param  string $extra
     * @access public
     * @return void
     */
    public function getList($browseType, $queryID = 0, $orderBy = 'id_desc', $pager = null, $extra = '')
    {
        $charterQuery = '';
        if($browseType == 'bysearch')
        {
            $query = $queryID ? $this->loadModel('search')->getQuery($queryID) : '';
            if($query)
            {
                $this->session->set('charterQuery', $query->sql);
                $this->session->set('charterForm', $query->form);
            }

            if($this->session->charterQuery == false) $this->session->set('charterQuery', ' 1 = 1');
            $charterQuery = $this->session->charterQuery;
        }

        $charters = $this->dao->select('*')->from(TABLE_CHARTER)
            ->where('deleted')->eq('0')
            ->beginIF($browseType != 'all' and $browseType != 'bysearch')->andWhere('status')->eq($browseType)->fi()
            ->beginIF($browseType == 'bysearch')->andWhere($charterQuery)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');

        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'charter', $browseType != 'bysearch');

        return $charters;
    }

    /**
     * Get charter pairs.
     *
     * @param  string $status
     * @access public
     * @return void
     */
    public function getPairs($status = 'launched')
    {
        return $this->dao->select('id, name')->from(TABLE_CHARTER)
            ->where('deleted')->eq('0')
            ->andWhere('status')->eq($status)
            ->orderBy('id_desc')
            ->fetchPairs();
    }

    /**
     * Create a charter.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        $today = helper::today();
        $now   = helper::now();
        $this->app->loadLang('project');

        $charter = fixer::input('post')
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', $now)
            ->setDefault('status', 'wait')
            ->setDefault('roadmap', 0)
            ->setIF($this->post->appliedBy, 'appliedDate', $now)
            ->remove('uid,files,labels')
            ->get();

        if(!empty($charter->budget))
        {
            if(!is_numeric($charter->budget))
            {
                dao::$errors['budget'] = sprintf($this->lang->project->budgetNumber);
                return false;
            }
            else if(is_numeric($charter->budget) and ($charter->budget < 0))
            {
                dao::$errors['budget'] = sprintf($this->lang->project->budgetGe0);
                return false;
            }
            else
            {
                $charter->budget = round((float)$this->post->budget, 2);
            }
        }

        $charter = $this->loadModel('file')->processImgURL($charter, $this->config->charter->editor->create['id'], $this->post->uid);
        $this->dao->insert(TABLE_CHARTER)->data($charter)
            ->autoCheck()
            ->batchCheck($this->config->charter->create->requiredFields, 'notempty')
            ->exec();

        if(!dao::isError())
        {
            $charterID = $this->dao->lastInsertID();

            if($this->post->roadmap)
            {
                $this->dao->update(TABLE_ROADMAP)->set('status')->eq('launching')->where('id')->eq($this->post->roadmap)->exec();
                $this->loadModel('action')->create('roadmap', $this->post->roadmap, 'linked2charter', '', $charterID);
            }

            $this->loadModel('file')->updateObjectID($this->post->uid, $charterID, 'charter');
            $files = $this->saveUpload('charter', $charterID);

            if($files) $this->dao->update(TABLE_CHARTER)->set('charterFiles')->eq(json_encode($files))->where('id')->eq($charterID)->exec();
            return $charterID;
        }

        return false;
    }

    /**
     * Close a charter.
     *
     * @param  int    $charterID
     * @access public
     * @return void
     */
    public function close($charterID)
    {
        $oldCharter = $this->dao->findById($charterID)->from(TABLE_CHARTER)->fetch();
        $now        = helper::now();
        $charter    = fixer::input('post')
            ->add('status', 'closed')
            ->setDefault('closedDate', $now)
            ->setDefault('closedBy', $this->app->user->account)
            ->setDefault('activatedBy', '')
            ->setDefault('activatedDate', '')
            ->remove('uid')
            ->get();

        $this->dao->update(TABLE_CHARTER)->data($charter, 'comment')
            ->autoCheck()
            ->where('id')->eq((int)$charterID)
            ->exec();
        if(!dao::isError()) return common::createChanges($oldCharter, $charter);

        return false;
    }

    /**
     * Activate a charter.
     *
     * @param  int    $charterID
     * @access public
     * @return void
     */
    public function activate($charterID)
    {
        $oldCharter = $this->dao->findById($charterID)->from(TABLE_CHARTER)->fetch();
        $lastStatus = $this->dao->select('*')->from(TABLE_ACTION)
            ->where('objectID')->eq($charterID)
            ->andWhere('objectType')->eq('charter')
            ->andWhere('action')->eq('closed')
            ->orderBy('id_desc')
            ->fetch('extra');

        $now        = helper::now();
        $charter    = fixer::input('post')
            ->add('status', $lastStatus)
            ->setDefault('activatedDate', helper::now())
            ->setDefault('activatedBy', $this->app->user->account)
            ->setDefault('closedBy', '')
            ->setDefault('closedDate', '')
            ->setDefault('closedReason', '')
            ->remove('uid')
            ->get();

        $this->dao->update(TABLE_CHARTER)->data($charter, 'comment')
            ->autoCheck()
            ->where('id')->eq((int)$charterID)
            ->exec();
        if(!dao::isError()) return common::createChanges($oldCharter, $charter);
        return false;
    }
    /**
     * Get info of uploaded files.
     *
     * @param  string $htmlTagName
     * @param  string $labelsName
     * @access public
     * @return array
     */
    public function getUpload($htmlTagName = 'files', $labelsName = 'labels')
    {
        $this->loadModel('file');
        $files = array();
        if(!isset($_FILES[$htmlTagName])) return $files;

        if(!is_array($_FILES[$htmlTagName]['error']) and $_FILES[$htmlTagName]['error'] != 0) return $_FILES[$htmlTagName];

        $this->app->loadClass('purifier', true);
        $config   = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $purifier = new HTMLPurifier($config);

        extract($_FILES[$htmlTagName]);
        foreach($name as $id => $filename)
        {
            if(empty($filename)) continue;
            if(!validater::checkFileName($filename)) continue;

            $title             = isset($_POST[$labelsName][$id]) ? $_POST[$labelsName][$id] : '';
            $file['extension'] = $this->file->getExtension($filename);
            $file['pathname']  = $this->file->setPathName($id, $file['extension']);
            $file['title']     = (!empty($title) and $title != $filename) ? htmlSpecialString($title) : $filename;
            $file['title']     = $purifier->purify($file['title']);
            $file['size']      = $size[$id];
            $file['tmpname']   = $tmp_name[$id];
            $file['type']      = $id;
            $files[] = $file;
        }

        return $files;
    }

    /**
     * Save upload.
     *
     * @param  string $objectType
     * @param  string $objectID
     * @param  string $extra
     * @param  string $filesName
     * @param  string $labelsName
     * @access public
     * @return array
     */
    public function saveUpload($objectType = '', $objectID = '', $extra = '', $filesName = 'files', $labelsName = 'labels')
    {
        $this->loadModel('file');
        $fileTitles = array();
        $now        = helper::today();
        $files      = $this->getUpload($filesName, $labelsName);

        foreach($files as $id => $file)
        {
            $type = $file['type'];
            if($file['size'] == 0) continue;
            if(!move_uploaded_file($file['tmpname'], $this->file->savePath . $this->file->getSaveName($file['pathname']))) return false;

            $file = $this->file->compressImage($file);

            $file['objectType'] = $objectType;
            $file['objectID']   = $objectID;
            $file['addedBy']    = $this->app->user->account;
            $file['addedDate']  = $now;
            $file['extra']      = $extra;
            unset($file['tmpname']);
            unset($file['type']);
            $this->dao->insert(TABLE_FILE)->data($file)->exec();
            $fileID = $this->dao->lastInsertId();
            $fileTitles[$type]['id']    = $fileID;
            $fileTitles[$type]['title'] = $file['title'];
        }
        return $fileTitles;
    }

    /**
     * Build charter operate menu.
     *
     * @param  object $charter
     * @param  string $type
     * @access public
     * @return void
     */
    public function buildOperateMenu($charter, $type = 'view')
    {
        $this->app->loadLang('story');
        $menu    = '';
        $params  = "id={$charter->id}";
        $account = $this->app->user->account;

        $title          = '';
        $disabled       = '';
        $reviewtitle    = '';
        $reviewdisabled = '';

        $roadmap      = $this->loadModel('roadmap')->getByID($charter->roadmap);
        $charterFiled = $this->dao->select(' id, name, reviewedResult')->from(TABLE_CHARTER)
            ->where('roadmap')->eq($charter->roadmap)
            ->fetchAll('id');

        foreach($charterFiled as $Filed)
        {
            if($Filed->reviewedResult === 'launched')
            {
                $charterInfo       =  new stdclass();
                $charterInfo->id   = $Filed->id;
                $charterInfo->name = $Filed->name;
            }
        }

        if($charter->status == 'closed')
        {
            $menu .= $this->buildMenu('charter', 'activate', $params, $charter, $type, 'magic', 'hiddenwin');
        }
        else
        {
            $menu .= $this->buildMenu('charter', 'close', $params, $charter, $type, 'off', '', 'iframe', true, '', $this->lang->close);
        }

        if($type == 'browse')
        {
            $menu .= $this->buildMenu('charter', 'edit', $params, $charter, $type);
            $menu .= $this->buildMenu('charter', 'review', $params, $charter, $type, 'confirm', 'hiddenwin',  "iframe $reviewdisabled", true, $reviewdisabled, $reviewtitle);
        }

        if($type == 'view')
        {
            $menu .= "<div class='divider'></div>";
            $menu .= $this->buildMenu('charter', 'edit', $params, $charter, $type);
            $menu .= $this->buildMenu('charter', 'review', $params, $charter, $type, 'confirm', 'hiddenwin',  "iframe $reviewdisabled", true, $reviewdisabled, $reviewtitle);

            if($charter->status == 'launched') $title = $this->lang->charter->confirmLaunchedNotice;
            if($charter->status != 'wait') $disabled = 'disabled';
            $menu .= $this->buildMenu('charter', 'delete', $params, $charter, 'button', 'trash', 'hiddenwin', "showinonlybody $disabled", true, '', $title);
        }

        return $menu;
    }

    /**
     * Judge btn is clickable.
     *
     * @param  object   $charter
     * @param  string   $action
     * @static
     * @access public
     * @return void
     */
    public static function isClickable($charter, $action)
    {
        global $app, $config, $dbh;
        $account = $app->user->account;
        $action  = strtolower($action);

        if($action == 'edit') return $charter->status == 'wait';
        if($action == 'delete') return $charter->status == 'wait';
        if($action == 'review') return $charter->status != 'closed';
        return true;
    }
}
