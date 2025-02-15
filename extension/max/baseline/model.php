<?php
/**
 * The model file of baseline module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2020 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Qiyu Xie <xieqiyu@easycorp.ltd>
 * @package     baseline
 * @version     $Id: model.php 5107 2020-09-09 09:46:12Z xieqiyu@easycorp.ltd $
 * @link        http://www.zentao.net
 */
class baselineModel extends model
{
    /**
     * Get template list.
     *
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return void
     */
    public function getList($orderBy, $pager = null)
    {
        return $this->dao->select('*')->from(TABLE_DOC)
            ->where('templateType')->ne('')
            ->andWhere('lib')->eq('')
            ->andWhere('deleted')->eq(0)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();
    }

    /**
     * Get template by id.
     *
     * @param  int $templateID
     * @access public
     * @return void
     */
    public function getByID($templateID)
    {
        $template = $this->dao->select('*')->from(TABLE_DOC)
            ->where('id')->eq($templateID)
            ->fetch();
        if(empty($template)) return false;
        $content  = $this->dao->select('*')->from(TABLE_DOCCONTENT)
            ->where('doc')->eq($templateID)
            ->fetch();

        $template->content     = isset($content->content) ? $content->content : '';
        $template->contentType = isset($content->type)    ? $content->type : '';

        $template = $this->loadModel('file')->replaceImgURL($template, 'content');

        return $template;
    }

    /**
     * Get pairs by template type.
     *
     * @param  string $type
     * @param  string $from review|doc
     * @param  string $contentType word|ppt|url|text|article|markdown|book
     * @access public
     * @return object
     */
    public function getPairsByType($type, $from = 'review', $contentType = '')
    {
        if(!$type) return array();

        return $this->dao->select('id, title')->from(TABLE_DOC)
            ->where('project')->eq(0)
            ->beginIF(!empty($type) and $type != 'all')->andWhere('templateType')->eq($type)->fi()
            ->beginIF(!empty($contentType))->andWhere('type')->in($contentType)->fi()
            ->beginIF($from == 'doc')->andWhere('type')->ne('book')->fi()
            ->andWhere('product')->eq(0)
            ->andWhere('execution')->eq(0)
            ->fetchPairs();
    }

    /**
     * Get pairs by template.
     *
     * @param  string $template
     * @param  string $from review|doc
     * @param  string $contentType word|ppt|url|text|article|markdown|book
     * @access public
     * @return object
     */
    public function getPairsByTemplate($template, $from = 'review', $project = 0, $contentType = '')
    {
        if(!$template) return array();

        return $this->dao->select('id, title')->from(TABLE_DOC)
            ->where('template')->eq($template)
            ->beginIF(!empty($contentType))->andWhere('type')->in($contentType)->fi()
            ->beginIF($from == 'doc')->andWhere('type')->ne('book')->fi()
            ->beginIF($project != 0)->andWhere('project')->eq($project)->fi()
            ->fetchPairs();
    }

