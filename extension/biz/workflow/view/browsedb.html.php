<?php
/**
 * The browse view file of workflow module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflow
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
?>
<?php include '../view/header.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/sortable.html.php';?>
<div class='space space-sm'></div>
<div class='row'>
  <?php if(strpos($subTableTipsReaders, ",{$this->app->user->account},") === false):?>
  <div class='alert alert-warning'>
    <p><i class='icon-alert icon-md'></i> <?php echo $lang->workflow->tips->subTable;?></p>
  </div>
  <?php endif;?>

  <div class='col-md-7'>
    <div class='panel'>
      <div class='panel-heading'>
        <strong><?php echo $currentTable ? $currentTable->name : $lang->workflow->subTable . '&nbsp;' . $lang->workflow->field;?></strong>
        <div class='panel-actions pull-right'>
        </div>
      </div>
      <div class='panel-body'>
        <table class='table table-form'>
          <?php foreach($fields as $field):?>
          <tr>
            <th class='w-100px'><?php echo $field->name;?> </th>
            <td>
              <?php
              if($field->field == 'id' or $field->field == 'parent')
              {
                  echo html::input($field->field, $lang->workflowfield->placeholder->auto, "class='form-control' disabled='disabled'");
              }
              else
              {
                  echo $this->loadModel('flow')->buildControl($field, '', "preview_{$field->field}", '', true, true);
              }
              ?>
            </td>
            <td class='w-160px'></td>
          </tr>
          <?php endforeach;?>
        </table>
      </div>
    </div>
  </div>
  <div class='col-md-5'>
    <div class='panel'>
      <div class='panel-heading'>
        <strong><?php echo $lang->workflow->subTableSettings;?></strong>
        <div class='panel-actions pull-right'>
          <?php extCommonModel::printLink('workflow', 'create', "type=table&parent={$flow->module}", '<i class="icon-plus"> </i> ' . $lang->workflowtable->create, "class='btn btn-primary btn-create-table' data-toggle='modal'");?>
        </div>
      </div>
      <div class='panel-body main-table'>
        <table class='table'>
          <thead>
            <tr>
              <th><?php echo $lang->workflowtable->name;?></th>
              <th class='w-120px'><?php echo $lang->workflowtable->module;?></th>
              <th class='w-160px'><?php echo $lang->workflow->desc;?></th>
              <th class='w-120px text-center'><?php echo $lang->actions;?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($tables as $table):?>
            <tr class="<?php echo $table->module == $currentTable->module ? 'checked row-check-begin row-check-end' : '';?>" data-id='<?php echo $table->id;?>' data-url="<?php echo $this->createLink('workflow', 'browsedb', "parent={$flow->module}&table={$table->module}")?>">
              <td title='<?php echo $table->name;?>'><?php echo $table->name;?></td>
              <td title='<?php echo $table->module?>'><?php echo $table->module;?></td>
              <td title='<?php echo $table->desc;?>'><?php echo $table->desc;?></td>
              <td class='actions'>
                <?php
                extCommonModel::printLink('workflowfield', 'browse', "module=$table->module", $lang->workflow->field, "class='fields'");
                extCommonModel::printLink('workflow', 'edit', "id=$table->id", $lang->edit, "class='edit' data-toggle='modal'");
                extCommonModel::printLink('workflow', 'delete', "id=$table->id", $lang->delete, "class='deleter'");
                ?>
              </td>
            </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
