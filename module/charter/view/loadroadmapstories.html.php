<?php
/**
 * The ajax select story view file of charter module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     charter
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<style>
.c-id,
</style>
<div id="mainContent" class="main-content">
  <div class='main-header'>
    <h2><?php echo $lang->charter->roadmapStory;?></h2>
  </div>
  <table class='table table-fixed table-bordered' id='storyList'>
    <thead>
      <tr>
        <th class='w-60px text-center'><?php echo $lang->idAB;?></th>
        <th class='w-60px text-center'><?php echo $lang->priAB;?></th>
        <th><?php echo $lang->story->requirement;?></th>
        <th class='w-100px text-center'><?php echo $lang->story->module;?></th>
        <th class='w-100px text-center'><?php echo $lang->story->status;?></th>
      </tr>
    </thead>
    <?php if($stories):?>
    <tbody>
      <?php foreach($stories as $story):?>
      <?php $storyLink = $this->createLink('story', 'view', "storyID=$story->id");?>
      <tr>
        <td class='c-id text-center'><?php echo $story->id;?></td>
        <?php $priClass = $story->pri ? "label-pri label-pri-{$story->pri}" : '';?>
        <td class='text-center'><span class='<?php echo $priClass;?>'><?php echo zget($lang->story->priList, $story->pri);?></span></td>
        <td class='text-left nobr c-name' title="<?php echo $story->title?>"><?php echo common::hasPriv('story', 'view') ? html::a($storyLink, $story->title) : $story->title;?></td>
        <td class='text-center'><?php echo zget($modules, $story->module);?></td>
        <td class='text-center'><?php echo zget($lang->story->statusList, $story->status);?></td>
      </tr>
      <?php endforeach;?>
    </tbody>
    <?php endif;?>
  </table>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
