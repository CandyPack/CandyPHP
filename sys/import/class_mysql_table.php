<?php
class Mysql_Table {
  protected static $arr = [];
  protected static $statements = ['=','>','>=','<','<=','!=','LIKE','NOT LIKE','IN','NOT IN','BETWEEN','NOT BETWEEN','IS NULL','IS NOT NULL'];

  function __construct($arr=null) {
    if(is_array($arr)){
      self::$arr = $arr;
    }else{
      self::$arr = [];
    }
  }

  public static function query(){
    $arr_q = ['select','where','limit'];
    $query = "";
    foreach($arr_q as $key){
      if(isset(self::$arr[$key])){
        $query .= " ".strtoupper($key)." ";
        $query .= self::$arr[$key];
      }
    }
    return $query;
  }
  public static function table($t){
    self::$arr['table'] = $t;
    return new static(self::$arr);
  }
  public static function where(){
    if(count(func_get_args()) == 1 && !is_array(func_get_args()[0])){
      self::$arr['where'] = is_numeric(func_get_args()[0]) ? "id='".func_get_args()[0]."'" : "";
    }elseif(count(func_get_args()) > 0){
      self::$arr['where'] = isset(self::$arr['where']) && trim(self::$arr['where'])!='' ? self::$arr['where'].' AND '.self::whereExtract(func_get_args()) : self::whereExtract(func_get_args());
    }
    return new static(self::$arr);
  }
  public static function orWhere(){
    if(count(func_get_args()) > 0){
      self::$arr['where'] = isset(self::$arr['where']) && trim(self::$arr['where'])!='' ? self::$arr['where'].' OR '.self::whereExtract(func_get_args()) : self::whereExtract(func_get_args());
    }
    return new static(self::$arr);
  }
  public static function limit($v1,$v2=null){
    self::$arr['limit'] = $v2===null ? $v1 : "$v1, $v2";
    return new static(self::$arr);
  }
  public static function get($b=false){
    global $conn;
    $query = "SELECT * FROM `".self::$arr['table']."` ".self::query();
    $data = [];
    $sql = mysqli_query($conn, $query);
    while($row = ($b ? mysqli_fetch_assoc($sql) : mysqli_fetch_object($sql))){
      $data[] = $row;
    }
    mysqli_free_result($sql);
    return $data;
  }
  public static function delete($b=false){
    global $conn;
    $query = "DELETE FROM `".self::$arr['table']."` ".self::query();
    $sql = mysqli_query($conn, $query);
    return $sql;
  }
  public static function rows($b=false){
    global $conn;
    $query = "SELECT * FROM `".self::$arr['table']."` ".self::query();
    $data = [];
    $sql = mysqli_query($conn, $query);
    return mysqli_num_rows($sql);
  }
  public static function set($arr){
    global $conn;
    $vars = "";
    foreach($arr as $key => $val) {
      $vars .= '`'.Mysql::escape($key).'` = '.(is_numeric($val) ? $val : '"'.Mysql::escape($val).'"').',';
    }
    $query = "UPDATE `".self::$arr['table']."` SET ".substr($vars,0,-1)." ".self::query();
    $sql = mysqli_query($conn, $query);
    return $sql;
  }
  public static function first($b=false){
    self::$arr['limit'] = 1;
    return self::get()[0];
  }
  private static function whereExtract($arr){
    $q = "";
    $loop = 1;
    $in_arr = false;
    $state = '=';
    $last = 0;
    foreach ($arr as $key){
      if(is_array($key) && ($state != 'IN' && $state != 'NOT IN') && (!isset($key['ct']) || $key['ct']!=$GLOBALS['candy_token_mysql'])){
        $q .= $last == 1 ? ' AND '.self::whereExtract($key) : self::whereExtract($key);
        $in_arr = true;
        $last = 1;
      }elseif(count($arr)==2 && $loop==2){
        $q .= isset($key['v']) ? " = " . $key['v'] . " " : " = '" . Mysql::escape($key) . "' ";
      }elseif($in_arr){
        $q .= strtoupper($key)=='OR' ? " OR " : " AND ";
        $last = 2;
      }elseif(count($arr)==3 && $loop==2){
        $state = in_array(strtoupper($key),self::$statements) ? strtoupper($key) : "=";
        $q .= " ".$state;
        $last = 1;
      }else{
        if((!isset($key['ct']) || $key['ct']!=$GLOBALS['candy_token_mysql']) && is_array($key) && ($state == 'IN' || $state == 'NOT IN')){
          $esc = [];
          foreach ($key as $val){
            $esc[] = Mysql::escape($val);
          }
          $q .= " ('".implode("','",$esc)."')";
        }else{
          $q .= isset($key['v']) ? " " . $key['v'] . " " : " `".Mysql::escape($key)."`";
        }
        $last = 1;
      }
        $loop++;
    }
    return '('.$q.')';
  }
}
