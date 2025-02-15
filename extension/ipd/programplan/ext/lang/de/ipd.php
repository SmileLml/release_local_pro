<?php
$lang->programplan->stageCustom->point = 'Show Point';

$lang->programplan->parallel = 'Phase whether parallel is allowed';

$lang->programplan->parallelTip = "Not supporting parallelism means that the next stage can't start until the previous stage is complete, for example, the planning stage can't start until the concept stage is complete, including the tasks under the stage.
Parallelism means that there is no dependency between phases, and the beginning state of phases and tasks is not affected by the state of other phases.";

$lang->programplan->parallelList[0] = 'No';
$lang->programplan->parallelList[1] = 'Yes';

$lang->programplan->error->outOfDate  = 'The start time of the plan should be greater than the end time of the previous phase.';
$lang->programplan->error->lessOfDate = 'The end time of the plan should be less than the start time of the next phase.';
