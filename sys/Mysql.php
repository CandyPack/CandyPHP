<?php
class Mysql {
  public static $conn;
  public static $name;
  private static $arr_conn = [];
  private static $tb_user = null;
  private static $tb_token = null;
  private static $storage = null;
  private static $user_signed = null;
  private static $user_fetch = null;

  public static function connect($db=0,$user=0,$pass=0,$server=0){
    self::$storage = self::$storage===null ? Candy::storage('sys')->get('mysql') : self::$storage;
    self::$storage->error = isset(self::$storage->error) && is_object(self::$storage->error) ? self::$storage->error : new \stdClass;
    self::$storage->error->info = isset(self::$storage->error->info) && is_object(self::$storage->error->info) ? self::$storage->error->info : new \stdClass;

    if(isset($GLOBALS['candy_mysql']) && is_array($GLOBALS['candy_mysql']) && $user===0 && $pass===0 && $server===0){
      if($db!==0){
        $vall = $db;
        $db = $GLOBALS['candy_mysql'][$vall]['database'];
        $user = $GLOBALS['candy_mysql'][$vall]['user'];
        $pass = $GLOBALS['candy_mysql'][$vall]['password'];
        $server = $GLOBALS['candy_mysql'][$vall]['host'];
        $name = $GLOBALS['candy_mysql'][$vall]['name'];
      }else{
        foreach($GLOBALS['candy_mysql'] as $key => $val){
          if($val['default']===true){
            $db = $val['database'];
            $user = $val['user'];
            $pass = $val['password'];
            $server = $val['host'];
            $name = $val['name'];
          }
        }
      }
    }else{
      $db = $db===0 ? (defined('MYSQL_DB') ? MYSQL_DB : '') : $db;
      $user = $user===0 ? (defined('MYSQL_USER') ? MYSQL_USER : '') : $user;
      $pass = $pass===0 ? (defined('MYSQL_PASS') ? MYSQL_PASS : '') : $pass;
      $server = $server===0 ? (defined('MYSQL_SERVER') ? MYSQL_SERVER : '127.0.0.1') : $server;
      $name = 'candy_default';
    }
    self::$name = $name;
    if(isset(self::$arr_conn[$name])){
      self::$conn = self::$arr_conn[$name];
    }else{
      self::$conn = mysqli_connect($server, $user, $pass, $db);
      self::$arr_conn[$name] = self::$conn;
      if(self::$conn){
        mysqli_set_charset(self::$conn,"utf8");
        mysqli_query(self::$conn,"SET NAMES utf8mb4");
      }else{
        Config::errorReport('MYSQL','Unable to connect to mysql server');
        if(Config::check('MASTER_MAIL') && (!isset(self::$storage->error->info->date) || self::$storage->error->info->date!=date('d/m/Y'))){
          Candy::quickMail( MASTER_MAIL,
          '<b>Date</b>: '.date("Y-m-d H:i:s").'<br />
          <b>Message</b>: Unable to connect to mysql server<br /><br />
          <b>Server</b>: '.$server.'<br />
          <b>Database</b>: '.$db.'<br />
          <b>Username</b>: '.$user.'<br />
          <b>Password</b>: '.$pass.'<br /><br />
          <b>Details</b>: <br />
          SERVER:
          <pre>'.print_r($_SERVER,true).'</pre>
          SESSION:
          <pre>'.print_r($_SESSION,true).'</pre>
          COOKIE:
          <pre>'.print_r($_COOKIE,true).'</pre>
          POST:
          <pre>'.print_r($_POST,true).'</pre>
          GET:
          <pre>'.print_r($_GET,true).'</pre>',
          $_SERVER['SERVER_NAME'].' - INFO',
          ['mail' => 'candyphp@'.$_SERVER['SERVER_NAME'], 'name' => 'Candy PHP']
        );
        self::$storage->error->info->date = date('d/m/Y');
        Candy::storage('sys')->set('mysql',self::$storage);
      }
      if(isset($GLOBALS['candy_mysql'][$name]['abort'])) Candy::abort($GLOBALS['candy_mysql'][$name]['abort'], (Candy::isDev() ? 'Mysql Connection Error' : ''));
    }
  }
    return self::$conn;
  }

  public static function query($query,$b = true){
    $result = new \stdClass();
    $sql = mysqli_query(self::$conn, $query);
    if($b){
      $data = array();
      while($row = mysqli_fetch_assoc($sql)){
        $data[] = $row;
      }
      $result->rows = mysqli_num_rows($sql);
      $result->fetch = $data;
      return $result;
    }
  }

