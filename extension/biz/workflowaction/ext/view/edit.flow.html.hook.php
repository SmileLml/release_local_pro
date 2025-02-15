<?php
$openBrowseModules = $config->workflowaction->openBrowseModules;
$openBrowseActions = $config->workflowaction->openBrowseActions;
?>
<?php if($flow->buildin and $action->method == 'browse' and (strpos($openBrowseModules, ",{$action->module},") === false and (!isset($openBrowseActions[$action->module]) or $openBrowseActions[$action->module] != $action->action))):?>
<script>
$(function()
{
    $('#extensionType option[value="extend"]').remove();
})
</script>
<?php endif;?>
<?php if($flow->buildin):?>
<script>
$(function()
{
    $('#type option[value="batch"]').remove();
    $('#position option[value="browse"]').remove();
    $('#position option[value="browseandview"]').remove();
    $('#show option[value="dropdownlist"]').remove();
    $('#show').val('direct');
})
</script>
<?php endif;?>