    /**
     * Create a template.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        $this->lang->doc->title = $this->lang->baseline->templateTitle;
        $now = helper::now();
        $doc = fixer::input('post')
            ->callFunc('title', 'trim')
            ->add('addedBy', $this->app->user->account)
            ->add('addedDate', $now)
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', $now)
            ->add('acl', 'open')
            ->stripTags($this->config->baseline->editor->createtemplate['id'], $this->config->allowedTags)
            ->remove('files,labels,uid')
            ->get();

        $doc->contentMarkdown = $this->post->contentMarkdown;

        $doc = $this->loadModel('file')->processImgURL($doc, $this->config->doc->editor->create['id'], $this->post->uid);
        if($doc->type == 'url')
        {
            $doc->content     = $doc->url;
            $doc->contentType = 'html';
        }

        $docContent = new stdclass();
        $docContent->title   = $doc->title;
        $docContent->content = $doc->contentType == 'html' ? $doc->content : $doc->contentMarkdown;
        $docContent->type    = $doc->contentType;
        $docContent->version = 1;
        if($doc->contentType == 'markdown') $docContent->content = str_replace('&gt;', '>', $docContent->content);
        unset($doc->contentMarkdown);
        unset($doc->contentType);
        unset($doc->url);

        $this->dao->insert(TABLE_DOC)->data($doc, 'content')->autoCheck()
            ->batchCheck($this->config->baseline->createtemplate->requiredFields, 'notempty')
            ->check('title', 'unique', "deleted = '0'")
            ->exec();

        if(!dao::isError())
        {
            $docID = $this->dao->lastInsertID();
            $this->file->updateObjectID($this->post->uid, $docID, 'doc');

            $docContent->doc = $docID;
            $this->dao->insert(TABLE_DOCCONTENT)->data($docContent)->exec();
            return $docID;
        }
        return false;
    }

    /**
     * Edit a template.
     *
     * @param  int $templateID
     * @access public
     * @return void
     */
    public function update($templateID)
    {
        $oldDoc = $this->getByID($templateID);
        $now = helper::now();
        $doc = fixer::input('post')
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', $now)
            ->setDefault('content', '')
            ->setIF($oldDoc->type == 'chapter' or $oldDoc->type == 'article', 'parent', $oldDoc->parent)
            ->stripTags($this->config->baseline->editor->edittemplate['id'], $this->config->allowedTags)
            ->remove('files,labels,uid')
            ->get();

        $doc->contentMarkdown = $this->post->contentMarkdown;

        $doc = $this->loadModel('file')->processImgURL($doc, $this->config->doc->editor->edit['id'], $this->post->uid);
        if($oldDoc->type == 'url')
        {
            $doc->content     = $doc->url;
            $doc->contentType = 'html';
        }

        $docContent = new stdclass();
        $docContent->title   = $doc->title;
        $docContent->content = $doc->contentType == 'html' ? $doc->content : $doc->contentMarkdown;
        $docContent->type    = $doc->contentType;
        $docContent->version = 1;
        if($doc->contentType == 'markdown') $docContent->content = str_replace('&gt;', '>', $docContent->content);
        unset($doc->contentMarkdown);
        unset($doc->contentType);
        unset($doc->url);

        $stmt = $this->dao->update(TABLE_DOC)->data($doc, 'content')->autoCheck()
            ->batchCheck($this->config->baseline->createtemplate->requiredFields, 'notempty')
            ->where('id')->eq($templateID);
        if($oldDoc->type != 'chapter' and $oldDoc->type != 'article') $stmt->check('title', 'unique', "deleted = '0' and id != $templateID");
        $stmt->exec();

        if(!dao::isError())
        {
            $this->file->updateObjectID($this->post->uid, $templateID, 'doc');

            $docContent->doc = $templateID;
            $this->dao->update(TABLE_DOCCONTENT)->data($docContent)->where('doc')->eq($templateID)->exec();
            if($oldDoc->type == 'chapter' || $oldDoc->type == 'article') $this->loadModel('doc')->fixPath($oldDoc->template);
            return true;
        }

        return false;
    }

    /**
     * Manage book.
     *
     * @param  int $bookID
     * @param  int $parentNodeID
     * @access public
     * @return void
     */
    public function manageBook($bookID, $parentNodeID = 0)
    {
        if($parentNodeID) $parentNode = $this->getById($parentNodeID);

        $now = helper::now();
        $node = new stdclass();
        $node->parent = $parentNodeID ? $parentNode->id : $bookID;
        $node->grade  = $parentNodeID ? $parentNode->grade + 1 : 1;

        $nodeContent = new stdclass();

        foreach($this->post->title as $key => $nodeTitle)
        {
            if(empty($nodeTitle)) continue;
            $mode = $this->post->mode[$key];

            /* First, save the child without path field. */
            $node->title       = $nodeTitle;
            $node->type        = $this->post->type[$key];
            $node->template    = $bookID;
            $node->chapterType = $node->type == 'article' ? '' : $this->post->chapterType[$key];
            $node->keywords    = $this->post->keywords[$key];
            $node->addedBy     = $this->app->user->account;
            $node->addedDate   = $now;
            $node->editedBy    = $this->app->user->account;
            $node->editedDate  = $now;
            $node->order       = $this->post->order[$key];
            $node->keywords    = $this->post->keywords[$key];

            if($mode == 'new')
            {
                $this->dao->insert(TABLE_DOC)->data($node)->exec();

                /* After saving, update it's path. */
                $nodeID   = $this->dao->lastInsertID();
                $nodePath = $parentNodeID ? $parentNode->path . "$nodeID," : ",$nodeID,";
                $this->dao->update(TABLE_DOC)->set('path')->eq($nodePath)->where('id')->eq($nodeID)->exec();

                $nodeContent->doc     = $nodeID;
                $nodeContent->title   = $nodeTitle;
                $nodeContent->type    = 'html';
                $nodeContent->version = 1;
                $this->dao->insert(TABLE_DOCCONTENT)->data($nodeContent)->exec();
            }
            else
            {
                $nodeID = $key;
                $node->editedBy   = $this->app->user->account;
                $node->editedDate = $now;
                $this->dao->update(TABLE_DOC)->data($node)->autoCheck()->where('id')->eq($nodeID)->exec();
                $this->dao->update(TABLE_DOCCONTENT)->set('title')->eq($nodeTitle)->where('doc')->eq($nodeID)->exec();
            }
        }

        return !dao::isError();
    }

