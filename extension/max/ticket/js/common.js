/**
 * Load all by product.
 *
 * @param  int    productID
 *
 * @access public
 * @return void
 */
function loadAll(productID)
{
    loadModules(productID);
    loadBuilds(productID);
}

/**
 * Load modules by product.
 *
 * @param  int    productID
 *
 * @access public
 * @return void
 */
function loadModules(productID)
{
    link = createLink('tree', 'ajaxGetOptionMenu', 'productID=' + productID + '&viewtype=ticket&branch=all&rootModuleID=0&returnType=html');
    $('#moduleBox').load(link, function(data)
    {
        var $inputGroup = $(this);
        $inputGroup.find('select').chosen()
        $inputGroup.prepend("<span class='input-group-addon'>" + moduleLang + "</span>");
    });
}

/**
 * Load builds by product.
 *
 * @param  int productID
 *
 * @access public
 * @return void
 */
function loadBuilds(productID)
{
    if(typeof(oldOpenedBuild) == 'undefined') oldOpenedBuild = 0;
    link = createLink('build', 'ajaxGetProductBuilds', 'productID=' + productID + '&varName=openedBuild&build=' + oldOpenedBuild);
    $.get(link, function(data)
    {
        if(!data) data = '<select id="openedBuild" name="openedBuild" class="form-control" multiple=multiple></select>';
        $('#openedBuild').replaceWith(data);
        $('#openedBuild_chosen').remove();
        $('#openedBuild').next('.picker').remove();
        $("#openedBuild").chosen();
    })
}
