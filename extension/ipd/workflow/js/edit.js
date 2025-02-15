$(document).ready(function()
{
    $('#app').change();
    $.setAjaxForm('#editForm', function(response)
    {
        if(response.result == 'success')
        {
            $('body', window.parent.document).find('.app-btn[data-id=' + response.entryID + ']').attr('data-reload', true);
            setTimeout(function(){$('#triggerModal .close').click();window.location.reload();}, 1200);
        }
    });
});
