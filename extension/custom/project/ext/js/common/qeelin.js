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