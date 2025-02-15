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