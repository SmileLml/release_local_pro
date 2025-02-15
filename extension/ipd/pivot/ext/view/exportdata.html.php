<?php js::import($this->app->getWebRoot() . 'js/sheetjs/xlsx.full.min.js');?>
<?php js::import($this->app->getWebRoot() . 'js/filesaver/filesaver.js');?>
<?php $this->app->loadLang('file');?>
<?php js::set('untitled', $lang->file->untitled);?>
<div class="modal fade" id='export'>
  <div class="modal-dialog" style='width: 500px'>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">× </span></button>
        <h2 class="modal-title" style='font-weight: bold;'><?php echo $lang->export;?></h2>
      </div>
      <div class="modal-body">
        <div style="margin: 20px 50px 20px 30px;">
        <table class="table table-form" style="padding:30px">
          <tbody>
          <tr>
            <th class='w-120px'><?php echo $lang->setFileName;?></th>
            <td><?php echo html::input('fileName', '', "class='form-control' autofocus placeholder='{$lang->file->untitled}'");?></td>
          </tr>
          <tr>
            <th><?php echo $lang->pivot->exportType;?></th>
            <td><?php echo html::select('fileType',  $config->pivot->fileType, '', 'class="form-control" style="width: 140px"');?></td>
          </tr>
          <?php if(isset($exportMode) and $exportMode == 'preview'):?>
          <tr>
            <th><?php echo $lang->pivot->exportRange;?></th>
            <td><?php echo html::select('range',  $lang->pivot->rangeList, '', 'class="form-control" style="width: 140px"');?></td>
          </tr>
          <?php endif;?>
          <tr>
            <th></th>
            <td style='padding-left: 30px;'><button class='btn btn-primary' onclick='exportData()'><?php echo $lang->save;?></button></td>
          </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function()
{
    /* Page is not initialized before clicking export will have bug. */
    $('.btn-export').removeClass('hidden');
})
</script>
