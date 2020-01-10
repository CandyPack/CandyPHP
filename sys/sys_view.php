<?php
class View {
  public $_parts = array();
  public static function head($v){
    //define('VIEW_HEAD',$v);
    return self::set('head',$v);
  }
  public static function header($v){
    //define('VIEW_HEADER',$v);
    return self::set('header',$v);
  }
  public static function sidebar($v){
    //define('VIEW_SIDEBAR',$v);
    return self::set('sidebar',$v);
  }
  public static function content($v){
    //define('VIEW_CONTENT',$v);
    return self::set('content',$v);
  }
  public static function footer($v){
    //define('VIEW_FOOTER',$v);
    return self::set('footer',$v);
  }
  public static function script($v){
    //define('VIEW_SCRIPT',$v);
    return self::set('script',$v);
  }
  public function skeleton($v){
    //define('VIEW_SKELETON',$v);
    return self::set('skeleton',$v);
  }
  public function set($c,$v){
    global $_parts;
    define('VIEW_'.strtoupper($c),$v);
    $_parts[$c] = $v;
    return new static();
  }
  public function printView(){
    global $candy;
    global $route;
    global $conn;

    function get($v){
      global $candy;
      return $candy->get($v);
    }
    if(defined('VIEW_SKELETON')){
    $skeleton = defined('VIEW_SKELETON') ? 'skeleton/'.VIEW_SKELETON.'.skeleton' : 'skeleton/page.skeleton';
    $skeleton = file_get_contents($skeleton, FILE_USE_INCLUDE_PATH);
    $arr_test = explode('{{', $skeleton);
      foreach ($arr_test as $key) {
        if(strpos($key, '}}') !== false) {
          $arr_key = explode('}}',$key);
          if(defined('VIEW_'.trim($arr_key[0]))){
            if(file_exists('view/'.strtolower(trim($arr_key[0])).'_'.constant('VIEW_'.trim($arr_key[0])).'.php')){
              include('view/'.strtolower(trim($arr_key[0])).'_'.constant('VIEW_'.trim($arr_key[0])).'.php');
            }
          }
          if(isset($arr_key[1])){
            print($arr_key[1]);
          }
        }else{
          print($key);
        }
      }
    }
  }
}
$view = new View();
