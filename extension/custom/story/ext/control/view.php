<?php

helper::importControl('story');

class mystory extends story
{
    public function view($storyID, $version = 0, $param = 0, $storyType = 'story')
    {
        $story = $this->story->getById($storyID, $version, true);
        if($this->config->edition == 'ipd' and $story->type == 'story') $story = $this->story->getAffectObject('', $story->type, $story);

        $linkModuleName = $this->config->vision == 'lite' ? 'project' : 'product';
        if(!$story)
        {
            $story = $this->dao->select('*')->from(TABLE_STORY)->where('id')->eq($storyID)->fetch();
            if($story)
            {
                if(strpos($story->vision, $this->config->vision) === false && $story->status == 'active') return print(js::alert($this->lang->story->storyUnlinkRoadmap) . js::reload('parent'));
            }
            return print(js::error($this->lang->notFound) . js::locate($this->createLink($linkModuleName, 'all')));
        }

        $uri     = $this->app->getURI(true);
        $tab     = $this->app->tab;
        $storyID = (int)$storyID;
        $product = $this->loadModel('product')->getByID($story->product);

        if(!(defined('RUN_MODE') && RUN_MODE == 'api') and $tab == 'product' and !empty($product->shadow))
        {
            $backLink = $this->session->productList ? $this->session->productList : inlink('product', 'all');
            $js       = js::start();
            $js      .= "setTimeout(\"parent.$.apps.open('$uri#app=project')\", 100)";
            $js      .= js::end();
            return print(js::refresh($backLink . '#app=product', 'self', '10') . $js);
        }

        $buildApp   = $tab == 'product' ?   'project' : $tab;
        $releaseApp = $tab == 'execution' ? 'product' : $tab;
        $this->session->set('productList', $uri . "#app={$tab}", 'product');
        $this->session->set('productPlanList', $uri . "#app={$tab}", 'product');
        if(!isonlybody()) $this->session->set('buildList', $uri, $buildApp);
        $this->app->loadLang('bug');

        if(!$this->app->user->admin and strpos(",{$this->app->user->view->products},", ",$story->product,") === false) return print(js::error($this->lang->product->accessDenied) . js::locate('back'));
        if(!empty($story->fromBug)) $this->session->set('bugList', $uri, 'qa');

        $version = empty($version) ? $story->version : $version;
        $story   = $this->story->mergeReviewer($story, true);

        $this->story->replaceURLang($story->type);

        $plan          = $this->dao->findById($story->plan)->from(TABLE_PRODUCTPLAN)->fetch('title');
        $bugs          = $this->dao->select('id,title,status,pri,severity')->from(TABLE_BUG)->where('story')->eq($storyID)->andWhere('deleted')->eq(0)->fetchAll();
        $fromBug       = $this->dao->select('id,title')->from(TABLE_BUG)->where('id')->eq($story->fromBug)->fetch();
        $cases         = $this->dao->select('id,title,status,pri')->from(TABLE_CASE)->where('story')->eq($storyID)->andWhere('deleted')->eq(0)->fetchAll();
        $linkedMRs     = $this->loadModel('mr')->getLinkedMRPairs($storyID, 'story');
        $linkedCommits = $this->loadModel('repo')->getCommitsByObject($storyID, 'story');
        $modulePath    = $this->tree->getParents($story->module);
        $storyModule   = empty($story->module) ? '' : $this->tree->getById($story->module);
        $linkedStories = isset($story->linkStoryTitles) ? array_keys($story->linkStoryTitles) : array();
        $storyProducts = $this->dao->select('id,product')->from(TABLE_STORY)->where('id')->in($linkedStories)->fetchPairs();

        /* Set the menu. */
        $from = $this->app->tab;
        if($from == 'execution')
        {
            $result = $this->execution->setMenu($param);
            if($result) return;
        }
        elseif($from == 'project')
        {
            $projectID = $param ? $param : $this->session->project;
            if(!$projectID) $projectID = $this->dao->select('project')->from(TABLE_PROJECTSTORY)->where('story')->eq($storyID)->fetch('project');
            $this->loadModel('project')->setMenu($projectID);
        }
        elseif($from == 'qa')
        {
            $products = $this->product->getProductPairsByProject(0, 'noclosed');
            $this->loadModel('qa')->setMenu($products, $story->product);
        }
        else
        {
            $this->product->setMenu($story->product, $story->branch);
        }

        $this->view->hiddenPlan = false;
        $this->view->hiddenURS  = false;
        if(!empty($product->shadow))
        {
            $projectInfo = $this->dao->select('t2.model, t2.multiple, t2.id, t2.status')->from(TABLE_PROJECTPRODUCT)->alias('t1')
                ->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project = t2.id')
                ->where('t1.product')->eq($product->id)
                ->andWhere('t2.type')->eq('project')
                ->fetch();

            if($projectInfo->model == 'waterfall')
            {
                $this->view->hiddenPlan = true;
            }
            elseif($projectInfo->model == 'kanban')
            {
                $this->view->hiddenPlan = true;
                $this->view->hiddenURS  = true;
            }

            if(!$projectInfo->multiple) $this->view->hiddenPlan = true;
            $this->loadModel('project')->setMenu($projectInfo->id);
            $this->view->projectInfo  = $projectInfo;
        }

        if($product->type != 'normal') $this->lang->product->branch = sprintf($this->lang->product->branch, $this->lang->product->branchName[$product->type]);

        $reviewers  = $this->story->getReviewerPairs($storyID, $story->version);
        $reviewedBy = trim($story->reviewedBy, ',');

        $this->executeHooks($storyID);

        if($this->config->edition == 'ipd')
        {
            $this->view->roadmaps = $this->loadModel('roadmap')->getPairs($story->product);
        }

        $title      = "STORY #$story->id $story->title - $product->name";
        $position[] = html::a($this->createLink('product', 'browse', "product=$product->id&branch=$story->branch"), $product->name);
        $position[] = $this->lang->story->common;
        $position[] = $this->lang->story->view;

        $execution = empty($story->execution) ? array() : $this->dao->findById($story->execution)->from(TABLE_EXECUTION)->fetch();
        $project   = $param ? $this->dao->findById($param)->from(TABLE_PROJECT)->fetch() : array();

        $this->view->title         = $title;
        $this->view->position      = $position;
        $this->view->product       = $product;
        $this->view->branches      = $product->type == 'normal' ? array() : $this->loadModel('branch')->getPairs($product->id);
        $this->view->twins         = !empty($story->twins) ? $this->story->getByList($story->twins) : array();
        $this->view->plan          = $plan;
        $this->view->bugs          = $bugs;
        $this->view->fromBug       = $fromBug;
        $this->view->cases         = $cases;
        $this->view->story         = $story;
        $this->view->linkedMRs     = $linkedMRs;
        $this->view->linkedCommits = $linkedCommits;
        $this->view->track         = $this->story->getTrackByID($story->id);
        $this->view->users         = $this->user->getPairs('noletter');
        $this->view->reviewers     = $reviewers;
        $this->view->relations     = $this->story->getStoryRelation($story->id, $story->type);
        $this->view->executions    = $this->execution->getPairs(0, 'all', 'nocode');
        $this->view->execution     = $execution;
        $this->view->project       = $project;
        $this->view->actions       = $this->action->getList('story', $storyID);
        $this->view->storyModule   = $storyModule;
        $this->view->modulePath    = $modulePath;
        $this->view->storyProducts = $storyProducts;
        $this->view->version       = $version;
        $this->view->preAndNext    = $this->loadModel('common')->getPreAndNextObject('story', $storyID);
        $this->view->from          = $from;
        $this->view->param         = $param;
        $this->view->storyType     = $story->type;
        $this->view->builds        = $this->loadModel('build')->getStoryBuilds($storyID);
        $this->view->releases      = $this->loadModel('release')->getStoryReleases($storyID);
        $this->display();
    }
}