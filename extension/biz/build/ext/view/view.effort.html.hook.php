<?php if(!isonlybody()):?>
<?php if($canBeChangedProject){ $effortHtml = $this->loadModel('effort')->createAppendLink('build', $build->id); } ?>
<script>
if(canBeChangedProject) $('#mainMenu .pull-right').prepend(<?php echo json_encode($effortHtml);?>);
$(function()
{
    if(canBeChangedProject) $(".effort").addClass('btn-link').modalTrigger({width:1024, height:600, iframe:true, transition:'elastic'});
});
</script>
<?php endif;?>
