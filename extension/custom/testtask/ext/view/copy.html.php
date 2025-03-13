<?php
/**
 * The batch create view of story module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
 * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
 * @author      Yangyang Shi <shiyangyang@cnezsoft.com>
 * @package     story
 * @version     $Id$
 * @link        http://www.zentao.net
 */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<style>
    .c-begin, .c-end, .c-owner, .c-version {width: 120px;}
    .c-name, .c-member, .c-type {width: 200px;}
    .c-pri {width: 80px;}
    .c-status {width: 100px;}
</style>
<div id='mainContent' class='main-content fade'>
  <div class='main-header'>
    <h2>
      <?php echo $lang->testtask->copy;?>
    </h2>
  </div>
  <form class='main-form' method='post' target='hiddenwin' id='copyTestTaskForm' enctype='multipart/form-data'>
    <div class="table-responsive">
      <table class='table table-form'>
        <thead>
          <tr>
            <th class='c-id'><?php echo $lang->idAB;?></th>
            <th class='c-version required'><?php echo $lang->testtask->common . $lang->testtask->version;?></th>
            <th class='c-name'><?php echo $lang->testtask->name;?></th>
            <th class='c-status'><?php echo $lang->testtask->status;?></th>
            <th class='c-begin <?php echo in_array('begin', $requiredFields) ? 'required' : '';?>'> <?php echo $lang->testtask->begin;?></th>
            <th class='c-end <?php echo in_array('end', $requiredFields) ? 'required' : '';?>'> <?php echo $lang->testtask->end;?></th>
            <th class='c-owner <?php echo in_array('owner', $requiredFields) ? 'required' : '';?>'> <?php echo $lang->testtask->owner;?></th>
            <th class='c-member <?php echo in_array('bemembergin', $requiredFields) ? 'required' : '';?>'> <?php echo $lang->testtask->members?></th>
            <th class='c-pri <?php echo in_array('pri', $requiredFields) ? 'required' : '';?>'> <?php echo $lang->testtask->pri;?></th>
            <th class='c-type <?php echo in_array('type', $requiredFields) ? 'required' : '';?>'> <?php echo $lang->testtask->type;?></th>
          </tr>
        </thead>
        <tbody>
          <?php
            for($i = 1; $i <= $copyNumber; $i++):
          ?>
          <tr>
            <td class='text-left'><?php echo $i;?></td>
            <?php $name = $task->name . ($i == 10 ? "0" : "00") . $i; ?>
            <td><?php echo html::select("build[$i]", $builds, $task->build, "class='form-control chosen'");?></td>
            <td><?php echo html::input("name[$i]", $name, "class='form-control'");?></td>
            <td><?php echo html::select("status[$i]", $lang->testtask->statusList, $task->status, "class='form-control chosen'");?></td>
            <td><?php echo html::input("begin[$i]", $task->begin ?: '0000-00-00', 'size=4 class="form-control form-date"');?></td>
            <td><?php echo html::input("end[$i]", $task->end ?: '0000-00-00', 'size=4 class="form-control form-date"');?></td>
            <td><?php echo html::select("owner[$i]", $users, $task->owner, "class='form-control chosen'");?></td>
            <td><?php echo html::select("member[$i][]", $users, $task->members, "class='form-control picker-select' multiple");?></td>
            <td><?php echo html::select("pri[$i]", $lang->testtask->priList, $task->pri, "class='form-control chosen'");?></td>
            <td><?php echo html::select("type[$i][]", $lang->testtask->typeList, $task->type, "class='form-control picker-select' multiple");?></td>
          </tr>
          <?php endfor;?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan='8' class='text-center form-actions'>
              <?php echo html::submitButton();?>
              <?php echo html::backButton();?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </form>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
