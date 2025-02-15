<?php
$lang->workflow->common         = 'Workflow Flow';
$lang->workflow->browseFlow     = 'View Flow';
$lang->workflow->browseDB       = 'View DB';
$lang->workflow->create         = 'Create Flow';
$lang->workflow->copy           = 'Copy Flow';
$lang->workflow->edit           = 'Edit Flow';
$lang->workflow->view           = 'View Flow';
$lang->workflow->delete         = 'Delete Flow';
$lang->workflow->fullTextSearch = 'Full-Text Retrieval';
$lang->workflow->buildIndex     = 'Build Index';
$lang->workflow->custom         = 'Custom';
$lang->workflow->setApproval    = 'Set Approval';
$lang->workflow->setJS          = 'JS';
$lang->workflow->setCSS         = 'CSS';
$lang->workflow->backup         = 'Backup Flow';
$lang->workflow->upgrade        = 'Upgrade Flow';
$lang->workflow->upgradeAction  = 'Upgrade Flow';
$lang->workflow->preview        = 'Preview';
$lang->workflow->design         = 'Design';
$lang->workflow->release        = 'Release';
$lang->workflow->deactivate     = 'Disable';
$lang->workflow->activate       = 'Enable';
$lang->workflow->createApp      = 'New';
$lang->workflow->cover          = 'Cover';
$lang->workflow->approval       = 'Approval';
$lang->workflow->delimiter      = ',';

$lang->workflow->id            = 'ID';
$lang->workflow->parent        = 'Prev';
$lang->workflow->type          = 'Type';
$lang->workflow->app           = 'App';
$lang->workflow->position      = 'Location';
$lang->workflow->module        = 'Module';
$lang->workflow->table         = 'Table';
$lang->workflow->name          = 'Name';
$lang->workflow->titleField    = 'Title Field';
$lang->workflow->contentField  = 'Content Fields';
$lang->workflow->flowchart     = 'Flowchart';
$lang->workflow->ui            = 'UI';
$lang->workflow->js            = 'JS';
$lang->workflow->css           = 'CSS';
$lang->workflow->order         = 'Order';
$lang->workflow->buildin       = 'Built-in';
$lang->workflow->administrator = 'White List';
$lang->workflow->desc          = 'Description';
$lang->workflow->version       = 'Version';
$lang->workflow->status        = 'Status';
$lang->workflow->createdBy     = 'Created By';
$lang->workflow->createdDate   = 'Created';
$lang->workflow->editedBy      = 'Edited By';
$lang->workflow->editedDate    = 'Edited';
$lang->workflow->currentTime   = 'Current Time';

$lang->workflow->actionFlowWidth = 210;

$lang->workflow->copyFlow         = 'Copy';
$lang->workflow->source           = 'Source Flow';
$lang->workflow->field            = 'Field';
$lang->workflow->action           = 'Action';
$lang->workflow->label            = 'Label';
$lang->workflow->mainTable        = 'Main Table';
$lang->workflow->subTable         = 'Sub Table';
$lang->workflow->relation         = 'Relation';
$lang->workflow->report           = 'Report';
$lang->workflow->export           = 'Export';
$lang->workflow->subTableSettings = 'Settings';

$lang->workflow->statusList['wait']   = 'Wait';
$lang->workflow->statusList['normal'] = 'Normal';
$lang->workflow->statusList['pause']  = 'Pause';

$lang->workflow->positionList['before'] = 'Before';
$lang->workflow->positionList['after']  = 'After';

$lang->workflow->buildinList['0'] = 'No';
$lang->workflow->buildinList['1'] = 'Yes';

$lang->workflow->fullTextSearch = new stdclass();
$lang->workflow->fullTextSearch->common       = 'Full-Text Retrieval';
$lang->workflow->fullTextSearch->titleField   = 'Title Field';
$lang->workflow->fullTextSearch->contentField = 'Content Fields';

$lang->workflow->upgrade = new stdclass();
$lang->workflow->upgrade->common         = 'Upgrade';
$lang->workflow->upgrade->backup         = 'Backup';
$lang->workflow->upgrade->backupSuccess  = 'Upgraded';
$lang->workflow->upgrade->newVersion     = 'Get a new version';
$lang->workflow->upgrade->clickme        = 'Upgrade';
$lang->workflow->upgrade->start          = 'Start';
$lang->workflow->upgrade->currentVersion = 'Current Version';
$lang->workflow->upgrade->selectVersion  = 'New Version';
$lang->workflow->upgrade->confirm        = 'Confirm Upgrade SQL';
$lang->workflow->upgrade->upgrade        = 'Upgrade Current Module';
$lang->workflow->upgrade->upgradeFail    = 'Failed!';
$lang->workflow->upgrade->upgradeSuccess = 'Upgraded!';
$lang->workflow->upgrade->install        = 'Install New Module';
$lang->workflow->upgrade->installFail    = 'Failed!';
$lang->workflow->upgrade->installSuccess = 'Installed!';

