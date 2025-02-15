<?php
$lang->custom->libreOffice       = 'Office Convert';
$lang->custom->libreOfficeTurnon = 'LibreOffice';
$lang->custom->type              = 'Type';
$lang->custom->libreOfficePath   = 'Soffice Path';
$lang->custom->collaboraPath     = 'Collabora Path';

$lang->custom->errorSofficePath   = 'Soffice file does not exist.';
$lang->custom->errorRunSoffice    = "Failed to run soffice. Error: %s";
$lang->custom->errorRunCollabora  = "Failed to connect to Collabora. Check Collabora settings and see whether it is connected to the network.";
$lang->custom->cannotUseCollabora = "If you choose Collabora, ZenTao must be configured in static access.";

$lang->custom->turnonList[1] = 'On';
$lang->custom->turnonList[0] = 'Off';

$lang->custom->typeList['libreoffice'] = 'LibreOffice';
$lang->custom->typeList['collabora']   = 'Collabora Online';

$lang->custom->sofficePlaceholder   = 'Write the path for soffice in LibreOffice, e.g. /opt/libreoffice/program/soffice';
$lang->custom->collaboraPlaceholder = 'Write the path for Collabora URL, e.g. https://127.0.0.1:9980';

$lang->custom->feedback = new stdclass();
$lang->custom->feedback->fields['required']         = $lang->custom->required;
$lang->custom->feedback->fields['review']           = 'Feedback Review';
$lang->custom->feedback->fields['closedReasonList'] = 'Closed Reason';
$lang->custom->feedback->fields['typeList']         = 'Type List';
$lang->custom->feedback->fields['priList']          = 'Pri List';

$lang->custom->ticket = new stdclass();
$lang->custom->ticket->fields['required'] = $lang->custom->required;
$lang->custom->ticket->fields['priList']  = 'Pri List';
$lang->custom->ticket->fields['typeList'] = 'Type List';
