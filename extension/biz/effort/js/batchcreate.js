function updateAction(date)
{
    date = date.replace(/\-/g, '');
    link = createLink('effort', 'batchCreate', 'date=' + date);

    var hasContent = false;
    $('#objectTable tr.effortBox.new input[id^=work]').each(function(){if($(this).val().length > 0) hasContent = true});
    if(!hasContent) return location.href=link;

    cleanEffort();
}

function addEffort(clickedButton)
{
    effortRow = '<tr class="effortBox new">' + $(clickedButton).closest('tr').html() + '</tr>';
    $(clickedButton).closest('tr').after(effortRow);
    var nextBox = $(clickedButton).closest('tr').next('.effortBox');
    $(nextBox).find('input[id^=id]').val(num);
    $(nextBox).find('.chosen-container').remove();
    $(nextBox).find('select').chosen();
    $(nextBox).find('input[id^="left"]').attr('name', "left[" + num + "]").attr('id', "left[" + num + "]");
    $(nextBox).find('select[id^=execution]').attr('name', "execution[" + num + "]").attr('id', "execution" + num);
    if($(nextBox).find('select#objectType').val().indexOf('task_') < 0) $(nextBox).find('input[id^="left"]').attr('disabled', 'disabled');

    num++;
    updateID();
}

function deleteEffort(clickedButton)
{
    if($('.effortBox').size() == 1) return;
    $(clickedButton).parent().parent().remove();
    updateID();
}

function cleanEffort()
{
    $('#objectTable tbody tr.computed').remove();
    updateID();
}

function updateID()
{
    i = 1;
    $('.effortID').each(function(){$(this).html(i ++)});
}

