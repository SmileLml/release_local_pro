<?php
/**
 * The release view file of workflow module of ZDOO.
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
<?php if(!empty($errors)):?>
<div class='alert alert-warning'>
  <?php foreach($errors as $error):?>
  <p><?php echo $error;?></p>
  <?php endforeach;?>
</div>
<?php else:?>
<?php js::set('currentModule', $flow->module);?>
<form id='releaseForm' method='post' action='<?php echo inlink('release', "id=$flow->id");?>'>
  <table class='table table-form'>
    <tr>
      <th class='w-80px'><?php echo $lang->workflow->app;?></th>
      <td>
        <div class='required required-wrapper'></div>
        <div class='input-group'>
          <?php echo html::select('app', $apps, $flow->app, "class='form-control'");?>
          <?php echo html::input('name', '', "class='form-control newApp' style='display: none' placeholder='{$lang->entry->name}'");?>
          <span class='input-group-addon newApp'></span>
          <?php echo html::input('code', '', "class='form-control newApp' style='display: none' placeholder='{$lang->entry->code}'");?>
          <span class='input-group-addon'>
            <label class='checkbox-inline'>
              <input type='checkbox' name='createApp' id='createApp' value='1' /> <?php echo $lang->workflow->createApp;?>
            </label>
          </span>
        </div>
      </td>
      <td class='w-40px'></td>
    </tr>
    <tr>
      <th><?php echo $lang->workflow->position;?></th>
      <td>
        <div class='input-group'>
          <?php echo html::select('positionModule', $menus, $flow->positionModule, "class='form-control'");?>
          <span class='input-group-addon fix-border'></span>
          <?php echo html::select('position', $lang->workflow->positionList, $flow->position, "class='form-control'");?>
        </div>
      </td>
      <td></td>
    </tr>
    <tr>
      <th></th>
      <td class='form-actions'>
        <?php
        if($flow->buildin)
        {
            echo html::hidden('app', $flow->app);
            echo html::hidden('positionModule', $flow->positionModule);
            echo html::hidden('position', $flow->position);
        }
        else
        {
            if($flow->type != 'flow') echo html::hidden('app', $flow->app);
        }
        echo html::hidden('parent', $flow->parent);
        echo html::hidden('type', $flow->type);
        echo baseHTML::submitButton();
        ?>
      </td>
      <td></td>
    </tr>
  </table>
</form>
<?php endif;?>
<?php include '../../common/view/footer.modal.html.php';?>
