<?php
class Mysql_Table {
  protected $arr = [];
  protected $result = [];
  protected $statements = ['=','>','>=','<','<=','!=','LIKE','NOT LIKE','IN','NOT IN','BETWEEN','NOT BETWEEN','IS NULL','IS NOT NULL'];

 function __construct($arr=null) {
    if(is_array($arr)){
      $this->arr = $arr;
    }else{
      $this->arr = [];
    }
  }

  function query(){
    $arr_q = ['inner join', 'right join', 'left join', 'where','order by','limit'];
    $query = "";
    foreach($arr_q as $key){
      if(isset($this->arr[$key])){
        if(is_array($this->arr[$key])){
          $query .= " ".strtoupper($key)." ".implode(" ".strtoupper($key)." ",$this->arr[$key]);
        }else{
          $query .= " ".strtoupper($key)." ";
          $query .= $this->arr[$key];
        }
      }
    }
    return $query;
  }
  function table($t){
    $this->arr['table'] = $t;
    return new static($this->arr);
  }
  function where(){
    if(count(func_get_args()) == 1 && !is_array(func_get_args()[0])){
      $this->arr['where'] = is_numeric(func_get_args()[0]) ? "id='".func_get_args()[0]."'" : "";
    }elseif(count(func_get_args()) > 0){
      $this->arr['where'] = isset($this->arr['where']) && trim($this->arr['where'])!='' ? $this->arr['where'].' AND '.self::whereExtract(func_get_args()) : self::whereExtract(func_get_args());
    }
    return new static($this->arr);
  }
  function orWhere(){
    if(count(func_get_args()) > 0){
      $this->arr['where'] = isset($this->arr['where']) && trim($this->arr['where'])!='' ? $this->arr['where'].' OR '.self::whereExtract(func_get_args()) : self::whereExtract(func_get_args());
    }
    return new static($this->arr);
  }
  function whereJson($col,$val){
    //return 'JSON_SEARCH('.$col.', "one", "'.$val.'") IS NOT NULL';
    return new static($this->arr);
  }
  function get($b=false){
    $query = "SELECT ".(isset($this->arr['select']) ? $this->arr['select'] : '*')." FROM `".$this->arr['table']."` ".self::query();
    $data = [];
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false){
      $bt = debug_backtrace();
      $caller = array_shift($bt);
      return self::error($caller);
    }
    while($row = ($b ? mysqli_fetch_assoc($sql) : mysqli_fetch_object($sql))){
      $data[] = $row;
    }
    mysqli_free_result($sql);
    return $data;
  }
  function delete($b=false){
    $query = "DELETE FROM `".$this->arr['table']."` ".self::query();
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql;
  }
  function rows($b=false){
    $query = "SELECT * FROM `".$this->arr['table']."` ".self::query();
    $data = [];
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql===false ? false : mysqli_num_rows($sql);
  }
  function set($arr,$val=null){
    $vars = "";
    if(!is_array($arr) && $val !== null){
      $k = isset($arr['v']) ? " " . $arr['v'] . " " : "`".Mysql::escape($arr)."`";
      $v = is_numeric($val) ? $val : (isset($val['v']) && isset($val['ct']) && $val['ct']==$GLOBALS['candy_token_mysql'] ? " " . $val['v'] . " " : '"'.Mysql::escape($val).'"');
      $vars .= $k.' = '. $v .',';
    }else{
      foreach($arr as $key => $val) {
        $k = isset($key['v']) ? " " . $key['v'] . " " : "`".Mysql::escape($key)."`";
        $v = is_numeric($val) ? $val : (isset($val['v']) && isset($val['ct']) && $val['ct']==$GLOBALS['candy_token_mysql'] ? " " . $val['v'] . " " : '"'.Mysql::escape($val).'"');
        $vars .= $k.' = '. $v .',';
      }
    }
    $query = "UPDATE `".$this->arr['table']."` SET ".substr($vars,0,-1)." ".self::query();
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false){
      $bt = debug_backtrace();
      $caller = array_shift($bt);
      return self::error($caller);
    }
    $this->affected = mysqli_affected_rows(Mysql::$conn);
    return new static($this->arr);
    return $sql;
  }
  function add($arr){
    $query_key = '';
    $query_val = '';
    foreach ($arr as $key => $val){
      $query_key .= '`'.Mysql::escape($key).'`,';
      $query_val .= is_numeric($val) ? $val.',' : (isset($val['v']) && isset($val['ct']) && $val['ct']==$GLOBALS['candy_token_mysql'] ? $val['v'].',' : '"'.Mysql::escape($val).'",');
    }
    $query = "INSERT INTO `".$this->arr['table']."` ".' ('.substr($query_key,0,-1).') VALUES ('.substr($query_val,0,-1).')';
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false){
      $bt = debug_backtrace();
      $caller = array_shift($bt);
      return self::error($caller);
    }
    $result = new stdClass();
    $result->success = $sql;
    $result->id = mysqli_insert_id(Mysql::$conn);
    return $result;
  }
  function first($b=false){
    $this->arr['limit'] = 1;
    $sql = self::get($b);
    if($sql === false || !isset($sql[0])) return false;
    return $sql[0];
  }
  function select(){
    $this->arr['select'] = isset($this->arr['select']) ? $this->arr['select'] : '';
    $select = array_filter(explode(',',$this->arr['select']));
    foreach(func_get_args() as $key){
      if(is_array($key) && (!isset($key['v']) || !isset($key['ct']) || $key['ct']!=$GLOBALS['candy_token_mysql'])){
      }else{
        if(isset($key['v']) && isset($key['ct']) && $key['ct']==$GLOBALS['candy_token_mysql']){
          $select[] = $key['v'];
        }else{
          $exp_key = explode('.',$key);
          $select[] = isset($exp_key[1]) ? Mysql::escape($exp_key[0]).".`".Mysql::escape($exp_key[1])."`" : "`".Mysql::escape($key)."`";
        }
      }
      $this->arr['select'] = implode(', ',$select);
    }
    return new static($this->arr);
  }
  function orderBy($v1,$v2='asc'){
    $v1 = is_array($v1) && isset($v1['v']) ? $v1 : "`".Mysql::escape($v1)."`";
    $this->arr['order by'] = $v1.' '.($v2 === 'desc' ? 'DESC' : 'ASC');
    return new static($this->arr);
  }
  function limit($v1,$v2=null){
    $this->arr['limit'] = $v2===null ? $v1 : "$v1, $v2";
    return new static($this->arr);
  }
  function leftJoin($tb,$col1,$st,$col2=null){
    return self::join($tb,$col1,$st,$col2=null,$type='left join');
  }
  function rightJoin($tb,$col1,$st,$col2=null){
    return self::join($tb,$col1,$st,$col2=null,$type='right join');
  }
  function join($tb,$col1,$st,$col2=null,$type='inner join'){
    $this->arr[$type] = isset($this->arr[$type]) ? $this->arr[$type] : [];
    $tb = "`".Mysql::escape($tb)."`";
    $exp_col1 = explode('.',$col1);
    $col1 = isset($exp_col1[1]) ? Mysql::escape($exp_col1[0]).".`".Mysql::escape($exp_col1[1])."`" : "`".Mysql::escape($col1)."`";
    if($col2 !== null){
      $exp_col2 = explode('.',$col2);
      $col2 = isset($exp_col2[1]) ? Mysql::escape($exp_col2[0]).".`".Mysql::escape($exp_col2[1])."`" : "`".Mysql::escape($col2)."`";
      $state = in_array(strtoupper($st),$this->statements) ? strtoupper($st) : "=";
    }else{
      $exp_col2 = explode('.',$st);
      $col2 = isset($exp_col2[1]) ? Mysql::escape($exp_col2[0]).".`".Mysql::escape($exp_col2[1])."`" : "`".Mysql::escape($st)."`";
      $state = "=";
    }

    $this->arr[$type][] = $tb . ' ON ' . $col1 . ($state . $col2);
    return new static($this->arr);
  }
  private function whereExtract($arr){
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
        $state = in_array(strtoupper($key),$this->statements) ? strtoupper($key) : "=";
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
  private function error($caller){
    if(Candy::isDev()) printf("Candy Mysql Error: %s\n<br />".$caller['file'].' : '.$caller['line'], mysqli_error(Mysql::$conn));
    return false;
  }
}
