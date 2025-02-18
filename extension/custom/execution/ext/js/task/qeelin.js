$('#batchComment').click(function(){
    var dtable = $('#taskList').zui('dtable');
    var checkedList = dtable.$.getChecks();
    if(!checkedList)
    {
        alert(checkedNull);
        return;
    }
    $('#commentModel input[name=taskIDS]').val(checkedList)
    $('#batchCommentSubmit').click();
});

$('input[name^="showParentTask"]').click(function(){
    var showParentTask = $(this).is(':checked') ? 1 : 0;
    $.cookie('showParentTask', showParentTask, {expires:config.cookieLife, path:config.webRoot});
    window.location.reload();
});