$(function()
{
    prev = 0;
    $("input[name^=consumed]").click(function(){
        prev = typeof($(this).val()) == 'undefined' ? 0 : parseFloat($(this).val());
        prev = isNaN(prev) ? 0 : prev;
    });
    $("input[name^=consumed]").blur(function(){
        if(objectType == 'task' || objectType == 'bug')
        {
            id = $(this).attr('id');
            no = id.split('consumed')[1];
            ch = parseFloat(typeof($(this).val()) == 'undefined' ? 0 : $(this).val());
            ch = isNaN(ch) ? 0 : ch;
            if(ch > 0)
            {
                if(objectType == 'task')
                {
                    leftPrev = $('#left' + no).val()
                    if((estimateTotally - consumedTotally - ch) >= 0)
                    {
                        $('#left'+no).val(estimateTotally - consumedTotally - ch);
                    }
                    else
                    {
                        $('#left'+no).val(0);
                    }
                }

                if($('#dates' + no).val() == today)
                {
                    if(limitWorkHour - (hoursConsumedToday + ch - prev) < 0)
                    {
                        alert(hoursConsumedTodayOverflow);
                        $(this).val(prev ? prev : '');
                        if(objectType == 'task') $('#left'+no).val(leftPrev);
                        return;
                    }
                    hoursConsumedToday = hoursConsumedToday + ch - prev;
                    hoursConsumedToday = Math.round(hoursConsumedToday * 1000) / 1000;
                    hoursSurplusToday  = hoursSurplusToday  - ch + prev;
                    hoursSurplusToday = Math.round(hoursSurplusToday * 1000) / 1000;
                    $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
                    $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
                }
            }
        }
    });

    $('input[name^=dates]').change(function(){
        id = $(this).attr('id');
        no = id.split('dates')[1];
        ch = isNaN(parseFloat($('#consumed' + no).val())) ? 0 : parseFloat($('#consumed' + no).val());
        if(ch > 0)
        {
            if($(this).val() == today)
            {
                if(limitWorkHour - (hoursConsumedToday + ch) < 0)
                {
                    alert(hoursConsumedTodayOverflow);
                    $('#consumed' + no).val('');
                    if(objectType == 'task') $('#left' + no).val('');
                    return;
                }
                hoursConsumedToday += ch;
                hoursSurplusToday  -= ch;
            }
            else
            {
                hoursConsumedToday -= ch;
                hoursSurplusToday  += ch;
            }
            hoursConsumedToday = Math.round(hoursConsumedToday * 1000) / 1000;
            hoursSurplusToday = Math.round(hoursSurplusToday * 1000) / 1000;
            
            $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
            $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
        }
    });
})
