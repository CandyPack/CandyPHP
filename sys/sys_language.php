<?php
class Lang {
  public $lang;

  public static function get($var, $arr = null){
    $return =isset($GLOBALS['_lang'][$var]) ? $GLOBALS['_lang'][$var] : "";
    if($array!=null){
      if(is_array($arr)){
        $loop = 0;
        foreach ($arr as $key) {
          $return = str_replace('$'.$loop, $key, $return);
          $loop++;
        }
      }else{
        $return = str_replace('$0', $arr, $return);
      }
    }
    return $return;
  }

  public static function echo($var){
    $return = isset($GLOBALS['_lang'][$var]) ? $GLOBALS['_lang'][$var] : "";
    if($array!=null){
      if(is_array($arr)){
        $loop = 0;
        foreach ($arr as $key) {
          $return = str_replace('$'.$loop, $key, $return);
          $loop++;
        }
      }else{
        $return = str_replace('$0', $arr, $return);
      }
    }
    echo $return;
  }

  public static function setArray($arr){
    $GLOBALS['_lang'] = $arr;
  }

  public static function set($code){
    function returnLang($l){
      return require_once "lang/{$l}.php";
    }
    if(file_exists("lang/{$code}.php")){
      Lang::setArray(returnLang($code));
    }elseif(file_exists("lang/lang.php")){
      Lang::setArray(returnLang('lang'));
    }
  }

}
