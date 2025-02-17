$('#batchComment').click(function(){
    var dtable = $('#storyList').zui('dtable');
    var checkedList = dtable.$.getChecks();
    if(!checkedList)
    {
        alert(checkedNull);
        return;
    }
    $('#commentModel input[name=storyIDS]').val(checkedList)
    $('#batchCommentSubmit').click();
});