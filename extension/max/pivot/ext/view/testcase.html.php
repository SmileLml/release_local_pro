<style>
#mainContent > .side-col.col-lg{width: 235px}
.hide-sidebar #sidebar{width: 0 !important}
</style>
<div class='cell'>
  <div class='with-padding'>
    <div class="table-row" id='conditions'>
      <div class='input-group w-200px'>
        <?php echo html::select('product', $products, $productID, 'onchange="selectProduct(this.value);" class="form-control chosen"')?>
      </div>
    </div>
  </div>
</div>
<?php if(empty($modules)):?>
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
    <div data-ride='table'>
      <table class='table table-condensed table-striped table-bordered tablesorter table-fixed active-disabled' id="testCaseList">
        <thead>
          <tr class='colhead text-center'>
            <th><?php echo $lang->pivot->moduleName;?></th>
            <th><?php echo $lang->pivot->case->total;?></th>
            <th><?php echo $lang->testcase->resultList['pass'];?></th>
            <th><?php echo $lang->testcase->resultList['fail'];?></th>
            <th><?php echo $lang->testcase->resultList['blocked'];?></th>
            <th><?php echo $lang->pivot->case->run;?></th>
            <th><?php echo $lang->pivot->case->passRate;?></th>
          </tr>
        </thead>
        <?php if($modules):?>
        <tbody>
          <?php $allTotal = $allPass = $allFail = $allBlocked = $allRun = 0;?>
          <?php foreach($modules as $module):?>
          <tr class="text-center">
            <?php
            $allTotal += $module->total;
            $allPass += $module->pass;
            $allFail += $module->fail;
            $allBlocked += $module->blocked;
            $allRun += $module->run;
            ?>
            <td><?php echo $module->name;?></td>
            <td><?php echo $module->total;?></td>
            <td><?php echo $module->pass;?></td>
            <td><?php echo $module->fail;?></td>
            <td><?php echo $module->blocked;?></td>
            <td><?php echo $module->run;?></td>
            <td><?php echo $module->run ? round(($module->pass / $module->run) * 100, 2) . '%' : 'N/A';?></td>
          </tr>
          <?php endforeach;?>
          <tr class="text-center">
            <td><?php echo $lang->pivot->total;?></td>
            <td><?php echo $allTotal;?></td>
            <td><?php echo $allPass;?></td>
            <td><?php echo $allFail;?></td>
            <td><?php echo $allBlocked;?></td>
            <td><?php echo $allRun;?></td>
            <td><?php echo $allRun ? round(($allPass / $allRun) * 100, 2) . '%' : 'N/A';?></td>
          </tr>
        </tbody>
        <?php endif;?>
      </table>
    </div>
  </div>
</div>
<?php endif;?>
<script>
function selectProduct(productID)
{
    var params = window.btoa('productID=' + productID);
    var link = createLink('pivot', 'preview', 'dimension=' + dimension + '&group=' + groupID + '&module=pivot&method=testcase&params=' + params);
    location.href = link;
}
</script>
