<?php
/**
 * The control file of effort module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2012 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     business(商业软件)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     effort
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/datepicker.html.php';?>
<?php js::set('leftTip',    $lang->effort->leftTip);?>
<?php js::set('testTip',    $lang->task->testPackageVersion);?>
<?php js::set('executions', $executions);?>
<?php js::set('hoursConsumedTodayOverflow', $lang->effort->hoursConsumedTodayOverflow);?>
<?php js::set('hoursConsumedTodayOverflowOther', $lang->effort->hoursConsumedTodayOverflowOther);?>
<?php js::set('hoursConsumedToday', $hoursConsumedToday);?>
<?php js::set('hoursSurplusToday', $this->config->limitWorkHour - $hoursConsumedToday);?>
<?php js::set('limitWorkHour', $this->config->limitWorkHour);?>
<?php js::set('currentDate', helper::today());?>
<?php js::set('inputDate', $date);?>
<?php js::set('hoursConsumedNoObjectType', $lang->effort->hoursConsumedNoObjectType);?>
<?php js::set('hoursConsumedTodayTitle', $lang->effort->hoursConsumedToday);?>
<?php js::set('hoursSurplusTodayTitle', $lang->effort->hoursSurplusToday);?>
<?php js::set('hoursConsumedTodayOtherTitle', $lang->effort->hoursConsumedTodayOther);?>
<?php js::set('hoursSurplusTodayOtherTitle', $lang->effort->hoursSurplusTodayOther);?>
<?php js::set('tasks', $tasks);?>
<div class='cell'>
  <form class="modal-content load-indicator" method='post' target='hiddenwin' id="effortBatchAddForm">
    <div class="modal-header" id="effortBatchAddHeader">
      <div class="modal-actions">
        <?php echo html::commonButton($lang->effort->clean, "onclick='cleanEffort()' title='{$lang->effort->noticeClean}'", "btn btn-primary")?>
        <?php if(isonlybody()):?>
        <div class="divider"></div>
        <button type="button" class="btn btn-link" data-dismiss="modal"><i class="icon icon-close"></i></button>
        <?php endif;?>
      </div>
      <h4 class="modal-title pull-left"><?php echo $lang->effort->batchCreate;?></h4>
      <div class="input-group pull-left">
        <span class="input-group-addon"><?php echo $lang->effort->date;?></span>
        <input type="text" name="date" value="<?php echo $date;?>" class="form-control form-date" autocomplete="off" />
      </div>
      <span class="modal-title" style="padding-left: 20px;">
        <i class="icon-exclamation-sign"><?php echo $lang->effort->noChooseClosedProject; ?></i>
      </span>
      ;
      <?php if($date == helper::today()):?>
      <span><?php echo $lang->effort->hoursConsumedToday;?></span>
      <span class='hoursConsumedToday'><?php echo $hoursConsumedToday . 'h';?></span>
      ，
      <span><?php echo $lang->effort->hoursSurplusToday;?></span>
      <span class='hoursSurplusToday'><?php echo ($this->config->limitWorkHour - $hoursConsumedToday) . 'h';?></span>
      <?php else: ?>
      <span><?php echo $date . $lang->effort->hoursConsumedTodayOther;?></span>
      <span class='hoursConsumedToday'><?php echo $hoursConsumedToday . 'h';?></span>
      ，
      <span><?php echo $date . $lang->effort->hoursSurplusTodayOther;?></span>
      <span class='hoursSurplusToday'><?php echo ($this->config->limitWorkHour - $hoursConsumedToday) . 'h';?></span>
      <?php endif; ?>
    </div>
    <div class='modal-body'>
      <table class='table table-form' id='objectTable'>
        <thead>
          <tr>
            <th class='col-id'><?php echo $lang->idAB;?></th>
            <th class='col-work required'><?php echo $lang->effort->work;?></th>
            <th class='col-objectType required'><?php echo $lang->effort->objectType;?></th>
            <th class='col-execution required'><?php echo $lang->effort->execution;?></th>
            <th class='w-110px required'><?php echo $lang->effort->consumed . '(' . $lang->effort->hour . ')';?></th>
            <th class='col-left required'><?php echo $lang->effort->left . '(' . $lang->effort->hour . ')';?></th>
            <th class='w-110px required'><?php echo $lang->task->testPackageVersion;?></th>
            <th class='col-actions'></th>
          </tr>
        </thead>
        <tbody>
        <?php $i = 1;?>
          <?php foreach($actions as $action):?>
          <tr class="effortBox computed">
            <td class="col-id"><?php echo '<span class="effortID">' . $i . '</span>' . html::hidden("id[]", $i);?></td>
            <td><?php echo html::input("work[]", $action->work, 'class="form-control" ' . (empty($action->work) ? '' : 'tabindex="9999"'));?></td>
            <?php
              if($action->objectType == 'task'):
            ?>
            <td style='overflow:visible' data-shadowtask="<?php echo $action->objectID; ?>">
            <?php
              elseif($action->objectType == 'bug'):
            ?>
            <td style='overflow:visible' data-shadowbug="<?php echo $action->objectID; ?>">
            <?php
              endif;
            ?>
              <?php
              echo html::select("objectType[]", $typeList, "{$action->objectType}_{$action->objectID}", "class='form-control chosen' data-max_drop_width='548px' tabindex='9999'");
              echo html::hidden("actionID[]", $action->id);
              ?>
            </td>
            <td style='overflow:visible'>
              <?php
              $executionList   = $action->objectType == 'bug' ? $executionBug : $executionTask;
              $actionExecution = $action->objectType == 'bug' ? "bug_$action->objectID" : "task_$action->objectID";
              echo html::select("execution[$i]", $executions, $action->execution, "class='form-control chosen' tabindex='9999'");
              ?>
            </td>
            <td><?php echo html::input("consumed[]", '', 'autocomplete="off" class="form-control" onkeyup="consumedKeyup(this)" onfocus="consumedFocus(this)"');?></td>
            <td><?php echo html::input("left[$i]", '', "autocomplete='off' class='form-control' disabled title='{$lang->effort->leftTip}'");?></td>
            <td><?php echo html::input("testPackageVersion[$i]", ($action->objectType == 'task' ? $tasks[$action->objectID]->testPackageVersion : ''), "autocomplete='off' class='form-control' disabled onkeyup='testKeyup(this)' ");?></td>
            <td align='center'>
              <a href='javascript:;' onclick='addEffort(this)' tabindex="9999" class='btn btn-link btn-add'><i class="icon icon-plus"></i></a>
              <a href='javascript:;' onclick='deleteEffort(this)' tabindex="9999" class='btn btn-link btn-delete'><i class="icon icon-close"></i></a>
            </td>
          </tr>
          <?php $i++;?>
          <?php endforeach;?>
          <?php for($j = 0; $j < 8; $j++, $i++):?>
          <tr class="effortBox new">
            <td class="col-id"><?php echo '<span class="effortID">' . $i . '</span>' . html::hidden("id[]", $i);?></td>
            <td><?php echo html::input("work[]", '', 'class=form-control');?></td>
            <td style='overflow:visible'><?php echo html::select("objectType[]", $typeList, 'custom', "tabindex='9999' class='form-control chosen'");?></td>
            <td style='overflow:visible'><?php echo html::select("execution[$i]", $executions, 0, "tabindex='9999' class='form-control chosen'");?></td>
            <td><?php echo html::input("consumed[]", '', 'autocomplete="off" class="form-control" onkeyup="consumedKeyup(this)" onfocus="consumedFocus(this)"');?></td>
            <td><?php echo html::input("left[$i]", '', "autocomplete='off' class='form-control' disabled title='{$lang->effort->leftTip}'");?></td>
            <td><?php echo html::input("testPackageVersion[$i]", '', "autocomplete='off' class='form-control' disabled onkeyup='testKeyup(this)' ");?></td>
            <td align='center'>
              <a href='javascript:;' onclick='addEffort(this)' tabindex="9999" class='btn btn-link btn-add'><i class="icon icon-plus"></i></a>
              <a href='javascript:;' onclick='deleteEffort(this)' tabindex="9999" class='btn btn-link btn-delete'><i class="icon icon-close"></i></a>
            </td>
          </tr>
          <?php endfor;?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan='7' class='text-center'>
              <?php echo html::submitButton();?>
              <?php if(!isonlybody()) echo html::a($this->createLink('my', 'effort', 'type=all'), $lang->goback, '', "class='btn btn-back btn-wide'");?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </form>
</div>

<div id='executionTpl' class='hidden'>
  <?php echo html::select("execution", $executions, 0, "tabindex='9999' class='form-control'");?>
</div>
<?php js::set('num', $i)?>
<?php js::set('executionTask', $executionTask);?>
<?php js::set('executionBug', $executionBug);?>
<?php if(isonlybody()):?>
<script>
$(function()
{
    parent.$('.modal-header').hide();
    parent.$('.modal-body').css('padding', '0px');
    $('#closeModal').click(function(){parent.$.closeModal();});
    $(".form-date").datetimepicker('setEndDate', '<?php echo date(DT_DATE1)?>');
})
</script>
<?php endif;?>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
