<?php
/**
 * The browse view file of workflowlinkage module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowlinkage
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
?>
<?php include '../../common/view/header.modal.html.php';?>
<?php js::set('action', $action->id);?>
<div id='createLinkage'>
  <?php extCommonModel::printLink('workflowlinkage', 'create', "action=$action->id", "<i class='icon-plus'> </i>" . $lang->workflowlinkage->create, "class='btn btn-primary loadInModal iframe'");?>
</div>
<div class='panel main-table'>
  <table class='table table-fixed' id='linkageTable'>
    <thead>
      <tr>
        <th><?php echo $lang->workflowlinkage->source;?></th>
        <th><?php echo $lang->workflowlinkage->target;?></th>
        <th class='w-80px text-center'><?php echo $lang->actions;?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($action->linkages as $key => $linkage):?>
      <?php $sources = zget($linkage, 'sources', array());?>
      <?php $targets = zget($linkage, 'targets', array());?>
      <tr>
        <td>
          <?php
          foreach($sources as $source)
          {
              if(!isset($fields[$source->field])) continue;
          
              $field = $fields[$source->field];
              echo $field->name . zget($config->workflowlinkage->operatorList, $source->operator);
              if($field->control == 'multi-select' or $field->control == 'checkbox')
              {
                  $values = explode(',', $source->value);
                  foreach($values as $value) echo zget($field->options, $value) . ' ' ;
              }
              else
              {
                  echo zget($field->options, $source->value);
              }
          }
          ?>
        </td>
        <td>
          <?php
          foreach($targets as $target)
          {
              if(!isset($fields[$target->field])) continue;

              $field = $fields[$target->field];
              echo $field->name . "[{$lang->workflowlinkage->statusList[$target->status]}]&nbsp;&nbsp;&nbsp;&nbsp;";
          }
          ?>
        </td>
        <td class='actions'>
          <?php extCommonModel::printLink('workflowlinkage', 'edit',   "action=$action->id&key=$key", $lang->edit, "class='edit loadInModal iframe'");?>
          <?php extCommonModel::printLink('workflowlinkage', 'delete', "action=$action->id&key=$key", $lang->delete, "class='deleter reloadModal'");?>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
<?php include '../../common/view/footer.modal.html.php';?>
