<?php
class Mysql_Table {
  protected $arr = [];
  protected $result = [];
  protected $statements = ['=','>','>=','<','<=','!=','LIKE','NOT LIKE','IN','NOT IN','BETWEEN','NOT BETWEEN'];
  protected $val_statements = ['IS NULL','IS NOT NULL'];

 function __construct($arr=null) {
    if(is_array($arr)){
      $this->arr = $arr;
    }else{
      $this->arr = [];
    }
  }

  function query($type = null){
    $arr_q = ['inner join', 'right join', 'left join', 'where','group by','order by','limit'];
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
    if($type == 'add') return "INSERT INTO ".$this->escape($this->arr['table'],'table').' ('.$this->arr['into'].') VALUES ('.$this->arr['values'].')';
    if($type == 'get') return "SELECT ".(isset($this->arr['select']) ? $this->arr['select'] : '*')." FROM `".$this->arr['table']."` ".$query;
    if($type == 'set') return "UPDATE `".$this->arr['table']."` SET ".$this->arr['set']." ".$query;
    if($type == 'delete') return "DELETE FROM `".$this->arr['table']."` ".$query;
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
      $this->arr['where'] = isset($this->arr['where']) && trim($this->arr['where'])!='' ? $this->arr['where'].' AND '.$this->whereExtract(func_get_args()) : $this->whereExtract(func_get_args());
    }
    return new static($this->arr);
  }
  function orWhere(){
    if(count(func_get_args()) > 0){
      $this->arr['where'] = isset($this->arr['where']) && trim($this->arr['where'])!='' ? $this->arr['where'].' OR '.$this->whereExtract(func_get_args()) : $this->whereExtract(func_get_args());
    }
    return new static($this->arr);
  }
  function whereJson($col,$val){
    //return 'JSON_SEARCH('.$col.', "one", "'.$val.'") IS NOT NULL';
    return new static($this->arr);
  }
  function get($b=false){
    $query = $this->query('get');
    $data = [];
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false) return $this->error();
    while($row = ($b ? mysqli_fetch_assoc($sql) : mysqli_fetch_object($sql))){
      $data[] = $row;
    }
    mysqli_free_result($sql);
    return $data;
  }
  function delete($b=false){
    $query = $this->query('delete');
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql;
  }
  function rows($b=false){
    $query = $this->query('get');
    $data = [];
    $sql = mysqli_query(Mysql::$conn, $query);
    return $sql===false ? false : mysqli_num_rows($sql);
  }
  function set($arr,$val=null){
    $vars = "";
    if(!is_array($arr) && $val !== null){
      $vars .= $this->escape($arr,'col').' = '. $this->escape($val) .',';
    }else{
      foreach($arr as $key => $val) {
        $vars .= $this->escape($key,'col').' = '. $this->escape($val) .',';
      }
    }
    $this->arr['set'] = substr($vars,0,-1);
    $query = $this->query('set');
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false) return $this->error();
    $this->affected = mysqli_affected_rows(Mysql::$conn);
    return new static($this->arr);
    return $sql;
  }
  function add($arr){
    $query_key = '';
    $query_val = '';
    foreach ($arr as $key => $val){
      $query_key .= $this->escape($key,'col').',';
      $query_val .= $this->escape($val).',';
    }
    $this->arr['into'] = substr($query_key,0,-1);
    $this->arr['values'] = substr($query_val,0,-1);
    $query = $this->query('add');
    $sql = mysqli_query(Mysql::$conn, $query);
    if($sql === false) return $this->error();
    $this->success = $sql;
    $this->id = mysqli_insert_id(Mysql::$conn);
    return new static($this->arr);
  }
  function first($b=false){
    $this->arr['limit'] = 1;
    $sql = $this->get($b);
    if($sql === false || !isset($sql[0])) return false;
    return $sql[0];
  }
  function select(){
    $this->arr['select'] = isset($this->arr['select']) ? $this->arr['select'] : '';
    $select = array_filter(explode(',',$this->arr['select']));
    if(count(func_get_args())==1 && is_array(func_get_args()[0])){
      if(isset(func_get_args()[0]['ct']) && isset(func_get_args()[0]['v']) && func_get_args()[0]['ct'] == $GLOBALS['candy_token_mysql']){
        $select[] = func_get_args()[0]['v'];
      }else{
        foreach(func_get_args()[0] as $key => $value){
          $select[] = $this->escape($key,'col').' AS '.$this->escape($value);
        }
      }
    }else{
      foreach(func_get_args() as $key){
        $select[] = $this->escape($key,'col');
      }
    }
    $this->arr['select'] = implode(', ',$select);
    return new static($this->arr);
  }
  function orderBy($v1,$v2='asc'){
    $this->arr['order by'] = $this->escape($v1,'col').(strtolower($v2) == 'desc' ? 'DESC' : 'ASC');
    return new static($this->arr);
  }
  function groupBy($v){
    $this->arr['group by'] = $this->escape($v,'col');
    return new static($this->arr);
  }
  function limit($v1,$v2=null){
    $this->arr['limit'] = $v2===null ? $v1 : "$v1, $v2";
    return new static($this->arr);
  }
  function leftJoin($tb,$col1,$st=null,$col2=null){
    return $this->join($tb,$col1,$st,$col2,'left join');
  }
  function rightJoin($tb,$col1,$st=null,$col2=null){
    return $this->join($tb,$col1,$st,$col2,'right join');
  }
  function join($tb,$col1,$st=null,$col2=null,$type='inner join'){
    $this->arr[$type] = isset($this->arr[$type]) ? $this->arr[$type] : [];
    $tb = $this->escape($tb,'col');
    if($st===null && $col2===null){
      $col1 = self::whereExtract($col1);
      $col2 = '';
      $state = '';
    }else{
      $col1 = $this->escape($col1,'col');
      $col2 = $this->escape(($col2 !== null ? $col2 : $st),'col');
      $state = $this->escape(($col2 !== null ? $st : '='),'st');
    }
    $this->arr[$type][] = $tb . ' ON ' . $col1 . $state . $col2;
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
        $q .= $last == 1 ? ' AND '.$this->whereExtract($key) : $this->whereExtract($key);
        $in_arr = true;
        $last = 1;
      }elseif(count($arr)==2 && $loop==2){
        if(!is_array($key) && in_array(strtoupper($key),$this->val_statements)){
          $q .= " ".strtoupper($key);
        }else{
          $q .= " =" . $this->escape($key);
        }
      }elseif($in_arr){
        $q .= strtoupper($key)=='OR' ? " OR " : " AND ";
        $last = 2;
      }elseif(count($arr)==3 && $loop==2){
        $state = in_array(strtoupper($key),$this->statements) ? strtoupper($key) : "=";
        $q .= " ".$state;
        $last = 1;
      }else{
        $q .= ' '.$this->escape($key,($loop==1 ? 'table' : 'value')).' ';
        $last = 1;
      }
        $loop++;
    }
    return '('.$q.')';
  }
  private function escape($v,$type = 'value'){
    if(is_array($v) && isset($v['ct']) && isset($v['v']) && $v['ct']==$GLOBALS['candy_token_mysql']) return ' '.$v['v'].' ';
    if($type == 'value'){
      if(is_numeric($v)) return $v;
      if(is_array($v)) return ' ("'.implode('","',array_map(function($val){return(Mysql::escape($val));},$v)).'") ';
      return ' "'.Mysql::escape($v).'" ';
    }elseif($type == 'table' || $type == 'col'){
      $as = "";
      if(is_array($v)){
        $as = array_values($v)[0];
        $v = array_keys($v)[0];
        $as = "AS $as ";
      }
      if(strpos($v,'.') !== false) return ' `'.implode('`.`',array_map(function($val){return(Mysql::escape($val));},explode('.',$v))).'` '.$as;
      return ' `'.Mysql::escape($v).'` '.$as;
    }elseif($type == 'statement' || $type == 'st'){
      return in_array(strtoupper($v),$this->statements) ? strtoupper($v) : "=";
    }
  }
  private function error($sql=null){
    $bt = debug_backtrace();
    $caller = $bt[1];
    Config::errorReport('MYSQL',mysqli_error(Mysql::$conn),$caller['file'],$caller['line']);
    if(Candy::isDev() && defined('DEV_ERRORS')) printf("Candy Mysql Error: %s\n<br />".$caller['file'].' : '.$caller['line'], mysqli_error(Mysql::$conn));
    return false;
  }
}
