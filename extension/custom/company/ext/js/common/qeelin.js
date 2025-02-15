function loadProductProject(productID)
{
    if(productID == 0)
    {
        replaceProject(defaultProject, true);
        loadProductExecutions(productID, $('#project').val());
        return;
    }

    var project = $('#project').length > 0 ? $('#project').val() : 0;
    var link = createLink('product', 'ajaxGetProjectsCustom', 'productID=' + productID + '&branch=0&project=' + project);

    $.get(link, function(data)
    {
        replaceProject(data);
        $('#projectIdBox select').trigger('change');
    });
}