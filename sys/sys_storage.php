<?php
class Storage
{
    private static $_path = 'sys.json';
    private static $_json = '';
    private static $_arr;

    public static function select($s='sys'){
      self::$_arr = new \stdClass();
      self::$_path = BASE_PATH.'/storage/'.$s.'.json';
      if(!file_exists(self::$_path)){
        if(!file_exists('storage/')){
          mkdir('storage/', 0777, true);
        }
        $_file = fopen(self::$_path, "w");
        fclose($_file);
      }
      $_file = fopen(self::$_path, "r");
      self::$_json = fgets($_file);
      fclose($_file);
      self::$_arr = json_decode(self::$_json);
      return new static();
    }

    public static function set($key,$var){
      if(!is_object(self::$_arr)){
        self::$_arr = new \stdClass();
      }
      self::$_arr->$key = $var;
      self::$_json = json_encode(self::$_arr);
      $_file = fopen(self::$_path, "w");
      fwrite($_file, self::$_json);
      fclose($_file);
      return new static();
    }

    public static function get($key){
      if(isset(self::$_arr->$key)){
        return self::$_arr->$key;
      }else{
        return new \stdClass();
      }
    }

    public static function check($key){
      if(isset(self::$_arr->$key)){
        return true;
      }else{
        return false;
      }
    }
}
