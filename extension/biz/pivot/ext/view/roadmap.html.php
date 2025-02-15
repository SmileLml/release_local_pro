<style>
<?php helper::import('../css/roadmap/report.css');?>
#mainContent > .side-col.col-lg{width: 235px}
.hide-sidebar #sidebar{width: 0 !important}
#conditions {display: flex;}
#conditions .condition-options {margin-left: 16px;}
</style>
<div class='cell'>
  <div class='panel'>
    <div class="panel-heading">
      <div class="panel-title">
        <div id='conditions'>
          <div><?php echo $title;?></div>
          <div class='condition-options'>
            <div class="checkbox-primary inline-block">
              <input type="checkbox" name="closedProduct" value="closedProduct" id="closedProduct" <?php if(strpos($conditions, 'closedProduct') !== false) echo "checked='checked'"?> />
              <label for="closedProduct"><?php echo $lang->pivot->closedProduct?></label>
            </div>
          </div>
        </div>
      </div>
      <nav class="panel-actions btn-toolbar"></nav>
    </div>
  </div>
</div>
<?php if(empty($products)):?>
<div class="cell">
  <div class="table-empty-tip">
    <p><span class="text-muted"><?php echo $lang->error->noData;?></span></p>
  </div>
</div>
<?php else:?>
<div class='cell'>
  <div class='panel'>
    <div class='roadmap-wrap' data-ride='table'>
      <table id="roadmap" class='table table-condensed table-striped table-bordered table-fixed active-disabled'>
        <thead>
          <tr class='colhead'>
            <th class="w-200px"><?php echo $lang->pivot->product;?></th>
            <th><?php echo $lang->pivot->plan;?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($products as $productID => $product):?>
          <tr class="text-center">
            <td title="<?php echo $product?>"><?php echo $product?></td>
            <td>
              <?php if(!empty($plans[$productID])):?>
              <?php foreach($plans[$productID] as $plan):?>
              <div class='plan'>
                <div class='text-ellipsis' title='<?php echo $plan->title;?>' ><?php echo $plan->title?></div>
                <div>
                <?php
                if($plan->begin == $config->productplan->future and $plan->end == $config->productplan->future)
                {
                    echo $lang->pivot->future;
                }
                else
                {
                    if($plan->begin == $config->productplan->future) $plan->begin = $lang->pivot->future;
                    if($plan->end == $config->productplan->future) $plan->end = $lang->pivot->future;
                    echo $plan->begin . ' ~ ' . $plan->end;
                }
                ?>
                </div>
              </div>
              <?php endforeach;?>
              <?php endif;?>
            </td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif;?>
<script><?php helper::import('../js/roadmap/report.js');?></script>
