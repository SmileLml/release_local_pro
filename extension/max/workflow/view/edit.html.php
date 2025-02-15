<?php
/**
 * The edit view file of workflow module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflow 
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
?>
<?php include '../../common/view/header.modal.html.php';?>
<?php js::set('currentModule', $flow->module);?>
<form id='editForm' method='post' action='<?php echo inlink('edit', "id=$flow->id");?>'>
  <table class='table table-form'>
    <tr>
      <th class='w-80px'><?php echo $lang->workflow->name;?></th>
      <td><?php echo html::input('name', $flow->name, "class='form-control'" . ($flow->buildin ? "readonly='readonly'" : ''));?></td>
      <td class='w-40px'></td>
    </tr>
    <tr>
      <th><?php echo $lang->workflow->module;?></th>
      <td><?php echo html::input('module', $flow->module, "class='form-control' readonly='readonly'");?></td>
      <td></td>
    </tr>
    <?php if(!$flow->buildin && $flow->type == 'flow' && $flow->status != 'wait'):?>
    <tr>
      <th><?php echo $lang->workflow->app;?></th>
      <td><?php echo html::select('app', $apps, $flow->app, "class='form-control chosen'");?></td>
      <td></td>
    </tr>
    <tr>
      <th><?php echo $lang->workflow->position;?></th>
      <td>
        <div class='input-group'>
          <?php echo html::select('positionModule', $menus, $flow->positionModule, "class='form-control chosen'");?>
          <span class='input-group-addon fix-border'></span>
          <?php echo html::select('position', $lang->workflow->positionList, $flow->position, "class='form-control chosen'");?>
        </div>
      </td>
      <td></td>
    </tr>
    <?php endif;?>
    <tr>
      <th><?php echo $lang->workflow->desc;?></th>
      <td><?php echo html::textarea('desc', $flow->desc, "rows='3' class='form-control'");?></td>
      <td></td>
    </tr>
    <tr>
      <th></th>
      <td class='form-actions'>
        <?php
        echo html::hidden('parent', $flow->parent);
        echo html::hidden('type', $flow->type);
        echo baseHTML::submitButton();
        ?>
      </td>
      <td></td>
    </tr>
  </table>
</form>
<?php include '../../common/view/footer.modal.html.php';?>