/* Tips */
$lang->workflow->tips = new stdclass();
$lang->workflow->tips->noCSSTag       = 'No &lt;style&gt;&lt;/style&gt; tag';
$lang->workflow->tips->noJSTag        = 'No &lt;script&gt;&lt;/script&gt;tag';
$lang->workflow->tips->flowCSS        = ', loaded in all pages.';
$lang->workflow->tips->flowJS         = ', loaded in all pages.';
$lang->workflow->tips->actionCSS      = ', loaded in the page of current action.';
$lang->workflow->tips->actionJS       = ', loaded in the page of current action.';
$lang->workflow->tips->deactivate     = 'Are you sure to disable the flow?';
$lang->workflow->tips->create         = 'Nice One! You have successfully created a workflow, Would you like to design your workflow now? ';
$lang->workflow->tips->subTable       = 'If the detailed information is required to fill in the form, use a sub-table to do it. For example, the specifi information is required for requesting the reimbursement. You can add a sub-table "reimbursement details" to the reimbursement request.';
$lang->workflow->tips->flowchart      = 'The decision and result do not control the flow, and set it through the extended actions of the advanced mode.';
$lang->workflow->tips->buildinFlow    = 'The built-in flows can not use quick editor.';
$lang->workflow->tips->fullTextSearch = 'To use the full-text retrieval function, you need to set which fields can be retrieved. The title field has less weight in full-text retrieval, while the content field has more weight. The higher the weight, the higher the search results. <br/>After setting the field, you need to rebuild the index to take effect. The speed of index reconstruction is directly proportional to the amount of content. Please wait patiently for the index reconstruction to complete.';
$lang->workflow->tips->buildIndex     = 'It may take some time to rebuild the index. Are you sure you want to perform the operation?';
$lang->workflow->tips->deleteConfirm  = array('Are you sure you want to delete this workflow?', 'After deleting a workflow, the associated data, such as history and approval records, will be deleted.', 'This operation is irreversible. The deleted content cannot be recovered!');

$lang->workflow->notNow   = 'No,not now';
$lang->workflow->toDesign = 'Yes!Enter Workflow Editor';

/* Title */
$lang->workflow->title = new stdclass();
$lang->workflow->title->subTable   = 'Sub tables are used to record details of %s.';
$lang->workflow->title->noCopy     = 'The build-in flow cannot be copy.';
$lang->workflow->title->noLabel    = 'The build-in flow cannot set labels.';
$lang->workflow->title->noSubTable = 'The build-in flow cannot set sub tables.';
$lang->workflow->title->noRelation = 'The build-in flow cannot set relations.';
$lang->workflow->title->noJS       = 'The build-in flow cannot js.';
$lang->workflow->title->noCSS      = 'The build-in flow cannot css.';

/* Placeholder */
$lang->workflow->placeholder = new stdclass();
$lang->workflow->placeholder->module       = 'Letters only. It cannot be changed once it is saved.';
$lang->workflow->placeholder->titleField   = 'There can only be one title field, which has less weight in full-text retrieval.';
$lang->workflow->placeholder->contentField = 'The content field can have more than one, so it has more weight in full-text retrieval.';

/* Error */
$lang->workflow->error = new stdclass();
$lang->workflow->error->createTableFail = 'Failed to create a table.';
$lang->workflow->error->buildInModule   = 'The flow code should not be same as the built-in module in Zdoo Pro.';
$lang->workflow->error->wrongCode       = '<strong> %s </strong> should be letters.';
$lang->workflow->error->conflict        = '<strong> %s </strong> conflicts with system language.';
$lang->workflow->error->notFound        = 'The flow <strong> %s </strong> not found.';
$lang->workflow->error->flowLimit       = 'You can create %s flows.';
$lang->workflow->error->buildIndexFail  = 'Failed to rebuild index.';

$lang->workflowtable = new stdclass();
$lang->workflowtable->common = 'Sub Table';
$lang->workflowtable->browse = 'View Table';
$lang->workflowtable->create = 'Create Table';
$lang->workflowtable->edit   = 'Edit Table';
$lang->workflowtable->view   = 'View Table';
$lang->workflowtable->delete = 'Delete Table';
$lang->workflowtable->module = 'Code';
$lang->workflowtable->name   = 'Name';

