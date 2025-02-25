<?php

$config->task->exportFields .= ', levelType';

$config->task->copyActionCommentType = ['started', 'edited', 'paused', 'restarted', 'finished', 'assigned', 'closed', 'activated', 'canceled'];