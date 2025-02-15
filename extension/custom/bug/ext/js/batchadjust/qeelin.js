$(function(){
    renderBuilds();
});

function renderBuilds()
{
    $.each(bugList, function(id, val){
        $.get(createLink('bug', 'ajaxGetAllBuilds', 'bugID=' + id), function(data)
        {
            var openedBuilds = data.openedBuilds;
            $('#build-' + id).data('zui.picker').destroy();
            $('#build-' + id).picker({list: openedBuilds});
            $('#build-' + id).data('zui.picker').setValue('' + val.openedBuild);
        }, 'json');
    });
}

function loadProductBranches(productID, bugID)
{
    var branchID = $('#branch-' + bugID).find("option:selected").val();
    if(typeof(branchID) == 'undefined') branchID = 0;
    $('#branch-' + bugID).remove();
    $('#branch_' + bugID + '_chosen').remove();
    $('#branch-' + bugID).next('.picker').remove();
    var param = "productID=" + productID + "&bugID=" + bugID + "&branchID=" + branchID;
    $.get(createLink('branch', 'ajaxGetBranchesForBugBatchAdjuct', param), function(data)
    {
        if(data)
        {
            $('#branch-css-' + bugID).show();
            $('#product-'    + bugID).closest('.input-group').append(data);
            $('#branch-'     + bugID).chosen();
        }
        else
        {
            $('#branch-css-' + bugID).hide();
        }
        branchID = $('#branch-' + bugID).find("option:selected").val();
        if(typeof(branchID) == 'undefined') branchID = 0;
        loadProductProjects(productID, branchID, bugID);
    });
}

function loadProductProjects(productID, branchID, bugID)
{
    var projectID = $('#project-' + bugID).find("option:selected").val();
    link = createLink('product', 'ajaxGetProjectsForBugBatchAdjuct', 'productID=' + productID + '&bugID=' + bugID + '&branchID=' + branchID + '&projectID=' + projectID);
    $('#projectBox-' + bugID).load(link, function()
    {
        $(this).find('select').chosen();
        projectID = $('#project-' + bugID).find("option:selected").val()
        if(typeof(projectID) == 'undefined') projectID = 0;
        loadBuilds(productID, branchID, bugID, projectID);
    });
}

function loadBuilds(productID, branchID, bugID, projectID)
{
    var build = $('#build-' + bugID).val() ? $('#build-' + bugID).val() : 0;
    var link  = createLink('build', 'ajaxGetBuildsForBugBatchAdjuct', 'productID=' + productID + '&bugID=' + bugID + '&branchID=' + branchID + '&projectID=' + projectID + '&build=' + build);
    /* $('#buildBox-'+bugID).load(link, function(){$(this).find('select').picker({optionRender: markReleasedBuilds})}); */
    $('#buildBox-'+bugID).load(link, function(){$(this).find('select').picker({})});
}