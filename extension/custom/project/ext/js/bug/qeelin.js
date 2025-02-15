$('#batchSetDeadline').click(function(){
    var deadline = $('#deadlineModal input[name="deadline"]').val();
    $('#deadline').val(deadline)
    setFormAction(deadlineAction, '','#bugList');
});

$('#batchAdjust').click(function(){
    var product     = $('select#product').val();
    var branch      = $('select#branch').val();
    var project     = $('select#project').val();
    var execution   = $('select#execution').val();
    var openedBuild = $('select#openedBuild').val();
    product         = parseInt(product);
    project         = parseInt(project);
    execution       = parseInt(execution);
    if(isNaN(product) || !product)
    {
        alert(productLang + noempty);
        return;
    }
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
    $('#adjustProduct').val(product)
    $('#adjustBranch').val(branch)
    $('#adjustProject').val(project)
    $('#adjustExecution').val(execution)
    $('#adjustBuild').val(openedBuild)
    setFormAction(adjustAction, 'hiddenwin','#bugList');
});

function loadProductBranches(productID)
{
    $('#branch').remove();
    $('#branch_chosen').remove();
    $('#branch').next('.picker').remove();

    $.get(createLink('branch', 'ajaxGetBranches', "productID=" + productID + "&oldBranch=0&param=active"), function(data)
    {
        if(data)
        {
            $('#product').closest('.input-group').append(data);
            $('#branch').css('width', '65px');
            $('#branch').chosen();
        }

        loadProductProjects(productID);
    });
}

/**
 * Load projects of product.
 *
 * @param  int    $productID
 * @access public
 * @return void
 */
function loadProductProjects(productID)
{
    branch = $('#branch').val();
    if(typeof(branch) == 'undefined') branch = 0;
    link = createLink('product', 'ajaxGetProjectsCustom', 'productID=' + productID + '&branch=' + branch + '&projectID=&noEmpty=1');
    $('#projectBox').load(link, function()
    {
        $(this).find('select').chosen();
        var projectID = $('#project').find("option:selected").val();
        loadProductExecutions(productID, projectID);
    });
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
    branch   = $('#branch').val();
    if(typeof(branch) == 'undefined') branch = 0;

    if(projectID != 0 && projectExecutionPairs[projectID] !== undefined)
    {
        $('#executionIdBox').parents('.executionBox').hide();
        var execution = projectExecutionPairs[projectID];
        showExecution = false;
    }
    else
    {
        $('#executionIdBox').parents('.executionBox').show();
        $('#executionIdBox').parents('.executionBox').removeClass('hidden');
        var execution = $('#execution').val();
        showExecution = true;
    }

    link = createLink('product', 'ajaxGetExecutions', 'productID=' + productID + '&projectID=' + projectID + '&branch=' + branch + '&number=&executionID=' + execution + '&from=&mode=stagefilter');
    $('#executionIdBox').load(link, function()
    {
        $(this).find('select').chosen();
        if(typeof(bugExecution) == 'string') $('#executionIdBox').prepend("<span class='input-group-addon' id='executionBox' style='border-left-width: 0px;'>" + bugExecution + "</span>");
    });

    projectID != 0 ? loadProjectBuilds(projectID) : loadProductBuilds(productID);

    loadExecutionRelated(execution);
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
    var branch = $('#branch').val();
    if(typeof(branch) == 'undefined') branch = 0;
    var productID      = $('#product').val();
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
    })

}


/**
 * Load product builds.
 *
 * @param  productID $productID
 * @access public
 * @return void
 */
function loadProductBuilds(productID)
{
    branch = $('#branch').val();
    if(typeof(branch) == 'undefined') branch = 0;
    if(typeof(oldOpenedBuild) == 'undefined') oldOpenedBuild = 0;
    link = createLink('build', 'ajaxGetProductBuilds', 'productID=' + productID + '&varName=openedBuild&build=' + oldOpenedBuild + '&branch=' + branch);

    $.get(createLink('bug', 'ajaxGetReleasedBuilds', 'productID=' + productID), function(data){releasedBuilds = data;}, 'json');
    $.get(link, function(data)
    {
        if(!data) data = '<select id="openedBuild" name="openedBuild" class="form-control" multiple=multiple></select>';
        data = data.replace(/<span\b[^<]*(?:(?!<\/span>)<[^<]*)*<\/span>/gi,"");
        $('#openedBuild').replaceWith(data);
        $('#pickerDropMenu-pk_openedBuild').remove();
        $('#openedBuild').next('.picker').remove();
        $("#openedBuild").picker({optionRender: markReleasedBuilds});
    })

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

    if(executionID)
    {
        if(currentProjectID == 0) loadProjectByExecutionID(executionID);
        loadExecutionBuilds(executionID);
    }
    else
    {
        var currentProductID = $('#product').val();
        currentProjectID != 0 ? loadProjectBuilds(currentProjectID) : loadProductBuilds(currentProductID);
    }
}

/**
 * Load a project by execution id.
 *
 * @param  executionID $executionID
 * @access public
 * @return void
 */
function loadProjectByExecutionID(executionID)
{
    link      = createLink('project', 'ajaxGetPairsByExecution', 'executionID=' + executionID, 'json');
    productID = $('#product').val();

    $.post(link, function(data)
    {
        var originProject = $('#project').html();

        if($('#project').find('option[value="' + data.id + '"]').length > 0)
        {
            $('#project').find('option[value="' + data.id + '"]').attr('selected', 'selected');
            originProject = $('#project').html();
            $('#project').replaceWith('<select id="project" name="project" class="form-control" onchange="loadProductExecutions(' + productID + ', this.value)">' + originProject + '</select>');
        }

        $('#project_chosen').remove();
        $('#project').next('.picker').remove();
        $('#project').chosen();

    }, 'json')
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
    var branch         = $('#branch').val();
    var oldOpenedBuild = $('#openedBuild').val() ? $('#openedBuild').val() : 0;
    var productID      = $('#product').val();

    if(typeof(branch) == 'undefined') var branch = 0;
    if(typeof(productID) == 'undefined') var productID = 0;

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
            $('#buildBoxActions').insertBefore('#buildBox .input-group-btn');
            $("#openedBuild").picker({optionRender: markReleasedBuilds});
        })

}

/**
 * Load by branch.
 *
 * @access public
 * @return void
 */
function loadBranch()
{
    productID = $('#product').val();
    loadProductProjects(productID);
    loadProductBuilds(productID);
}

/**
 * Mark released builds.
 *
 * @param  object    $option
 * @access public
 * @return void
 */
function markReleasedBuilds($option)
{
    var build = $option.attr('data-value');
    if($.inArray(build, releasedBuilds) != -1)
    {
        if(!$option.find('.label-released').length)
        {
            var optionText = $option.find('.picker-option-text').html();
            $option.find('.picker-option-text').replaceWith("<p class='picker-option-text no-margin'>" + optionText + " <span class='label label-released label-primary label-outline'>" + released + "</span></p>");
        }
    }
}

function checkMaxSelect()
{
    if($('input[name^=bugIDList]:checked').length > batchCheckMax)
    {
        alert(batchCheckMaxLang);
        return;
    }
    $('#popModal').click();
}