$lang->workfloweditor = new stdclass();
$lang->workfloweditor->nextStep              = 'Next';
$lang->workfloweditor->prevStep              = 'Prev';
$lang->workfloweditor->quickEditor           = 'Quick Editor';
$lang->workfloweditor->advanceEditor         = 'Advanced Editor';
$lang->workfloweditor->switchTo              = '%s';
$lang->workfloweditor->switchConfirmMessage  = 'It will switch to the advanced workflow editor. <br> You can set extensions, design labels and sub-table in advanced editor. <br> Are you sure to switch?';
$lang->workfloweditor->cancelSwitch          = 'Not now';
$lang->workfloweditor->confirmSwitch         = 'Confirm switch';
$lang->workfloweditor->flowchart             = 'Flow Chart';
$lang->workfloweditor->elementCode           = 'Code';
$lang->workfloweditor->elementType           = 'Type';
$lang->workfloweditor->elementName           = 'Name';
$lang->workfloweditor->nameAndCodeRequired   = 'Name and code must be required';
$lang->workfloweditor->uiDesign              = 'UI Design';
$lang->workfloweditor->selectField           = 'Select Field';
$lang->workfloweditor->uiPreview             = 'UI Preview';
$lang->workfloweditor->fieldProperties       = 'Field Properties';
$lang->workfloweditor->uiControls            = 'Controls';
$lang->workfloweditor->showedFields          = 'Exists Fields';
$lang->workfloweditor->selectFieldToEditTip  = 'Select form field to edit here';
$lang->workfloweditor->addFieldOption        = 'Add Option';
$lang->workfloweditor->confirmReleaseMessage = 'You can set extension or labels by the Advanced Editor. Sure to release?';
$lang->workfloweditor->switchMessage         = 'Switch Editor Here';
$lang->workfloweditor->continueRelease       = 'Release';
$lang->workfloweditor->enterToAdvance        = 'Advanced Editor';
$lang->workfloweditor->labelAll              = 'All';
$lang->workfloweditor->confirmToDelete       = 'Are you sure to delete this %s?';
$lang->workfloweditor->startOrStopDuplicated = 'Only one start node and one end node can be added to the chart';
$lang->workfloweditor->leavePageTip          = 'The current page has unsaved changes. Are you sure you want to leave the page?';
$lang->workfloweditor->addFile               = 'Add File';
$lang->workfloweditor->fieldWidth            = 'Column Width';
$lang->workfloweditor->fieldPosition         = 'Text Align';
$lang->workfloweditor->dragDropTip           = 'Drag and drop here';
$lang->workfloweditor->moreSettingsLabel     = 'More Settings';

$lang->workfloweditor->elementTypes = array();
$lang->workfloweditor->elementTypes['start']    = 'Start';
$lang->workfloweditor->elementTypes['process']  = 'Process';
$lang->workfloweditor->elementTypes['decision'] = 'Decision';
$lang->workfloweditor->elementTypes['result']   = 'Result';
$lang->workfloweditor->elementTypes['stop']     = 'Stop';
$lang->workfloweditor->elementTypes['relation'] = 'Relation';

$lang->workfloweditor->defaultFlowchartData = array();
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'start', 'text' => 'Start', 'id' => 'start', 'readonly' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'process', 'text' => 'Create', 'id' => 'create', 'code' => 'create', '_saved' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'process', 'text' => 'Edit', 'id' => 'edit', 'code' => 'edit', '_saved' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'stop', 'text' => 'Stop', 'id' => 'stop', 'readonly' => true);
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'relation', 'from' => 'start', 'to' => 'create', 'id' => 'start-add');
$lang->workfloweditor->defaultFlowchartData[] = array('type' => 'relation', 'from' => 'create', 'to' => 'edit', 'id' => 'create-edit');

$lang->workfloweditor->quickSteps = array();
$lang->workfloweditor->quickSteps['flowchart'] = 'Flow Chart|workflow|flowchart';
$lang->workfloweditor->quickSteps['ui']        = 'UI Design|workflow|ui';

$lang->workfloweditor->advanceSteps = array();
$lang->workfloweditor->advanceSteps['mainTable'] = 'Main Table|workflowfield|browse';
$lang->workfloweditor->advanceSteps['subTable']  = 'Sub Table|workflow|browsedb';
$lang->workfloweditor->advanceSteps['action']    = 'Actions|workflowaction|browse';
$lang->workfloweditor->advanceSteps['label']     = 'Lists|workflowlabel|browse';
$lang->workfloweditor->advanceSteps['setting']   = array('link' => 'More Settings|workflow|more', 'subMenu' => array('workflowrelation' => 'admin', 'workflowfield' => 'setValue,setExport,setSearch', 'workflow' => 'setJS,setCSS,setFulltextSearch', 'workflowreport' => 'browse'));

