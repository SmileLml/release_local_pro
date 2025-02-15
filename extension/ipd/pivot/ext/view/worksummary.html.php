<style>
<?php
helper::import('../css/worksummary/report.css');
if($app->getClientLang() == 'zh-cn') helper::import('../css/worksummary/report.zh-cn.css');
elseif($app->getClientLang() == 'zh-tw') helper::import('../css/worksummary/report.zh-tw.css');
else helper::import('../css/worksummary/report.en.css');
?>
</style>
<div class='cell'>
  <div class='with-padding'>
    <div class="table-row" id='conditions'>
      <form method='post'>
        <div class='col-sm-3'>
          <div class='input-group input-group-sm'>
            <span class='input-group-addon'><?php echo $lang->pivot->dept;?></span>
            <?php echo html::select('dept', $depts, $dept, "class='form-control chosen' onchange='changeParams()'");?>
          </div>
        </div>
        <div class='col-sm-5'>
          <div class='input-group input-group-sm'>
            <span class='input-group-addon'><?php echo $lang->pivot->taskFinishedDate;?></span>
            <div class='datepicker-wrapper datepicker-date'><?php echo html::input('begin', $begin, "class='form-control form-date' onchange='changeParams()'");?></div>
            <span class='input-group-addon'><?php echo $lang->pivot->to;?></span>
            <div class='datepicker-wrapper datepicker-date'><?php echo html::input('end', $end, "class='form-control form-date' onchange='changeParams()'");?></div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if(empty($userTasks)):?>
<div class="cell">
  <div class="table-empty-tip">
    <p><span class="text-muted"><?php echo $lang->error->noData;?></span></p>
  </div>
