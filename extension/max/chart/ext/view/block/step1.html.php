<div id='mainContent' class='main-row'>
  <div class='main-col'>
    <style><?php include $this->app->getModuleRoot() . 'dataview/css/querybase.css';?></style>
    <?php include $this->app->getModuleRoot() . 'dataview/view/querybase.html.php';?>
    <script><?php include $this->app->getModuleRoot() . 'dataview/js/querybase.js';?></script>
  </div>
</div>
<script>
$(function()
{
    $('#dataform .btn.query').after(<?php echo json_encode('<button type="button" class="btn btn-primary btn-next-step pull-right" onclick="nextStep()">' . $lang->chart->nextStep . '</button>');?>);
});
</script>
