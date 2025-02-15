<?php
$config->task->listFields     = "module,assignedTo,mode,story,pri,type";
$config->task->sysListFields  = "module,story";
$config->task->templateFields = "execution,module,story,assignedTo,mode,name,desc,type,pri,estimate,estStarted,deadline";

$config->task->exportFields = '
    id, project, execution, module, story, fromBug,
    name, desc,
    type, pri, estStarted, realStarted, deadline, status,estimate, consumed, left,
    mailto, progress, mode,
    openedBy, openedDate, assignedTo, assignedDate,
    finishedBy, finishedDate, canceledBy, canceledDate,
    closedBy, closedDate, closedReason,
    lastEditedBy, lastEditedDate,files
    ';
