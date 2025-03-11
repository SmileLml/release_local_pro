var $prev = 0;
$(function(){
    $('input[name=date]').change(function(){
        var date = $(this).val().replace(/\-/g, '');
        inputDate = $(this).val();
        if(inputDate != currentDate)
        {
            $('.hoursConsumedToday').closest('span').prev().html(inputDate + hoursConsumedTodayOtherTitle);
            $('.hoursSurplusToday').closest('span').prev().html(inputDate + hoursSurplusTodayOtherTitle);
        }
        else
        {
            $('.hoursConsumedToday').closest('span').prev().html(hoursConsumedTodayTitle);
            $('.hoursSurplusToday').closest('span').prev().html(hoursSurplusTodayTitle)
        }

        $.get(createLink('effort', 'getAccountStatistics', 'account&date=' + date), function(data)
        {
            if(typeof(data) == 'number')
            {
                $('.hoursConsumedToday').html(data + 'h');
                $('.hoursSurplusToday').html((limitWorkHour - data) + 'h');
                var inputAll = 0;
                $('select#objectType').each(function()
                {
                    var value = $(this).val();
                    var $consumedInput = $(this).closest('td').next().next().find('input');
                    if(value.indexOf('task_') >= 0 || value.indexOf('bug_') >= 0)
                    {
                        var workConsumed = parseFloat(typeof($consumedInput.val()) == 'undefined' ? 0 : $consumedInput.val());
                        workConsumed = isNaN(workConsumed) ? 0 : workConsumed;
                        workConsumed = Math.round(workConsumed * 1000) / 1000;
                        inputAll += workConsumed;
                    }
                });
                inputAll = Math.round(inputAll * 1000) / 1000;
                if(data + inputAll > limitWorkHour)
                {
                    if(inputDate == currentDate)
                    {
                        alert(hoursConsumedTodayOverflow);
                    }
                    else
                    {
                        alert(inputDate + hoursConsumedTodayOverflowOther);
                    }
                    $('select#objectType').each(function()
                    {
                        var value = $(this).val();
                        var $consumedInput = $(this).closest('td').next().next().find('input');
                        if(value.indexOf('task_') >= 0 || value.indexOf('bug_') >= 0)
                        {
                            $consumedInput.val('')
                        }
                    });
                    return;
                }
                else
                {
                    hoursConsumedToday = data + inputAll;
                    hoursSurplusToday  = limitWorkHour - hoursConsumedToday;
                    $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
                    $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
                }
            }
        }, 'json');
    });
});

function cleanEffort()
{
    var inputAll = 0;
    $('#objectTable tbody tr.computed select#objectType').each(function()
    {
        var value = $(this).val();
        var $consumedInput = $(this).closest('td').next().next().find('input');
        if(value.indexOf('task_') >= 0 || value.indexOf('bug_') >= 0)
        {
            var workConsumed = parseFloat(typeof($consumedInput.val()) == 'undefined' ? 0 : $consumedInput.val());
            workConsumed = isNaN(workConsumed) ? 0 : workConsumed;
            workConsumed = Math.round(workConsumed * 1000) / 1000;
            inputAll += workConsumed;
        }
    });

    inputAll = Math.round(inputAll * 1000) / 1000;
    if(inputAll > 0)
    {
        hoursConsumedToday = hoursConsumedToday - inputAll;
        hoursSurplusToday  = limitWorkHour - hoursConsumedToday;
        $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
        $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
    }
    
    $('#objectTable tbody tr.computed').remove();
    updateID();
}

