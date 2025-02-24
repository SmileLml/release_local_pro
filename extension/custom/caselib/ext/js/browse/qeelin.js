window.setCheckedCookie = function() {
    var checkeds = [];
    var $checkboxes = $('#mainContent .main-table tbody>tr input[type="checkbox"][name^=caseIDList]:checked');
    $checkboxes.each(function() {
        checkeds.push($(this).val());
    });
    $.cookie('checkedItem', checkeds.join(','), {expires: config.cookieLife, path: config.webRoot});
};

/**
 * Get checked items.
 *
 * @access public
 * @return array
 */
function getCheckedItems()
{
    var checkedItems = [];
    $('#testcaseForm [name^=caseIDList]:checked').each(function(index, ele)
    {
        checkedItems.push($(ele).val());
    });
    return checkedItems;
};