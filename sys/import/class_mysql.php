<?php
class Mysql {
  public $conn;
  public $arr_conn = [];
  public $usercheck = 0;
  public $user_result;
  public $tb_user = null;
  public $tb_token = null;
  public $storage = null;

  public static function connect($db=0,$user=0,$pass=0,$server=0){
    global $conn;
    global $arr_conn;
    global $storage;
    $storage = $storage===null ? Candy::storage('sys')->get('mysql') : $storage;
    $storage->error = isset($storage->error) && is_object($storage->error) ? $storage->error : new \stdClass;
    $storage->error->info = isset($storage->error->info) && is_object($storage->error->info) ? $storage->error->info : new \stdClass;

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
    if(isset($arr_conn[$name])){
      $conn = $arr_conn[$name];
    }else{
      $conn = mysqli_connect($server, $user, $pass, $db);
      $arr_conn[$name] = $conn;
      if($conn){
        mysqli_set_charset($conn,"utf8");
        mysqli_query($conn,"SET NAMES utf8mb4");
      }else{
        if(Config::check('MASTER_MAIL') && (!isset($storage->error->info->date) || $storage->error->info->date!=date('d/m/Y'))){
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
        $storage->error->info->date = date('d/m/Y');
        Candy::storage('sys')->set('mysql',$storage);
      }
      echo "Mysql connection error" . PHP_EOL;
      exit;
    }
  }
    return $conn;
  }

  public static function query($query,$b = true){
    global $conn;
    $result = new \stdClass();
    $sql = mysqli_query($conn, $query);
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
    global $conn;
    global $storage;
    global $table_token;
    global $table_user;
    $result = new \stdClass();
    $query = '';
    foreach ($arr as $key => $value) {
      if($key!='table_user' && $key!='table_token'){
        $query .= $key.'="'.mysqli_real_escape_string($conn,$value).'" OR ';
      }
    }
    $query = '('.substr($query,0,-3).')';
    $sql_user = mysqli_query($conn, 'SELECT * FROM '.$arr['table_user'].' WHERE '.$query);
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
          setcookie("token1", $token1, time() + 61536000, "/", null);
          setcookie("token2", $token2, time() + 61536000, "/", null);
          $table_token = isset($arr['table_token']) ? $arr['table_token'] : 'candy_token';
          $check_table = mysqli_query($conn, 'SHOW TABLES LIKE "'.$table_token.'"');
          if(mysqli_num_rows($check_table)==0){
            $sql_create = mysqli_query($conn, "CREATE TABLE ".$table_token." (id INT NOT NULL AUTO_INCREMENT, userid INT NOT NULL, token1 VARCHAR(255) NOT NULL, token2 VARCHAR(255) NOT NULL, token3 VARCHAR(255) NOT NULL, ip VARCHAR(255) NOT NULL, PRIMARY KEY (id))");
          }
          $sql_token = mysqli_query($conn, 'INSERT INTO '.$table_token.' (userid,token1,token2,token3,ip) VALUES ("' . $result->fetch[0]['id'] . '","' . $token1 . '","' . $token2 . '","' . $token3 . '","'.$_SERVER['REMOTE_ADDR'].'")');
        }
        $table_user = $arr['table_user'];
        $storage = $storage===null ? Candy::storage('sys')->get('mysql') : $storage;
        if(!isset($storage->login->table_token) || !isset($storage->login->table_user) || $table_token!=$storage->login->table_token || $table_user!=$storage->login->table_user){
          $storage->login = isset($storage->login) && is_object($storage->login) ? $storage->login : new \stdClass;
          $storage->login->table_user = $arr['table_user'];
          $storage->login->table_token = $table_token;
          Candy::storage('sys')->set('mysql',$storage);
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
    global $conn;
    global $usercheck;
    global $user;
    global $storage;
    global $tb_token;
    global $tb_user;
    global $user_result;
    $storage = $storage===null ? Candy::storage('sys')->get('mysql') : $storage;
    if($tb_token===null){
      $tb_token = isset($storage->login->table_token) ? $storage->login->table_token : 'tb_token';
    }
    if($tb_user===null){
      $tb_user = isset($storage->login->table_user) ? $storage->login->table_user : 'tb_user';
    }
    if($usercheck==0 || $fetch){
      $result = new \stdClass();
      if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
        $token1 = mysqli_real_escape_string($conn, $_COOKIE['token1']);
        $token2 = mysqli_real_escape_string($conn, $_COOKIE['token2']);
        $token3 = md5($_SERVER['HTTP_USER_AGENT']);
        $sql_token = mysqli_query($conn, 'SELECT * FROM '.mysqli_real_escape_string($conn,$tb_token).' WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
        if($sql_token && mysqli_num_rows($sql_token) == 1){
          if($fetch){
            $get_token = mysqli_fetch_assoc($sql_token);
            $sql_user = mysqli_query($conn,'SELECT * FROM '.$tb_user.' WHERE id="'.$get_token['userid'].'"');
            $result->success = true;
            $data = array();
            $row = mysqli_fetch_assoc($sql_user);
            $data = $row;
            $result->fetch = $data;
            $user = $data;
            $result->rows = mysqli_num_rows($sql_user);
            $user_result = $result;
            return $result;
          }else{
            $usercheck = 1;
            return true;
          }
        }
      }else{
        $usercheck = 2;
        return false;
      }
    }else{
        return $usercheck==1;
    }
  }

