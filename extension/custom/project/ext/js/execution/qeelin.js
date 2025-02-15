$(function()
{   
    if($.cookie('showTask') == 1 && canBeChange) $('input#editExecution1').prop('disabled', true).attr('title', disabledExecutionTip);
    if(!canBeChange) $.cookie('editExecution', 0, {expires:config.cookieLife, path:config.webRoot});
    if($.cookie('editExecution') == 1 && canBeChange)
    {
        $('input#editExecution1').prop('checked', 'true');
        showEditCheckbox(true);
        $('input[name^="showTask"]').prop('disabled', true).attr('title', disabledTaskTip);
    }
});