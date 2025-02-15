<script>
$(function()
{
    $('tr[data-module=project][data-field=type]').remove();
    $('tr[data-module=project][data-field=project]').remove();
    <?php if(isset($action) && $action->buildin && $action->method == 'view'):?>
    $('.form-actions a[href*=block]').remove();
    <?php endif;?>
})
</script>
