<?php
  /**
   * The bug view file of project module of ZenTaoPMS.
   *
   * @copyright   Copyright 2009-2015 禅道软件（青岛）有限公司(ZenTao Software (Qingdao) Co., Ltd. www.cnezsoft.com)
   * @license     ZPL(http://zpl.pub/page/zplv12.html) or AGPL(https://www.gnu.org/licenses/agpl-3.0.en.html)
   * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
   * @package     project
   * @version     $Id: bug.html.php 4894 2013-06-25 01:28:39Z wyd621@gmail.com $
   * @link        http://www.zentao.net
   */
?>
<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/datatable.fix.html.php';?>
<?php include $app->getModuleRoot() . 'common/view/datepicker.html.php';?>
<?php
js::set('deadlineAction', $this->createLink('bug', 'batchSetDeadline'));
js::set('projectExecutionPairs', $projectExecutionPairs);
js::set('bugExecution',   $lang->bug->execution);
js::set('showExecution',  true);
js::set('released',       $lang->build->released);
js::set('adjustAction',   $this->createLink('bug', 'batchAdjust'));
js::set('productLang',    $lang->bug->product);
js::set('projectLang',    $lang->bug->project);
js::set('executionLang',  $lang->bug->execution);
js::set('bugOpenedBuild', $lang->bug->openedBuild);
js::set('noempty',        $lang->bug->noempty);
js::set('batchCheckMax',     $config->bug->batchCheckMax);
js::set('batchCheckMaxLang', $lang->bug->batchCheckMax);
?>
  <style>
      #subHeader #dropMenu .col-left .list-group {margin-bottom: 0px; padding-top: 10px;}
      #subHeader #dropMenu .col-left {padding-bottom: 0px;}
      #currentBranch + #dropMenu .col-left {padding-bottom: 30px;}
      .c-deadline {text-align: center;}
      #mainContent .main-col {padding-bottom: 20px;}
  </style>
  <div id="mainMenu" class="clearfix main-row fade in">
    <div id="sidebarHeader">
      <div class="title" title="<?php echo $moduleName;?>">
        <?php
          echo $moduleName;
          if(!empty($moduleID))
          {
            $removeLink = $this->createLink('project', 'bug', "project={$project->id}&productID={$productID}&branch=$branchID&orderBy=$orderBy&build=$buildID&type=$type&param=0&recTotal=$pager->recTotal&recPerPage=$pager->recPerPage");
            echo html::a($removeLink, "<i class='icon icon-sm icon-close'></i>", '', "class='text-muted' data-app='{$this->app->tab}'");
          }
        ?>
      </div>
    </div>
    <div class="btn-toolbar pull-left">
      <?php
        $buildName = $build ? " <span class='label label-danger'>Build:{$build->name}</span>" : '';
        $module    = $type != 'bysearch' ?  "&param=$param" : '';
        foreach($lang->project->featureBar['bug'] as $key => $label)
        {
          $activeClass = $key == $type ? 'btn-active-text' : '';
          $label       = "<span class='text'>{$label}</span>";
          if($type == $key) $label .= " <span class='label label-light label-badge'>{$pager->recTotal}</span>$buildName";
          echo html::a($this->inlink('bug', "projectID={$project->id}&productID={$productID}&branch=$branchID&orderBy=status,id_desc&build=$buildID&type={$key}$module"), $label, '', "id='{$key}Tab' class='btn btn-link {$activeClass}'");
        }
      ?>
      <a class="btn btn-link querybox-toggle" id="bysearchTab"><i class="icon icon-search muted"></i> <?php echo $lang->bug->search;?></a>
    </div>
    <div class="btn-toolbar pull-right">
      <?php common::printLink('bug', 'export', "productID=$productID&orderBy=$orderBy&browseType=&projectID=$project->id", "<i class='icon icon-export muted'> </i> " . $lang->bug->export, '', "class='btn btn-link export'");?>
      <?php if(common::canModify('project', $project)) common::printLink('bug', 'create', "productID=$productID&branch=$branchID&extras=projectID=$project->id", "<i class='icon icon-plus'></i> " . $lang->bug->create, '', "class='btn btn-primary'");?>
    </div>
  </div>
  <div id="mainContent" class='main-row split-row fade'>
    <div class="side-col" id='sidebar' data-min-width='235'>
      <div class="sidebar-toggle"><i class="icon icon-angle-left"></i></div>
      <div class='cell'>
        <?php if(!$moduleTree):?>
          <hr class="space">
          <div class="text-center text-muted"><?php echo $lang->bug->noModule;?></div>
          <hr class="space">
        <?php else:?>
          <?php echo $moduleTree;?>
        <?php endif;?>
        <div class="text-center"></div>
      </div>
    </div>
    <div class='main-col' data-min-width='400'>
      <div class="cell <?php if($type == 'bysearch') echo 'show';?>" id="queryBox" data-module='projectBug'></div>
      <?php if(empty($bugs)):?>
        <div class="table-empty-tip">
          <p>
            <span class="text-muted"><?php echo $lang->bug->noBug;?></span>
            <?php if(common::canModify('project', $project) and common::hasPriv('bug', 'create')):?>
              <?php echo html::a($this->createLink('bug', 'create', "productID=$productID&branch=$branchID&extra=projectID=$project->id"), "<i class='icon icon-plus'></i> " . $lang->bug->create, '', "class='btn btn-info' data-app='project'");?>
            <?php endif;?>
          </p>
        </div>
      <?php else:?>
        <?php
        $datatableId  = $this->moduleName . ucfirst($this->methodName);
        $useDatatable = (isset($config->datatable->$datatableId->mode) and $config->datatable->$datatableId->mode == 'datatable');
        ?>
        <form class='main-table' method='post' id='projectBugForm' <?php if(!$useDatatable) echo "data-ride='table'";?>>
          <div class="table-header fixed-right">
            <nav class="btn-toolbar pull-right setting"></nav>
          </div>
          <?php
            $vars = "projectID={$project->id}&productID=$productID&branch=$branchID&orderBy=%s&build=$buildID&type=$type&param=$param&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}";
            if($useDatatable) include $app->getModuleRoot() . 'common/view/datatable.html.php';
            else              include $app->getModuleRoot() . 'common/view/tablesorter.html.php';

            $setting = $this->datatable->getSetting('project');
            $widths  = $this->datatable->setFixedFieldWidth($setting);
            $columns = 0;

            $canBatchAssignTo = (common::hasPriv('bug', 'batchAssignTo') and common::canModify('project', $project));
            $canBatchAdjust   = (common::hasPriv('bug', 'batchAdjust')   and common::canModify('project', $project));
            $canBatchEdit     = (common::hasPriv('bug', 'batchEdit')     and common::canModify('project', $project));
            $canBatchAction   = ($canBatchAssignTo || $canBatchAdjust || $canBatchEdit);
          ?>
          <?php if(!$useDatatable) echo '<div class="table-responsive">';?>
          <table class='table has-sort-head<?php if($useDatatable) echo ' datatable';?>' id='bugList' data-fixed-left-width='<?php echo $widths['leftWidth']?>' data-fixed-right-width='<?php echo $widths['rightWidth']?>'>
            <thead>
            <tr>
              <?php
                foreach($setting as $key => $value)
                {
                  if(!$project->hasProduct and $project->model != 'scrum' and $value->id == 'plan') continue;
                  if(!$project->hasProduct and $value->id == 'branch') continue;
                  if(!common::canModify('project', $project) and $value->id == 'actions') continue;
                  if($value->show)
                  {
                    $this->datatable->printHead($value, $orderBy, $vars, $canBatchAction);
                    $columns ++;
                  }
                }
              ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach($bugs as $bug):?>
              <?php
              $canBeChanged = common::canBeChanged('bug', $bug);
              $arrtibute    = $canBeChanged ? '' : 'disabled';
              ?>
              <tr data-id='<?php echo $bug->id?>'>
                <?php foreach($setting as $value)
                {
                  if(!$project->hasProduct and $project->model != 'scrum' and $value->id == 'plan') continue;
                  if(!$project->hasProduct and $value->id == 'branch') continue;
                  if(!common::canModify('project', $project) and $value->id == 'actions') continue;
                  $bug->assignedToChange = true;
                  if(!common::canModify('project', $project)) $bug->assignedToChange = false;
                  $this->bug->printCell($value, $bug, $users, $builds, $branchOption, $modulePairs, $executions, $plans, $stories, $tasks, $useDatatable ? 'datatable' : 'table', $projectPairs);
                }
                ?>
              </tr>
            <?php endforeach;?>
            </tbody>
          </table>
          <?php if(!$useDatatable) echo '</div>';?>
          <div class='table-footer'>
            <?php if($canBatchAction):?>
              <div class="checkbox-primary check-all"><label><?php echo $lang->selectAll?></label></div>
              <div class="table-actions btn-toolbar">
                <?php if($canBatchAssignTo):?>
                <div class="btn-group dropup">
                  <button data-toggle="dropdown" type="button" class="btn"><?php echo $lang->bug->assignedTo?> <span class="caret"></span></button>
                  <?php
                    $withSearch = count($memberPairs) > 10;
                    $actionLink = $this->createLink('bug', 'batchAssignTo', "projectID={$project->id}&type=project");
                    echo html::select('assignedTo', $memberPairs, '', 'class="hidden"');

                    if($withSearch)
                    {
                      echo "<div class='dropdown-menu search-list search-box-sink' data-ride='searchList'>";
                      echo '<div class="input-control search-box has-icon-left has-icon-right search-example">';
                      echo '<input id="userSearchBox" type="search" class="form-control search-input" autocomplete="off" />';
                      echo '<label for="userSearchBox" class="input-control-icon-left search-icon"><i class="icon icon-search"></i></label>';
                      echo '<a class="input-control-icon-right search-clear-btn"><i class="icon icon-close icon-sm"></i></a>';
                      echo '</div>';
                      $membersPinYin = common::convert2Pinyin($memberPairs);
                    }
                    else
                    {
                      echo "<div class='dropdown-menu search-list'>";
                    }
                    echo '<div class="list-group">';
                    foreach($memberPairs as $key => $value)
                    {
                      if(empty($key)) continue;
                      $searchKey = $withSearch ? ('data-key="' . zget($membersPinYin, $value, '') . " @$key\"") : "data-key='@$key'";
                      echo html::a("javascript:$(\".table-actions #assignedTo\").val(\"$key\");setFormAction(\"$actionLink\")", $value, '', $searchKey);
                    }
                    echo "</div>";
                    echo "</div>";
                  ?>
                </div>
                <?php endif;?>
                <?php
                  if($canBatchAdjust)
                  {
                    echo html::hidden('adjustProduct', '');
                    echo html::hidden('adjustBranch', '');
                    echo html::hidden('adjustProject', '');
                    echo html::hidden('adjustExecution', '');
                    echo html::hidden('adjustBuild', '');
                    echo html::commonButton($lang->bug->batchAdjust, "onclick='checkMaxSelect()'", 'btn btn-info');
                    echo html::commonButton($lang->bug->batchAdjust, "href='#adjustModal' data-toggle='modal' id='popModal'", 'btn btn-info hidden');
                  }
                  if($canBatchEdit)
                  {
                    echo html::hidden('deadline', '');
                    echo html::commonButton($lang->bug->deadline, "href='#deadlineModal' data-toggle='modal'", 'btn btn-info');
                  }
                ?>
              </div>
            <?php endif;?>
            <?php $pager->show('right', 'pagerjs');?>
          </div>
        </form>
      <?php endif;?>
    </div>
  </div>
  <div class="modal fade" id="deadlineModal" tabindex="-1" role="dialog">
    <div class="modal-dialog w-600px">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><i class="icon icon-close"></i></button>
          <h4 class="modal-title"><i class="icon-cog-outline"></i> <?php echo $lang->bug->deadline;?></h4>
        </div>
        <div class="modal-body">
          <form class="form-condensed not-watch no-stash" method='post' target='hiddenwin'>
            <table class='table table-form'>
              <tr>
                <td class='w-160px'><?php echo $lang->bug->deadline;?></td>
                <td><span><?php echo html::input('deadline', helper::today(), "class='form-control form-date'");?></span></td>
              </tr>
              <tr>
                <td colspan='2' class='text-center'><button type='button' id='batchSetDeadline' class='btn btn-primary'><?php echo $lang->save?></button></td>
              </tr>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="adjustModal" tabindex="-1" role="dialog">
    <div class="modal-dialog w-960px">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><i class="icon icon-close"></i></button>
          <h4 class="modal-title"><i class="icon-cog-outline"></i> <?php echo $lang->bug->batchAdjust;?></h4>
        </div>
        <div class="modal-body">
          <form class="form-condensed not-watch no-stash" method='post' target='hiddenwin'>
            <table class='table table-form'>
              <tbody>
                <tr>
                  <th style='width: 150px'><?php echo $lang->bug->product;?></th>
                  <td class="required">
                    <div class='input-group'>
                      <?php echo html::select('product', $products, '', "onchange='loadProductBranches(this.value);' class='form-control chosen control-product'");?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th><?php echo $lang->bug->project . '/' . $lang->execution->common;?></th>
                  <td class="required">
                    <div class='table-row'>
                      <div class='table-col' id='projectBox'>
                        <?php echo html::select('project', [], '', "class='form-control chosen' onchange=''");?>
                      </div>
                      <div class="table-col executionBox hidden">
                        <div class='input-group' id='executionIdBox'>
                          <span class='input-group-addon fix-border' id='executionBox'></span>
                          <?php echo html::select('execution', [], '', "class='form-control chosen' onchange=''");?>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr>
                  <th><?php echo $lang->bug->openedBuild;?></th>
                  <td class="required">
                    <div class='input-group' id='buildBox'>
                      <?php echo html::select('openedBuild[]', [], '', "multiple=multiple class='picker-select form-control' data-items='" . count($builds) . "'");?>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan='2' class='text-center'><button type='button' id='batchAdjust' class='btn btn-primary'><?php echo $lang->save?></button></td>
                </tr>
              </tfoot>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php js::set('replaceID', 'bugList');?>
<?php js::set('browseType', $type);?>
<?php js::set('param', $param);?>
  <script>
    <?php if(!empty($useDatatable)):?>
    $(function(){$('#projectBugForm').table();})
    <?php endif;?>
  </script>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
