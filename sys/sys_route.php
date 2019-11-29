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
   global $conn;
   function set($p,$v){
     global $candy;
     $candy->set($p,$v);
   }
   if(defined('PAGE') && file_exists('controller/controller_'.PAGE.'.php')){
     include('controller/controller_'.PAGE.'.php');
   }elseif(defined('PAGE404') && file_exists('controller/controller_'.PAGE404.'.php')){
     http_response_code(404);
     include('controller/controller_'.PAGE404.'.php');
   }
   $view->printView();
 }

 public function cron($controller){
   $run = true;
   function minute($at){
     global $run;
     $run = $at==date('i') && $run;
   }
   if(defined('CRON_JOBS') && CRON_JOBS===true && $run){
     if(isset($_GET['_candy']) && $_GET['_candy']=='cron'){
       if($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']){
         include('cron/cron_'.$controller.'.php');
       }
     }
   }
 }
}

$route = new Route();
