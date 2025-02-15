<style>
#mainContent > .side-col.col-lg{width: 235px}
.hide-sidebar #sidebar{width: 0 !important}
</style>
<div class='cell'>
  <div class='with-padding'>
    <div class="table-row" id='conditions'>
      <div class='col-sm-4'>
        <div class='input-group'>
          <span class='input-group-addon'><?php echo $lang->pivot->product;?></span>
          <?php echo html::select('product', $products, $productID, 'onchange="selectProduct(this.value);" class="form-control chosen"')?>
        </div>
      </div>
      <div class='col-sm-4'>
        <div class='input-group'>
          <span class='input-group-addon'><?php echo $lang->pivot->moduleName;?></span>
          <?php echo html::select('module' , $modules,  $moduleID,  'onchange="selectModule(this.value);"  class="form-control chosen"')?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php if(empty($stories)):?>
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
      <table class='table table-condensed table-striped table-bordered table-fixed' id="stroyBugsList">
        <thead>
          <tr class='colhead text-center'>
            <th class="w-250px"><?php echo $lang->pivot->bug->story;?></th>
            <th><?php echo $lang->pivot->bug->title;?></th>
            <th class="w-120px"><?php echo $lang->pivot->bug->status;?></th>
            <th class="w-120px"><?php echo $lang->pivot->bug->total;?></th>
          </tr>
        </thead>
        <?php if($stories):?>
        <tbody>
          <?php foreach($stories as $story):?>
          <?php if(!empty($story['bugList'])):?>
          <?php foreach($story['bugList'] as $key => $bug):?>
          <tr class="text-center">
            <?php if($story['total'] < 2 || ($story['total'] > 1 && !$key)):?>
            <td <?php if(!$key && $story['total'] > 1) echo 'rowspan="' . $story['total'] . '"';?>><?php echo $story['title'];?></td>
            <?php endif;?>

            <td><?php echo $bug->title;?></td>
            <td><?php echo $lang->bug->statusList[$bug->status];?></td>

            <?php if($story['total'] < 2 || ($story['total'] > 1 && !$key)):?>
            <td <?php if(!$key && $story['total'] > 1) echo 'rowspan="' . $story['total'] . '"';?>><?php echo $story['total'];?></td>
            <?php endif;?>
          </tr>
          <?php endforeach;?>
          <?php endif;?>
          <?php endforeach;?>
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
    var link = createLink('pivot', 'preview', 'dimension=' + dimension + '&group=' + groupID + '&module=pivot&method=storylinkedbug&params=' + params);
    location.href = link;
}

function selectModule(moduleID)
{
    var params = window.btoa('productID=<?php echo $productID;?>&moduleID=' + moduleID);
    var link = createLink('pivot', 'preview', 'dimension=' + dimension + '&group=' + groupID + '&module=pivot&method=storylinkedbug&params=' + params);
    location.href = link;
}
</script>
