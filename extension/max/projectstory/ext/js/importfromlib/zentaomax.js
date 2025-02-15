function reload(libID)
{
    link = createLink('projectstory', 'importFromLib', 'projectID=' + projectID + '&productID=' + productID + '&libID=' + libID + '&storyType=' + storyType);
    location.href = link;
}

$(function()
{
    $('#navbar .nav li').removeClass('active');
    $('#navbar .nav li[data-id=' + storyType + ']').addClass('active');

    storyMenu = $('#navbar .nav li[data-id=' + storyType + ']' + ' ul');
    if(storyMenu && storyMenu.children.length == 2)
    {
        $('#navbar .nav>li[data-id=story]').addClass('active');
        $('#navbar .nav>li[data-id=story]>a').html($('.active [data-id=' + storyType + ']').text() + '<span class="caret"></span>');
    }
});
