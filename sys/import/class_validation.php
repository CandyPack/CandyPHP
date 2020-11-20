<?php
class Validation
{
  private $_name = '';
  private $_request = null;
  private $_error = false;
  private $_message = [];
  private $_method = [];

  function __construct($name='',$request=null,$error=false,$message=[],$method=[]){
    $this->_name = $name;
    $this->_request = $request;
    $this->_error = $error;
    $this->_message = $message;
    $this->_method = $method;
  }

  function validator($m){
    $this->_request = $m;
    return new static($this->_name,$this->_request,$this->_error,$this->_message,$this->_method);
  }

  function post($n){
    $this->_method=$_POST;
    $this->_name=$n;
    $this->_error = false;
    return new static($this->_name,$this->_request,$this->_error,$this->_message,$this->_method);
  }

  function get($n){
    $this->_method=$_GET;
    $this->_name=$n;
    $this->_error = false;
    return new static($this->_name,$this->_request,$this->_error,$this->_message,$this->_method);
  }

  function message($m){
    if($this->_error){
      $this->_message[$this->_name] = $m;
      $this->_error = false;
    }
    return new static($this->_name,$this->_request,$this->_error,$this->_message,$this->_method);
  }

  function brute($try=5){
    $ip = $_SERVER['REMOTE_ADDR'];
    $now = substr(date('YmdHi'),0,-1);
    $page = PAGE;
    $storage = Candy::storage('sys')->get('validation');
    $this->_name='_candy_form';
    if(count($this->_message) > 0){
      $storage->brute                   = isset($storage->brute)                   ? $storage->brute : new \stdClass;
      $storage->brute->$now             = isset($storage->brute->$now)             ? $storage->brute->$now : new \stdClass;
      $storage->brute->$now->$page      = isset($storage->brute->$now->$page)      ? $storage->brute->$now->$page : new \stdClass;
      $storage->brute->$now->$page->$ip = isset($storage->brute->$now->$page->$ip) ? ($storage->brute->$now->$page->$ip + 1) : 1;
      $this->_error = $storage->brute->$now->$page->$ip >= $try;
    }else{
      $this->_error = isset($storage->$now->$ip) ? $storage->$now->$ip >= $try : false;
    }

    Candy::storage('sys')->set('validation',$storage);
    return new static($this->_name,$this->_request,$this->_error,$this->_message,$this->_method);
  }

  function validate($m=null,$data = []){
    switch($this->_request){
      case 'ajax':
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'){
          Candy::abort(404);
        }else{
          $result['success']['result'] = count($this->_message)==0;
          $result['success']['message'] = count($this->_message)==0 ? $m : '';
          $result['data'] = count($this->_message)==0 ? $data : [];

          $result['errors'] = isset($this->_message['_candy_form']) ? ['_candy_form' => $this->_message['_candy_form']] : $this->_message;
          if(!$result['success']['result']){
            Candy::return($result);
            die();
          }elseif($result['success']['message']!==null){
            Candy::return($result);
          }
        }
        break;
      default:
    }
    $GLOBALS['_candy']['oneshot']['_validation'] = $this->_message;
    return $this->_message;
  }

  function check($c){
    if(is_bool($c) || is_a($c,'Mysql_Table')){
      $this->_error = $c === false;
    }else{
      foreach(explode('|',$c) as $key){
        $vars = explode(':',$key);
        if(!$this->_error && !isset($this->_message[$this->_name])){
          switch($vars[0]){
            case 'required':
              $this->_error = !isset($this->_method[$this->_name]) || $this->_method[$this->_name]=='' || $this->_method[$this->_name]==null;
              break;
            case 'numeric':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && !is_numeric($this->_method[$this->_name]);
              break;
            case 'email':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && !filter_var($this->_method[$this->_name], FILTER_VALIDATE_EMAIL);
              break;
            case 'ip':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && !filter_var($this->_method[$this->_name], FILTER_VALIDATE_IP);
              break;
            case 'float':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && !filter_var($this->_method[$this->_name], FILTER_VALIDATE_FLOAT);
              break;
            case 'mac':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && !filter_var($this->_method[$this->_name], FILTER_VALIDATE_MAC);
              break;
            case 'domain':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && !filter_var($this->_method[$this->_name], FILTER_VALIDATE_DOMAIN);
              break;
            case 'url':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$this->_method[$this->_name]));
              break;
            case 'username':
              $this->_error = isset($this->_method[$this->_name]) && !(ctype_alnum($this->_method[$this->_name]));
              break;
            case 'xss':
              $this->_error = isset($this->_method[$this->_name]) && (strip_tags($this->_method[$this->_name]) != $this->_method[$this->_name]);
              break;
            case 'min':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && isset($vars[1]) && $this->_method[$this->_name]<$vars[1];
              break;
            case 'max':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && isset($vars[1]) && $this->_method[$this->_name]>$vars[1];
              break;
            case 'minlen':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && isset($vars[1]) && strlen($this->_method[$this->_name])<$vars[1];
              break;
            case 'maxlen':
              $this->_error = isset($this->_method[$this->_name]) && $this->_method[$this->_name]!='' && isset($vars[1]) && strlen($this->_method[$this->_name])>$vars[1];
              break;
            case 'same':
              $this->_error = isset($this->_method[$this->_name]) && isset($this->_method[$vars[1]]) && $this->_method[$this->_name]!==$this->_method[$vars[1]];
              break;
            case 'equal':
              $this->_error = isset($this->_method[$this->_name]) && isset($vars[1]) && $this->_method[$this->_name]!==$vars[1];
              break;
            case 'notin':
              $this->_error = isset($this->_method[$this->_name]) && isset($vars[1]) && (strpos($this->_method[$this->_name], $vars[1])!==false);
              break;
            case 'in':
              $this->_error = isset($this->_method[$this->_name]) && isset($vars[1]) && (!(strpos($this->_method[$this->_name], $vars[1])!==false));
              break;
            case 'not':
              $this->_error = isset($this->_method[$this->_name]) && isset($vars[1]) && $this->_method[$this->_name]==$vars[1];
              break;
            case 'regex':
              $this->_error = isset($this->_method[$this->_name]) && isset($vars[1]) && empty(preg_match("/".$vars[1]."/", $this->_method[$this->_name]));
              break;
          }
        }
      }
    }
    return new static($this->_name,$this->_request,$this->_error,$this->_message,$this->_method);
  }

  function result($v){
    return isset($_SESSION['_candy']['oneshot']['_validation'][$v]) ? $_SESSION['_candy']['oneshot']['_validation'][$v] : false;
  }
}
