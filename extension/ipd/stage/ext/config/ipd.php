<?php
$config->stage->ipdReviewPoint = new stdclass();
$config->stage->ipdReviewPoint->concept   = array('TR1', 'CDCP');
$config->stage->ipdReviewPoint->plan      = array('TR2', 'TR3', 'PDCP');
$config->stage->ipdReviewPoint->develop   = array('TR4', 'TR5');
$config->stage->ipdReviewPoint->qualify   = array('TR6', 'ADCP');
$config->stage->ipdReviewPoint->launch    = array();
$config->stage->ipdReviewPoint->lifecycle = array('LDCP');

$config->stage->ipdStageOrder = array('concept', 'plan', 'develop', 'qualify', 'launch', 'lifecycle');
