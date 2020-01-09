<?php
class Lang {
  public $lang;

  public function get($var){
    global $lang;
    return $lang[$var];
  }

  public function echo($var){
    global $lang;
    echo $lang[$var];
  }

  public function setArray($arr){
    global $lang;
    $lang = $arr;
  }
}
