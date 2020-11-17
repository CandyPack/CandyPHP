<?php
session_start();

require_once('sys_language.php');
require_once('sys_settings.php');
require_once('sys_storage.php');
require_once('sys_functions.php');
require_once('sys_view.php');
require_once('sys_cron.php');
require_once('sys_route.php');
Candy::configCheck();
Route::print();