  public static function loginCheck($arr,$t = true,$fetch = false){
    $result = new \stdClass();
    $query = '';
    foreach ($arr as $key => $value) {
      if($key!='table_user' && $key!='table_token'){
        $query .= $key.'="'.self::escape($value).'" OR ';
      }
    }
    $query = '('.substr($query,0,-3).')';
    $sql_user = mysqli_query(self::$conn, 'SELECT * FROM '.$arr['table_user'].' WHERE '.$query);
    if(mysqli_num_rows($sql_user)>=1){
      $is_equal = false;
      while($get = mysqli_fetch_assoc($sql_user)){
      $user = (object)$get;
        if(!$is_equal){
          $is_equal = true;
          foreach($get as $key => $val){
            if(isset($arr[$key])){
              if(strpos($val, '$2y$')!==false){
                $is_equal = $is_equal && Candy::hash($arr[$key],$val);
              }else{
                $is_equal = $is_equal && $arr[$key]==$val;
              }
            }
          }
          $data[] = $is_equal ? $get : [];
        }
      }
      $result->fetch = $data;
      if($is_equal){
        if($t){
          $token1 = uniqid(mt_rand(), true).rand(10000,99999).(time()*100);
          $token2 = md5($_SERVER['REMOTE_ADDR']);
          $token3 = md5($_SERVER['HTTP_USER_AGENT']);
          setcookie("token1", $token1, time() + 61536000, "/", (!empty(ini_get('session.cookie_domain')) ? ini_get('session.cookie_domain') : null),false,true);
          setcookie("token2", $token2, time() + 61536000, "/", (!empty(ini_get('session.cookie_domain')) ? ini_get('session.cookie_domain') : null),false,true);
          self::$tb_token = isset($arr['table_token']) ? $arr['table_token'] : 'candy_token';
          $check_table = mysqli_query(self::$conn, 'SHOW TABLES LIKE "'.self::$tb_token.'"');
          if(mysqli_num_rows($check_table)==0){
            $sql_create = mysqli_query(self::$conn, "CREATE TABLE ".self::$tb_token." (id INT NOT NULL AUTO_INCREMENT, userid INT NOT NULL, token1 VARCHAR(255) NOT NULL, token2 VARCHAR(255) NOT NULL, token3 VARCHAR(255) NOT NULL, ip VARCHAR(255) NOT NULL, `date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id))");
          }
          $sql_token = mysqli_query(self::$conn, 'INSERT INTO '.self::$tb_token.' (userid,token1,token2,token3,ip) VALUES ("' . $result->fetch[0]['id'] . '","' . $token1 . '","' . $token2 . '","' . $token3 . '","'.$_SERVER['REMOTE_ADDR'].'")');
        }
        self::$tb_user = $arr['table_user'];
        self::$storage = self::$storage===null ? Candy::storage('sys')->get('mysql') : self::$storage;
        if(!isset(self::$storage->login->table_token) || !isset(self::$storage->login->table_user) || self::$tb_token!=self::$storage->login->table_token || self::$tb_user!=self::$storage->login->table_user){
          self::$storage->login = isset(self::$storage->login) && is_object(self::$storage->login) ? self::$storage->login : new \stdClass;
          self::$storage->login->table_user = $arr['table_user'];
          self::$storage->login->table_token = self::$tb_token;
          Candy::storage('sys')->set('mysql',self::$storage);
        }else{
          self::table(self::$tb_token)->where('date','<',self::raw('NOW() - INTERVAL 2 MONTH'))->delete();
        }
        if($fetch){
          return $user;
        }else{
          return true;
        }
      }else{
        return false;
      }
    }else{
      return false;
    }
  }

  public static function userCheck($fetch = false){
    if(self::$user_signed !== null && $fetch===false) return self::$user_signed;
    self::$storage = self::$storage===null ? Candy::storage('sys')->get('mysql') : self::$storage;
    if(self::$tb_token===null) self::$tb_token = isset(self::$storage->login->table_token) ? self::$storage->login->table_token : 'candy_token';
    if(self::$tb_user===null) self::$tb_user = isset(self::$storage->login->table_user) ? self::$storage->login->table_user : 'tb_user';
    if(self::$user_signed!==null && $fetch===false) return self::$user_signed===true;
    $result = new \stdClass();
    $result->success = false;
    if(!isset($_COOKIE['token1']) || !isset($_COOKIE['token2'])) return $fetch === false ? false : $result;
    $token1 = mysqli_real_escape_string(self::$conn, $_COOKIE['token1']);
    $token2 = mysqli_real_escape_string(self::$conn, $_COOKIE['token2']);
    $token3 = md5($_SERVER['HTTP_USER_AGENT']);
    $sql_token = Mysql::table(self::$tb_token)->where(['token1',$token1],['token2',$token2],['token3',$token3]);
    if($sql_token->rows() != 1) return $fetch === false ? false : $result;
    $get_token = $sql_token->first();
    $ip_update = isset($get_token->date) && (intval(Candy::dateFormatter($get_token->date,'YmdH'))+1 < intval(date('YmdH'))) ? $sql_token->set(['ip' => $_SERVER['REMOTE_ADDR']]) : false;
    self::$user_signed = true;
    if($fetch === false) return true;
    $result->success = true;
    $sql_user = Mysql::table(self::$tb_user)->where($get_token->userid);
    $user = $sql_user->first(true);
    $result->fetch = $user;
    $result->rows = $sql_user->rows();
    return $result;
  }

