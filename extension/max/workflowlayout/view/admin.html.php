<?php
/**
 * The admin ui view file of workflowlayout module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowlayout
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
?>
<?php include '../../common/view/header.modal.html.php';?>
<?php if(isset($emptyCustomFields)):?>
<div class='alert alert-warning'>
  <p class='clearfix'>
    <?php echo sprintf($lang->workflowlayout->error->emptyCustomFields, $this->createLink('workflowfield', 'browse', "module={$module}"));?>
  </p>
</div>
<?php else:?>
<?php include '../../common/view/picker.html.php';?>
<?php js::set('action', $action->action);?>
<?php js::set('method', $action->method);?>
<?php js::set('mode', $mode);?>
<?php if($mode == 'edit'):?>
<form id='adminLayoutForm' method='post' action='<?php echo inlink('admin', "module=$action->module&action=$action->action&mode=$mode");?>'>
<?php endif;?>
  <div class='panel'>
    <table id='fixedEnabled' class='table table-layout'>
      <tr class='fixed-enabled head'>
        <?php $cols = $action->method == 'view' ? 4 : 6;?>
        <?php if($action->buildin == '1' && $action->method == 'edit' && $action->layout == 'side') $cols = 7;?>
        <td colspan="<?php echo $cols;?>"><i class='icon-check'></i> <span class='title'><span class='title-bar'><strong><?php echo $lang->workflow->mainTable . $lang->colon . $flow->name;?></strong></span></span></td>
      <tr>
    </table>
    <table id='fixedRequired' class='table table-layout'></table>

    <?php include 'fields.html.php';?>
  </div>

  <?php
  if($action->method != 'browse' && $action->type == 'single')
  {
      if($subTables) include 'subtables.html.php';
      if($prevModules) include 'prevmodules.html.php';
  }
  ?>

  <?php /* Form actions. */ ?>
  <?php if($mode == 'edit'):?>
  <div class='form-actions text-center'>
    <div class='btn-group'>
      <?php echo baseHTML::commonButton($lang->selectAll, 'btn btn-default no-margin', "id='allchecker'");?>
      <?php echo baseHTML::commonButton($lang->selectReverse, 'btn btn-default', "id='reversechecker'");?>
    </div>
    <?php echo baseHTML::submitButton();?>
  </div>
  <?php endif;?>

<?php if($mode == 'edit'):?>
</form>
<?php endif;?>

<?php /* Page actions. */ ?>
<?php if($mode == 'view'):?>
<div class='form-actions text-center'>
  <?php extCommonModel::printLink('workflowlayout', 'admin', "module=$action->module&action=$action->action&mode=edit", $lang->edit, "class='btn loadInModal iframe'");?>
  <?php if($action->method == 'view') extCommonModel::printLink('workflowlayout', 'block', "module=$action->module", $lang->workflowlayout->block, "class='btn loadInModal iframe'");?>
</div>
<?php endif;?>

<?php endif;?>
<?php include '../../common/view/footer.modal.html.php';?>