</div>
<?php else:?>
<div class='cell'>
  <div class='panel'>
    <div class="panel-heading">
      <div class="panel-title"><?php echo $title;?></div>
      <nav class="panel-actions btn-toolbar"></nav>
    </div>
    <div class='table-responsive' data-ride='table'>
      <table class='table table-condensed table-striped table-bordered table-fixed' id="worksummary">
        <thead>
          <tr class='colhead text-center'>
            <th class="userWidth"><?php echo $lang->task->finishedByAB;?></th>
            <?php if($this->config->systemMode == 'ALM'):?>
            <th class="<?php echo common::checkNotCN() ? 'w-100px' : 'w-80px';?>"><?php echo $lang->task->project;?></th>
            <?php endif;?>
            <th class="<?php echo common::checkNotCN() ? 'w-100px' : 'w-80px';?>"><?php echo $lang->task->execution;?></th>
            <th class="w-50px"><?php echo $lang->task->id;?></th>
            <th class="<?php echo common::checkNotCN() ? 'w-100px' : 'w-80px';?>"><?php echo $lang->task->name;?></th>
            <th class="priWidth"><?php echo $lang->priAB;?></th>
            <th class="dateWidth"><?php echo $lang->task->estStarted;?></th>
            <th class="dateWidth"><?php echo $lang->task->realStarted;?></th>
            <th class="dateWidth"><?php echo $lang->task->deadline;?></th>
            <th class="dateWidth"><?php echo $lang->task->finishedDate;?></th>
            <th class="delayWidth"><?php echo $lang->pivot->delay . '(' . $lang->pivot->day . ')';?></th>
            <th class="estWidth"><?php echo $lang->task->estimate;?></th>
            <th class="<?php echo common::checkNotCN() ? 'w-110px' : 'w-90px';?>"><?php echo $lang->pivot->taskConsumed;?></th>
            <th class="taskTotalWidth"><?php echo $lang->pivot->taskTotal;?></th>
            <th class="projectConsumedWidth"><?php echo $lang->pivot->executionConsumed;?></th>
            <th class="userConsumedWidth"><?php echo $lang->pivot->userConsumed;?></th>
          </tr>
        </thead>
        <tbody>
          <?php $color = false;?>
          <?php $i     = 0;?>
          <?php foreach($userTasks as $user => $projectTaskGroup):?>
          <?php
          if(!isset($users[$user])) continue;
          $taskTotal         = $totalConsumed = 0;
          $executionConsumed = array();
          foreach($projectTaskGroup as $executionTasks)
          {
              foreach($executionTasks as $tasks)
              {
                  $taskTotal += count($tasks);
                  foreach($tasks as $task)
                  {
                      if(!isset($executionConsumed[$task->execution])) $executionConsumed[$task->execution] = 0;
                      $executionConsumed[$task->execution] += $task->consumed;
                      $totalConsumed += $task->consumed;
                  }
              }
          }
          ?>
          <tr class="text-center">
            <td class="w-user" rowspan="<?php echo $taskTotal;?>"><?php echo zget($users, $user);?></td>
            <?php $j = 0;?>
            <?php foreach($projectTaskGroup as $projectID => $executionTasks):?>
            <?php
            $projectTaskTotal = count($executionTasks, 1) - count($executionTasks);
            if($j != 0) echo "<tr class='text-center'>";
            $projectName = zget($projects, $projectID, '');
            ?>
            <?php if($this->config->systemMode == 'ALM'):?>
            <td class='text-left' rowspan="<?php echo $projectTaskTotal;?>" title='<?php echo $projectName?>'><?php echo $projectName;?></td>
            <?php endif;?>
            <?php $g = 0;?>
            <?php foreach($executionTasks as $executionID => $tasks):?>
            <?php
            $executionTaskTotal = count($tasks);
            if($g != 0) echo "<tr class='text-center'>";
            $executionName = zget($executions, $executionID, '');
            ?>
            <td class='text-left' rowspan="<?php echo $executionTaskTotal;?>" title='<?php echo $executionName?>'><?php echo $executionName;?></td>
            <?php $k = 0;?>
            <?php foreach($tasks as $task):?>
            <?php if($k != 0) echo "<tr class='text-center'>"?>
            <td><?php echo $task->id;?></td>
            <td class="text-left" title="<?php echo $task->name;?>">
              <?php
              if($task->parent > 0) echo "[{$this->lang->task->childrenAB}] ";
              if($task->multiple)   echo "[{$this->lang->task->multipleAB}] ";
              echo $task->name;
              ?>
            </td>
            <td><span class='<?php echo 'pri' . $task->pri?>'><?php echo $task->pri;?></span></td>
            <td><?php echo $task->estStarted;?></td>
            <td><?php echo substr($task->realStarted, 0, 10);?></td>
            <td><?php echo $task->deadline;?></td>
            <td><?php echo substr($task->finishedDate, 0, 10);?></td>
            <td>
              <?php
              if(!helper::isZeroDate($task->deadline))
              {
                  $finishedDate = strtotime(substr($task->finishedDate, 0, 10));
                  $deadline     = strtotime($task->deadline);
                  $days         = round(($finishedDate - $deadline) / 3600 / 24);
                  if($days > 0) echo $days;
              }
              ?>
            </td>
            <td><?php echo $task->estimate;?></td>
            <td><?php echo $task->consumed;?></td>
            <?php if($k == 0):?>
            <td rowspan="<?php echo $executionTaskTotal;?>"><?php echo $executionTaskTotal;?></td>
            <td rowspan="<?php echo $executionTaskTotal;?>"><?php echo zget($executionConsumed, $executionID, '');?></td>
            <?php endif;?>
            <?php if($j == 0):?>
            <td rowspan="<?php echo $taskTotal;?>"><?php echo $totalConsumed;?></td>
            <?php endif;?>
          </tr>
          <?php $i++; $j++; $k++; $g++;?>
          <?php endforeach;?>
          <?php endforeach;?>
          <?php endforeach;?>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <div class='table-footer'>
      <?php echo $this->pivot->getFullTablePager($recTotal, $recPerPage, $page, $leftPage, $rightPage, $pageTotal, 'workSummaryPager(this)');?>
    </div>
  </div>
</div>
<?php endif;?>
<script><?php helper::import('../js/worksummary/report.js');?></script>
