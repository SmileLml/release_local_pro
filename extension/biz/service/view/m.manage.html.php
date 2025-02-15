<?php include $app->getModuleRoot() . 'common/view/m.header.html.php';?>
<style>
.treemap-data{padding:0;margin:0px;}
li{list-style: none;}
.treemap-data>li{padding:5px;border-bottom:1px solid #ccc;}
.treemap-data>li .icon{float:right;}
#page .table tbody tr td{padding:0px;}
.treemap-data>li>ul{display:none;}
.show{display: block !important;}
</style>
<section id='page' class='section list-with-actions list-with-pager'>
  <table class="table">
    <thead>
    <tr>
      <th><?php echo $lang->service->all;?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td><?php echo $tree?></td>
    </tr>
    </tbody>
  </table>
</section>
<script>
$(function()
{
    $('.treemap-data>li').each(function()
    {
        if($(this).find('ul').length > 0) $(this).prepend("<i class='icon icon-chevron-down'></i>");
    });

    $('.treemap-data>li').click(function()
    {
        $(this).find('.icon').remove();
        $('table tbody tr').css('background', '#fff');
        if($(this).find('ul:first').hasClass('show'))
        {
            $(this).find('ul:first').removeClass('show');
            $(this).prepend("<i class='icon icon-chevron-down'></i>");
        }
        else if($(this).find('ul').length > 0)
        {
            $(this).find('ul:first').addClass('show');
            $(this).prepend("<i class='icon icon-chevron-up'></i>");
        }
    });
})
</script>
<?php include $app->getModuleRoot() . 'common/view/m.footer.html.php';?>
