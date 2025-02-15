<script>
$('#app').prev().remove();
$('#app').remove();
$('#submit').after("<input type='hidden' id='app' name='app' value='system'>");
var module = $('#module').val();
$('#module').load(createLink('workflowdatasource', 'ajaxGetAppModules', 'app=' + $('#app').val()), function()
{
    $('#module').val(module);
});
</script>
