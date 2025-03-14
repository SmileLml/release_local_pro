<?php
/**
 * The import view file of task module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     task
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/datepicker.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/tablesorter.html.php';?>
<?php js::set('browseType', $browseType);?>
<?php js::set('isonlybody', isonlybody());?>
<style>
    #importBugForm .bug-pri {overflow: visible;}
    .c-type-box {width: 100px;}
    .c-module-box .c-mailto-box{width: 150px;}
</style>
<div id='mainMenu' class='clearfix'>
  <div class='btn-toolbar pull-left'>
    <?php echo html::a(inlink('importBug', "executionID=$executionID"), "<span class='text'>{$lang->execution->importBug}</span>", '', "class='btn btn-link btn-active-text'");?>
  </div>
</div>
<div id='mainContent'>
  <div class='cell space-sm'>
    <div id='queryBox' data-module='importBug' class='show'></div>
  </div>
  <form class='main-table' method='post' target='hiddenwin' id='importBugForm' data-ride='table'>
    <table class='table table-form has-sort-head table-fixed'>
      <thead>
        <tr>
          <th class='c-id'>
            <div class="checkbox-primary check-all" title="<?php echo $lang->selectAll?>">
              <label></label>
            </div>
            <?php echo $lang->idAB;?>
          </th>
          <th class='c-severity' title=<?php echo $lang->bug->severity;?>> <?php echo $lang->bug->severityAB;?></th>
          <th class='c-pri' title=<?php echo $lang->execution->pri;?>><?php echo $lang->priAB;?></th>
          <th><?php echo $lang->task->name;?></th>
          <th class='c-status'><?php echo $lang->bug->statusAB;?></th>
          <th class='c-pri-box <?php echo in_array('pri', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->pri;?></th>
          <th class='c-type-box <?php echo in_array('type', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->type;?></th>
          <th class='c-module-box <?php echo in_array('module', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->module;?></th>
          <th class='c-assigned-box <?php echo in_array('assignedTo', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->assignedTo;?></th>
          <th class='c-mailto-box <?php echo in_array('mailto', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->mailto;?></th>
          <th class='c-estimate-box  <?php echo in_array('estimate', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->estimate;?></th>
          <th class='c-date-box <?php echo in_array('estStarted', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->estStarted;?></th>
          <th class='c-date-box <?php echo in_array('deadline', $requiredFields) ? 'required' : ''?>'><?php echo $lang->task->deadline;?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($bugs as $bug):?>
        <tr>
          <td class='c-id'>
            <div class="checkbox-primary">
              <input type='checkbox' name='import[<?php echo $bug->id;?>]' value='<?php echo $bug->id;?>' />
              <label></label>
            </div>
            <?php echo sprintf('%03d', $bug->id) . html::hidden("id[$bug->id]", $bug->id);?>
          </td>
          <?php
            $severityValue = zget($this->lang->bug->severityList, $bug->severity);
            $severityClass = !is_numeric($severityValue) ? 'label-severity-custom' : 'label-severity';
            $bugSeverity   = !is_numeric($severityValue) ? $severityValue : '';
          ?>
          <td class='c-severity'><span class='<?php echo $severityClass;?>' data-severity='<?php echo $bug->severity;?>' title="<?php echo $severityValue;?>"><?php echo $bugSeverity;?></span></td>
          <td><span class='label-pri <?php echo 'label-pri-' . $bug->pri;?>' title='<?php echo zget($lang->bug->priList, $bug->pri, $bug->pri)?>'><?php echo zget($lang->bug->priList, $bug->pri, $bug->pri)?></span></td>
          <td class='nobr'><?php echo html::input("name[$bug->id]", $bug->title, "class='form-control'")?></td>
          <td><span class='status-bug status-<?php echo $bug->status?>'><?php echo $this->processStatus('bug', $bug);?></span></td>
          <td class='bug-pri'><?php echo html::select("pri[$bug->id]", $lang->task->priList, zget($lang->task->priList, $bug->pri ? $bug->pri : 3, 3), "class='form-control chosen'");?></td>
          <td style='overflow:visible'><?php echo html::select("type[$bug->id]", $lang->task->typeList, '', "class='form-control chosen' onchange='setOwners(this.value,{$bug->id})'");?></td>
          <td style='overflow:visible'><?php echo html::select("module[$bug->id]", $moduleOptionMenu, '', "class='form-control chosen'");?></td>
          <td style='overflow:visible'><?php echo html::select("assignedTo[$bug->id]", $members, zget($members, $bug->assignedTo, '', $bug->assignedTo), "class='form-control chosen'");?></td>
          <td style='overflow:visible'><?php echo html::select("mailto[$bug->id][]", $users, '',  "class='form-control chosen' multiple");?></td>
          <td><?php echo html::input("estimate[$bug->id]", '', 'size=4 class="form-control"');?></td>
          <td><?php echo html::input("estStarted[$bug->id]", '0000-00-00', 'size=4 class="form-control form-date"');?></td>
          <?php $deadline = ($bug->deadline > helper::today() and $bug->deadline > $execution->begin) ? $bug->deadline : '0000-00-00';?>
          <td><?php echo html::input("deadline[$bug->id]", $deadline, 'size=4 class="form-control form-date"');?></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
    <div class='table-footer'>
      <div class="checkbox-primary check-all"><label><?php echo $lang->selectAll?></label></div>
      <div class="table-actions btn-toolbar show-always">
        <?php echo html::submitButton('<i class="icon icon-import icon-sm"></i> ' . $lang->import, '', 'btn btn-primary');?>
      </div>
      <div class='btn-toolbar'>
        <?php if(isonlybody()):?>
        <?php echo html::commonButton('<i class="icon icon-sm"></i> ' . $lang->goback, "onclick='goback($executionID)'", 'btn');?>
        <?php else:?>
        <?php echo html::backButton('','','btn');?>
        <?php endif;?>
      </div>
      <?php $pager->show('right', 'pagerjs');?>
    </div>
  </form>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
