<?php

namespace Candy;

class Variable{
  function __construct($string=''){
    $this->str = $string;
    $this->any = false;
  }

  public function clear($arr){
    $replace = [];
    $arr = is_array($arr) ? $arr : func_get_args();
    if(is_array($this->str)) {
      foreach ($arr as $val) if(($key = array_search($val, $this->str)) !== false) unset($this->str[$key]);
      return $this->str;
    } else {
      foreach ($arr as $key) $replace[$key] = '';
      return self::replace($replace);
    }
  }

  public function contains($val){
    $any = $this->any;
    $this->any = false;
    if(!is_array($val)) $val = [$val];
    $result = !$any;
    foreach($val as $key){
      if($any) $result = $result || (strpos($this->str, $key) !== false);
      else     $result = $result && (strpos($this->str, $key) !== false);
    }
    return $result;
  }

  public function date($format){
    return date($format,strtotime($this->str));
  }

  public function containsAny($val){
    $this->any = true;
    return $this->contains($val);
  }

  public function is($val){
    $any = $this->any;
    $this->any = false;
    $val = is_array($val) ? $val : func_get_args();
    $result = !$any;
    if(in_array('alpha',        $val)) $result = (($result || $any) && (($any && $result) || ctype_alpha($this->str) ));
    if(in_array('alphanumeric', $val)) $result = (($result || $any) && (($any && $result) || ctype_alnum($this->str) ));
    if(in_array('domain',       $val)) $result = (($result || $any) && (($any && $result) || preg_match('/([a-z0-9\-]+\.){1,2}[a-z]{2,6}/i', $this->str) ));
    if(in_array('email',        $val)) $result = (($result || $any) && (($any && $result) || filter_var($this->str, FILTER_VALIDATE_EMAIL) ));
    if(in_array('float',        $val)) $result = (($result || $any) && (($any && $result) || filter_var($this->str, FILTER_VALIDATE_FLOAT) ));
    if(in_array('host',         $val)) $result = (($result || $any) && (($any && $result) || filter_var((\Candy::string($this->str)->is('ip') ? $this->str : gethostbyname($this->str)), FILTER_VALIDATE_IP) ));
    if(in_array('ip',           $val)) $result = (($result || $any) && (($any && $result) || filter_var($this->str, FILTER_VALIDATE_IP) ));
    if(in_array('json',         $val)) $result = (($result || $any) && (($any && $result) || (json_decode($this->str) && json_last_error() === JSON_ERROR_NONE) ));
    if(in_array('mac',          $val)) $result = (($result || $any) && (($any && $result) || filter_var($this->str, FILTER_VALIDATE_MAC) ));
    if(in_array('md5',          $val)) $result = (($result || $any) && (($any && $result) || (bool)preg_match('/^[a-f0-9A-F]{32}$/', $this->str) ));
    if(in_array('numeric',      $val)) $result = (($result || $any) && (($any && $result) || is_numeric($this->str) ));
    if(in_array('url',          $val)) $result = (($result || $any) && (($any && $result) || preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$this->str) ));
    return $result;
  }

  public function isAny($val){
    $this->any = true;
    return $this->is(is_array($val) ? $val : func_get_args());
  }

  public function isBegin($var){
    $str = $this->str;
    return substr($str,0,strlen($var)) == $var;
  }

  public function isEnd($var){
    $str = $this->str;
    return substr($str,0 - strlen($var)) == $var;
  }

  public function replace($arr){
    return \str_replace(array_keys($arr),$arr,$this->str);
  }

  public function slug(){
    $str = $this->str;
    $str = preg_replace('~[^\pL\d]+~u', '-', $str);
    $str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);
    $str = preg_replace('~[^-\w]+~', '', $str);
    $str = trim($str, '-');
    $str = preg_replace('~-+~', '-', $str);
    $str = strtolower($str);
    if(empty($str)) return '';
    return $str;
  }

  public function format($format){
    $str = $this->str;
    $result = '';
    $letter = 0;
    for ($i=0; $i < strlen($format); $i++) {
      if(substr($format,$i,1)=='?'){
        $result .= substr($str,$letter,1);
        $letter = $letter + 1;
      }elseif(substr($format,$i,1)=='*'){
        $result .= substr($str,$letter);
        $letter = $letter + strlen(substr($str,$letter));
      }else{
        $result .= substr($format,$i,1);
      }
    }
    return $result;
  }
}
