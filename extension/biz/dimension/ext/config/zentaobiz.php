<?php
$config->dimension->create = new stdclass();
$config->dimension->edit   = new stdclass();
$config->dimension->create->requiredFields = 'name,code';
$config->dimension->edit->requiredFields   = 'name,code';

$config->dimension->changeDimensionLink['screen-design']    = 'screen|browse|dimensionID=%s';
$config->dimension->changeDimensionLink['pivot-browse']     = 'pivot|browse|dimensionID=%s';
$config->dimension->changeDimensionLink['pivot-design']     = 'pivot|browse|dimensionID=%s';
$config->dimension->changeDimensionLink['chart-browse']     = 'chart|browse|dimensionID=%s';
$config->dimension->changeDimensionLink['chart-create']     = 'chart|create|dimensionID=%s';
$config->dimension->changeDimensionLink['chart-edit']       = 'chart|browse|dimensionID=%s';
$config->dimension->changeDimensionLink['chart-design']     = 'chart|browse|dimensionID=%s';
$config->dimension->changeDimensionLink['tree-browsegroup'] = 'tree|browsegroup|dimensionID=%s&groupID=0&type=%s';
