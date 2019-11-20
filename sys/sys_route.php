<?php
class Route {

 public function page($page,$controller,$type = 'page'){
   $get_page = isset($_GET['page']) ? $_GET['page'] : '';
   if($get_page==$page){
     define('PAGE',$controller);
     define('PAGE_METHOD',$type);
   }
 }

 public function page404($controller){
     define('PAGE404',$controller);
     define('PAGE404_METHOD','page');
 }

 public function printPage(){
   global $view;
   global $candy;
   
   if(defined('PAGE')){
     include('controller/controller_'.PAGE.'.php');
   }elseif(defined('PAGE404')){
     include('controller/controller_'.PAGE404.'.php');
   }
 }
}

$route = new Route();
