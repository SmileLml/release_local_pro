$(document).ready(function()
{
    $panelHeadingHeight = $('.panel-heading').outerHeight(true);
    $panelMarginBottom  = $('.panel').css('margin-bottom').replace('px', '');
    $editorNavHeight    = $('#editorNav').outerHeight(true);
    $editorMenuHeight   = $('#editorMenu').outerHeight();
    $spaceHeight        = $('.space.space-sm').outerHeight(true);
    
    $maxHeight = $(window).height() - $panelHeadingHeight - $panelMarginBottom - $editorNavHeight - $editorMenuHeight - $spaceHeight;
    $('.panel-body').css('max-height', $maxHeight + 'px');
});