  public static function logout(){
    global $conn;
    global $storage;
    global $tb_token;
    $storage = $storage===null ? Candy::storage('sys')->get('mysql') : $storage;
    if($tb_token===null){
      $tb_token = isset($storage->login->table_token) ? $storage->login->table_token : 'tb_token';
    }
    if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
      $token1 = mysqli_real_escape_string($conn, $_COOKIE['token1']);
      $token2 = mysqli_real_escape_string($conn, $_COOKIE['token2']);
      $token3 = md5($_SERVER['HTTP_USER_AGENT']);
      $sql_token = mysqli_query($conn, 'DELETE FROM '.mysqli_real_escape_string($conn,$tb_token).' WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
      setcookie("token1", "", time() - 3600);
      setcookie("token2", "", time() - 3600);
    }
  }

  public static function select($tb = '0',$where = null){
    global $conn;

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
      if($sql = mysqli_query($conn, $query)){
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
    global $conn;
    $result = new \stdClass();
    if(is_array($value)){
      $query_key = '';
      $query_val = '';
      foreach ($value as $key => $val) {
        $query_key .= $key.',';
        $query_val .= is_numeric($val) ? $val.',' : '"'.mysqli_real_escape_string($conn, $val).'",';
      }
      $query = 'INSERT INTO '.$table.' ('.substr($query_key,0,-1).') VALUES ('.substr($query_val,0,-1).')';
      $sql = mysqli_query($conn, $query);
      $result->query = $query;
      $result->success = $sql;
      $result->id = $conn->insert_id;
      return $result;
    }else{
      return false;
    }
  }

  public static function update($table,$where,$value){
    global $conn;
    if(is_array($value)){
      $query = 'UPDATE '.$table.' SET ';

      foreach ($value as $key => $val) {
        $query .= $key.'="'.mysqli_real_escape_string($conn,$val).'",';
      }
      if(is_numeric($where)){
        $query = substr($query,0,-1) . ' WHERE id="'.$where.'"';
      }else{
        $query = substr($query,0,-1) . ' WHERE '.$where;
      }
      $sql = mysqli_query($conn, $query);
      return $sql;
    }else{
      return false;
    }
  }

  public static function delete($table,$where){
    global $conn;
    if(is_numeric($where)){
      return $sql = mysqli_query($conn, 'DELETE FROM '.$table.' WHERE id="'.$where.'"');
    }else{
      return $sql = mysqli_query($conn, 'DELETE FROM '.$table.' WHERE '.$where);
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
    global $conn;
    return mysqli_real_escape_string($conn,$v);
  }

  public static function raw($v){
    $GLOBALS['candy_token_mysql'] = isset($GLOBALS['candy_token_mysql']) ? $GLOBALS['candy_token_mysql'] : rand(100,999);
    return ['ct' => $GLOBALS['candy_token_mysql'], 'v' => $v];
  }

  public static function table($tb = null){
    global $conn;
    Candy::import('mysql_table');
    $table = new Mysql_Table();
    return $table->table($tb);
  }
}
global $mysql;
$mysql = new Mysql();
$class = $mysql;
