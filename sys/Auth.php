<?php

class Auth{
  static $user = false;

  public static function check($where = null){
    if(!isset($GLOBALS['_candy']['auth']['status']) || !$GLOBALS['_candy']['auth']['status']) return false;
    $_table = $GLOBALS['_candy']['auth']['table'];
    if($where !== null){
      $sql = \Mysql::table($_table);
      foreach($where as $key => $val) $sql->orWhere($key, $val);
      if(empty($sql->rows())) return false;
      $get = $sql->get();
      foreach ($get as $user) {
        $equal = count($where) > 0;
        foreach ($where as $key => $val) {
          if(!isset($user->$key)) $equal = false;
          if($user->$key == $val) $equal = $equal && true;
          elseif(Candy::string($user->$key)->is('bcrypt')) $equal = $equal && Candy::hash($val,$user->$key);
          elseif(Candy::string($user->$key)->is('md5')) $equal = $equal && md5($val) == $user->$key;
        }
        if($equal) break;
      }
      if(!$equal) return false;
      return $user;
    } else {
      $check_table = Mysql::query('SHOW TABLES LIKE "'.$GLOBALS['_candy']['auth']['token'].'"',true);
      if($check_table->rows == 0) return false;
      if(!isset($_COOKIE['token1']) || !isset($_COOKIE['token2']) || !isset($_SERVER['HTTP_USER_AGENT'])) return false;
      $token1 = $_COOKIE['token1'];
      $token2 = $_COOKIE['token2'];
      $browser = $_SERVER['HTTP_USER_AGENT'];
      $sql_token = Mysql::table($GLOBALS['_candy']['auth']['token'])->where(['token1',$token1],['token2',$token2],['browser',$browser]);
      if($sql_token->rows() != 1) return false;
      $get_token = $sql_token->first();
      $ip_update = isset($get_token->date) && (intval(Candy::dateFormatter($get_token->date,'YmdH'))+1 < intval(date('YmdH'))) ? $sql_token->set(['ip' => $_SERVER['REMOTE_ADDR']]) : false;
      self::$user = Mysql::table($_table)->where($GLOBALS['_candy']['auth']['key'], $get_token->userid)->first();
      return true;
    }
  }

  public static function login($where){
    self::$user = false;
    $user = self::check($where);
    if(!$user) return false;
    $_key = $GLOBALS['_candy']['auth']['key'];
    $_token = $GLOBALS['_candy']['auth']['token'];
    $_table = $GLOBALS['_candy']['auth']['table'];
    if($_token !== null){
      $check_table = Mysql::query('SHOW TABLES LIKE "'.$_token.'"',true);
      if($check_table->rows == 0){
        $sql_create = Mysql::query("CREATE TABLE ".$_token." (id INT NOT NULL AUTO_INCREMENT, userid INT NOT NULL, token1 VARCHAR(255) NOT NULL, token2 VARCHAR(255) NOT NULL, browser VARCHAR(255) NOT NULL, ip VARCHAR(255) NOT NULL, `date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id))", false);
      }
      $token = [
        'userid' => $user->$_key,
        'token1' => uniqid(mt_rand(), true).rand(10000,99999).(time()*100),
        'token2' => md5($_SERVER['REMOTE_ADDR']),
        'browser' => $_SERVER['HTTP_USER_AGENT'],
        'ip' => $_SERVER['REMOTE_ADDR']
      ];
      setcookie("token1", $token['token1'], time() + 61536000, "/", (!empty(ini_get('session.cookie_domain')) ? ini_get('session.cookie_domain') : null),false,true);
      setcookie("token2", $token['token2'], time() + 61536000, "/", (!empty(ini_get('session.cookie_domain')) ? ini_get('session.cookie_domain') : null),false,true);
      $check_table = Mysql::query("SHOW TABLES LIKE '$_table'");
      if($check_table->rows == 0) Mysql::query("CREATE TABLE ".$_table." (id INT NOT NULL AUTO_INCREMENT, userid INT NOT NULL, token1 VARCHAR(255) NOT NULL, token2 VARCHAR(255) NOT NULL, token3 VARCHAR(255) NOT NULL, ip VARCHAR(255) NOT NULL, `date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id))", false);
      $sql = Mysql::table($_token)->add($token);
    }
    return $sql !== false;
  }

  public static function register($vars){
    self::$user = false;
    switch ($GLOBALS['_candy']['auth']['storage']) {
      case 'mysql':
        if($GLOBALS['_candy']['auth']['db']) Mysql::connect($GLOBALS['_candy']['auth']['db']);
        else Mysql::connect();
        $add = Mysql::table($GLOBALS['_candy']['auth']['table'])
                    ->add($vars);
        if($add === false) return false;
        $primary = $GLOBALS['_candy']['auth']['key'];
        self::login([$primary => $add->$primary]);
        return true;
        break;

      default:
        return false;
        break;
    }
  }

  public static function logout(){
    self::$user = false;
  }

  public static function user($col = null){
    if(empty(self::$user)) self::check();
    if(empty(self::$user)) return false;
    if($col === null) return self::$user;
    else return self::$user->$col;
  }

}
