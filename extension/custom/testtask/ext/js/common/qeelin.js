function setFormActionCustom(actionLink, hiddenwin, obj)
{
    if($('input[name^=caseIDList]:checked').length > batchCheckMax)
    {
        alert(batchCheckMaxLang);
        return;
    }
    setFormAction(actionLink, hiddenwin, obj);
}