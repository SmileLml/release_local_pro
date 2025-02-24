/* Set the assignedTos field. */
function setOwners(result, bugID)
{
    if(result == 'affair')
    {
        $('#assignedTo'+bugID+', #assignedTo'+bugID+'_chosen').removeClass('hidden');
        $('#assignedTo'+bugID).next('.picker').removeClass('hidden');
        $('#assignedTo'+bugID).attr('multiple', 'multiple');
        $('#assignedTo'+bugID).chosen('destroy');
        $('#assignedTo'+bugID).chosen();
    }
    else if($('#assignedTo'+bugID).attr('multiple') == 'multiple')
    {
        $('#assignedTo'+bugID).removeAttr('multiple');
        $('#assignedTo'+bugID).chosen('destroy');
        $('#assignedTo'+bugID).chosen();
    }
}