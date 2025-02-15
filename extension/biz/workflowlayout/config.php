<?php
$config->workflowlayout->noTotalFields = 'id,parent';

$config->workflowlayout->disabledFields['view']        = 'parent,deleted';
$config->workflowlayout->disabledFields['browse']      = 'parent,deleted,files';
$config->workflowlayout->disabledFields['create']      = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['batchcreate'] = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['batchedit']   = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['batchassign'] = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['edit']        = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['assign']      = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['delete']      = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate,actions,files';
$config->workflowlayout->disabledFields['custom']      = 'id,parent,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate';
$config->workflowlayout->disabledFields['subTables']   = 'id,parent,status,subStatus,assignedTo,createdBy,createdDate,editedBy,editedDate,deleted,assignedBy,assignedDate,actions,files';

$config->workflowlayout->default = new stdclass();
$config->workflowlayout->default->required = array();
$config->workflowlayout->default->required['browse'] = array('actions');

$config->workflowlayout->approval = new stdclass();
$config->workflowlayout->approval->required = array();
$config->workflowlayout->approval->required['approvalreview'] = array('reviewResult', 'reviewOpinion');

$config->workflowlayout->approval->layouts = array();
$config->workflowlayout->approval->layouts['approvalreview'] = array();
$config->workflowlayout->approval->layouts['approvalreview']['reviewResult']  = array('default' => 'pass');
$config->workflowlayout->approval->layouts['approvalreview']['reviewOpinion'] = array('require' => true);

$config->workflowlayout->buildin = new stdclass();
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['id']              = array('width' => 80,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['customer']        = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['product']         = array('width' => 130,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['plan']            = array('width' => 90,     'mobileShow' => 0, 'position' => 'right', 'summary' => 'sum');
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['real']            = array('width' => 90,     'mobileShow' => 1, 'position' => 'right', 'summary' => 'sum');
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['assignedTo']      = array('width' => 90,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['status']          = array('width' => 70,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['contactedDate']   = array('width' => 90,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['nextDate']        = array('width' => 110,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['order']['browse']['actions']         = array('width' => 160,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['id']          = array('width' => 60,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['code']        = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['name']        = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['amount']      = array('width' => 100,    'mobileShow' => 1, 'position' => 'right', 'summary' => 'sum');
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['createdDate'] = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['begin']       = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['end']         = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['return']      = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['delivery']    = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['status']      = array('width' => 70,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['contract']['browse']['actions']     = array('width' => 240,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['id']          = array('width' => 60,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['code']        = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['name']        = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['amount']      = array('width' => 100,    'mobileShow' => 1, 'position' => 'right', 'summary' => 'sum');
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['createdDate'] = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['begin']       = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['end']         = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['return']      = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['delivery']    = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['status']      = array('width' => 70,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['purchasecontract']['browse']['actions']     = array('width' => 240,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['id']              = array('width' => 100,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['name']            = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['assignedTo']      = array('width' => 70,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['level']           = array('width' => 70,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['size']            = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['type']            = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['status']          = array('width' => 70,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['createdDate']     = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['contactedDate']   = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['nextDate']        = array('width' => 110,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['customer']['browse']['actions']         = array('width' => 200,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['id']          = array('width' => 60,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['name']        = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['size']        = array('width' => 110,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['type']        = array('width' => 70,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['area']        = array('width' => 160,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['industry']    = array('width' => 150,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['createdDate'] = array('width' => 110,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['provider']['browse']['actions']     = array('width' => 200,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['id']       = array('width' => 60,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['realname'] = array('width' => 100,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['customer'] = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['gender']   = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['phone']    = array('width' => 200,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['email']    = array('width' => 200,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['qq']       = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['weixin']   = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['contact']['browse']['actions']  = array('width' => 200,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['id']              = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['realname']        = array('width' => 80,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['nextDate']        = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['company']         = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['gender']          = array('width' => 60,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['phone']           = array('width' => 160,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['email']           = array('width' => 160,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['qq']              = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['weixin']          = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['origin']          = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['leads']['browse']['actions']         = array('width' => 200,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['product']['browse']['id']       = array('width' => 60,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['name']     = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['code']     = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['category'] = array('width' => 100,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['subject']  = array('width' => 120,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['model']    = array('width' => 70,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['unit']     = array('width' => 70,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['amount']   = array('width' => 70,     'mobileShow' => 0, 'position' => 'right');
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['type']     = array('width' => 60,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['status']   = array('width' => 70,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['product']['browse']['actions']  = array('width' => 80,     'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['id']           = array('width' => 60,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['serialNumber'] = array('width' => 60,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['sn']           = array('width' => 160,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['customer']     = array('width' => 180,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['money']        = array('width' => 100,    'mobileShow' => 1, 'position' => 'right', 'summary' => 'sum');
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['type']         = array('width' => 160,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['saleType']     = array('width' => 80,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['status']       = array('width' => 80,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['desc']         = array('width' => 'auto', 'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['invoice']['browse']['actions']      = array('width' => 220,    'mobileShow' => 0);

$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['id']          = array('width' => 60,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['pri']         = array('width' => 40,     'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['title']       = array('width' => 'auto', 'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['product']     = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['customer']    = array('width' => 200,    'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['contact']     = array('width' => 120,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['createdDate'] = array('width' => 150,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['assignedTo']  = array('width' => 100,    'mobileShow' => 0);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['status']      = array('width' => 80,     'mobileShow' => 1);
$config->workflowlayout->buildin->layouts['crm']['feedback']['browse']['actions']     = array('width' => 160,    'mobileShow' => 0);