function deleteEffort(clickedButton)
{
    if($('.effortBox').size() == 1) return;
    $objectType = $(clickedButton).closest('tr').find('select#objectType');
    $consumed   = $(clickedButton).closest('tr').find('input[name^=consumed]');

    if($objectType.val().indexOf('task_') >= 0 || $objectType.val().indexOf('bug_') >= 0)
    {
        var workConsumed = parseFloat(typeof($consumed.val()) == 'undefined' ? 0 : $consumed.val());
        workConsumed = isNaN(workConsumed) ? 0 : workConsumed;
        workConsumed = Math.round(workConsumed * 1000) / 1000;
        hoursConsumedToday = hoursConsumedToday - workConsumed;
        hoursConsumedToday = Math.round(hoursConsumedToday * 1000) / 1000;
        hoursSurplusToday  = limitWorkHour - hoursConsumedToday;
        $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
        $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
        if($objectType.val().indexOf('task_') >= 0)
        {
            var taskID = $objectType.val().slice(5);
            tasks[taskID].consumed = Math.round((parseFloat(tasks[taskID].consumed) - workConsumed) * 1000) / 1000;
            var oldConsumed = Math.round(parseFloat(tasks[taskID].oldConsumed) * 1000) / 1000;
            var consumedAll = 0;
            var id = $(clickedButton).closest('tr').find(' .col-id > input[name^=id]').val();

            $('#objectTable tbody tr select#objectType').each(function()
            {
                var value = $(this).val();
                if(value.indexOf('task_') >= 0 && value.slice(5) == taskID && $(this).closest('td').prev().prev().find('input[name^=id]').val() != id)
                {
                    var consumedInput = $(this).closest('td').next().next().find('input');
                    var leftInput     = $(this).closest('td').next().next().next().find('input');
                    var consumed      = Math.round((parseFloat(consumedInput.val()) * 1000)) / 1000;
                    var left          = Math.round((parseFloat(leftInput.val()) * 1000)) / 1000;
                    if(!isNaN(consumed) && typeof(consumed) != 'undefined' && !isNaN(left) && typeof(left) != 'undefined')
                    {
                        consumedAll += consumed;
                        if((consumed + left + oldConsumed) != tasks[taskID].estimate)
                        {
                            $(this).closest('td').next().next().next().find('input').val(Math.round((tasks[taskID].estimate - oldConsumed - consumedAll) * 1000) / 1000);
                        }
                    }
                }
            });
        }
    }
    $(clickedButton).parent().parent().remove();
    updateID();
}

function consumedKeyup(e)
{
    $objectType = $(e).closest('td').prev().prev().find('select#objectType').val();
    if(!$objectType)
    {
        alert(hoursConsumedNoObjectType);
        $(e).val('');
        return;
    }
    $current = $(e).val();
    $current = parseFloat(typeof($current) == 'undefined' ? 0 : $current);
    $current = isNaN($current) ? 0 : $current;
    $objectType = $(e).closest('td').prev().prev().find('select#objectType').val();
    if($objectType.indexOf('task_') >= 0 || $objectType.indexOf('bug_') >= 0)
    {
        if(limitWorkHour - (hoursConsumedToday + $current - $prev) < 0)
        {
            $(e).closest('td').next().find('input').val('');
            if($('input[name=date]').val() == currentDate)
            {
                alert(hoursConsumedTodayOverflow);
            }
            else
            {
                alert(inputDate + hoursConsumedTodayOverflowOther);
            }
            $(e).val($prev ? $prev : '');
            return;
        }
        hoursConsumedToday = hoursConsumedToday + $current - $prev;
        hoursConsumedToday = Math.round(hoursConsumedToday * 1000) / 1000;
        hoursSurplusToday  = hoursSurplusToday  - $current + $prev;
        hoursSurplusToday  = Math.round(hoursSurplusToday * 1000) / 1000;
        $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
        $('.hoursSurplusToday').html(hoursSurplusToday + 'h');

        if($objectType.indexOf('task_') >= 0)
        {
            var taskID = $objectType.slice(5);
            tasks[taskID].consumed = Math.round((tasks[taskID].consumed + $current - $prev) * 1000) / 1000;
            var task = tasks[taskID];
            var estimate = task.estimate;
            var consumed = task.consumed;
            if(estimate == 0 || estimate <= consumed)
            {
                $(e).closest('td').next().find('input').val(0);
                $(e).closest('td').next().next().find('input').removeAttr('disabled').removeAttr('title').val(tasks[taskID].testPackageVersion);
            }
            else
            {
                $(e).closest('td').next().next().find('input').attr('disabled', true).attr('title', testTip).val('');
                $(e).closest('td').next().find('input').val(Math.round((estimate - consumed) * 1000) / 1000);
            }
        }
        $prev = $(e).val();
        $prev = parseFloat(typeof($prev) == 'undefined' ? 0 : $prev);
        $prev = isNaN($prev) ? 0 : $prev;
    }
}

function consumedFocus(e)
{
    $prev = parseFloat(typeof($(e).val()) == 'undefined' ? 0 : $(e).val());
    $prev = isNaN($prev) ? 0 : $prev;
}

function testKeyup(e)
{
    var taskID = $(e).closest('td').prev().prev().prev().prev().attr('data-shadowtask');
    tasks[taskID].testPackageVersion = $(e).val();
    $('select#objectType').each(function()
    {
        if($(this).closest('td').attr('data-shadowtask') == taskID);
        var testInput = $(this).closest('td').next().next().next().next().find('input');
        if(testInput.attr('disabled') != 'disabled') testInput.val($(e).val());
    });
}