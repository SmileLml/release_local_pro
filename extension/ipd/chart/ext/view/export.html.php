<?php js::set('emptyLang', $emptyLang);?>
<style>
.modal-title-name {font-weight: 700;}
</style>
<form method='post' onsubmit='setDownloading()' action='<?php echo $this->createLink('chart', 'export')?>' target='hiddenwin' style="margin:15px 20px;" id='exportForm'>
  <table class='table table-form' style='padding:30px'>
    <tr>
      <th class='w-150px'><?php echo $lang->setFileName;?></th>
      <td class='required'><input type="text" name="fileName" id="fileName" class="form-control"></td>
      <td>
        <?php
        echo html::select('fileType',   array('docx' => 'docx'), '', 'class="form-control"');
        ?>
      </td>
      <td><?php echo html::submitButton($lang->save, '', 'btn btn-primary upload-btn') . html::hidden('items', $chartID) . html::hidden('datas');?></td>
    </tr>
  </table>
</form>
<script>
$(function()
{
    $('#exportForm #submit').click(function()
    {
        if($('#datas').size() == 0) return true;

        $(":checkbox:checked[name^='charts']").each(function()
        {
            items = $('#exportForm #items').val();
            items += $(this).val() + ',';
            $('#exportForm #items').val(items);
        });

        var dataBox    = "<input type='hidden' name='%name%' id='%id%' />";
        var canvasSize = $('.echart-content').size();
        if(canvasSize == 0)
        {
            alert('<?php echo $lang->report->errorNoChart?>');
            return false;
        }
        $('.echart-content').each(function(i)
        {
            var $canvas = $(this).find('canvas');
            var canvas  = $canvas[0];

            if($canvas.length > 0 && typeof(canvas.toDataURL) == 'undefined')
            {
                alert('<?php echo $lang->report->errorExportChart?>');
                return false;
            }
            var dataURL     = $canvas.length > 0 ? canvas.toDataURL("image/png") : '';
            var dataID      = $(this).data('id');
            var groupID     = $(this).data('group');
            var chartID     = groupID + '_' + dataID;
            var thisDataBox = dataBox.replace('%name%', chartID);
            thisDataBox = thisDataBox.replace('%id%', chartID);
            $('#exportForm #submit').after(thisDataBox);
            if($canvas.length > 0) $('#exportForm #' + chartID).val(dataURL);

            if(i == canvasSize - 1) $('#datas').remove();
        });
    });
})

function setDownloading()
{
    if($('#fileName').val() == '')
    {
        bootbox.alert(emptyLang);
        return false;
    }
    if(navigator.userAgent.toLowerCase().indexOf("opera") > -1) return true;   // Opera don't support, omit it.
    $.cookie('downloading', 0);
    $('.upload-btn').attr('disabled', 'disabled').addClass('disabled loading');
    time = setInterval("closeWindow()", 300);
    return true;
}

function closeWindow()
{
    if($.cookie('downloading') == 1)
    {
        $.cookie('downloading', null);
        clearInterval(time);
        $('#triggerModal').modal('hide');
    }
}
</script>
