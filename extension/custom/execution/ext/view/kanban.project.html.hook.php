<?php
  js::set('canBeChangedByProject', $canBeChangedByProject);
?>
<script>
  $(function (){
    if(!canBeChangedByProject)
    {
      $('.actions a').hide();
      // hideAction();
      // hideKanbanAction();
      
    }
  });
</script>
