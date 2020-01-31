<?php
class Validation
{
  private static $_name = '';
  private static $_request = null;
  private static $_error = false;
  private static $_message = array();
  private static $_method = array();

  public static function validator($m){
    self::$_request = $m;
    return new static();
  }

  public static function post($n){
    self::$_method=$_POST;
    self::$_name=$n;
    self::$_error = false;
    return new static();
  }

  public static function get($n){
    self::$_method=$_GET;
    self::$_name=$n;
    self::$_error = false;
    return new static();
  }

  public static function message($m){
    if(self::$_error){
      self::$_message[self::$_name] = $m;
      self::$_error = false;
    }
    return new static();
  }

  public static function validate($m=null){
    switch(self::$_request){
      case 'ajax':
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'){
          Candy::direct(404);
        }else{
          $result['success']['result'] = count(self::$_message)==0;
          $result['success']['message'] = count(self::$_message)==0 ? $m : '';
          $result['errors'] = self::$_message;
          Candy::return($result);
          if(!$result['success']['result']){
            die();
          }
        }
        break;
      default:
    }
    $GLOBALS['_candy']['oneshot']['_validation'] = self::$_message;
    return self::$_message;
  }

  public static function check($c){
    if(is_bool($c)){
      self::$_error = !$c;
    }else{
      foreach(explode('|',$c) as $key){
        $vars = explode(':',$key);
        if(!self::$_error && !isset(self::$_message[self::$_name])){
          switch($vars[0]){
            case 'required':
              self::$_error = !isset(self::$_method[self::$_name]) || self::$_method[self::$_name]=='' || self::$_method[self::$_name]==null;
              break;
            case 'numeric':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && !is_numeric(self::$_method[self::$_name]);
              break;
            case 'email':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && !filter_var(self::$_method[self::$_name], FILTER_VALIDATE_EMAIL);
              break;
            case 'url':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",self::$_method[self::$_name]));
              break;
            case 'min':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && isset($vars[1]) && self::$_method[self::$_name]<$vars[1];
              break;
            case 'max':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && isset($vars[1]) && self::$_method[self::$_name]>$vars[1];
              break;
            case 'minlen':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && isset($vars[1]) && strlen(self::$_method[self::$_name])<$vars[1];
              break;
            case 'maxlen':
              self::$_error = isset(self::$_method[self::$_name]) && self::$_method[self::$_name]!='' && isset($vars[1]) && strlen(self::$_method[self::$_name])>$vars[1];
              break;
            case 'same':
              self::$_error = isset(self::$_method[self::$_name]) && isset(self::$_method[$vars[1]]) && self::$_method[self::$_name]!==self::$_method[$vars[1]];
              break;
            case 'equal':
              self::$_error = isset(self::$_method[self::$_name]) && isset($vars[1]) && self::$_method[self::$_name]!==$vars[1];
              break;
          }
        }
      }
    }
    return new static();
  }

  public static function result($v){
    return isset($_SESSION['_candy']['oneshot']['_validation'][$v]) ? $_SESSION['_candy']['oneshot']['_validation'][$v] : false;
  }
}
