<?php
$config->sqlbuilder = new stdclass();

$config->sqlbuilder->sqlviewPrefix = 'ztv_';
$config->sqlbuilder->defaultAction = 'CREATE OR REPLACE VIEW ztv_ AS ';

$config->sqlbuilder->createsqlview = new stdclass();
$config->sqlbuilder->createsqlview->requiredFields = 'name,code,sql';

$config->sqlbuilder->tableList = array();

$config->sqlbuilder->filterTableList = array();
