$(function(){
    var $prev = taskConsumed;
    $('#consumed,#currentConsumed').keyup(function(){
        $current = $(this).val();
        $current = parseFloat(typeof($current) == 'undefined' ? 0 : $current);
        $current = isNaN($current) ? 0 : $current;
        if($current < taskConsumed) return;
        if(limitWorkHour - (hoursConsumed + $current - $prev) < 0)
        {
            alert(hoursConsumedTodayOverflow);
            $(this).val($prev ? $prev : '');
            return;
        }

        hoursConsumed = hoursConsumed + $current - $prev;
        hoursConsumed = Math.round(hoursConsumed * 1000) / 1000;
        hoursSurplus  = hoursSurplus  - $current + $prev;
        hoursSurplus  = Math.round(hoursSurplus * 1000) / 1000;
        $('.hoursConsumed').html(hoursConsumed + 'h');
        $('.hoursSurplus').html(hoursSurplus + 'h');
        $prev = $(this).val();
        $prev = parseFloat(typeof($prev) == 'undefined' ? 0 : $prev);
        $prev = isNaN($prev) ? 0 : $prev;
    });
});