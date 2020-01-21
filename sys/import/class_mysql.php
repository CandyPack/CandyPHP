<?php
class Mysql {
  public $conn;
  public $usercheck = 0;

  public function connect($db=0,$user=0,$pass=0,$server=0){
    global $conn;
    $storage = Candy::storage('sys')->get('mysql');
    $storage->error = isset($storage->error) && is_object($storage->error) ? $storage->error : new \stdClass;
    $storage->error->info = isset($storage->error->info) && is_object($storage->error->info) ? $storage->error->info : new \stdClass;

    $db = $db===0 ? (defined('MYSQL_DB') ? MYSQL_DB : '') : $db;
    $user = $user===0 ? (defined('MYSQL_USER') ? MYSQL_USER : '') : $user;
    $pass = $pass===0 ? (defined('MYSQL_PASS') ? MYSQL_PASS : '') : $pass;
    $server = $server===0 ? (defined('MYSQL_SERVER') ? MYSQL_SERVER : '127.0.0.1') : $server;
    $conn = mysqli_connect($server, $user, $pass, $db);
    if($conn){
      mysqli_set_charset($conn,"utf8");
      mysqli_query($conn,"SET NAMES utf8mb4");
    }else{
      if(Config::check('INFO_MAIL,MASTER_MAIL') && (!isset($storage->error->info->date) || $storage->error->info->date!=date('d/m/Y'))){
        Candy::quickMail( MASTER_MAIL,
                    '<b>Date</b>: '.date("Y-m-d H:i:s").'<br />
                     <b>Message</b>: Unable to connect to mysql server<br /><br />
                     <b>Details</b>: <br />
                     SERVER:
                     <pre>'.print_r($_SERVER,true).'</pre>
                     SESSION:
                     <pre>'.print_r($_SESSION,true).'</pre>
                     COOKIE:
                     <pre>'.print_r($_COOKIE,true).'</pre>',
                     $_SERVER['SERVER_NAME'].' - INFO',
                     ['mail' => 'candyphp@'.$_SERVER['SERVER_NAME'], 'name' => 'Candy PHP']
                   );
        $storage->error->info->date = date('d/m/Y');
        Candy::storage('sys')->set('mysql',$storage);
      }
        echo "Mysql connection error" . PHP_EOL;
        exit;
    }
    return $conn;
  }

  public function query($query,$b = true){
    global $conn;
    $result = new \stdClass();
    $sql = mysqli_query($conn, $query);
    $data = array();
    while($row = mysqli_fetch_assoc($sql)){
      $data[] = $row;
    }
    $result->rows = mysqli_num_rows($sql);
    $result->fetch = $data;
    if($b){
      return $result;
    }
  }

  public function loginCheck($arr,$t = true){
    global $conn;
    $result = new \stdClass();
    $query = '';
    foreach ($arr as $key => $value) {
      if($key!='table_user' && $key!='table_token'){
        $query .= $key.'="'.mysqli_real_escape_string($conn,$value).'" AND ';
      }
    }
    $query = '('.substr($query,0,-4).')';
    $sql_user = mysqli_query($conn, 'SELECT * FROM '.$arr['table_user'].' WHERE '.$query);
    if(mysqli_num_rows($sql_user)==1){
      $result->success = true;
      $data = array();
      while($row = mysqli_fetch_assoc($sql_user)){
        $data[] = $row;
      }
      $result->fetch = $data;
      $result->rows = mysqli_num_rows($sql_user);
      if($t){
        $token1 = $data['id'].(time()*100).rand(10000,99999);
        $token2 = md5($_SERVER['REMOTE_ADDR']);
        $token3 = md5($_SERVER['HTTP_USER_AGENT']);
        setcookie("token1", $token1, time() + 61536000, "/");
        setcookie("token2", $token2, time() + 61536000, "/");
        $sql_token = mysqli_query($conn, 'INSERT INTO '.$arr['table_token'].' (userid,token1,token2,token3,ip) VALUES ("' . $result->fetch[0]['id'] . '","' . $token1 . '","' . $token2 . '","' . $token3 . '","'.$_SERVER['REMOTE_ADDR'].'")');
      }
        return $result;
    }else{
      $result->success = false;
      return $result;
    }
  }

  public function userCheck($fetch = true){
    global $conn;
    global $usercheck;
    if($usercheck==0){
      $result = new \stdClass();
      if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
        $token1 = mysqli_real_escape_string($conn, $_COOKIE['token1']);
        $token2 = mysqli_real_escape_string($conn, $_COOKIE['token2']);
        $token3 = md5($_SERVER['HTTP_USER_AGENT']);
        $sql_token = mysqli_query($conn, 'SELECT * FROM tb_token WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
        if(mysqli_num_rows($sql_token) == 1){
          if($fetch){
            $result->success = true;
            $data = array();
            while($row = mysqli_fetch_assoc($sql_user)){
              $data[] = $row;
            }
            $result->fetch = $data;
            $result->rows = mysqli_num_rows($sql_user);
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

  public function logout(){
    global $conn;
    if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
      $token1 = mysqli_real_escape_string($conn, $_COOKIE['token1']);
      $token2 = mysqli_real_escape_string($conn, $_COOKIE['token2']);
      $token3 = md5($_SERVER['HTTP_USER_AGENT']);
      $sql_token = mysqli_query($conn, 'DELETE FROM tb_token WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
      setcookie("token1", "", time() - 3600);
      setcookie("token2", "", time() - 3600);
    }
  }

  public function select($tb = '0',$where = null){
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

  public function insert($table, $value){
    global $conn;
    $result = new \stdClass();
    if(is_array($value)){
      $query_key = '';
      $query_val = '';
      foreach ($value as $key => $val) {
        $query_key .= $key.',';
        $query_val .= '"'.mysqli_real_escape_string($conn, $val).'",';
      }
      $query = 'INSERT INTO '.$table.' ('.substr($query_key,0,-1).') VALUES ('.substr($query_val,0,-1).')';
      $sql = mysqli_query($conn, $query);
      $result->success = $sql;
      $result->id = $conn->insert_id;
      return $result;
    }else{
      return false;
    }
  }

  public function update($table,$where,$value){
    global $conn;
    if(is_array($value)){
      $query = 'UPDATE '.$table.' SET ';

      foreach ($value as $key => $val) {
        $query .= $key.'="'.$val.'",';
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

  public function delete($table,$where){
    global $conn;
    return $sql = mysqli_query($conn, 'DELETE FROM '.$table.' WHERE '.$where);
  }
}
global $mysql;
$mysql = new Mysql();
$class = $mysql;
