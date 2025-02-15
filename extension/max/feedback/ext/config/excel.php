<?php
$config->feedback->listFields     = "module,product,type,public,notify,pri";
$config->feedback->sysListFields  = "module,product,type,public,notify,pri";
$config->feedback->templateFields = 'product,module,type,title,pri,desc,source,feedbackBy,notifyEmail,public,notify';
$config->feedback->cascade        = array('module' => 'product');