$(function()
{
    $('select#objectType').each(function()
    {
        var value = $(this).val();
        var $leftInput = $(this).closest('td').next().next().next().find('input');
        if(value.indexOf('task_') >= 0)
        {
            $leftInput.removeAttr('disabled').removeAttr('title');
        }
    });

    $(document).on('change', 'select#objectType', function()
    {
        var value       = $(this).val();
        var type        = value.indexOf('task') > -1 ? 'task' : (value.indexOf('bug') > -1 ? 'bug' : '');
        var executionID = type == 'task' ? (executionTask[value] ? executionTask[value] : 0) : (executionBug[value] ? executionBug[value] : 0);

        var id          = $(this).closest('tr').find('#id').val();
        var selectName  = 'execution[' + id + ']';
        var selectID    = 'execution' + id;
        var prevTaskConsumedMark = /^[\d.]+$/.test($(this).closest('td').next().next().find('input').val());
        var prevTaskLeftMark = /^[\d.]+$/.test($(this).closest('td').next().next().next().find('input').val());

        var $consumedInput = $(this).closest('td').next().next().find('input');
        var $leftInput     = $(this).closest('td').next().next().next().find('input');
        var $testInput     = $(this).closest('td').next().next().next().next().find('input');
        var taskID = $(this).closest('td').attr('data-shadowtask');
        var bugID  = $(this).closest('td').attr('data-shadowbug');
        var newTaskID = value.slice(5);
        var newBugID  = value.slice(4);
        if(value == '')
        {
            if(taskID)
            {
                if(prevTaskConsumedMark)
                {
                    var workConsumed = parseFloat(typeof($consumedInput.val()) == 'undefined' ? 0 : $consumedInput.val());
                    workConsumed = isNaN(workConsumed) ? 0 : workConsumed;
                    workConsumed = Math.round(workConsumed * 1000) / 1000;
                    if(workConsumed > 0)
                    {
                        hoursConsumedToday = hoursConsumedToday - workConsumed;
                        hoursSurplusToday  = limitWorkHour - hoursConsumedToday;
                        $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
                        $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
                    }
                    tasks[taskID].consumed = tasks[taskID].consumed - $consumedInput.val();
                }
                $consumedInput.val('');
                $(this).closest('td').removeAttr('data-shadowtask');
            }
            else if(bugID)
            {
                if(prevTaskConsumedMark)
                {
                    var workConsumed = parseFloat(typeof($consumedInput.val()) == 'undefined' ? 0 : $consumedInput.val());
                    workConsumed = isNaN(workConsumed) ? 0 : workConsumed;
                    workConsumed = Math.round(workConsumed * 1000) / 1000;
                    if(workConsumed > 0)
                    {
                        hoursConsumedToday = hoursConsumedToday - workConsumed;
                        hoursSurplusToday  = limitWorkHour - hoursConsumedToday;
                        $('.hoursConsumedToday').html(hoursConsumedToday + 'h');
                        $('.hoursSurplusToday').html(hoursSurplusToday + 'h');
                    }
                }
                $consumedInput.val('');
                $(this).closest('td').removeAttr('data-shadowbug');
            }
            var executionTpl = $('#executionTpl').html();
        }
        else
        {
            if(taskID)
            {
                if(type == 'task')
                {
                    if(prevTaskConsumedMark)
                    {
                        tasks[taskID].consumed    = Math.round((parseFloat(tasks[taskID].consumed) - parseFloat($consumedInput.val())) * 1000) / 1000;
                        tasks[newTaskID].consumed = Math.round((parseFloat(tasks[newTaskID].consumed) + parseFloat($consumedInput.val())) * 1000) / 1000;
                        if(tasks[newTaskID].estimate == 0 || tasks[newTaskID].estimate <= tasks[newTaskID].consumed)
                        {
                            $leftInput.val(0);
                            $testInput.removeAttr('disabled').removeAttr('title').val(tasks[newTaskID].testPackageVersion);
                        }
                        else
                        {
                            $leftInput.val(Math.round((tasks[newTaskID].estimate - tasks[newTaskID].consumed) * 1000) / 1000);
                        }
                    }
                    $(this).closest('td').attr('data-shadowtask', newTaskID);
                }
                else if(type == 'bug')
                {
                    if(prevTaskConsumedMark)
                    {
                        tasks[taskID].consumed = Math.round((parseFloat(tasks[taskID].consumed) - parseFloat($consumedInput.val())) * 1000) / 1000;
                    }
                    $(this).closest('td').removeAttr('data-shadowtask');
                    $(this).closest('td').attr('data-shadowbug', newBugID);
                }
            }
            else if(bugID)
            {
                if(type == 'task')
                {
                    if(prevTaskConsumedMark)
                    {
                        tasks[newTaskID].consumed = Math.round((parseFloat(tasks[newTaskID].consumed) + parseFloat($consumedInput.val())) * 1000) / 1000;
                        if(tasks[newTaskID].estimate == 0 || tasks[newTaskID].estimate <= tasks[newTaskID].consumed)
                        {
                            $leftInput.val(0);
                            $testInput.removeAttr('disabled').removeAttr('title').val(tasks[newTaskID].testPackageVersion);
                        }
                        else
                        {
                            $leftInput.val(Math.round((tasks[newTaskID].estimate - tasks[newTaskID].consumed) * 1000) / 1000);
                        }
                    }
                    $(this).closest('td').removeAttr('data-shadowbug')
                    $(this).closest('td').attr('data-shadowtask', newTaskID);
                }
                else if(type == 'bug')
                {
                    $(this).closest('td').attr('data-shadowbug', newBugID);
                }
            }
            else
            {
                if(type == 'task') $(this).closest('td').attr('data-shadowtask', newTaskID);
                if(type == 'bug')  $(this).closest('td').attr('data-shadowbug', newBugID);
            }

            var executionName = executions[executionID] ? executions[executionID] : '';
            var executionTpl = '<select name="' + selectName + '" id="' + selectID + '" tabindex="9999" class="form-control" ' + ((executionName == undefined || executionName == '') ? 'disabled="disable"' : '') + '>';

            executionTpl    += '<option value="" title="" data-keys=" "></option>';
            if(executionName != undefined && executionName != '') executionTpl += '<option value="' + executionID + '" title="' + executionName + '" data-keys=" ">' + executionName + '</option>';
        }

        var $executionTd = $(this).parent().next();
        $executionTd.empty();
        $executionTd.append(executionTpl);

        var $execution = $(this).parent().next().find('select');
        $execution.chosen();
        $execution.val(executionID);
        $execution.trigger("chosen:updated");


        if(value.indexOf('task_') >= 0)
        {
            $leftInput.removeAttr('disabled').removeAttr('title');
        }
        else
        {
            $leftInput.attr('disabled', true).attr('title', leftTip).val('');
            $testInput.attr('disabled', true).attr('title', testTip).val('');
        }
    });
});