    /**
     * Get catalog.
     *
     * @param  int    $bookID
     * @param  int    $nodeID
     * @param  int    $serials
     * @access public
     * @return void
     */
    function getCatalog($bookID, $nodeID, $serials)
    {
        $catalog = '';

        $book = $this->getById($bookID);
        $node = $this->getById($nodeID);
        if(!$node)
        {
            $node = new stdclass();
            $node->id    = $book->id;
            $node->title = $book->title;
            $node->type  = $book->type;
        }

        $children = $this->getChildren($bookID, $nodeID);
        if($node->type != 'book') $serial = $serials[$nodeID];

        $anchor      = "name='node{$node->id}' id='node{$node->id}'";
        $titleLink   = ($node->type == 'article') ? html::a(helper::createLink('baseline', 'view', "node=$node->id"), $node->title) : html::a(helper::createLink('baseline', 'manageBook', "bookID=$bookID&node=$node->id"), $node->title);
        $editLink    = commonModel::hasPriv('baseline', 'editbook')    ? html::a(helper::createLink('baseline', 'editbook', "nodeID=$node->id"), $this->lang->edit, '', $anchor) : '';
        $delLink     = commonModel::hasPriv('baseline', 'delete')  ? html::a(helper::createLink('baseline', 'delete', "nodeID=$node->id"), $this->lang->delete, 'hiddenwin') : '';
        $catalogLink = commonModel::hasPriv('baseline', 'manageBook') ? html::a(helper::createLink('baseline', 'manageBook', "bookID=$bookID&nodeID=$node->id"), $this->lang->doc->catalog) : '';
        $moveLink    = commonModel::hasPriv('doc', 'sort') ? html::a('javascript:;', "<i class='icon-move'></i>", '', "class='sort sort-handle'") : '';

        $childrenHtml = '';
        if($children)
        {
            $childrenHtml .= '<dl>';
            foreach($children as $child) $childrenHtml .=  $this->getCatalog($bookID, $child->id, $serials);
            $childrenHtml .= '</dl>';
        }

        if($node->type == 'book') $catalog .= $childrenHtml;

        if(isset($node->chapterType) && $node->chapterType== 'input') $catalog .= "<dd class='catalog chapter' data-id='" . $node->id . "'><strong><span class='order'>" . $serial . '</span>&nbsp;' . $titleLink . '</strong><span class="actions">' . $editLink . $catalogLink . $delLink . $moveLink . '</span>' . $childrenHtml . '</dd>';

        if(isset($node->chapterType) && $node->chapterType== 'system') $catalog .= "<dd class='catalog chapter' data-id='" . $node->id . "'><strong><span class='order'>" . $serial . '</span>&nbsp;' . $titleLink . '</strong><span class="actions">' . $editLink . $delLink . $moveLink . '</span>' . $childrenHtml . '</dd>';

        if($node->type == 'article') $catalog .= "<dd class='catalog article' data-id='" . $node->id . "'><strong><span class='order'>" . $serial . '</span>&nbsp;' . $titleLink . '</strong> ' . '<span class="actions">' . $editLink . $delLink . $moveLink . '</span>' . $childrenHtml . '</dd>';

        return $catalog;
    }

    /**
     * Get children.
     *
     * @param  int    $bookID
     * @param  int    $nodeID
     * @access public
     * @return void
     */
    public function getChildren($bookID, $nodeID = 0)
    {
        $parent = $nodeID ? $nodeID : $bookID;
        return $this->dao->select('*')->from(TABLE_DOC)
            ->where('template')->eq($bookID)
            ->andWhere('deleted')->eq(0)
            ->andWhere('parent')->eq($parent)
            ->orderBy('`order`, id')
            ->fetchAll('id');
    }

    /**
     * Adjust the action is clickable.
     *
     * @param  object  $object
     * @param  varchar $action
     * @access public
     * @return void
     */
    public static function isClickable($object, $action)
    {
        $action = strtolower($action);

        if($action == 'managebook') return $object->type == 'book';

        return true;
    }

    /**
     * Gets the number of commits of the object today.
     *
     * @param string $object
     * @access public
     * @return int
     */
    public function getSerialNumber($object)
    {
        return $this->dao->select('count(*) as sum')->from(TABLE_OBJECT)
            ->where('category')->eq($object)
            ->andWhere('type')->eq('taged')
            ->andWhere('createdDate')->eq(date('Y-m-d'))
            ->fetch('sum');
    }
}
