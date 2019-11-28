<?php
class Lang {
  public $lang;

  public function setVar($var,$text){
    global $lang;
    return $lang[$var] = $text;
  }

  public function getVar($var){
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
