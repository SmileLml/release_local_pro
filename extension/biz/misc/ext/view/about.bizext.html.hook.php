<?php
$version = $config->version;
$version = str_replace('pro', $lang->proName . ' ', $config->version);
?>
<?php if($this->config->edition == 'open'):?>
<script>
$(function()
{
    $('#mainContent h4').html(<?php echo json_encode($version);?>);
})
</script>
<?php endif;?>
