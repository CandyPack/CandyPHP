<?php
class Candy {

  public function hello(){
    echo 'Hi, World !';
  }

  public function import($class){
    include('import/class_'.$class.'.php');
  }

  public function userCheck(){
    return false;
  }
}
$candy = new Candy();
