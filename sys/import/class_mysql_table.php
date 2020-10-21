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
    $arr_q = ['where','order by','limit'];
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
  public static function whereJson($col,$val){
    //return 'JSON_SEARCH('.$col.', "one", "'.$val.'") IS NOT NULL';
    return new static(self::$arr);
  }
  public static function get($b=false){
    $query = "SELECT ".(isset(self::$arr['select']) ? self::$arr['select'] : '*')." FROM `".self::$arr['table']."` ".self::query();
    $data = [];
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false) return false;
    while($row = ($b ? mysqli_fetch_assoc($sql) : mysqli_fetch_object($sql))){
      $data[] = $row;
    }
    mysqli_free_result($sql);
    return $data;
  }
  public static function delete($b=false){
    $query = "DELETE FROM `".self::$arr['table']."` ".self::query();
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql;
  }
  public static function rows($b=false){
    $query = "SELECT * FROM `".self::$arr['table']."` ".self::query();
    $data = [];
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql===false ? false : mysqli_num_rows($sql);
  }
  public static function set($arr){
    $vars = "";
    foreach($arr as $key => $val) {
      $k = isset($key['v']) ? " " . $key['v'] . " " : "`".Mysql::escape($key)."`";
      $v = is_numeric($val) ? $val : (isset($val['v']) && isset($val['ct']) && $val['ct']==$GLOBALS['candy_token_mysql'] ? " " . $val['v'] . " " : '"'.Mysql::escape($val).'"');
      $vars .= $k.' = '. $v .',';
    }
    $query = "UPDATE `".self::$arr['table']."` SET ".substr($vars,0,-1)." ".self::query();
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql;
  }
  public static function add($arr){
    $query_key = '';
    $query_val = '';
    foreach ($arr as $key => $val){
      $query_key .= '`'.Mysql::escape($key).'`,';
      $query_val .= is_numeric($val) ? $val.',' : (isset($val['v']) && isset($val['ct']) && $val['ct']==$GLOBALS['candy_token_mysql'] ? $val['v'].',' : '"'.Mysql::escape($val).'",');
    }
    $query = "INSERT INTO `".self::$arr['table']."` ".' ('.substr($query_key,0,-1).') VALUES ('.substr($query_val,0,-1).')';
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql;
  }
  public static function first($b=false){
    self::$arr['limit'] = 1;
    $sql = self::get($b);
    if($sql === false || !isset($sql[0])) return false;
    return $sql[0];
  }
  public static function select(){
    self::$arr['select'] = isset(self::$arr['select']) ? self::$arr['select'] : '';
    $select = array_filter(explode(',',self::$arr['select']));
    foreach(func_get_args() as $key){
      if(is_array($key)){
      }else{
        $select[] = "`".Mysql::escape($key)."`";
      }
      self::$arr['select'] = implode(',',$select);
    }
    return new static(self::$arr);
  }
  public static function orderBy($v1,$v2='asc'){
    $v1 = is_array($v1) && isset($v1['v']) ? $v1 : "`".Mysql::escape($v1)."`";
    self::$arr['order by'] = $v1.' '.($v2 === 'desc' ? 'DESC' : 'ASC');
    return new static(self::$arr);
  }
  public static function limit($v1,$v2=null){
    self::$arr['limit'] = $v2===null ? $v1 : "$v1, $v2";
    return new static(self::$arr);
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
          if($loop==1){
            $q .= isset($key['v']) ? " " . $key['v'] . " " : " `".Mysql::escape($key)."`";
          }else{
            $q .= isset($key['v']) ? " " . $key['v'] . " " : ' "'.Mysql::escape($key).'"';
          }
        }
        $last = 1;
      }
        $loop++;
    }
    return '('.$q.')';
  }
}
