$(document).ready(function()
{
    $('#app').change();

    $.setAjaxForm('#createForm', function(response)
    {
        if(response.result == 'success')
        {
            $('#triggerModal .close').click();

            bootbox.dialog(
            {
                title: '&nbsp;',
                message: window.createTips,
                buttons:
                {
                    no:
                    {
                        label: window.notNow,
                        className: 'btn-secondary',
                        callback: function(){location.reload();}
                    },
                    yes:
                    {
                        label: window.toDesign,
                        className: 'btn-primary',
                        callback: function(){location.href = createLink('workflow', 'flowchart', 'module=' + response.module);}
                    }
                },
                onEscape: function(result)
                {
                    window.location.reload();
                }
            });
        }
    });

    $('input[name=approval]').change(function()
    {
        var approval = $(this).val();
        $('.approval').toggle(approval == 'enabled');
    })
    $('input[name=approval][checked=checked]').change();

});
