<?php
/**
 * The create view file of workflow module of ZDOO.
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
<?php js::set('currentModule', '');?>
<?php js::set('createTips', $lang->workflow->tips->create);?>
<?php js::set('notNow', $lang->workflow->notNow);?>
<?php js::set('toDesign', $lang->workflow->toDesign);?>
<form id="<?php echo $type == 'table' ? 'ajaxForm' : 'createForm';?>" method='post' action='<?php echo inlink('create', "type=$type&parent=$parent&app=$currentApp");?>'>
  <table class='table table-form'>
    <tr>
      <th class='w-80px'><?php echo $lang->workflow->name;?></th>
      <td><?php echo html::input('name', '', "class='form-control'");?></td>
      <td class='w-40px'></td>
    </tr>
    <tr>
      <th><?php echo $lang->workflow->module;?></th>
      <td><?php echo html::input('module', '', "class='form-control' placeholder='{$lang->workflow->placeholder->module}'");?></td>
      <td></td>
    </tr>
    <?php if($type == 'flow' && !empty($this->config->openedApproval)):?>
    <tr>
      <th><?php echo $this->lang->workflow->approval;?></th>
      <td><?php echo html::radio('approval', $lang->workflowapproval->approvalList, 'disabled');?></td>
      <td></td>
    </tr>
    <tr class='approval'>
      <th><?php echo $this->lang->workflowapproval->approvalFlow;?></th>
      <td>
        <?php echo html::select('approvalFlow', array('') + $approvalFlows, '', "class='form-control chosen'");?>
      </td>
      <td></td>
    </tr>
    <?php endif;?>
    <tr>
      <th><?php echo $lang->workflow->desc;?></th>
      <td><?php echo html::textarea('desc', '', "rows='3' class='form-control'");?></td>
      <td></td>
    </tr>
    <?php if($type == 'flow' && !empty($this->config->openedApproval)):?>
    <tr><th></th><td class='text-important'><?php echo $this->lang->workflowapproval->openLater;?></td></tr>
    <?php endif;?>
    <tr>
      <th></th>
      <td class='form-actions'>
        <?php echo html::hidden('parent', $parent);?>
        <?php echo html::hidden('type', $type);?>
        <?php echo baseHTML::submitButton();?>
      </td>
      <td></td>
    </tr>
  </table>
</form>
<?php include '../../common/view/footer.modal.html.php';?>
