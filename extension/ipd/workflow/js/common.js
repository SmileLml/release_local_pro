function setAutoHeight()
{
    var height = $(window).height() - 105;
    $('.auto-height').each(function()
    {
        var $this = $(this);
        var offset = $this.data('offset') || 0;
        $this.height(height - offset);
        if($this.hasClass('main-row'))
        {
            $this.find('.side-col>.cell,.main-col>.cell').height(height - 20 - offset);
        }
    });
};
