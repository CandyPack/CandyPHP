<?php
class Storage
{
    private static $_path = 'sys.json';
    private static $_json = '';
    private static $_arr;

    public static function select($s='sys'){
      self::$_arr = new \stdClass();
      $s = explode('/',$s);
      self::$_path = BASE_PATH.'/storage/';
      for($i=0; $i < (count($s)-1); $i++){
        self::$_path .= $s[$i].'/';
        if(!is_dir(self::$_path)) mkdir(self::$_path, 0777, true);
      }
      self::$_path .= end($s).'.json';
      if(!file_exists(self::$_path)) file_put_contents(self::$_path, '');
      self::$_json = file_get_contents(self::$_path,FILE_USE_INCLUDE_PATH);
      self::$_arr = json_decode(self::$_json);
      return new static();
    }

    public static function set($key,$var){
      if(!is_object(self::$_arr)) self::$_arr = new \stdClass();
      self::$_arr->$key = $var;
      self::$_json = json_encode(self::$_arr);
      file_put_contents(self::$_path, self::$_json);
      return new static();
    }

    public static function get($key){
      if(isset(self::$_arr->$key)) return self::$_arr->$key;
      else return new \stdClass();
    }

    public static function check($key){
      return isset(self::$_arr->$key);
    }
}
