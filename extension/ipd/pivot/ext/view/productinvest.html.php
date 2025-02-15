<style>
#mainContent > .side-col.col-lg{width: 235px}
.hide-sidebar #sidebar{width: 0 !important}
.c-count {width: 120px;}
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
<?php if(empty($investData)):?>
<div class="cell">
  <div class="table-empty-tip">
    <p><span class="text-muted"><?php echo $lang->error->noData;?></span></p>
  </div>
</div>
<?php else:?>
<div class='cell'>
  <div class='panel'>
    <div data-ride='table'>
      <table class='table table-condensed table-striped table-bordered table-fixed no-margin' id='productList'>
        <thead>
          <tr class='text-center'>
            <th class='c-name text-left'><?php echo $lang->product->name;?></th>
            <th class='c-count'><?php echo $lang->pivot->projects;?></th>
            <th class="c-count"><?php echo $lang->pivot->storyConsumed;?></th>
            <th class="c-count"><?php echo $lang->pivot->taskConsumed;?></th>
            <th class="c-count"><?php echo $lang->pivot->bugConsumed;?></th>
            <th class="c-count"><?php echo $lang->pivot->caseConsumed;?></th>
            <th class="c-count"><?php echo $lang->pivot->totalConsumed;?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($investData as $invest):?>
          <tr class="text-center">
            <td><div class='text-ellipsis text-left' title='<?php echo $invest->name;?>'><?php echo $invest->name?></div></td>
            <td><?php echo $invest->projectCount;?></td>
            <td><?php echo $invest->storyConsumed;?></td>
            <td><?php echo $invest->taskConsumed;?></td>
            <td><?php echo $invest->bugConsumed;?></td>
            <td><?php echo $invest->caseConsumed;?></td>
            <td><?php echo $invest->totalConsumed;?></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif;?>
<script><?php helper::import('../js/productinvest/report.js');?></script>
