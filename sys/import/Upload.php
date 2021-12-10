<?php
<?php

namespace Candy;

class Upload {
  function __construct($name=''){
    $this->name = $name;
  }

  public function put($file=null){
    if(!isset($_FILES[$this->name])) return false;
    if(!$file) 'storage/upload/'.Candy::var(basename($_FILES[$this->name]['name']))->slug();
    if(!$this->dir($file)) return false;
    return move_uploaded_file($_FILES[$this->name]['tmp_name'], $file);
  }

  private function dir($path){
    if(!Candy::var($path)->contains('/')) return true;
    $exp = explode('/',$path);
    unset($exp[count($exp) - 1]);
    $dir = '';
    foreach($exp as $key){
      $dir .= ($dir === '' ? '' : '/').$key;
      if(!file_exists($dir) || !is_dir($dir)) mkdir($dir);
    }
    return file_exists($dir) && is_dir($dir);
  }
}
