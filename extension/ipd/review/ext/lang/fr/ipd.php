<?php
$lang->review->begin = 'Begin';

$lang->review->reviewPoint = new stdclass();
$lang->review->reviewPoint->titleList['TR1']  = 'TR1-Product requirements and concept review';
$lang->review->reviewPoint->titleList['TR2']  = 'TR2-Requirement decomposition and specification review';
$lang->review->reviewPoint->titleList['TR3']  = 'TR3-Overall scheme review';
$lang->review->reviewPoint->titleList['TR4']  = 'TR4-Module/system review';
$lang->review->reviewPoint->titleList['TR5']  = 'TR5-Prototype review';
$lang->review->reviewPoint->titleList['TR6']  = 'TR6-Small batch production review';
$lang->review->reviewPoint->titleList['CDCP'] = 'CDCP-Concept decision review';
$lang->review->reviewPoint->titleList['PDCP'] = 'PDCP-Planning decision review';
$lang->review->reviewPoint->titleList['ADCP'] = 'ADCP-Attainability decision review';
$lang->review->reviewPoint->titleList['LDCP'] = 'LDCP-End-of-life decision review';

$lang->review->stageNotStartTip   = 'The stage of the review point is still the beginning, and the review cannot be initiated for the time being';
$lang->review->prePointNotPassTip = 'The current review point can initiate a review only after the previous review point has passed the review';

$lang->review->errorLetter = "Deadline cannot be less than the plan start time";
