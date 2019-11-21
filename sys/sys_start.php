<?php
session_start();

require_once('sys_settings.php');
require_once('sys_functions.php');
require_once('sys_view.php');
require_once('sys_route.php');
$candy->configCheck();
require_once('route/route_check.php');
