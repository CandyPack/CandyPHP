<?php
class Lang {
  public $lang;

  public static function get($var, $arr = null){
    $return =isset($GLOBALS['_lang'][$var]) ? $GLOBALS['_lang'][$var] : "";
    if($array!==null){
      if(is_array($arr)){
        $loop = 0;
        foreach($arr as $key){
          $return = str_replace('$'.$loop, $key, $return);
          $loop++;
        }
      }else{
        $parameters = func_get_args();
        unset($parameters[0]);
        if(count($parameters) > 0) return call_user_func_array("sprintf", array_values($parameters));
      }
    }
    return $return;
  }

  public static function echo($var,$arr=null){
    echo self::get($var,$arr);
  }

  public static function setArray($arr){
    $GLOBALS['_lang'] = $arr;
  }

  public static function set($code){
    function returnLang($l){
      return include "lang/$l.php";
    }
    if(file_exists("lang/$code.php")){
      Lang::setArray(returnLang($code));
    }elseif(file_exists("lang/".$GLOBALS['_candy']['language']['default'].".php")){
      Lang::setArray(returnLang($GLOBALS['_candy']['language']['default']));
    }
  }

}
