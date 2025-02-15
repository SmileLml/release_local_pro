<style>
#main > .container > #menuActions {margin-top: -10px !important; padding-bottom: 5px;}
.main-table tbody tr td {white-space: nowrap; overflow: hidden;}
.main-table tbody>tr>td>a {line-height: 14px;}
.table.has-sort-head thead > tr > th {padding: 2px 8px;}
.actions {text-align: right;}
</style>
<script>
$('.deleter').attr('data-toggle', 'ajax');
$('.actions .disabled').remove();
$('.main-table thead tr th:last').removeClass('w-130px').width('120');
$('.main-table tbody tr').each(function(i)
{
    $datasourceTD = $(this).find('td').eq(3);
    $datasourceTD.attr('title', $datasourceTD.html());

    $(this).find('td:last').addClass('c-actions');
    $actionsTD = $(this).find('td:last');

    /** Add actions title. */
    $actionsTD.find("a[href*='tree']").attr('title', $actionsTD.find("a[href*='tree']").text());
    $actionsTD.find('a.edit').attr('title', $actionsTD.find('a.edit').text());
    $actionsTD.find('a.deleter').attr('title', $actionsTD.find('a.deleter').text());

    /** Add actions icon. */
    $actionsTD.find("a[href*='tree']").addClass('btn').html("<i class='icon-common-split icon-split'></i>");
    $actionsTD.find('a.edit').addClass('btn').html("<i class='icon-common-edit icon-edit'></i>");
    $actionsTD.find('a.deleter').addClass('btn').html("<i class='icon icon-trash'></i>");
})
</script>
