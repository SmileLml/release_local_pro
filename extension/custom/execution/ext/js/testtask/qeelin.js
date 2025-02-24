$('#toCopyButton').on('click', function()
{
    var copyNumber = $('#copyNumber').val();
    var testtaskID  = $('#toCopy, #copyTaskID').val();

    if(testtaskID && copyNumber)
    {
        $('#cancelButton').click();
        var link = createLink('testtask', 'copy','productID=&projectID=&executionID=' + executionID + '&testtaskID=' + testtaskID + '&copyNumber=' + copyNumber);
        window.parent.$.apps.open(link, 'execution');
    }
    else
    {
        $('#cancelButton').click();
    }
});

function copyTesttask(taskID)
{
    $('#toCopy, #copyTaskID').val(taskID);
    $('#model' + taskID).click();
}