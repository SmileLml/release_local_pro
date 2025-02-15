<style>
.modal-dialog.modal-sm{width:500px !important;}
</style>
<script>
$('a[disabled=disabled]').addClass('disabled');
lang.confirmDelete = '<?php echo $lang->workflowfield->confirmDelete;?>';

var html = $('#fieldList td.actions a.edit:first').html();
$('#fieldList td.actions a.edit').attr('title', html).addClass('btn').html("<i class='icon icon-edit'></i>");

var html = $('#fieldList td.actions a.deleteField:first').html();
$('#fieldList td.actions a.deleteField').attr('title', html).addClass('btn').html("<i class='icon icon-trash'></i>");

var html = $('#fieldList td.actions a.disabled:first').html();
$('#fieldList td.actions a.disabled').attr('title', html).addClass('btn').html("<i class='icon icon-trash'></i>");

$('#fieldList td.buildin1').next('td.actions').find('a.disabled').prev('a.edit').addClass('disabled');

$(function()
{
    $(document).off('click', '.deleteField');
    $('.deleteField').click(function()
    {
        if(confirm(lang.confirmDelete))
        {
            var deleter = $(this);
            $.getJSON(deleter.attr('href'), function(response)
            {
                if(response.result == 'success') return location.reload();
                return bootbox.alert({message:response.message, size: "small"});
            });
        }
        return false;
    });
})
</script>
