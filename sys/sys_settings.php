<?php
class Config {
  public function displayError($b = true){
    $b ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
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
  public function languageDetect($b  = true){
    $langg = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if(file_exists("lang/lang_{$langg}.php")){
      require_once "lang/lang_{$langg}.php";
      Lang::setArray($lang);
    }elseif(file_exists("lang/lang.php")){
      require_once "lang/lang.php";
      Lang::setArray($lang);
    }
  }
}
$config = new Config();
include('config.php');
