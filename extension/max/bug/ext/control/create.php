<?php
helper::importControl('bug');
class myBug extends bug
{
    public function create($productID, $branch = '', $extras = '')
    {
        $realExtras = $extras;
        $extras     = str_replace(array(',', ' '), array('&', ''), $extras);
        parse_str($extras, $params);
        foreach($params as $varName => $varValue) $$varName = $varValue;

        if(!empty($fromType))
        {
            if(empty($this->products)) $this->locate($this->createLink('product', 'create'));

            $fromObject = $this->loadModel($fromType)->getById($fromID);
            $actions    = $this->loadModel('action')->getList($fromType, $fromID);
            if(!$fromObject) die(js::error($this->lang->notFound) . js::locate('back', 'parent'));

            switch($fromType)
            {
                case 'feedback':
                    $this->loadModel('feedback')->setMenu($productID, 'feedback', $realExtras);

                    foreach($actions as $action)
                    {
                        if($action->action == 'reviewed' and $action->comment)
                        {
                            $feedback->desc .= $feedback->desc ? '<br/>' . $this->lang->feedback->reviewOpinion . '：' . $action->comment : $this->lang->feedback->reviewOpinion . '：' . $action->comment;
                        }
                    }

                    $location = $this->createLink('feedback', 'adminView', "feedbackID=$fromID");
                    break;
                default:
                    $location = $this->createLink($fromType, 'view', "fromObjectID=$fromID");
                    break;
            }

            $this->view->users = $this->user->getPairs('devfirst|noclosed|nodeleted');
            $this->app->loadLang('release');

            if(!empty($_POST) and !isset($_POST['stepIDList']))
            {
                $response['result']  = 'success';
                $response['message'] = $this->lang->saveSuccess;

                setcookie('lastBugModule', (int)$this->post->module, $this->config->cookieLife, $this->config->webRoot, '', false, false);
                $bugResult = $this->bug->create();
                if(!$bugResult or dao::isError())
                {
                    $response['result']  = 'fail';
                    $response['message'] = dao::getError();
                    return $this->send($response);
                }

                $bugID = $bugResult['id'];
                if($bugResult['status'] == 'exists')
                {
                    $response['message'] = sprintf($this->lang->duplicate, $this->lang->bug->common);
                    $response['locate']  = $this->createLink('bug', 'view', "bugID=$bugID");
                    return $this->send($response);
                }

                $actionID = $this->action->create('bug', $bugID, 'From' . ucfirst($fromType), '', $fromID);

                $response['locate'] = $location;
                if(isonlybody()) $response['locate'] = 'parent';
                return $this->send($response);
            }

            /* Get product, then set menu. */
            $productID   = $this->product->saveState($productID, $this->products);
            $productInfo = $this->product->getById($productID);

            if($branch === '') $branch = (int)$this->cookie->preBranch;
            if($branch == 0)
            {
                $branch = $this->loadModel('branch')->getPairsByProjectProduct($projectID, $productID);
                $branch = key($branch);
            }
            $branches = $productInfo->type != 'normal' ? $this->loadModel('branch')->getPairs($productID, 'active') : array();

            /* Set Menu. */
            $this->lang->feedback->menu->browse['subModule'] = 'bug';

            /* Init vars. */
            $projectID   = 0;
            $moduleID    = 0;
            $executionID = 0;
            $taskID      = 0;
            $storyID     = 0;
            $buildID     = 0;
            $caseID      = 0;
            $runID       = 0;
            $testtask    = 0;
            $version     = 0;
            $title       = '';
            $steps       = $this->lang->bug->tplStep . $this->lang->bug->tplResult . $this->lang->bug->tplExpect;
            $os          = '';
            $browser     = '';
            $assignedTo  = '';
            $deadline    = '';
            $mailto      = '';
            $keywords    = '';
            $severity    = 3;
            $type        = 'codeerror';
            $pri         = 3;
            $color       = '';
            $steps       = htmlspecialchars($fromObject->desc);

            /* Parse the extras. extract fix php7.2. */
            $extras = str_replace(array(',', ' '), array('&', ''), $extras);
            parse_str($extras, $output);
            extract($output);

            if($runID and $resultID) extract($this->bug->getBugInfoFromResult($resultID, 0, 0, isset($stepIdList) ? $stepIdList : ''));// If set runID and resultID, get the result info by resultID as template.
            if(!$runID and $caseID)  extract($this->bug->getBugInfoFromResult($resultID, $caseID, $version, isset($stepIdList) ? $stepIdList : ''));// If not set runID but set caseID, get the result info by resultID and case info.

            /* If bugID setted, use this bug as template. */
            if(isset($bugID))
            {
                $bug = $this->bug->getById($bugID);
                extract((array)$bug);

                $executionID = $bug->execution;
                $moduleID    = $bug->module;
                $taskID      = $bug->task;
                $storyID     = $bug->story;
                $buildID     = $bug->openedBuild;
                $severity    = $bug->severity;
                $type        = $bug->type;
                $assignedTo  = $bug->assignedTo;
                $deadline    = $bug->deadline;
                $color       = $bug->color;
                $testtask    = $bug->testtask;
            }

            if($testtask)
            {
                $testtask = $this->loadModel('testtask')->getById($testtask);
                $buildID  = $testtask->build;
            }

            if(isset($todoID))
            {
                $todo  = $this->loadModel('todo')->getById($todoID);
                $title = $todo->name;
                $steps = $todo->desc;
                $pri   = $todo->pri;
            }

            /* If executionID is setted, get builds and stories of this execution. */
            if($executionID)
            {
                $builds  = $this->loadModel('build')->getBuildPairs($productID, $branch ? $branch : 0, 'noempty,noterminate,nodone,noreleased', $executionID, 'execution');
                $stories = $this->story->getExecutionStoryPairs($executionID);
                if(!$projectID) $projectID = $this->dao->select('project')->from(TABLE_EXECUTION)->where('id')->eq($executionID)->fetch('project');
            }
            else
            {
                $builds  = $this->loadModel('build')->getBuildPairs($productID, $branch ? $branch : 0, 'noempty,noterminate,nodone,withbranch,noreleased');
                $stories = $this->story->getProductStoryPairs($productID, $branch);
            }

            $builds[''] = '';

            $moduleOwner = $this->bug->getModuleOwner($moduleID, $productID);

            /* Set team members of the latest execution as assignedTo list. */
            $productMembers = $this->bug->getProductMemberPairs($productID);
            if(empty($productMembers)) $productMembers = $this->view->users;
            if($assignedTo and !isset($productMembers[$assignedTo]))
            {
                $user = $this->loadModel('user')->getById($assignedTo);
                if($user) $productMembers[$assignedTo] = $user->realname;
            }

            $moduleOptionMenu = $this->tree->getOptionMenu($productID, $viewType = 'bug', $startModuleID = 0, $branch);
            if(empty($moduleOptionMenu)) die(js::locate(helper::createLink('tree', 'browse', "productID=$productID&view=story")));

            /* Get products and projects. */
            $products     = $this->config->CRProduct ? $this->products : $this->product->getPairs('noclosed');
            $projects     = array(0 => '');
            $projectModel = '';
            if($projectID)
            {
                $products    = array();
                $productList = $this->config->CRProduct ? $this->product->getOrderedProducts('all', 40, $projectID) : $this->product->getOrderedProducts('normal', 40, $projectID);
                foreach($productList as $product) $products[$product->id] = $product->name;

                $project = $this->loadModel('project')->getByID($projectID);
                if($project)
                {
                    $projects += array($projectID => $project->name);
                    $projectModel = $project->model;
                }

                /* Set project menu. */
                if($this->app->tab == 'project') $this->project->setMenu($projectID);
            }
            else
            {
                $projects += $this->product->getProjectPairsByProduct($productID, $branch);
            }

            /* Get block id of assinge to me. */
            $blockID = 0;
            if(isonlybody())
            {
                $blockID = $this->dao->select('id')->from(TABLE_BLOCK)
                    ->where('block')->eq('assingtome')
                    ->andWhere('module')->eq('my')
                    ->andWhere('account')->eq($this->app->user->account)
                    ->orderBy('order_desc')
                    ->fetch('id');
            }

            /* Get executions. */
            $executions = array('' => '');
            if(isset($projects[$projectID])) $executions += $this->product->getExecutionPairsByProduct($productID, $branch ? "0,$branch" : 0, 'id_desc', $projectID);
            $execution  = $executionID ? $this->loadModel('execution')->getByID($executionID) : '';
            $executions = isset($executions[$executionID]) ? $executions : $executions + array($executionID => $execution->name);

            if(!isset($projectID))
            {
                $executionID = $projectID;
                $projectID   = 0;
            }
            $projects  = array(0 => '');
            $projects += $this->product->getProjectPairsByProduct($productID, $branch);

            switch($fromType)
            {
                case 'feedback':
                    $moduleID                = $fromObject->module;
                    $sourceObject            = $this->loadModel('feedback')->getById($fromID);
                    $this->view->feedbackID  = $fromID;
                    $this->view->feedback    = $fromObject;
                    $this->view->sourceFiles = $sourceObject->files;
                    break;
                case 'ticket':
                    $moduleID                = $fromObject->module;
                    $sourceObject            = $this->loadModel('ticket')->getByID($fromID);
                    $this->view->ticketID    = $fromID;
                    $this->view->ticket      = $fromObject;
                    $this->view->sourceFiles = $sourceObject->files;
                    break;
            }

            /* Set custom. */
            foreach(explode(',', $this->config->bug->list->customCreateFields) as $field) $customFields[$field] = $this->lang->bug->$field;

            $this->view->title                 = $this->products[$productID] . $this->lang->colon . $this->lang->bug->create;
            $this->view->customFields          = $customFields;
            $this->view->showFields            = $this->config->bug->custom->createFields;
            $this->view->gobackLink            = (isset($output['from']) and $output['from'] == 'global') ? $this->createLink('bug', 'browse', "productID=$productID") : '';
            $this->view->productID             = $productID;
            $this->view->productName           = $this->products[$productID];
            $this->view->moduleOptionMenu      = $this->tree->getOptionMenu($productID, $viewType = 'bug', $startModuleID = 0, $branch);
            $this->view->users                 = $this->user->getPairs('devfirst|noclosed|nodeleted');
            $this->view->stories               = $stories;
            $this->view->executions            = $this->product->getExecutionPairsByProduct($productID, $branch ? "0,$branch" : 0, 'id_asc', $projectID);
            $this->view->builds                = $builds;
            $this->view->projectExecutionPairs = $this->loadModel('project')->getProjectExecutionPairs();
            $this->view->moduleID              = $moduleID ? $moduleID : (int)$this->cookie->lastBugModule;
            $this->view->projectModel          = $projectModel;
            $this->view->execution             = $execution;
            $this->view->taskID                = $taskID;
            $this->view->storyID               = $storyID;
            $this->view->buildID               = $buildID;
            $this->view->caseID                = $caseID;
            $this->view->runID                 = $runID;
            $this->view->version               = $version;
            $this->view->pri                   = $pri;
            $this->view->color                 = $color;
            $this->view->testtask              = $testtask;
            $this->view->bugTitle              = $fromObject->title;
            $this->view->steps                 = $steps;
            $this->view->os                    = $os;
            $this->view->browser               = $browser;
            $this->view->productMembers        = $productMembers;
            $this->view->assignedTo            = $assignedTo;
            $this->view->mailto                = $mailto;
            $this->view->keywords              = $keywords;
            $this->view->severity              = $severity;
            $this->view->type                  = $type;
            $this->view->branch                = $branch;
            $this->view->branches              = $branches;
            $this->view->projectID             = $projectID;
            $this->view->projects              = $projects;
            $this->view->blockID               = 0;
            $this->view->deadline              = '';
            $this->view->product               = $productInfo;
            $this->view->stepsRequired         = strpos($this->config->bug->create->requiredFields, 'steps');
            $this->view->isStepsTemplate       = $steps == $this->lang->bug->tplStep . $this->lang->bug->tplResult . $this->lang->bug->tplExpect ? true : false;

            $this->display();
        }
        else
        {
            return parent::create($productID, $branch, $extras);
        }
    }
}
