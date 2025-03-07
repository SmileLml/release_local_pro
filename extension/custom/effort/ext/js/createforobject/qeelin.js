$(function()
{
    var $prev = 0;

    $("input[name^=consumed]").focus(function(){
        $prev = parseFloat(typeof($(this).val()) == 'undefined' ? 0 : $(this).val());
        $prev = isNaN($prev) ? 0 : $prev;
        $prev = Math.ceil($prev * 100) / 100;
    });

    $('input[name^=dates]').change(function(){
        id = $(this).attr('id');
        no = id.split('dates')[1];
        ch = isNaN(parseFloat($('#consumed' + no).val())) ? 0 : parseFloat($('#consumed' + no).val());
        if(ch > 0)
        {
            if($(this).val() == today)
            {
                if(limitWorkHour - ((parseInt(roundToTwoDecimals(hoursConsumedToday * 100)) + parseInt(roundToTwoDecimals(ch * 100))) / 100) < 0)
                {
                    alert(hoursConsumedTodayOverflow);
                    $('#consumed' + no).val('');
                    if(objectType == 'task')
                    {
                        $('#left' + no).val('');
                        testPackageVersionInput(false);
                    }
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
            hoursConsumedToday = roundToTwoDecimals(hoursConsumedToday);
            hoursSurplusToday = roundToTwoDecimals(hoursSurplusToday);
            
            $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
            $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
        }
    });

    $("input[name^=consumed]").keyup(function(){
        if(objectType == 'task' || objectType == 'bug')
        {
            id = $(this).attr('id');
            no = id.split('consumed')[1];
            ch = parseFloat(typeof($(this).val()) == 'undefined' ? 0 : $(this).val());
            ch = isNaN(ch) ? 0 : ch;

            if(objectType == 'task')
            {
                leftPrev = $('#left' + no).val()
                var leftNum = (parseInt(roundToTwoDecimals(estimateTotally * 100)) - parseInt(roundToTwoDecimals(consumedTotally * 100)) - parseInt(roundToTwoDecimals(ch * 100)) + parseInt(roundToTwoDecimals($prev * 100))) / 100;
                if(leftNum >= 0)
                {
                    $('#left' + no).val(leftNum);
                }
                else
                {
                    $('#left' + no).val(0);
                }
                var left = leftNum > 0 ? leftNum : 0;
                if(left == 0)
                {
                    testPackageVersionInput(true);
                }
                else
                {
                    testPackageVersionInput(false);
                }
            }

            if($('#dates' + no).val() == today)
            {
                if(limitWorkHour - ((parseInt(roundToTwoDecimals(hoursConsumedToday * 100)) + parseInt(roundToTwoDecimals(ch * 100)) - parseInt(roundToTwoDecimals($prev * 100))) / 100) < 0)
                {
                    alert(hoursConsumedTodayOverflow);
                    $(this).val($prev ? $prev : '');
                    if(objectType == 'task')
                    {
                        $('#left' + no).val(leftPrev);
                    }
                    return;
                }
                hoursConsumedToday = (parseInt(roundToTwoDecimals(hoursConsumedToday * 100)) + parseInt(roundToTwoDecimals(ch * 100)) - parseInt(roundToTwoDecimals($prev * 100))) / 100;
                hoursSurplusToday  = (parseInt(roundToTwoDecimals(hoursSurplusToday * 100)) - parseInt(roundToTwoDecimals(ch * 100)) + parseInt(roundToTwoDecimals($prev * 100))) / 100;
                $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
                $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
            }
            consumedTotally = (parseInt(roundToTwoDecimals(consumedTotally * 100)) + parseInt(roundToTwoDecimals(ch * 100)) - parseInt(roundToTwoDecimals($prev * 100))) / 100;
            $prev = $(this).val();
            $prev = parseFloat(typeof($prev) == 'undefined' ? 0 : $prev);
            $prev = isNaN($prev) ? 0 : $prev;
        }
    });

    $("input[name^=left]").keyup(function(){
        if($(this).val() == 0)
        {
            testPackageVersionInput(true);
        }
        else
        {
            testPackageVersionInput(false);
        }
    });
})

function testPackageVersionInput(show)
{
    if(show)
    {
        $('#testPackageVersionID').show();
    }
    else
    {
        var hideMark = true;
        $('input[name^=left]').each(function()
        {
            if(!isNaN(parseFloat($(this).val())) && $(this).val() == 0) hideMark = false;
        });
        if(hideMark) $('#testPackageVersionID').hide();
    }
}