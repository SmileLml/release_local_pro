<?php
$config->openMethods[] = 'vm.register';
$config->openMethods[] = 'host.register';

$config->routes['/vm/register']   = 'vm';
$config->routes['/host/register'] = 'host';
