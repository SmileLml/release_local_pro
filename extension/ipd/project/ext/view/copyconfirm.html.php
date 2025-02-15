<?php
/**
 * The html template file of index method of index module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     ZenTaoPMS
 * @version     $Id: index.html.php 4129 2013-01-18 01:58:14Z wwccss $
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/datepicker.html.php';?>
<?php
js::set('copyProjectID', $copyProjectID);
js::set('weekend', $this->config->execution->weekend);
?>
<div id='mainContent' class='main-content'>
  <div class='main-header'>
    <h2 class='confirm-title'><?php echo in_array($project->model, array('waterfall', 'waterfallplus')) ? $lang->project->stageInfoConfirm : $lang->project->executionInfoConfirm;?></h2>
    <?php if($project->model == 'scrum' or $project->model == 'agileplus'):?>
    <h2 class='info-tips'><?php echo $lang->project->executionInfoTips;?></h1>
    <?php endif;?>
  </div>
  <form method='post' class='load-indicator main-form form-ajax' enctype='multipart/form-data' id="copyConfirmForm">
    <?php if($project->model == 'scrum' or $project->model == 'agileplus'):?>
    <table class="table table-form">
      <thead>
        <tr>
          <?php if($project->model == 'agileplus'):?>
          <th class='c-id'><?php echo $lang->execution->method;?></th>
          <?php endif;?>
          <th class='c-name required' style='width:100%'><?php echo $lang->execution->name;?></th>
          <?php if(isset($config->setCode) and $config->setCode == 1):?>
          <th class='c-code required'><?php echo $lang->execution->code;?></th>
          <?php endif;?>
          <th class='c-type'><?php echo $lang->execution->type;?></th>
          <th class="c-user"> <?php echo $lang->project->PM;?></th>
          <th class='c-date required'><?php echo $lang->execution->begin;?></th>
          <th class='c-date required'><?php echo $lang->execution->end;?></th>
          <th class='c-days'><?php echo $lang->execution->days;?></th>
        </tr>
      </thead>
      <tbody>
        <?php $hasInfo = 'has-info';?>
        <?php foreach($executions as $executionID => $execution):?>
        <tr>
          <?php if($project->model == 'agileplus'):?>
          <td title='<?php echo zget($lang->execution->typeList, $execution->type);?>'><?php echo zget($lang->execution->typeList, $execution->type);?></td>
          <?php endif;?>
          <td title='<?php echo $execution->name;?>'>
            <?php echo html::hidden("executionIDList[$executionID]", $executionID);?>
            <?php echo html::input("names[$executionID]", $execution->name, "class='form-control $hasInfo'" );?>
          </td>
          <?php if(isset($config->setCode) and $config->setCode == 1):?>
          <td title='<?php echo $execution->code;?>'><?php echo html::input("codes[$executionID]", $execution->code, "class='form-control $hasInfo'");?></td>
          <?php endif;?>
          <td class='type'><?php echo html::select("lifetimes[$executionID]", $lang->execution->lifeTimeList,   $executions[$executionID]->lifetime,   'class=form-control');?></td>
          <td class='text-left' style='overflow:visible'><?php echo html::select("PMs[$executionID]", $users, $executions[$executionID]->PM, "class='form-control picker-select'");?></td>
          <td><?php echo html::input("begins[$executionID]", '', "class='form-control form-date $hasInfo' onchange='computeWorkDays(this.id)'");?></td>
          <td><?php echo html::input("ends[$executionID]", '',   "class='form-control form-date $hasInfo' onchange='computeWorkDays(this.id)'");?></td>
          <td class='days'>
            <div class='input-group'>
              <?php echo html::input("dayses[$executionID]", '', "class='form-control'");?>
              <span class='input-group-addon'><?php echo $lang->execution->day;?></span>
            </div>
          </td>
          <?php echo html::hidden("parents[$executionID]", $executions[$executionID]->parent);?>
        </tr>
        <?php if($hasInfo == 'has-info') $hasInfo = '';?>
        <?php endforeach;?>
      </tbody>
    </table>
    <?php elseif(in_array($project->model, array('waterfall', 'waterfallplus')) and $project->division == 1):?>
    <?php $showProduct = !(count($executionIdList) == 1 and count($oldProductPairs) <= 1);?>
    <?php foreach($executionIdList as $productID => $stageIdList):?>
    <?php if($showProduct):?>
    <div class='waterfallstage'>
      <div class='waterfallheader'>
        <?php
        $oldProductPairs[0] = $lang->project->notCopyStage;
        reset($oldProductPairs);
        $productChosen = html::select('', $oldProductPairs, isset($oldProductPairs[$productID]) ? $productID : key($oldProductPairs), "class='copyproducts' onchange='loadStages(this)'");
        ?>
        <span><?php printf($lang->project->chosenProductStage, zget($productPairs, $productID), $productChosen);?></span>
      </div>
      <div class='waterfallbody' data-productid='<?php echo $productID?>'>
    <?php endif;?>
    <?php include './ajaxloadstages.html.php';?>
    <?php if($showProduct):?>
      </div>
    </div>
    <?php endif;?>
    <?php endforeach;?>
    <?php elseif(in_array($project->model, array('waterfall', 'waterfallplus', 'ipd')) and $project->division == 0 and !empty($executionIdList)):?>
    <?php
    $productID = 0;
    $stageIdList = current($executionIdList);
    include './ajaxloadstages.html.php';
    ?>
    <?php endif;?>
    <div class="text-center form-actions">
        <?php echo html::submitButton($lang->project->completeCopy);?>
        <?php echo html::a($this->createLink('project', 'copyProject', "project$project->model&programID=0&copyProjectID=$copyProjectID&extra=copyType=previous"), $lang->project->previous, '', "class='btn btn-wide'");?>
    </div>
  </form>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
