<?php
class Candy {

  public function hello(){
    echo 'Hi, World !';
  }

  public function import($class){
    include('import/class_'.$class.'.php');
  }
}
$candy = new Candy();
