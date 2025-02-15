<?php
/**
 * The browse view file of workflowcondition module of ZDOO.
 *
 * @copyright   Copyright 2009-2016 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     商业软件，非开源软件
 * @author      Gang Liu <liugang@cnezsoft.com>
 * @package     workflowcondition
 * @version     $Id$
 * @link        http://www.zdoo.com
 */
?>
<?php include '../../common/view/header.modal.html.php';?>
<?php js::set('action', $action->id);?>
<div id='createCondition'>
  <?php extCommonModel::printLink('workflowcondition', 'create', "action=$action->id", "<i class='icon-plus'> </i>" . $lang->workflowcondition->create, "class='btn btn-primary loadInModal iframe'");?>
</div>
<div class='panel main-table'>
  <table class='table table-fixed' id='conditionTable'>
    <thead>
      <tr>
        <th><?php echo $lang->workflowcondition->condition;?></th>
        <th class='w-80px text-center'><?php echo $lang->actions;?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($action->conditions as $key => $condition):?>
      <tr>
        <?php 
        $output = '';
        if($condition->conditionType == 'sql')
        {
            $output = $condition->sql;
        }
        else
        {
            foreach($condition->fields as $index => $field)
            {
                if($index > 0)
                {
                    if(empty($field->logicalOperator)) $field->logicalOperator = 'and';

                    $output .= ' ' . $lang->workflowcondition->logicalOperatorList[$field->logicalOperator] . ' ';
                }
                $output .=  zget($fields, $field->field);
                $output .=  zget($lang->workflowcondition->operatorList, $field->operator);
                if(strpos($config->workflow->virtualParams, ",$field->param,") !== false)
                {
                    $output .= $lang->workflowcondition->options[$field->param];
                }
                else
                {
                    $output .= $field->param;
                }
            }
        }
        ?>
        <td title="<?php echo $output;?>"><?php echo $output;?></td>
        <td class='actions'>
          <?php extCommonModel::printLink('workflowcondition', 'edit',   "action=$action->id&key=$key", $lang->edit, "class='edit loadInModal iframe'");?>
          <?php extCommonModel::printLink('workflowcondition', 'delete', "action=$action->id&key=$key", $lang->delete, "class='deleter reloadModal'");?>
        </td>
      </tr>
      <?php endforeach;?>
    </tbody>
  </table>
</div>
<?php include '../../common/view/footer.modal.html.php';?>
