<?php
$lang->block->default['waterfall']['project']['1']['title']  = 'Estimate';
$lang->block->default['waterfall']['project']['1']['block']  = 'waterfallestimate';
$lang->block->default['waterfall']['project']['1']['source'] = 'project';
$lang->block->default['waterfall']['project']['1']['grid']   = 4;

$lang->block->default['waterfall']['project']['3']['title']  = $lang->projectCommon . 'Weekly';
$lang->block->default['waterfall']['project']['3']['block']  = 'waterfallreport';
$lang->block->default['waterfall']['project']['3']['source'] = 'project';
$lang->block->default['waterfall']['project']['3']['grid']   = 8;

$lang->block->default['waterfall']['project']['4']['title']  = $lang->projectCommon . 'Chart';
$lang->block->default['waterfall']['project']['4']['block']  = 'waterfallprogress';
$lang->block->default['waterfall']['project']['4']['grid']   = 4;

$lang->block->default['waterfall']['project']['5']['title']  = $lang->projectCommon . 'Issue';
$lang->block->default['waterfall']['project']['5']['block']  = 'waterfallissue';
$lang->block->default['waterfall']['project']['5']['source'] = 'project';
$lang->block->default['waterfall']['project']['5']['grid']   = 8;

$lang->block->default['waterfall']['project']['5']['params']['type']    = 'all';
$lang->block->default['waterfall']['project']['5']['params']['count']   = '15';
$lang->block->default['waterfall']['project']['5']['params']['orderBy'] = 'id_desc';

$lang->block->default['waterfall']['project']['7']['title']  = $lang->projectCommon . 'Risk';
$lang->block->default['waterfall']['project']['7']['block']  = 'waterfallrisk';
$lang->block->default['waterfall']['project']['7']['source'] = 'project';
$lang->block->default['waterfall']['project']['7']['grid']   = 8;

$lang->block->default['waterfall']['project']['7']['params']['type']    = 'all';
$lang->block->default['waterfall']['project']['7']['params']['count']   = '15';
$lang->block->default['waterfall']['project']['7']['params']['orderBy'] = 'id_desc';

$lang->block->default['scrum']['project']['10']['title']  = $lang->projectCommon . 'Issue';
$lang->block->default['scrum']['project']['10']['block']  = 'waterfallissue';
$lang->block->default['scrum']['project']['10']['source'] = 'project';
$lang->block->default['scrum']['project']['10']['grid']   = 8;

$lang->block->default['scrum']['project']['10']['params']['type']    = 'all';
$lang->block->default['scrum']['project']['10']['params']['count']   = '15';
$lang->block->default['scrum']['project']['10']['params']['orderBy'] = 'id_desc';

$lang->block->default['scrum']['project']['11']['title']  = $lang->projectCommon . 'Risk';
$lang->block->default['scrum']['project']['11']['block']  = 'waterfallrisk';
$lang->block->default['scrum']['project']['11']['source'] = 'project';
$lang->block->default['scrum']['project']['11']['grid']   = 8;

$lang->block->default['scrum']['project']['11']['params']['type']    = 'all';
$lang->block->default['scrum']['project']['11']['params']['count']   = '15';
$lang->block->default['scrum']['project']['11']['params']['orderBy'] = 'id_desc';

$lang->block->modules['waterfall']['index']->availableBlocks->waterfallreport   = $lang->projectCommon . 'Week Report';
$lang->block->modules['waterfall']['index']->availableBlocks->waterfallestimate = 'Estimate';
$lang->block->modules['waterfall']['index']->availableBlocks->waterfallprogress = 'Progress';
$lang->block->modules['waterfall']['index']->availableBlocks->waterfallissue    = $lang->projectCommon . 'Issue';
$lang->block->modules['waterfall']['index']->availableBlocks->waterfallrisk     = $lang->projectCommon . 'Risk';

$lang->block->modules['scrum']['index']->availableBlocks->scrumissue = $lang->projectCommon . 'Issue';
$lang->block->modules['scrum']['index']->availableBlocks->scrumrisk  = $lang->projectCommon . 'Risk';
