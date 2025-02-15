<?php
$config->todo->moduleList[] = 'feedback';

$config->todo->getUserObjectsMethod['feedback'] = 'ajaxGetUserFeedback';

$config->todo->objectList['feedback'] = 'feedbacks';
