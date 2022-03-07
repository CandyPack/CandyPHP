<?php
session_start();

require_once(BASE_PATH.'/sys/Mysql.php');
require_once(BASE_PATH.'/sys/Lang.php');
require_once(BASE_PATH.'/sys/Candy.php');
require_once(BASE_PATH.'/sys/Config.php');
require_once(BASE_PATH.'/sys/View.php');
require_once(BASE_PATH.'/sys/Cron.php');
require_once(BASE_PATH.'/sys/Route.php');
require_once(BASE_PATH.'/sys/Auth.php');

Candy::config()->start();
Route::print();