$lang->workfloweditor->moreSettings = array();
$lang->workfloweditor->moreSettings['approval']  = "Approval|workflow|setapproval|module=%s";
$lang->workfloweditor->moreSettings['relation']  = "Relations|workflowrelation|admin|prev=%s";
$lang->workfloweditor->moreSettings['setReport'] = "Report Settings|workflowreport|browse|module=%s";
$lang->workfloweditor->moreSettings['setValue']  = "Display Values|workflowfield|setValue|module=%s";
$lang->workfloweditor->moreSettings['setExport'] = "Export Settings|workflowfield|setExport|module=%s";
$lang->workfloweditor->moreSettings['setSearch'] = "Search Settings|workflowfield|setSearch|module=%s";
$lang->workfloweditor->moreSettings['fulltext']  = "Full-Text Retrieval|workflow|setFulltextSearch|id=%s";
$lang->workfloweditor->moreSettings['setJS']     = "JS|workflow|setJS|id=%s";
$lang->workfloweditor->moreSettings['setCSS']    = "CSS|workflow|setCSS|id=%s";

if(empty($this->config->openedApproval)) unset($lang->workfloweditor->moreSettings['approval']);

$lang->workfloweditor->validateMessages = array();
$lang->workfloweditor->validateMessages['nameRequired']        = 'Field name is required';
$lang->workfloweditor->validateMessages['nameDuplicated']      = 'The field name has been used, please use a different name';
$lang->workfloweditor->validateMessages['fieldRequired']       = 'Field code is required';
$lang->workfloweditor->validateMessages['fieldInvalid']        = 'Field code can only contain letters';
$lang->workfloweditor->validateMessages['fieldDuplicated']     = 'The field code is the same as the existing field "%s", please use a different code';
$lang->workfloweditor->validateMessages['lengthRequired']      = 'Field length is required';
$lang->workfloweditor->validateMessages['failSummary']         = 'There are %s errors in multiple fields, please modify them before saving.';
$lang->workfloweditor->validateMessages['defaultNotInOptions'] = 'Default value “%s” is not in options';
$lang->workfloweditor->validateMessages['defaultNotOptionKey'] = 'Default value must be a option key, dot not use value "%s"';
$lang->workfloweditor->validateMessages['widthInvalid']        = 'Width value must be number or "auto"';

$lang->workfloweditor->error = new stdclass();
$lang->workfloweditor->error->unknown = 'Unknown error, please retry.';

$lang->workflowapproval = new stdclass();
$lang->workflowapproval->enabled         = 'Enable approval';
$lang->workflowapproval->approval        = 'Appoval';
$lang->workflowapproval->approvalFlow    = 'Appoval Flow';
$lang->workflowapproval->noApproval      = 'There is no approval process available,';
$lang->workflowapproval->createTips      = array('You can', 'You can contact the administrator to create an approval process.');
$lang->workflowapproval->createApproval  = 'Create Approval';
$lang->workflowapproval->waiting         = 'Waiting';
$lang->workflowapproval->conflictField   = 'Fields:';
$lang->workflowapproval->conflictAction  = 'Actions:';
$lang->workflowapproval->openLater       = 'You can also turn approval on or off later in the advanced editor.';
$lang->workflowapproval->disableApproval = 'The flow cannot turn on the approval function.';
$lang->workflowapproval->conflict        = array('Enabled Approval', 'Enabling the approval function requires adding new fields and actions. The system detects conflicts between the following fields and actions:', 'You can click Cancel to resolve the conflict by yourself, such as "modify field code, delete field, delete action", and then re enable the approval function.', 'You can also click cover to resolve the conflict. The system will delete the conflicting fields and actions and add new fields and actions.', 'Note: the cover operation is irreversible, and the deleted fields and actions cannot be restored!');

$lang->workflowapproval->approvalList = array('enabled' => 'Enabled', 'disabled' => 'Disabled');

$lang->workflowapproval->tips = new stdclass();
$lang->workflowapproval->tips->processesInProgress = 'There is an approval process in progress. Please complete or withdraw the approval.';

$lang->workflowapproval->buildInFields = array('name' => array(), 'options' => array());
$lang->workflowapproval->buildInFields['name']['reviewers']     = 'Reviewers';
$lang->workflowapproval->buildInFields['name']['reviewStatus']  = 'Review Status';
$lang->workflowapproval->buildInFields['name']['reviewResult']  = 'Review Result';
$lang->workflowapproval->buildInFields['name']['reviewOpinion'] = 'Review Opinion';

$lang->workflowapproval->buildInFields['options']['reviewStatus'] = array('wait' => 'wait', 'doing' => 'doing', 'pass' => 'pass', 'reject' => 'reject');
$lang->workflowapproval->buildInFields['options']['reviewResult'] = array('pass' => 'pass', 'reject' => 'reject');

$lang->workflowapproval->buildInActions = array('name' => array('submit' => 'submit', 'cancel' => 'cancel', 'review' => 'review'));
