<?php
class Route {
  public $is_cron = false;
  public $request = array();

  public function page($page,$controller,$type = 'page'){
    $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
    if($get_page==$page || self::checkRequest($page,$get_page)){
      if(!defined('PAGE')){
        define('PAGE',$controller);
        define('PAGE_METHOD',$type);
      }
    }
  }
  public function get($page,$controller,$check='',$t=true){
    $arr_get = $_GET;
    unset($arr_get['_page']);
    if(Candy::getCheck($check,$t)){
      $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
      if(($get_page==$page || self::checkRequest($page,$get_page)) && isset($_GET)){
        if(!defined('PAGE')){
          define('PAGE',$controller);
          define('PAGE_METHOD','get');
        }
      }
    }
  }
  public function post($page,$controller,$check='',$t=true){
    if($check=='' || Candy::postCheck($check,$t)){
      $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
      if(($get_page==$page || self::checkRequest($page,$get_page)) && isset($_POST)){
        if(!defined('PAGE')){
          define('PAGE',$controller);
          define('PAGE_METHOD','post');
        }
      }
    }
  }
  public function page404($controller){
    if(!defined('PAGE')){
      define('PAGE404',$controller);
      define('PAGE_METHOD','page');
    }
  }
  public function printPage(){
    global $view;
    global $candy;
    global $conn;
    global $request;
    function set($p,$v){
      global $candy;
      $candy->set($p,$v);
    }
    function request($v){
      global $request;
      return $request[$v];
    }
    if(defined('PAGE') && file_exists('controller/controller_'.PAGE.'.php')){
      include('controller/controller_'.PAGE.'.php');
    }elseif(defined('PAGE404') && file_exists('controller/controller_'.PAGE404.'.php')){
      http_response_code(404);
      include('controller/controller_'.PAGE404.'.php');
    }
    if(PAGE_METHOD=='page' || true){
      $view->printView();
    }
  }
  public function cron($controller,$array='*'){
    global $is_cron;
    $run = true;
    $now = getdate();
    if(is_array($array)){
      if(!isset($array['minute']) || $array['minute']==='*' || $array['minute']==$now['minutes']){
        if(!isset($array['hour']) || $array['hour']==='*' || $array['hour']==$now['hours']){
          if(!isset($array['day']) || $array['day']==='*' || $array['day']==$now['mday']){
            if(!isset($array['month']) || $array['month']==='*' || $array['month']==$now['mon']){
              if(!isset($array['year']) || $array['year']==='*' || $array['year']==$now['year']){
                $run = true;
              }else{$run=false;}
            }else{$run=false;}
          }else{$run=false;}
        }else{$run=false;}
      }else{$run=false;}
    }

    if(defined('CRON_JOBS') && CRON_JOBS===true){
      if(isset($_GET['_candy']) && $_GET['_candy']=='cron'){
        if($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']){
          if($_SERVER['HTTP_USER_AGENT']=='candyPHP-cron'){
            $is_cron = true;
            if($run){
              include('cron/cron_'.$controller.'.php');
            }
          }
        }
      }
    }
  }
  public function authPage($page,$controller,$else=''){
    if(Mysql::userCheck(false)){
      Route::page($page,$controller);
    }elseif($else!=''){
      Route::page($page,$else);
    }
  }
  public function authGet($page,$controller,$else='',$check='',$t=true){
    if(Mysql::userCheck(false)){
      Route::get($page,$controller,$check,$t);
    }elseif($else!=''){
      Route::get($page,$else,$check,$t);
    }
  }
  public function authPost($page,$controller,$else='',$check='',$t=true){
    if(Mysql::userCheck(false)){
      Route::post($page,$controller,$check,$t);
    }elseif($else!=''){
      Route::post($page,$else,$check,$t);
    }
  }
  private function checkRequest($route,$page){
    global $request;
    if((strpos($route, '{')!==false) && (strpos($route, '}')!==false) && $route!=''){
      $continue = true;
      $var = array();
      $arr_route1 = explode('{',$route);
      $url = "";
      foreach ($arr_route1 as $key => $value) {
        $arr_route1[$key] = explode('}',$value);
      }
      $chk_page = $page;
      foreach ($arr_route1 as $key) {
        if(count($key)==1){
          if(substr($chk_page,0,strlen($key[0]))==$key[0]){
            $chk_page = substr($chk_page,strlen($key[0]));
            $url .= $key[0];
          }else{
            $continue = false;
          }
        }else{
          if(isset($key[1]) && $key[1]!=''){
            $arr_page = explode($key[1],$chk_page,2);
            if(isset($arr_page[1])){
              $var[$key[0]] = htmlentities($arr_page[0]);
              $chk_page = $arr_page[1];
              $url .= $arr_page[0].$key[1];
            }else{
              $continue = false;
            }
          }else{
            $var[$key[0]] = htmlentities($chk_page);
          }
        }
      }
      if($url==$page){
        $return = $continue;
        $request = $var;
      }else{
        $return = false;
      }
    }else{
      $return = false;
    }
    return $return;
  }
}

$route = new Route();
