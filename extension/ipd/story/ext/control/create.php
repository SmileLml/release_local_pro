<?php
helper::importControl('story');
class myStory extends story
{
    /**
     * Create story from feedback.
     *
     * @param  int    $productID
     * @param  int    $branch
     * @param  int    $moduleID
     * @param  int    $storyID
     * @param  int    $objectID
     * @param  int    $bugID
     * @param  int    $planID
     * @param  int    $todoID
     * @param  string $extra
     * @param  string $storyType story|requirement
     * @access public
     * @return void
     */
    public function create($productID = 0, $branch = 0, $moduleID = 0, $storyID = 0, $objectID = 0, $bugID = 0, $planID = 0, $todoID = 0, $extra = '', $storyType = 'story')
    {
        if($extra)
        {
            $extras = str_replace(array(',', ' '), array('&', ''), $extra);
            parse_str($extras, $params);
            foreach($params as $varName => $varValue) $$varName = $varValue;
        }

        if(!empty($fromType))
        {
            $this->story->replaceURLang($storyType);

            /* Get information and history of from object. */
            $fromObject = $this->loadModel($fromType)->getById($fromID);
            $actions    = $this->loadModel('action')->getList($fromType, $fromID);
            if(!$fromObject) die(js::error($this->lang->notFound) . js::locate('back', 'parent'));

            $fromOpenedBy = $this->loadModel('user')->getById($fromObject->openedBy);

            switch($fromType)
            {
                case 'feedback': // Change desc if feedback has been reviewed.
                    foreach($actions as $action)
                    {
                        if($action->action == 'reviewed' and $action->comment)
                        {
                            $fromObject->desc .= $fromObject->desc ? '<br/>' . $this->lang->feedback->reviewOpinion . '：' . $action->comment : $this->lang->feedback->reviewOpinion . '：' . $action->comment;
                        }
                    }

                    $moduleID                = $fromObject->module;
                    $location                = $this->createLink('feedback', 'adminView', "feedbackID=$fromID");
                    $this->view->feedbackID  = $fromObject->id;
                    $this->view->sourceFiles = $fromObject->files;
                    break;

                case 'ticket':
                    $moduleID                = $fromObject->module;
                    $location                = $this->createLink($fromType, 'view', "fromObjectID=$fromID");
                    $this->view->ticketID    = $fromObject->id;
                    $this->view->sourceFiles = $fromObject->files;
                    break;

                default:
                    $location = $this->createLink($fromType, 'view', "fromObjectID=$fromID");
                    break;
            }

            /* Create story from fromObject and send email. */
            if(!empty($_POST))
            {
                $response['result']  = 'success';
                $response['message'] = $this->post->status == 'draft' ? $this->lang->story->saveDraftSuccess : $this->lang->saveSuccess;

                setcookie('lastStoryModule', (int)$this->post->module, $this->config->cookieLife, $this->config->webRoot, '', false, false);
                $storyResult = $this->story->create($objectID, $bugID);
                if(!$storyResult or dao::isError())
                {
                    $response['result']  = 'fail';
                    $response['message'] = dao::getError();
                    return $this->send($response);
                }

                $storyID = $storyResult['id'];
                $response['locate'] = $location;
                if(isonlybody()) $response['locate'] = 'parent';
                if($storyResult['status'] == 'exists')
                {
                    $response['message'] = sprintf($this->lang->duplicate, $this->lang->story->common);
                    return $this->send($response);
                }

                $this->action->create('story', $storyID, 'From' . ucfirst($fromType), '', $fromID);

                /* Record the action of twins story. */
                $twinsStoryIdList = zget($storyResult, 'ids', '');
                if(!empty($twinsStoryIdList))
                {
                    foreach($twinsStoryIdList as $twinsStoryID)
                    {
                        if($twinsStoryID == $storyID) continue;
                        $this->action->create('story', $twinsStoryID, 'From' . ucfirst($fromType), '', $fromID);
                    }
                }

                if($this->post->newStory)
                {
                    $response['message'] = $this->lang->story->successSaved . $this->lang->story->newStory;
                    return $this->send($response);
                }

                return $this->send($response);
            }

            /* Set products, users and module. */
            $products = $this->product->getPairs('noclosed', 0, '', 'all');
            $product  = $this->product->getById($productID ? $productID : key($products));
            if(!isset($products[$product->id])) $products[$product->id] = $product->name;

            /* If From feedback and is requirement unset kanban project of no product. */
            if($storyType == 'requirement' and !empty($fromType) and $fromType == 'feedback')
            {
                $kanbanProjects = $this->dao->select('t2.product')->from(TABLE_PROJECT)->alias('t1')
                    ->leftJoin(TABLE_PROJECTPRODUCT)->alias('t2')->on('t1.id = t2.project')
                    ->where('t1.model')->eq('kanban')
                    ->andWhere('t1.type')->eq('project')
                    ->andWhere('t1.hasProduct')->eq(0)
                    ->fetchPairs('product', 'product');

                foreach($products as $id => $productName)
                {
                    if(isset($kanbanProjects[$id])) unset($products[$id]);
                }
            }

            $this->view->hiddenURS     = false;
            $this->view->hiddenPlan    = false;
            $this->view->hiddenProduct = false;
            $this->view->hiddenParent  = false;
            $this->view->hiddenPlan    = false;
            $this->view->teamUsers     = array();

            if($product->shadow)
            {
                $project = $this->loadModel('project')->getByShadowProduct($product->id);

                if(empty($project->hasProduct))
                {
                    $this->view->teamUsers = $this->project->getTeamMemberPairs($project->id);

                    if($project->model !== 'scrum')  $this->view->hiddenPlan = true;
                    if(!$project->multiple)          $this->view->hiddenPlan = true;
                    if($project->model === 'kanban') $this->view->hiddenURS  = true;
                }
            }

            $users = $this->user->getPairs('pdfirst|noclosed|nodeleted');
            $moduleOptionMenu = $this->tree->getOptionMenu($productID, $viewType = 'story', 0, $branch);

            /* Set feedback menu. */
            $this->lang->feedback->menu->browse['subModule'] = 'story';
            $this->lang->feedback->menu->ticket['subModule'] = 'story';

            /* Init vars. */
            $pri       = 3;
            $estimate  = '';
            $verify    = '';
            $keywords  = '';
            $mailto    = '';

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

            /* Get reviewers. */
            $reviewers = $product->reviewer;
            if(!$reviewers and $product->acl != 'open') $reviewers = $this->loadModel('user')->getProductViewListUsers($product, '', '', '', '');

            /* Set Custom*/
            foreach(explode(',', $this->config->story->list->customCreateFields) as $field) $customFields[$field] = $this->lang->story->$field;
            $this->view->customFields = $customFields;
            $this->view->showFields   = $this->config->story->custom->createFields;

            $this->view->title            = $product->name . $this->lang->colon . $this->lang->story->create;
            $this->view->position[]       = html::a($this->createLink('product', 'browse', "product=$productID&branch=$branch"), $product->name);
            $this->view->position[]       = $this->lang->story->common;
            $this->view->position[]       = $this->lang->story->create;
            $this->view->products         = $products;
            $this->view->users            = $users;
            $this->view->gobackLink       = (isset($output['from']) and $output['from'] == 'global') ? $this->createLink('product', 'browse', "productID=$productID") : '';
            $this->view->moduleID         = $moduleID ? $moduleID : (int)$this->cookie->lastStoryModule;
            $this->view->moduleOptionMenu = $moduleOptionMenu;
            $this->view->plans            = $this->loadModel('productplan')->getPairsForStory($productID, $branch == 0 ? '' : $branch, 'skipParent|unexpired|noclosed');
            $this->view->planID           = $planID;
            $this->view->source           = $fromOpenedBy->role;
            $this->view->sourceNote       = $fromOpenedBy->realname;
            $this->view->pri              = $pri;
            $this->view->branch           = $branch;
            $this->view->branches         = $product->type != 'normal' ? $this->loadModel('branch')->getPairs($productID, 'active') : array();
            $this->view->stories          = $this->story->getParentStoryPairs($productID);
            $this->view->productID        = $fromObject->product;
            $this->view->product          = $product;
            $this->view->reviewers        = $this->user->getPairs('noclosed|nodeleted', '', 0, $reviewers);
            $this->view->objectID         = $objectID;
            $this->view->objectType       = $fromType;
            $this->view->blockID          = $blockID;
            $this->view->estimate         = $estimate;
            $this->view->storyTitle       = $fromObject->title;
            $this->view->spec             = $fromObject->desc;
            $this->view->verify           = $verify;
            $this->view->keywords         = $keywords;
            $this->view->mailto           = $mailto;
            $this->view->URS              = $storyType == 'story' ? $this->story->getRequirements($productID) : '';
            $this->view->needReview       = ($this->app->user->account == $product->PO || $objectID > 0 || $this->config->story->needReview == 0) ? "checked='checked'" : "";
            $this->view->type             = $storyType;
            $this->view->category         = !empty($category) ? $category : 'feature';
            $this->view->feedbackBy       = '';
            $this->view->notifyEmail      = '';
            $this->view->showFeedbackBox  = in_array($fromOpenedBy->role, $this->config->story->feedbackSource);

            $this->display();
        }
        else
        {
            return parent::create($productID, $branch, $moduleID, $storyID, $objectID, $bugID, $planID, $todoID, $extra, $storyType);
        }
    }
}
