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
                    var $consumedInput = $(this).closest('td').next().next().next().find('input');
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
                        var $consumedInput = $(this).closest('td').next().next().next().find('input');
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

    var $prev = 0;
    $("input[name^=consumed]").keyup(function(){
        $objectType = $(this).closest('td').prev().prev().prev().find('select#objectType').val();
        if(!$objectType)
        {
            alert(hoursConsumedNoObjectType);
            $(this).val('');
            return;
        }
        $current = $(this).val();
        $current = parseFloat(typeof($current) == 'undefined' ? 0 : $current);
        $current = isNaN($current) ? 0 : $current;
        $objectType = $(this).closest('td').prev().prev().prev().find('select#objectType').val();
        if($objectType.indexOf('task_') >= 0 || $objectType.indexOf('bug_') >= 0)
        {
            if(limitWorkHour - (hoursConsumedToday + $current - $prev) < 0)
            {
                if($('input[name=date]').val() == currentDate)
                {
                    alert(hoursConsumedTodayOverflow);
                }
                else
                {
                    alert(inputDate + hoursConsumedTodayOverflowOther);
                }
                $(this).val($prev ? $prev : '');
                return;
            }

            hoursConsumedToday = hoursConsumedToday + $current - $prev;
            hoursConsumedToday = Math.round(hoursConsumedToday * 1000) / 1000;
            hoursSurplusToday  = hoursSurplusToday  - $current + $prev;
            hoursSurplusToday  = Math.round(hoursSurplusToday * 1000) / 1000;
            $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
            $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
            $prev = $(this).val();
            $prev = parseFloat(typeof($prev) == 'undefined' ? 0 : $prev);
            $prev = isNaN($prev) ? 0 : $prev;
        }
    });

    $("input[name^=consumed]").focus(function(){
        $prev = parseFloat(typeof($(this).val()) == 'undefined' ? 0 : $(this).val());
        $prev = isNaN($prev) ? 0 : $prev;
    });

    $(document).on('change', 'select#objectType', function()
    {
        var $consumedInput = $(this).closest('td').next().find('input');
    });
});

function cleanEffort()
{
    var inputAll = 0;
    $('#objectTable tbody tr.computed select#objectType').each(function()
    {
        var value = $(this).val();
        var $consumedInput = $(this).closest('td').next().next().next().find('input');
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
    }
    $(clickedButton).parent().parent().remove();
    updateID();
}