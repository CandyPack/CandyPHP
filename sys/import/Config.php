<?php

namespace Candy;

class Config {
  private static $storage = [];
  private static $data = [];
  private static $vars = [];

  function __construct(){
    self::$vars = is_array(func_get_args()[0]) ? func_get_args()[0] : func_get_args();
  }

  public static function get(){
    if(!self::$storage){
      self::$storage = \Candy::storage('sys')->get('candy',true);
      self::$data = array_replace_recursive((is_array(self::$storage) ? self::$storage : []),self::$data);
    }
    $val = self::$data;
    foreach (self::$vars as $key) $val = isset($val[$key]) ? $val[$key] : null;
    return $val;
  }

  public static function set($val=null){
    $key = $val;
    foreach (array_reverse(self::$vars) as $value) $key = [$value => $key];
    self::$data = array_replace_recursive(self::$data,$key);
    return true;
  }

  public static function save($val=null){
    $key = $val;
    foreach (array_reverse(self::$vars) as $value) $key = [$value => $key];
    self::$data = array_replace_recursive(self::$data,$key);
    self::$storage = array_replace_recursive(self::$storage,$key);
    \Candy::storage('sys')->set('candy',self::$storage);
    return true;
  }

  public static function start(){
    header('X-POWERED-BY: Candy PHP');
    register_shutdown_function(function(){
      $error = error_get_last();
      if(!empty($error)){
        $types = [
          1    => 'Fatal',
          2    => 'Warning',
          4    => 'Syntax',
          8    => 'Notice',
          256  => 'User',
          512  => 'User Warning',
          1024 => 'User Notice',
          2048 => 'Strictly'
        ];
        $type = 'PHP '.(isset($types[$error["type"]]) ? $types[$error["type"]] : 'Unknown');
        \Config::errorReport($type,$error["message"],$error["file"],$error["line"]);
      }
    });
    $config = file_get_contents(BASE_PATH.'/config.php',FILE_USE_INCLUDE_PATH);
    include(BASE_PATH.'/config.php');
    if(defined('MYSQL_CONNECT') && MYSQL_CONNECT==true) \Mysql::connect();
    \Config::runBackup();
    \Config::runUpdate();
    \Config::backupClear();
    if(!defined('CANDY_COMPOSER') || (defined('CANDY_COMPOSER') && CANDY_COMPOSER)){
      if(defined('CANDY_COMPOSER_DIRECTORY')){
        include(CANDY_COMPOSER_DIRECTORY);
      }elseif(file_exists('../vendor/autoload.php')){
        include('../vendor/autoload.php');
      }elseif(file_exists('vendor/autoload.php')){
        include('vendor/autoload.php');
      }
    }
    if(intval(date('d'))==2 && intval(date('H'))<=2) foreach(glob(BASE_PATH."/storage/cache/*") as $key) if(!is_dir($key) && filemtime($key)+10000 < time()) unlink($key);
  }
}
