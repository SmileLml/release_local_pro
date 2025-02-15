<?php
$config->ipdVersion = '1.4';

define('TABLE_DEMANDPOOL',     '`' . $config->db->prefix . 'demandpool`');
define('TABLE_DEMAND',         '`' . $config->db->prefix . 'demand`');
define('TABLE_DEMANDSPEC',     '`' . $config->db->prefix . 'demandspec`');
define('TABLE_DEMANDREVIEW',   '`' . $config->db->prefix . 'demandreview`');
define('TABLE_ROADMAP',        '`' . $config->db->prefix . 'roadmap`');
define('TABLE_ROADMAPSTORY',   '`' . $config->db->prefix . 'roadmapstory`');
define('TABLE_CHARTER',        '`' . $config->db->prefix . 'charter`');
define('TABLE_MARKET',         '`' . $config->db->prefix . 'market`');
define('TABLE_MARKETREPORT',   '`' . $config->db->prefix . 'marketreport`');
define('TABLE_MARKETRESEARCH', '`' . $config->db->prefix . 'project`');

$config->objectTables['demand']         = TABLE_DEMAND;
$config->objectTables['demandpool']     = TABLE_DEMANDPOOL;
$config->objectTables['demandspec']     = TABLE_DEMANDSPEC;
$config->objectTables['demandreview']   = TABLE_DEMANDREVIEW;
$config->objectTables['roadmap']        = TABLE_ROADMAP;
$config->objectTables['charter']        = TABLE_CHARTER;
$config->objectTables['market']         = TABLE_MARKET;
$config->objectTables['marketreport']   = TABLE_MARKETREPORT;
$config->objectTables['marketresearch'] = TABLE_PROJECT;
$config->objectTables['researchstage']  = TABLE_PROJECT;

$filter->charter = new stdclass();
$filter->charter->default = new stdclass();
$filter->charter->default->cookie['browseType'] = 'reg::word';

$filter->marketreport = new stdclass();
$filter->marketreport->browse = new stdclass();
$filter->marketreport->all    = new stdclass();
$filter->marketreport->browse->cookie['involvedReport'] = 'code';
$filter->marketreport->all->cookie['involvedReport']    = 'code';

$filter->marketresearch = new stdclass();
$filter->marketresearch->browse  = new stdclass();
$filter->marketresearch->all     = new stdclass();
$filter->marketresearch->reports = new stdclass();
$filter->marketresearch->browse->cookie['involvedResearch'] = 'code';
$filter->marketresearch->all->cookie['involvedResearch']    = 'code';
$filter->marketresearch->reports->cookie['involvedReport']  = 'code';

$filter->demand = new stdclass();

$filter->demand->browse = new stdclass();
$filter->demand->export = new stdclass();

$filter->demand->browse->cookie['requirementModule'] = 'int';
$filter->demand->export->cookie['checkedItem']       = 'reg::checked';

$config->openMethods[] = 'demand.showimport';
$config->openMethods[] = 'story.showimport';

if($config->edition == 'ipd') $config->featureGroup->product = array('roadmap', 'track');
