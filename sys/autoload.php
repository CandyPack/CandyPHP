<?php
session_start();

require_once('Lang.php');
require_once('Config.php');
require_once('Storage.php');
require_once('Candy.php');
require_once('View.php');
require_once('Cron.php');
require_once('Route.php');
Candy::configCheck();
Route::print();
