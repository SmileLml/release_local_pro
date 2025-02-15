<?php $this->app->loadLang('file');?>
<form method='post' onsubmit='setDownloading()' action='<?php echo $this->createLink('report', 'export', "module=$module&productID=$productID&taskID=$taskID")?>' target='hiddenwin' style="padding: 10px 0px 10px" id='exportForm'>
  <table class="w-p100 table-form">
    <tr>
      <th class="w-110px"><?php echo $lang->setFileName?></th>
      <td class="w-150px"><input type="text" name="fileName" id="fileName" class="form-control"></td>
      <td><?php echo html::submitButton($lang->save, '', 'btn btn-primary upload-btn') . html::hidden('items') . html::hidden('datas');?></td>
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
        var canvasSize = $('.chart-wrapper canvas').size();
        if(canvasSize == 0)
        {
            alert('<?php echo $lang->report->errorNoChart?>');
            return false;
        }
        $('.chart-wrapper canvas').each(function(i)
        {
            var canvas  = this;
            var $canvas = $(canvas);

            if(canvas.width === $canvas.width() && canvas.width < 800)
            {
                canvas.width = canvas.width * 2;
                canvas.height = canvas.height * 2;
                var chart = $canvas.closest('.chart-row').find('.table-chart').data('zui.chart');
                if(chart)
                {
                    chart.chart.ctx.scale(2, 2);
                    chart.render();
                }
            }

            if(typeof(canvas.toDataURL) == 'undefined')
            {
                alert('<?php echo $lang->report->errorExportChart?>');
                return false;
            }
            var dataURL     = canvas.toDataURL("image/png");
            var dataID      = $canvas.attr('id');
            var thisDataBox = dataBox.replace('%name%', dataID);
            thisDataBox = thisDataBox.replace('%id%', dataID);
            $('#exportForm #submit').after(thisDataBox);
            $('#exportForm #' + dataID).val(dataURL);

            if(i == canvasSize - 1) $('#datas').remove();
        });
    });
})

function setDownloading()
{
    if(navigator.userAgent.toLowerCase().indexOf("opera") > -1) return true;   // Opera don't support, omit it.

    var $fileName = $('#fileName');
    if($fileName.val() === '') $fileName.val('<?php echo $lang->file->untitled;?>');

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
        location.reload('parent.parent');
    }
}
</script>
