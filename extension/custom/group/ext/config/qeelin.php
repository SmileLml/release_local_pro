<?php

$config->group->package->manageBug->privs['bug-batchAdjust']        = array('edition' => 'open,biz,max,ipd', 'vision' => 'rnd', 'order' => 20, 'depend' => array('bug-edit'), 'recommend' => array());
$config->group->package->manageTesttask->privs['testtask-copy']     = array('edition' => 'open,biz,max,ipd', 'vision' => 'rnd', 'order' => 13, 'depend' => array('testtask-create'), 'recommend' => array());
$config->group->package->importCaseLib->privs['caselib-exportCase'] = array('edition' => 'open,biz,max,ipd', 'vision' => 'rnd', 'order' => 45, 'depend' => array('caselib-browse'), 'recommend' => array());
$config->group->package->manageProject->privs['project-collect']    = array('edition' => 'open,biz,max,ipd', 'vision' => 'rnd', 'order' => 10, 'depend' => array('project-browse'), 'recommend' => array());
$config->group->package->createProduct->privs['product-collect']    = array('edition' => 'open,biz,max,ipd', 'vision' => 'rnd', 'order' => 80, 'depend' => array('product-all'), 'recommend' => array());