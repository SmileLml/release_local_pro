<?php
$config->execution->custom->customGanttFields = 'id,branch,assignedTo,progress,begin,realBegan,deadline,realEnd,duration,estimate,consumed,left,delay,delayDays,openedBy,finishedBy';

$config->execution->ganttCustom = new stdclass();
$config->execution->ganttCustom->ganttFields = 'assignedTo,progress,begin,deadline,duration';

