$(document).ready(function()
{
    $(document).on('keyup', '#code', function()
    {
        var code = $('#code').val();
        code = defaultAction.replace(sqlviewPrefix, sqlviewPrefix + code);

        $('#action').val(code);
    })
});
