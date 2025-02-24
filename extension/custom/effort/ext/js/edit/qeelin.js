$(function(){
    var $prev = effortConsumed;
    $('#consumed').keyup(function(){
        $current = $(this).val();
        $current = parseFloat(typeof($current) == 'undefined' ? 0 : $current);
        $current = isNaN($current) ? 0 : $current;
        if(limitWorkHour - (hoursConsumed + $current - $prev) < 0)
        {
            if($('input[name=date]').val() == today)
            {
                alert(hoursConsumedTodayOverflow);
            }
            else
            {
                alert($('#date').val() + hoursConsumedTodayOverflowOther);
            }
            $(this).val($prev ? $prev : '');
            return;
        }

        hoursConsumed = hoursConsumed + $current - $prev;
        hoursConsumed = Math.round(hoursConsumed * 1000) / 1000;
        hoursSurplus  = hoursSurplus  - $current + $prev;
        hoursSurplus  = Math.round(hoursSurplus * 1000) / 1000;
        $('.hoursConsumed').html(hoursConsumed + 'h');
        $('.hoursSurplus').html(hoursSurplus + 'h');

        if(objectType == 'task')
        {
            taskConsumed = parseFloat(taskConsumed) - parseFloat($prev) + $current;
            if(taskEstimate == 0 || taskEstimate <= taskConsumed)
            {
                $('#left').val(0);
            }
            else
            {
                $('#left').val(Math.round((taskEstimate - taskConsumed) * 1000) / 1000);
            }
        }
        $prev = $(this).val();
        $prev = parseFloat(typeof($prev) == 'undefined' ? 0 : $prev);
        $prev = isNaN($prev) ? 0 : $prev;
    });

    $('#date').change(function(){
        var inputDate = $(this).val();
        var date = $(this).val().replace(/\-/g, '');
        $('.hoursConsumed').closest('span').prev().html(inputDate != today ? inputDate + hoursConsumedTodayOtherTitle : hoursConsumedTodayTitle);
        $('.hoursSurplus').closest('span').prev().html(inputDate != today ? inputDate + hoursSurplusTodayOtherTitle : hoursSurplusTodayTitle);

        $.get(createLink('effort', 'getAccountStatistics', 'account&date=' + date), function(data)
        {
            if(typeof(data) == 'number')
            {
                var inputAll = Math.round($('#consumed').val() * 1000) / 1000;

                if(inputDate == effortDate)
                {
                    var hoursConsumedShow = data + inputAll - effortConsumed;
                    if(hoursConsumedShow > limitWorkHour)
                    {
                        alert(hoursConsumedTodayOverflow);
                        $('#consumed').val(effortConsumed);
                        $('.hoursConsumed').html(data + 'h');
                        $('.hoursSurplus').html((limitWorkHour - data) + 'h');
                        return;
                    }
                }
                else
                {
                    var hoursConsumedShow = data + inputAll;
                    if(hoursConsumedShow > limitWorkHour)
                    {
                        alert(inputDate + hoursConsumedTodayOverflowOther);
                        $('#consumed').val('');
                        $('.hoursConsumed').html(data + 'h');
                        $('.hoursSurplus').html((limitWorkHour - data) + 'h');
                        return;
                    }
                }
                hoursConsumed = hoursConsumedShow;
                hoursSurplus  = limitWorkHour - hoursConsumed;
                $('.hoursConsumed').html(hoursConsumed + 'h');
                $('.hoursSurplus').html(hoursSurplus + 'h');
            }
        }, 'json');
    });
});