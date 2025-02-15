<?php
/**
 * The batch edit view of bug module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Congzhi Chen <congzhi@cnezsoft.com>
 * @package     bug
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php 
js::set('showFields', $showFields);
js::set('productID' , $productID);
js::set('branchID'  , $branchID);
js::set('tab'       , $app->tab);
js::set('bugList'   , $bugs);
js::set('bugList'   , $bugs);
?>
<div id='mainContent' class='main-content fade'>
  <div class='main-header'>
    <h2><?php echo $lang->bug->common . $lang->colon . $lang->bug->batchAdjust;?></h2>
  </div>
  <?php if(isset($suhosinInfo)):?>
  <div class='alert alert-info'><?php echo $suhosinInfo;?></div>
  <?php else:?>
  <?php
  $visibleFields  = array();
  $requiredFields = array();
  foreach(explode(',', $showFields) as $field) if($field) $visibleFields[$field] = '';
  $columns = count($visibleFields) + 2;
  ?>
  <form class='main-form' method='post' target='hiddenwin' enctype='multipart/form-data' action="<?php echo inLink('batchAdjust', "productID=$productID&branchID=$branchID")?>" id='batchAdjustForm'>
    <div class="table-responsive">
      <table class='table table-form'>
        <thead>
          <tr>
            <th class='c-id' style=""><?php echo $lang->idAB;?></th>
            <th class='' style="width:120px;"><?php echo $lang->bug->title;?></th>
            <?php if($app->tab != 'qa'):?>
            <th class='' style="width:240px;"><?php echo $lang->bug->product;?></th>
            <?php endif;?>
            <th class='required' style="width:200px;"><?php echo $lang->bug->project;?></th>
            <th class='required' style="width:200px;"><?php echo $lang->bug->openedBuild;?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($bugs as $bugID => $bug):?>
          <tr>
            <td><?php echo $bugID . html::hidden("bugIDList[$bugID]", $bugID);?></td>
            <td>
              <div class="title-adjust" title="<?php echo $bug->title;?>"><?php echo $bug->title;?></div>
            </td>
            <?php if($app->tab != 'qa'):?>
            <td>
              <div class='input-group'>
                <?php echo html::select("product[$bugID]", $products,       $bug->product, "onchange='loadProductBranches(this.value, $bugID);'                class='form-control chosen control-product' id='product-$bugID'");?>
                <div class="<?php echo $bug->productWithBranch ? '' : 'branch-adjust';?>" id="branch-css-<?php echo $bugID;?>">
                <?php echo html::select("branch[$bugID]",  $bug->branches,  $bug->branch,  "onchange='loadProductProjects($bug->product, this.value, $bugID);' class='form-control chosen control-branch'  id='branch-$bugID'");?>
                </div>
              </div>
            </td>
            <?php endif;?>
            <td>
              <div class='input-group' id="projectBox-<?php echo $bugID;?>">
                <?php echo html::select("project[$bugID]", $bug->projects, $bug->project, "onchange='loadBuilds($bug->product, $bug->branch, $bugID, this.value)' class='form-control chosen' id='project-$bugID'");?>
              </div>
            </td>
            <td>
              <div id='buildBox-<?php echo $bugID;?>' class='input-group'>
                <?php echo html::select("build[$bugID][]", array(), '', "size=4 multiple=multiple class='picker-select form-control' id='build-$bugID'");?>
              </div>
            </td>
          </tr>
          <?php echo html::hidden("lastEditedDate[$bugID]", $bug->lastEditedDate);?>
          <?php endforeach;?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan='<?php echo $columns;?>' class='text-center form-actions'>
              <?php echo html::submitButton();?>
              <?php echo $this->app->tab == 'product' ? html::a($this->session->bugList, $lang->goback, '', "class='btn btn-back btn-wide'") : html::backButton();?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </form>
  <?php endif;?>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
