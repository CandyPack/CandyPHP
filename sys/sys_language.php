<?php
class Lang {
  public $lang;

  public static function get($var){
    return $GLOBALS['_lang'][$var];
  }

  public static function echo($var){
    echo $GLOBALS['_lang'][$var];
  }

  public static function setArray($arr){
    $GLOBALS['_lang'] = $arr;
  }
}
