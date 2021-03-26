<?php

namespace Candy;

class Str{
  function __construct($string=''){
    $this->str = $string;
    $this->any = false;
  }

  public function is($val){
    $any = $this->any;
    $this->any = false;
    if(!is_array($val)) $val = [$val];
    $result = false;
    if(in_array('json',$val)){
      json_decode($this->str);
      if($any) $result = $result || json_last_error() === JSON_ERROR_NONE;
      else     $result = $result && json_last_error() === JSON_ERROR_NONE;
    }
    if(in_array('md5',$val)){
      if($any) $result = $result || ((bool) preg_match('/^[a-f0-9A-F]{32}$/', $this->str));
      else     $result = $result && ((bool) preg_match('/^[a-f0-9A-F]{32}$/', $this->str));
    }
    if(in_array('numeric',$val)){
      if($any) $result = $result || is_numeric($this->str);
      else     $result = $result && is_numeric($this->str);
    }
    return $result;
  }

  public function isAny($val){
    $this->any = true;
    return $this->is($val);
  }

  public function contains($val){
    $any = $this->any;
    $this->any = false;
    if(!is_array($val)) $val = [$val];
    $result = false;
    foreach($val as $key){
      if($any) $result = $result || (strpos($this->str, $key) !== false);
      else     $result = $result && (strpos($this->str, $key) !== false);
    }
    return $result;
  }

  public function containsAny($val){
    $this->any = true;
    return $this->contains($val);
  }

  public function replace($arr){
    $old = [];
    $new = [];
    foreach ($arr as $key => $val){
      $old[] = $key;
      $new[] = $key;
    }
    return \str_replace($old,$new,$this->str);
  }
}
