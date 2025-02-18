$('#batchSetDeadline').click(function(){
    var deadline = $('#deadlineModal input[name="deadline"]').val();
    $('#deadline').val(deadline);
    setFormAction(deadlineAction, '','#bugList');
});

$('#batchAdjust').click(function(){
    var project     = $('select#project').val();
    var execution   = $('select#execution').val();
    var openedBuild = $('select#openedBuild').val();
    project         = parseInt(project);
    execution       = parseInt(execution);
    if(isNaN(project) || !project)
    {
        alert(projectLang + noempty);
        return;
    }
    if(showExecution && (isNaN(execution) || !execution))
    {
        alert(bugExecution + noempty);
        return;
    }
    if(openedBuild == null || openedBuild == '' || openedBuild == 0 || openedBuild == '0')
    {
        alert(bugOpenedBuild + noempty);
        return;
    }
    $('#adjustProject').val(project)
    $('#adjustExecution').val(execution)
    $('#adjustBuild').val(openedBuild)
    setFormAction(adjustAction, 'hiddenwin','#bugList');
});

function checkMax(e)
{
    if($('input[name^=bugIDList]').length > batchCheckMax)
    {
        alert(batchCheckMaxLang);
        location.reload()
        return;
    }
    $('#adjustModal').removeClass('hidden')
}

/**
 * Load executions of product.
 *
 * @param  int    $productID
 * @param  int    $projectID
 * @access public
 * @return void
 */
function loadProductExecutions(productID, projectID = 0)
{
    if(projectExecutionPairs[projectID] !== undefined)
    {
        $('#executionIdBox').parents('.executionBox').hide();
        var execution = projectExecutionPairs[projectID];
        showExecution = false;
    }
    else
    {
        $('#executionIdBox').parents('.executionBox').show();
        var execution = $('#execution').val();
        showExecution = true;
    }
    if(projectModels[projectID] == 'kanban') bugExecution = kanbanLang;
    else bugExecution = executionLang;

    link = createLink('product', 'ajaxGetExecutions', 'productID=' + productID + '&projectID=' + projectID + '&branch=' + branch + '&number=&executionID=' + execution + '&from=&mode=stagefilter');
    $('#executionIdBox').load(link, function()
    {
        $(this).find('select').chosen();
        if(typeof(bugExecution) == 'string') $('#executionIdBox').prepend("<span class='input-group-addon' id='executionBox' style='border-left-width: 0px;'>" + bugExecution + "</span>");
    });

    loadProjectBuilds(projectID)
    loadExecutionRelated(execution)
}


/**
 * Load builds of a project.
 *
 * @param  int      projectID
 * @access public
 * @return void
 */
function loadProjectBuilds(projectID)
{
    var oldOpenedBuild = $('#openedBuild').val() ? $('#openedBuild').val() : 0;

    $.get(createLink('bug', 'ajaxGetReleasedBuilds', 'productID=' + productID), function(data){releasedBuilds = data;}, 'json');

    if($('#executionIdBox #execution').val() != 0)
    {
        $('#executionIdBox #execution').change();
        return;
    }

    var link = createLink('build', 'ajaxGetProjectBuilds', 'projectID=' + projectID + '&productID=' + productID + '&varName=openedBuild&build=&branch=' + branch);
    $.get(link, function(data)
    {
        if(!data) data = '<select id="openedBuild" name="openedBuild" class="form-control picker-select" multiple=multiple></select>';
        data = data.replace(/<span\b[^<]*(?:(?!<\/span>)<[^<]*)*<\/span>/gi,"");
        $('#openedBuild').replaceWith(data);
        $('#openedBuild').val(oldOpenedBuild);
        $('#pickerDropMenu-pk_openedBuild').remove();
        $('#openedBuild').next('.picker').remove();
        $("#openedBuild").picker({optionRender: markReleasedBuilds});
    });
}

/**
 * Load execution related bugs and tasks.
 *
 * @param  int    $executionID
 * @access public
 * @return void
 */
function loadExecutionRelated(executionID)
{
    executionID      = parseInt(executionID);
    currentProjectID = $('#project').val() == 'undefined' ? 0 : $('#project').val();
    if(executionID) loadExecutionBuilds(executionID);
    else loadProjectBuilds(currentProjectID);
}

/**
 * Load builds of a execution.
 *
 * @param  int      executionID
 * @param  int      num
 * @access public
 * @return void
 */
function loadExecutionBuilds(executionID)
{
    var oldOpenedBuild = $('#openedBuild').val() ? $('#openedBuild').val() : 0;

    $.get(createLink('bug', 'ajaxGetReleasedBuilds', 'productID=' + productID), function(data){releasedBuilds = data;}, 'json');
    link = createLink('build', 'ajaxGetExecutionBuilds', 'executionID=' + executionID + '&productID=' + productID + '&varName=openedBuild&build=' + oldOpenedBuild + "&branch=" + branch + "&index=0&needCreate=true");
    $.get(link, function(data)
    {
        if(!data) data = '<select id="openedBuild" name="openedBuild" class="form-control picker-select" multiple=multiple></select>';
        data = data.replace(/<span\b[^<]*(?:(?!<\/span>)<[^<]*)*<\/span>/gi,"");
        $('#buildBox .input-group-btn').remove();
        $('#pickerDropMenu-pk_openedBuild').remove();
        $('#openedBuild').next('.picker').remove();
        $('#openedBuild').replaceWith(data);
        $('#openedBuild').val(oldOpenedBuild);
        $("#openedBuild").picker({optionRender: markReleasedBuilds});
    });
}

function checkMaxSelect()
{
    var dtable = $('#bugList').zui('dtable');
    var checkedList = dtable.$.getChecks();
    if(checkedList.length > batchCheckMax)
    {
        alert(batchCheckMaxLang);
        return;
    }
    $('#popModal').click();
}

$('#batchComment').click(function(){
    var dtable = $('#bugList').zui('dtable');
    var checkedList = dtable.$.getChecks();
    if(!checkedList)
    {
        alert(checkedNull);
        return;
    }
    $('#commentModel input[name=bugIDS]').val(checkedList)
    $('#batchCommentSubmit').click();
});