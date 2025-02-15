<?php
class excelBug extends bugModel
{
    public function setListValue($productID, $branch = 0)
    {
        $product   = $this->loadModel('product')->getByID($productID);
        $projectID = $this->lang->navGroup->bug = 'project' ? $this->session->project : 0;
        $projects  = $this->product->getExecutionPairsByProduct($productID, $branch ? "0,$branch" : 0, 'id_asc', $projectID);
        $modules   = $this->loadModel('tree')->getOptionMenu($productID, 'bug', 0, $branch);
        $stories   = $this->loadModel('story')->getProductStories($productID, $branch);
        $builds    = $this->loadModel('build')->getBuildPairs($productID, $branch, 'noempty');

        $severityList = $this->lang->bug->severityList;
        $priList      = $this->lang->bug->priList;
        $typeList     = $this->lang->bug->typeList;
        $osList       = $this->lang->bug->osList;
        $browserList  = $this->lang->bug->browserList;

        unset($typeList['']);
        unset($projects['']);
        unset($typeList['designchange']);
        unset($typeList['newfeature']);
        unset($typeList['trackthings']);
        $projects[0] = '';

        foreach($projects as $id => $project) $projects[$id] = "$project(#$id)";
        foreach($modules  as $id => $module)  $modules[$id] .= "(#$id)";
        foreach($stories  as $id => $story)   $stories[$id]  = "$story->title(#$story->id)";
        foreach($builds as $id => $build)     $builds[$id]   = "$build(#$id)";

        if($product->type != 'normal')
        {
            $this->config->bug->export->listFields[] = 'branch';

            $branches = $this->loadModel('branch')->getPairs($product->id);
            foreach($branches as $id => $branch) $branches[$id] .= "(#$id)";

            $this->post->set('branchList', array_values($branches));
        }

        $this->post->set('moduleList',   array_values($modules));
        $this->post->set('storyList',    array_values($stories));
        $this->post->set('projectList',  array_values($projects));
        $this->post->set('severityList', join(',', $severityList));
        $this->post->set('priList',      join(',', $priList));
        $this->post->set('typeList',     join(',', $typeList));
        $this->post->set('osList',       join(',', $osList));
        $this->post->set('browserList',  join(',', $browserList));
        $this->post->set('listStyle',  $this->config->bug->export->listFields);
        $this->post->set('extraNum',   0);
        $this->post->set('product',    $product->name);
        $this->post->set('buildList',  $builds);
    }

    public function createFromImport($productID, $branch = 0)
    {
        $this->loadModel('action');
        $this->loadModel('story');
        $this->loadModel('file');
        $now    = helper::now();
        $branch = (int)$branch;
        $data   = fixer::input('post')->get();

        $this->app->loadClass('purifier', true);
        $purifierConfig = HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Filter.YouTube', 1);
        $purifier = new HTMLPurifier($purifierConfig);

        if(!empty($_POST['id'])) $oldBugs = $this->dao->select('*')->from(TABLE_BUG)->where('id')->in($_POST['id'])->andWhere('product')->eq($productID)->fetchAll('id');

        $bugs              = array();
        $line              = 1;
        $extendFields      = $this->getFlowExtendFields();
        $notEmptyRule      = $this->loadModel('workflowrule')->getByTypeAndRule('system', 'notempty');
        $storyVersionPairs = $this->story->getVersions($data->story);

        foreach($extendFields as $extendField)
        {
            if(strpos(",$extendField->rules,", ",$notEmptyRule->id,") !== false)
            {
                $this->config->bug->create->requiredFields .= ',' . $extendField->field;
            }
        }

        foreach($data->product as $key => $product)
        {
            $bugData = new stdclass();

            $bugData->project      = $this->session->project;
            $bugData->product      = $product;
            $bugData->branch       = isset($data->branch[$key]) ? (int)$data->branch[$key] : $branch;
            $bugData->module       = (int)$data->module[$key];
            $bugData->project      = (int)$data->project[$key];
            $bugData->openedBuild  = join(',', $data->openedBuild[$key]);
            $bugData->title        = $data->title[$key];
            $bugData->steps        = nl2br($purifier->purify($this->post->steps[$key]));
            $bugData->story        = (int)$data->story[$key];
            $bugData->pri          = (int)$data->pri[$key];
            $bugData->deadline     = $data->deadline[$key];
            $bugData->type         = $data->type[$key];
            $bugData->severity     = (int)$data->severity[$key];
            $bugData->os           = $data->os[$key];
            $bugData->browser      = $data->browser[$key];
            $bugData->keywords     = $data->keywords[$key];

            foreach($extendFields as $extendField)
            {
                $dataArray = $_POST[$extendField->field];
                $bugData->{$extendField->field} = $dataArray[$key];
                if(is_array($bugData->{$extendField->field})) $bugData->{$extendField->field} = join(',', $bugData->{$extendField->field});

                $bugData->{$extendField->field} = htmlSpecialString($bugData->{$extendField->field});
            }

            if(isset($this->config->bug->create->requiredFields))
            {
                $requiredFields = explode(',', $this->config->bug->create->requiredFields);
                foreach($requiredFields as $requiredField)
                {
                    $requiredField = trim($requiredField);
                    if(empty($bugData->$requiredField)) dao::$errors[] = sprintf($this->lang->bug->noRequire, $line, $this->lang->bug->$requiredField);
                }
            }

            $bugs[$key] = $bugData;
            $line++;
        }
        if(dao::isError()) die(js::error(dao::getError()));

        foreach($bugs as $key => $bugData)
        {
            $bugID = 0;
            if(!empty($_POST['id'][$key]) and empty($_POST['insert']))
            {
                $bugID = $data->id[$key];
                if(!isset($oldBugs[$bugID])) $bugID = 0;
            }

            if($bugID)
            {
                if($bugData->story != $oldBugs[$bugID]->story) $bugData->storyVersion = zget($storyVersionPairs, $bugData->story, 1);
                $bugData->steps = str_replace('src="' . common::getSysURL() . '/', 'src="', $bugData->steps);

                $oldBug = (array)$oldBugs[$bugID];
                $newBug = (array)$bugData;
                $oldBug['steps'] = trim($this->file->excludeHtml($oldBug['steps'], 'noImg'));
                $newBug['steps'] = trim($this->file->excludeHtml($newBug['steps'], 'noImg'));
                $changes = common::createChanges((object)$oldBug, (object)$newBug);
                if(empty($changes)) continue;

                $bugData->lastEditedBy   = $this->app->user->account;
                $bugData->lastEditedDate = $now;
                $this->dao->update(TABLE_BUG)->data($bugData)->where('id')->eq($bugID)->autoCheck()->checkFlow()->exec();

                if(!dao::isError())
                {
                    $actionID = $this->action->create('bug', $bugID, 'Edited');
                    $this->action->logHistory($actionID, $changes);
                }
            }
            else
            {
                if($bugData->story) $bugData->storyVersion = zget($storyVersionPairs, $bugData->story, 1);
                $bugData->openedBy   = $this->app->user->account;
                $bugData->openedDate = $now;

                $this->dao->insert(TABLE_BUG)->data($bugData)->autoCheck()->checkFlow()->exec();

                if(!dao::isError())
                {
                    $bugID = $this->dao->lastInsertID();
                    $this->action->create('bug', $bugID, 'Opened');
                }
            }
        }

        if($this->post->isEndPage)
        {
            unlink($this->session->fileImport);
            unset($_SESSION['fileImport']);
        }
    }
}
