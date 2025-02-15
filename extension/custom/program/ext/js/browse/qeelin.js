function showEditCheckbox(show)
{
    $('.icon-project,.icon-waterfall,.icon-scrum,.icon-kanban,.icon-agileplus,.icon-waterfallplus,.icon-ipd').each(function()
    {
        $this     = $(this);
        $tr       = $(this).closest('tr');
        projectID = $tr.attr('data-id');
        if(show)
        {
            var marginLeft = $tr.find('td:first').find('span.table-nest-icon').css('margin-left');
            if(programs[projectID] != undefined && programs[projectID].status == 'closed' && CRProject == 1) $tr.find('td:first').prepend("<div class='checkbox-primary custom-checkbox'><input type='checkbox' disabled style='cursor: not-allowed;'/><label for='projectIdList" + projectID + "'></lable></div>");
            else $tr.find('td:first').prepend("<div class='checkbox-primary'><input type='checkbox' name='projectIdList[]' value='" + projectID + "' id='projectIdList" + projectID + "'/><label for='projectIdList" + projectID + "'></lable></div>");
            $tr.find('td:first').find('.checkbox-primary').css('margin-left', marginLeft).css('width', '14');
            $tr.find('td:first').find('span.table-nest-icon').css('margin-left', '0');
        }
        else
        {
            var marginLeft = $tr.find('td:first').find('.checkbox-primary').css('margin-left');
            $tr.find('td:first').find('span.table-nest-icon').css('margin-left', marginLeft);
            $tr.find('td:first').find('[name^="projectIdList"]').parent().remove();
            $tr.find('td:first').find('.custom-checkbox').remove();
        }
    });
    if(show && hasProject)
    {
        var tableFooter = "<div class='editCheckbox'><div class='checkbox-primary check-all'><input type='checkbox' id='checkAll' /><label>" + selectAll + "</label></div><div class='table-actions btn-toolbar'><button type='submit' class='btn'>" + edit + "</button></div></div>";
        $('#programForm').attr('action', createLink('project', 'batchEdit', 'from=program'));
        $('.table-footer').prepend(tableFooter).show();
        $('body').scroll();
    }
    else
    {
        $('#programForm').removeClass('has-row-checked');
        $('#projectsSummary').addClass('hidden');
        $('#programSummary').removeClass('hidden');
        $('#programForm').find('.editCheckbox').remove();
        if($('#programForm .pager').length == 0) $('.table-footer').hide();
        $('#programForm').removeAttr('action');
    }
}
