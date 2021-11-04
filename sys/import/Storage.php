<?php

namespace Candy;

class Storage {
    private $path = 'sys.json';
    private $json = '';
    private $arr;
    private $cons = false;

    function __construct($s='sys') {
      $this->cons = true;
      $this->select($s);
      $this->cons = false;
    }

    public function select($s='sys'){
      $this->arr = new \stdClass();
      $s = explode('/',$s);
      $this->path = BASE_PATH.'/storage/';
      for($i=0; $i < (count($s)-1); $i++){
        $this->path .= $s[$i].'/';
        if(!is_dir($this->path)) mkdir($this->path, 0777, true);
      }
      $this->path .= end($s).'.json';
      if(!is_dir(BASE_PATH.'/storage/')) mkdir(BASE_PATH.'/storage');
      if(!file_exists($this->path)) file_put_contents($this->path, '');
      $this->json = file_get_contents($this->path,FILE_USE_INCLUDE_PATH);
      $this->arr = json_decode($this->json);
      if(!$this->cons) return new static();
    }

    public function set($key,$var){
      if(!is_object($this->arr)) $this->arr = new \stdClass();
      $this->arr->$key = $var;
      $this->json = json_encode($this->arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
      file_put_contents($this->path, $this->json);
      return new static();
    }

    public function get($key){
      if(isset($this->arr->$key)) return $this->arr->$key;
      else return new \stdClass();
    }

    public function check($key){
      return isset($this->arr->$key);
    }
}
