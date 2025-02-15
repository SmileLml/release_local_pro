<?php if($module == 'feedback' && $field == 'review'):?>
<table class='table table-form mw-900px' id='feedbackReview'>
  <tr>
    <th class='thWidth'><?php echo $lang->custom->feedback->fields['review'];?></th>
    <td class='w-350px'><?php echo html::radio('needReview', $lang->custom->reviewList, $needReview);?></td>
    <td></td>
  </tr>
  <tr <?php if($needReview) echo "class='hidden'"?>>
    <th><?php echo $lang->custom->forceReview;?></th>
    <td><?php echo html::select('forceReview[]', $users, $forceReview, "class='form-control chosen' multiple");?></td>
    <td><?php printf($lang->custom->notice->forceReview, $lang->feedback->common);?></td>
  </tr>
  <tr <?php if(!$needReview) echo "class='hidden'"?>>
    <th><?php echo $lang->custom->forceNotReview;?></th>
    <td><?php echo html::select('forceNotReview[]', $users, $forceNotReview, "class='form-control chosen' multiple");?></td>
    <td><?php printf($lang->custom->notice->forceNotReview, $lang->feedback->common);?></td>
  </tr>
  <tr>
    <th><?php echo $lang->feedback->reviewedByAB;?></th>
    <?php $users[''] = $lang->feedback->deptManager;?>
    <td><?php echo html::select('reviewer', $users, $reviewer, "class='form-control chosen'");?></td>
  </tr>
  <tr>
    <td colspan='2' class='text-center'><?php echo html::submitButton();?></td>
  </tr>
</table>
<script>
$(function()
{
    $('.main-form > .main-header ').after($('#feedbackReview'));

    $("input[name='needReview']").change(function()
    {
        if($(this).val() == 0)
        {
            $('#forceReview').closest('tr').removeClass('hidden');
            $('#forceNotReview').closest('tr').addClass('hidden');
        }
        else
        {
            $('#forceReview').closest('tr').addClass('hidden');
            $('#forceNotReview').closest('tr').removeClass('hidden');
        }
    })
})
</script>
<?php endif;?>

<?php if($module == 'feedback' && $field == 'closedReasonList'):?>
<script>
$('[name*=systems]').each(function()
{
    if($(this).val() == 1) $(this).closest('tr').find('.icon-close').parent().remove();
})
</script>
<?php endif;?>
