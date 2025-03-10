/**
 * Set duplicate field.
 *
 * @param  string $resolution
 * @param  int    $storyID
 * @access public
 * @return void
 */
function setDuplicateAndChild(resolution, storyID)
{
    if(resolution == 'duplicate')
    {
        $('#childStoryBox' + storyID).hide();
        $('#duplicateStoryBox' + storyID).show();
    }
    else if(resolution == 'subdivided')
    {
        $('#duplicateStoryBox' + storyID).hide();
        $('#childStoryBox' + storyID).show();
    }
    else
    {
        $('#duplicateStoryBox' + storyID).hide();
        $('#childStoryBox' + storyID).hide();
    }
}

function loadBranches(product, branch, storyID)
{
    if(typeof(branch) == 'undefined') branch = 0;
    if(!branch) branch = 0;

    var currentModuleID = $('#modules' + storyID).val();
    moduleLink = createLink('tree', 'ajaxGetOptionMenu', 'productID=' + product + '&viewtype=story&branch=' + branch + '&rootModuleID=0&returnType=html&fieldID=' + storyID + '&needManage=false&extra=nodeleted&currentModuleID=' + currentModuleID);
    $('#modules' + storyID).parent('td').load(moduleLink, function(){$('#modules' + storyID).chosen();});

    planID = $('#plans' + storyID).val();
    planLink = createLink('product', 'ajaxGetPlans', 'productID=' + product + '&branch=' + branch + '&planID=' + planID + '&fieldID=' + storyID + '&needCreate=false&expired=&param=skipParent');
    $('#plans' + storyID).parent('td').load(planLink, function(){$('#plans' + storyID).chosen();});
}

$(function()
{
    //Remove 'ditto' in first row.
    removeDitto();

    // Init bactch action form
    $('#batchEditForm').batchActionForm();

    $('#customField').click(function()
    {
        hiddenRequireFields();
    });

    $('select[id^="duplicateStoryIDList"]').picker(
    {
        disableEmptySearch : true,
        dropWidth : 'auto'
    });

    $('select[id^="assignedTo"],select[id^="closedBy"]').next('.picker').mousedown(function()
    {
            $select  = $(this).closest('td').find('select');
            selected = $select.val();
            if($select.hasClass('loaded')) return;

            $select.addClass('loaded');
            $select.empty();
            options = '';
            for(account in users)
            {
                    realname = users[account];
                    options += '<option value="' + account + '" title="' + realname + '">' + realname + '</option>';
            }
            $select.append(options);
            $select.val(selected);
            $select.trigger("chosen:updated");
    });
});

$(document).on('click', '.chosen-with-drop', function(){oldValue = $(this).prev('select').val();})//Save old value.

/* Set ditto value. */
$(document).on('change', 'select', function()
{
    if($(this).val() == 'ditto')
    {
        var index = $(this).closest('td').index();
        var row   = $(this).closest('tr').index();
        var tbody = $(this).closest('tr').parent();

        if($(this).attr('name').indexOf('closedReasons') != -1)
        {
            index = $(this).closest('tr').closest('td').index();
            row   = $(this).closest('tr').closest('td').parent().index();
            tbody = $(this).closest('tr').closest('td').parent().parent();
        }

        var value = '';
        for(i = row - 1; i >= 0; i--)
        {
            value = tbody.children('tr').eq(i).find('td').eq(index).find('select').val();
            if(value != 'ditto') break;
        }

        isModules = $(this).attr('name').indexOf('modules') != -1;
        isPlans   = $(this).attr('name').indexOf('plans')   != -1;

        if(isModules || isPlans)
        {

            var valueStr = ',' + $(this).find('option').map(function(){return $(this).val();}).get().join(',') + ',';
            if(valueStr.indexOf(',' + value + ',') != -1)
            {
                $(this).val(value);
            }
            else
            {
                alert(dittoNotice);
                $(this).val(oldValue);
            }
        }
        else
        {
            $(this).val(value);
        }

        $(this).trigger("chosen:updated");
        $(this).trigger("change");
    }
});