  public static function logout(){
    self::$storage = self::$storage===null ? Candy::storage('sys')->get('mysql') : self::$storage;
    if(self::$tb_token===null){
      self::$tb_token = isset(self::$storage->login->table_token) ? self::$storage->login->table_token : 'candy_token';
    }
    if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
      $token1 = mysqli_real_escape_string(self::$conn, $_COOKIE['token1']);
      $token2 = mysqli_real_escape_string(self::$conn, $_COOKIE['token2']);
      $token3 = md5($_SERVER['HTTP_USER_AGENT']);
      $sql_token = mysqli_query(self::$conn, 'DELETE FROM '.mysqli_real_escape_string(self::$conn,self::$tb_token).' WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
      setcookie("token1", "", time() - 3600);
      setcookie("token2", "", time() - 3600);
    }
  }

  public static function select($tb = '0',$where = null){
    $result = new \stdClass();
    if(is_array($tb) || $tb!='0'){
      if(!is_array($tb)){
        if($where===null){
          $query = 'SELECT * FROM '.$tb;
        }else{
          $query = is_numeric($where) ? 'SELECT * FROM '.$tb.' WHERE id="'.$where.'"' : 'SELECT * FROM '.$tb.' WHERE '.$where;
        }
      }else{
        if(isset($tb['SELECT'])){
          $query = 'SELECT '.$tb['SELECT'];
        }elseif(isset($tb['select'])){
          $query = 'SELECT '.$tb['select'];
        }else{
          $query = 'SELECT *';}
        foreach ($tb as $key => $value){
          if(strtoupper($key)!='SELECT'){
            $query .= ' '.strtoupper($key).' '.$value;
          }
        }
      }
      $result->query = $query;
      if($sql = mysqli_query(self::$conn, $query)){
        $result->success = true;
        $result->rows = mysqli_num_rows($sql);
        $data = array();
        while($row = mysqli_fetch_assoc($sql)){
          $data[] = $row;
        }

        $result->fetch = $data;
        mysqli_free_result($sql);
        return $result;
      }else{
        $result->success = false;
        return $result;
      }
    }else{
      $result->success = false;
      return $result;
    }
  }

  public static function insert($table, $value){
    $result = new \stdClass();
    if(is_array($value)){
      $query_key = '';
      $query_val = '';
      foreach ($value as $key => $val) {
        $query_key .= $key.',';
        $query_val .= is_numeric($val) ? $val.',' : '"'.mysqli_real_escape_string(self::$conn, $val).'",';
      }
      $query = 'INSERT INTO '.$table.' ('.substr($query_key,0,-1).') VALUES ('.substr($query_val,0,-1).')';
      $sql = mysqli_query(self::$conn, $query);
      $result->query = $query;
      $result->success = $sql;
      $result->id = self::$conn->insert_id;
      return $result;
    }else{
      return false;
    }
  }

  public static function update($table,$where,$value){
    if(is_array($value)){
      $query = 'UPDATE '.$table.' SET ';

      foreach ($value as $key => $val) {
        $query .= $key.'="'.mysqli_real_escape_string(self::$conn,$val).'",';
      }
      if(is_numeric($where)){
        $query = substr($query,0,-1) . ' WHERE id="'.$where.'"';
      }else{
        $query = substr($query,0,-1) . ' WHERE '.$where;
      }
      $sql = mysqli_query(self::$conn, $query);
      return $sql;
    }else{
      return false;
    }
  }

  public static function delete($table,$where){
    if(is_numeric($where)){
      return $sql = mysqli_query(self::$conn, 'DELETE FROM '.$table.' WHERE id="'.$where.'"');
    }else{
      return $sql = mysqli_query(self::$conn, 'DELETE FROM '.$table.' WHERE '.$where);
    }
  }

  public static function close($db=null){
    if($db===null){
      $close = mysqli_close(self::$conn);
    }else{
      $db == '' ? (defined('MYSQL_DB') ? MYSQL_DB : '') : $db;
      $close = mysqli_close(self::$arr_conn[$db]);
      unset(self::$arr_conn[$db]);
    }
    return $close;
  }

  public static function closeAll($db=null){
    foreach(self::$arr_conn as $key => $val){
      mysqli_close($val);
    }
    self::$arr_conn = [];
  }

  public static function escape($v){
    return mysqli_real_escape_string(self::$conn,$v);
  }

  public static function raw($v){
    $GLOBALS['candy_token_mysql'] = isset($GLOBALS['candy_token_mysql']) ? $GLOBALS['candy_token_mysql'] : rand(100,999);
    return ['ct' => $GLOBALS['candy_token_mysql'], 'v' => $v];
  }

  public static function table($tb = null){
    Candy::import('Mysql/Table');
    $table = new Mysql_Table();
    return $table->table($tb);
  }
}
global $mysql;
$mysql = new Mysql();
$class = $mysql;
