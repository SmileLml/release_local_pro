<?php
$lang->navIcons['feedback'] = "<i class='icon icon-feedback'></i>";

$lang->feedback     = new stdclass();
$lang->feedbackpriv = new stdclass();

$lang->feedback->common     = 'Feedback';
$lang->feedbackpriv->common = 'Feedback permission';

$lang->mainNav->feedback      = "<i class='icon icon-feedback'></i> Feedback|feedback|admin|";
$lang->navGroup->ticket       = 'feedback';
$lang->navGroup->feedback     = 'feedback';
$lang->navGroup->faq          = 'feedback';
$lang->navGroup->feedbackpriv = 'feedback';

$lang->searchLang = 'Search';

$lang->feedback->menu = new stdclass();
$lang->feedback->menu->browse   = array('link' => 'Feedback|feedback|admin|browseType=wait', 'alias' => 'create,edit,view,adminview,batchedit,browse,showimport,batchclose');
$lang->feedback->menu->ticket   = array('link' => 'Ticket|ticket|browse', 'alias' => 'create,edit,view,batchedit,browse,showimport');
$lang->feedback->menu->faq      = array('link' => 'FAQ|faq|browse', 'alias' => 'create');
$lang->feedback->menu->products = array('link' => 'Setting|feedback|products', 'alias' => 'manageproduct');

$lang->feedback->menuOrder[5]  = 'browse';
$lang->feedback->menuOrder[10] = 'ticket';
$lang->feedback->menuOrder[15] = 'faq';
$lang->feedback->menuOrder[20] = 'products';

$lang->ticket = new stdclass();
$lang->ticket->common = 'Ticket';
$lang->ticket->navGroup['ticket'] = 'feedback';

$lang->faq = new stdclass();
$lang->faq->navGroup['faq'] = 'feedback';

$lang->my->menu->work['subMenu']->feedback = "{$lang->feedback->common}|my|work|mode=feedback&type=assigntome";
$lang->my->menu->work['subMenu']->ticket   = array('link' => "{$lang->ticket->common}|my|work|mode=ticket&type=assignedtome", 'subModule' => 'feedback');
$lang->my->menu->work['menuOrder'][80] = 'feedback';

$lang->feedbackView[0] = 'Developer Interface';
$lang->feedbackView[1] = 'Feedback Interface';

$lang->switchFeedbackView[1] = 'Developer Interface';
$lang->switchFeedbackView[0] = 'Feedback Interface';

global $app;
if($config->vision == 'lite')
{
    $lang->feedback->menu->browse = array('link' => 'Feedback|feedback|browse|browseType=unclosed', 'alias' => 'create,edit,view,adminview,batchedit,browse,admin');

    unset($lang->feedback->menu->products);
    unset($lang->feedback->menuOrder[15]);
}

$lang->noMenuModule[] = 'faq';
$lang->noMenuModule[] = 'feedback';
$lang->noMenuModule[] = 'deploy';
$lang->noMenuModule[] = 'host';
$lang->noMenuModule[] = 'serverroom';
$lang->noMenuModule[] = 'service';
$lang->noMenuModule[] = 'ops';

$lang->icons['ticket'] = 'support-ticket';
