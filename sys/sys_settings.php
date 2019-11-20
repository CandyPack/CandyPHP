<?php
class Config {
  public function displayError($b = true){
    if($b)ini_set('display_errors', 1);
  }
  public function mysqlServer($s){
    definde(MYSQL_SERVER,$s);
  }
  public function mysqlDatabase($s){
    definde(MYSQL_DB,$s);
  }
  public function mysqlUsername($s){
    definde(MYSQL_USER,$s);
  }
  public function mysqlPassword($s){
    definde(MYSQL_PASS,$s);
  }
  public function mysqlConnection($s){
    definde(MYSQL_PASS,$s);
  }
}
$config = new Config();
include('config.php');
