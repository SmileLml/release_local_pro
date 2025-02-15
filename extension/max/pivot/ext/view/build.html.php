<style>
#mainContent > .side-col.col-lg{width: 235px}
.hide-sidebar #sidebar{width: 0 !important}
.icon-code-fork:before {transform: rotate(180deg) rotateY(179deg);}
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
<?php if(empty($bugs)):?>
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
      <table class='table table-condensed table-striped table-bordered table-fixed' id="buildList">
        <thead>
          <?php ksort($lang->bug->severityList);?>
          <?php unset($lang->bug->typeList[''])?>
          <?php unset($lang->bug->typeList['designchange'])?>
          <?php unset($lang->bug->typeList['newfeature'])?>
          <?php unset($lang->bug->typeList['trackthings'])?>
          <tr class='colhead text-center'>
            <th width="100" rowspan="2"><?php echo $lang->pivot->project;?></th>
            <th width="150" rowspan="2"><?php echo $lang->pivot->buildTitle;?></th>
            <th width="100" colspan="<?php echo  count($lang->bug->severityList)?>"><?php echo $lang->pivot->severity;?></th>
            <th colspan="<?php echo count($lang->bug->typeList);?>"><?php echo $lang->pivot->bugType;?></th>
            <th width="150" colspan="3"><?php echo $lang->pivot->bugStatus;?></th>
          </tr>
          <tr class="text-center colhead">
            <?php foreach($lang->bug->severityList as $key => $severity):?>
            <td class='text-ellipsis' title='<?php echo $severity;?>'><?php echo $severity;?></td>
            <?php endforeach;?>
            <?php foreach($lang->bug->typeList as $bugTypeKey => $bugType): ?>
            <td class='text-ellipsis' title='<?php echo $bugType;?>'><?php echo $bugType?></td>
            <?php endforeach; ?>
            <td><?php echo $lang->bug->statusList['active'];?></td>
            <td><?php echo $lang->bug->statusList['resolved'];?></td>
            <td><?php echo $lang->bug->statusList['closed'];?></td>
          </tr>
        </thead>
        <?php if($bugs):?>
        <tbody>
          <?php foreach($bugs as $key => $projectBuilds):?>
          <tr class="text-center">
            <?php $count = count($projectBuilds);?>
            <td class="text-left" rowspan="<?php echo $count;?>" title="<?php echo $projects[$key];?>"><?php echo $projects[$key];?></td>
            <?php foreach($projectBuilds as $buildId => $build):?>
            <td class='text-left' title="<?php echo isset($builds[$buildId]) ? $builds[$buildId] : '';?>">
              <?php if(isset($builds[$buildId])):?>
              <span class='build'>
              <?php echo "<span class='buildName'>" . $builds[$buildId] . '</span>';?>
              <?php if(!$build['execution']):?>
              <span class='icon icon-code-fork text-muted' title=<?php echo $lang->build->integrated;?>></span>
              <?php endif;?>
              </span>
              <?php endif;?>
            </td>
            <?php foreach($lang->bug->severityList as $severity => $val):?>
            <td><?php echo isset($build['severity'][$severity]) ? $build['severity'][$severity] : 0;?></td>
            <?php endforeach;?>
            <?php foreach($lang->bug->typeList as $bugTypeKey => $bugTypeVal):?>
            <td><?php echo isset($build['type'][$bugTypeKey]) ? $build['type'][$bugTypeKey] : 0;?></td>
            <?php endforeach;?>
            <td><?php echo isset($build['status']['active'])   ? $build['status']['active']   : 0;?></td>
            <td><?php echo isset($build['status']['resolved']) ? $build['status']['resolved'] : 0;?></td>
            <td><?php echo isset($build['status']['closed'])   ? $build['status']['closed']   : 0;?></td>
          </tr>
          <?php
          if($count != 1) echo '<tr class="text-center">';
          $count --;
          ?>
          <?php endforeach;?>
          <?php endforeach;?>
          <tr class="text-center">
            <td colspan='2' class='text-right'><?php echo $lang->pivot->total;?></td>
            <?php foreach($lang->bug->severityList as $key => $severity):?>
            <?php $total = isset($summary['severity'][$key]) ? count($summary['severity'][$key]) : 0;?>
            <td title='<?php echo $total?>'><?php echo $total;?></td>
            <?php endforeach;?>
            <?php foreach($lang->bug->typeList as $bugTypeKey => $bugType): ?>
            <?php $total = isset($summary['type'][$bugTypeKey]) ? count($summary['type'][$bugTypeKey]) : 0;?>
            <td title='<?php echo $total?>'><?php echo $total;?></td>
            <?php endforeach; ?>
            <?php $total = isset($summary['status']['active']) ? count($summary['status']['active']) : 0;?>
            <td title='<?php echo $total?>'><?php echo $total;?></td>
            <?php $total = isset($summary['status']['resolved']) ? count($summary['status']['resolved']) : 0;?>
            <td title='<?php echo $total?>'><?php echo $total;?></td>
            <?php $total = isset($summary['status']['closed']) ? count($summary['status']['closed']) : 0;?>
            <td title='<?php echo $total?>'><?php echo $total;?></td>
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
    var link = createLink('pivot', 'preview', 'dimension=' + dimension + '&group=' + groupID + '&module=pivot&method=build&params=' + params);
    location.href = link;
}

$(function()
{
    $('td .build .icon-code-fork').each(function()
    {
        $build = $(this).closest('.build');
        $td = $(this).closest('td');
        if($td.width() < $build.width())
        {
            $build.find('.buildName').css('display', 'inline-block').css('width', $td.width() - $(this).width()).css('overflow', 'hidden').css('float', 'left');
        }
    })
})
</script>
