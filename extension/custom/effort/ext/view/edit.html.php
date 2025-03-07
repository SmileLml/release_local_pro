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
<?php js::set('today',                           helper::today());?>
<?php js::set('effortDate',                      $effort->date);?>
<?php js::set('objectType',                      $effort->objectType);?>
<?php js::set('effortConsumed',                  $effort->consumed);?>
<?php
  if($effort->objectType == 'task')
  {
    js::set('taskConsumed', $consumed);
    js::set('taskEstimate', $estimate);
    js::set('task', $task);
    js::set('effort', $effort);
  }
?>
<?php js::set('noticeFinish',                    $lang->effort->noticeFinish);?>
<?php js::set('hoursConsumed',                   $hoursConsumed);?>
<?php js::set('hoursSurplus',                    $this->config->limitWorkHour - $hoursConsumed);?>
<?php js::set('limitWorkHour',                   $this->config->limitWorkHour);?>
<?php js::set('hoursConsumedTodayTitle',         $lang->effort->hoursConsumedToday);?>
<?php js::set('hoursSurplusTodayTitle',          $lang->effort->hoursSurplusToday);?>
<?php js::set('hoursSurplusTodayOtherTitle',     $lang->effort->hoursSurplusTodayOther);?>
<?php js::set('hoursConsumedTodayOtherTitle',    $lang->effort->hoursConsumedTodayOther);?>
<?php js::set('hoursConsumedTodayOverflow',      $lang->effort->hoursConsumedTodayOverflow);?>
<?php js::set('hoursConsumedTodayOverflowOther', $lang->effort->hoursConsumedTodayOverflowOther);?>
<div id='mainContent' class='main-content'>
  <div class='center-block'>
    <div class='main-header'>
      <h2>
        <span class='label label-id'><?php echo $effort->objectID;?></span>
        <?php echo $objectName;?>
        <small><?php echo $lang->arrow . $lang->effort->edit;?></small>
        <span class="modal-title">
          <i class="icon-exclamation-sign"><?php echo $lang->effort->noChooseClosedProject; ?></i>
        </span>
        <span><?php echo $effort->date == helper::today() ? $lang->effort->hoursConsumedToday : $effort->date . $lang->effort->hoursConsumedTodayOther;?></span>
        <span class='hoursConsumed'><?php echo $hoursConsumed . 'h';?></span>
        ，
        <span><?php echo $effort->date == helper::today() ? $lang->effort->hoursSurplusToday : $effort->date . $lang->effort->hoursSurplusTodayOther;?></span>
        <span class='hoursSurplus'><?php echo ($this->config->limitWorkHour - $hoursConsumed) . 'h';?></span>
      </h2>
    </div>
    <form method='post' target='hiddenwin' style='padding-bottom:80px;'>
      <table class='table table-form'>
        <?php if($effort->objectType == 'task'):?>
        <tr id='productBox'<?php if(empty($project->hasProduct) || $this->config->vision == 'or') echo "class='hide'";?>>
          <th class='w-100px'><?php echo $lang->effort->product;?></th>
          <td class='w-p45'><?php echo html::select('product[]', $products, $effort->product, 'class="form-control chosen" multiple' . (!$canBeChanged ? ' disabled' : ''));?></td><td></td>
        </tr>
        <?php endif;?>
        <tr id='executionBox'>
          <th class='w-80px'><?php echo $this->config->vision == 'or' ? $this->lang->stage->common : $lang->effort->execution;?></th>
          <td class='w-p45 required'><?php echo html::select('execution', array(0 => '') + $executions, $effort->execution, 'class="form-control chosen" data-drop_direction="down"'  . ($effort->objectType == 'bug' ? ' disabled' : ''));?></td><td></td>
        </tr>
        <tr>
          <th class='w-80px'><?php echo $lang->effort->date;?></th>
          <td class='w-p45 required'><?php echo html::input('date', $effort->date, "class='form-date form-control'"  . (!$canBeChanged ? ' disabled' : ''));?></td><td></td>
        </tr>
        <tr>
          <th><?php echo $lang->effort->consumed;?></th>
          <td class='required'><?php echo html::input('consumed', $effort->consumed, "class='form-control' autocomplete='off' oninput='let value = this.value; if(!/^\d*\.?\d{0,2}$/.test(value)) { this.value = value.slice(0, value.indexOf(\".\") + 3); }' "  . (!$canBeChanged ? ' disabled' : ''));?></td>
        </tr>
        <tr>
          <th><?php echo $lang->effort->left;?></th>
          <?php $readonly = $recentDateID === $effort->id ? '' : 'readonly';?>
          <?php if($effort->objectType == 'task' and !empty($task->team) and $effort->left == 0) $readonly = 'readonly';?>
          <td><?php echo html::input('left', $effort->left, "class='form-control' autocomplete='off' oninput='let value = this.value; if(!/^\d*\.?\d{0,2}$/.test(value)) { this.value = value.slice(0, value.indexOf(\".\") + 3); }' $readonly"  . (!$canBeChanged ? ' disabled' : ''));?></td>
        </tr>
        <tr id='testPackageVersionID' style='display:none;'>
          <th><?php echo $lang->task->testPackageVersion;?></th>
          <td class='required'><?php echo html::input('testPackageVersion', $task->testPackageVersion, 'class="form-control" autocomplete="off"' . (!$canBeChanged ? ' disabled' : ''));?></td>
        </tr>
        <tr>
          <th><?php echo $lang->effort->work;?></th>
          <td colspan='2'><?php echo html::input('work', $effort->work, "class='form-control'"  . (!$canBeChanged ? ' disabled' : ''));?></td>
        </tr>
        <tr>
          <td colspan='3' class='text-center'>
            <?php echo html::hidden('objectType', $effort->objectType);?>
            <?php echo html::hidden('objectID', $effort->objectID);?>
            <?php echo html::submitButton() . html::backButton();?>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
<script>
account='<?php echo $app->user->account;?>';
customHtml = $('#nameBox').html();
</script>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
