<?php
class Route {

 public function page($page,$controller,$type = 'page'){
   $get_page = isset($_GET['page']) ? $_GET['page'] : '';
   if($get_page==$page){
     if(!defined('PAGE')){
     define('PAGE',$controller);
     define('PAGE_METHOD',$type);
    }
   }
 }
 public function get($page,$controller){
    $get_page = isset($_GET['page']) ? $_GET['page'] : '';
    if($get_page==$page){
      if(!defined('PAGE')){
      define('PAGE',$controller);
      define('PAGE_METHOD','get');
     }
    }
  }
  public function post($page,$controller){
     $get_page = isset($_GET['page']) ? $_GET['page'] : '';
     if($get_page==$page){
       if(!defined('PAGE')){
       define('PAGE',$controller);
       define('PAGE_METHOD','post');
      }
     }
   }

 public function page404($controller){
   if(!defined('PAGE')){
     define('PAGE404',$controller);
     define('PAGE404_METHOD','page');
   }
 }

 public function printPage(){
   global $view;
   global $candy;
   function set($p,$v){
     global $candy;
     $candy->set($p,$v);
   }
   if(defined('PAGE')){
     include('controller/controller_'.PAGE.'.php');
   }elseif(defined('PAGE404')){
     include('controller/controller_'.PAGE404.'.php');
   }
   $view->printView();
 }
}

$route = new Route();
