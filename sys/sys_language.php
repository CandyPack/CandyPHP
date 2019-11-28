<?php
class Lang {
  public $lang;

  public function setVar($var,$text){
    return $lang[$var] = $text;
  }

  public function getVar($var){
    return $lang[$var];
  }
}
