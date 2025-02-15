<?php include $app->getModuleRoot() . 'common/view/header.html.php';?>
<?php if(isset($config->maxVersion)):?>
<div id="mainMenu" class="clearfix menu-secondary">
  <div class="btn-toolbar pull-left">
  <?php if(file_exists('../../measurement/view/menu.html.php')) include '../../measurement/view/menu.html.php';?>
  </div>
  <div class="btn-toolbar pull-right">
  <?php common::printLink('sqlbuilder', 'createSQLView', "", "<i class='icon icon-plus'></i>" . $this->lang->sqlbuilder->createSQLView, '', "class='btn btn-primary'");?>
  </div>
</div>
<?php endif;?>
<div id='mainContent' class='main-row'>
  <div class='main-col'>
    <div class='cell'>
      <div class='panel'>
        <div class='main-table' data-ride='table'>
          <table class='table table-condensed table-striped table-bordered table-fixed no-margin'>
            <thead>
              <tr>
                <th class='w-50px'><?php echo $lang->sqlview->id?></th>
                <th width='160'><?php echo $lang->sqlview->name?></th>
                <th width='160'><?php echo $lang->sqlview->code?></th>
                <th><?php echo $lang->sqlview->desc?></th>
                <th class='w-90px'><?php echo $lang->sqlview->createdBy?></th>
                <th class='w-150px'><?php echo $lang->sqlview->createdDate?></th>
                <th class='w-80px'><?php echo $lang->actions?></th>
              </tr>
            </thead>
            <tbody class='text-center'>
              <?php foreach($sqlViews as $sqlView):?>
              <tr>
                <td><?php echo $sqlView->id;?></td>
                <td class='text-left' title='<?php echo $sqlView->name?>'><?php echo $sqlView->name;?></td>
                <td class='text-left' title='<?php echo $sqlView->code?>'><?php echo $sqlView->code;?></td>
                <td class='text-left' title='<?php echo $sqlView->desc?>'><?php echo $sqlView->desc;?></td>
                <td class='text-center'><?php echo zget($users, $sqlView->createdBy, $sqlView->createdBy);?></td>
                <td class='text-center'><?php echo $sqlView->createdDate;?></td>
                <td>
                  <?php
                  if(common::hasPriv('sqlbuilder', 'editSQLView')) echo html::a(inlink('editSQLView', "viewID=$sqlView->id"), $lang->edit);
                  if(common::hasPriv('sqlbuilder', 'deleteSQLView')) echo html::a(inlink('deleteSQLView', "viewID=$sqlView->id"), $lang->delete, 'hiddenwin');
                  ?>
                </td>
              </tr>
              <?php endforeach;?>
            </tbody>
          </table>
          <div class='table-footer'>
            <?php $pager->show('right', 'pagerjs');?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include $app->getModuleRoot() . 'common/view/footer.html.php';?>
