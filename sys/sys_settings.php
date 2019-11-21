<?php
class Config {
  public function displayError($b = true){
    if($b)ini_set('display_errors', 1);
  }
  public function mysqlServer($s){
    define('MYSQL_SERVER',$s);
  }
  public function mysqlDatabase($s){
    define('MYSQL_DB',$s);
  }
  public function mysqlUsername($s){
    define('MYSQL_USER',$s);
  }
  public function mysqlPassword($s){
    define('MYSQL_PASS',$s);
  }
  public function mysqlConnection($b = true){
    define('MYSQL_CONNECT',$b);
  }
}
$config = new Config();
include('config.php');
