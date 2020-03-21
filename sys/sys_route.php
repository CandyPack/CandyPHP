<?php
class Route {
  public $is_cron = false;
  public $request = array();

  public static function all($controller,$type = 'page'){
    global $request;
    $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
    if(!defined('PAGE')){
      define('PAGE',$controller);
      define('PAGE_METHOD','page');
      $request['page'] = $get_page;
    }
  }
  public static function page($page,$controller,$type = 'page'){
    $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
    if($get_page==$page || self::checkRequest($page,$get_page)){
      if(!defined('PAGE')){
        define('PAGE',$controller);
        define('PAGE_METHOD','page');
      }
    }
  }
  public static function get($page,$controller,$check='',$t=true){
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
  public static function post($page,$controller,$check='',$t=true){
    if(Candy::postCheck($check,$t)){
      $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
      if(($get_page==$page || self::checkRequest($page,$get_page)) && isset($_POST)){
        if(!defined('PAGE')){
          define('PAGE',$controller);
          define('PAGE_METHOD','post');
        }
      }
    }
  }
  public static function page404($controller){
      define('PAGE404',$controller);
  }
  public static function printPage(){
    global $view;
    global $candy;
    global $conn;
    global $request;
    function set($p,$v){
      global $candy;
      $candy->set($p,$v);
    }
    function request($v,$method=null){
      global $request;
        if($method==null){
          if(isset($request[$v])){
            return $request[$v];
          }elseif(isset($_POST[$v])){
            return $_POST[$v];
          }elseif(isset($_GET[$v])){
            return $_GET[$v];
          }
        }else{
          switch($method){
            case 'post':
            return isset($_POST[$v]) ? $_POST[$v] : null;
            break;
            case 'get':
            return isset($_GET[$v]) ? $_GET[$v] : null;
            break;
          }
        }
      }
      if(defined('PAGE')){
        $page = PAGE_METHOD.'/'.PAGE;
        if(strpos(PAGE, '.') !== false){
          $page = str_replace('.','/',PAGE);
          $page = preg_replace("((.*)\/)", "$1/".PAGE_METHOD.'/', $page);
        }
      }
      if(defined('PAGE') && file_exists('controller/'.$page.'.php')){
        include('controller/'.$page.'.php');
        if(defined('DIRECT_404') && DIRECT_404 && defined('PAGE404') && file_exists('controller/page/'.PAGE404.'.php')){
          http_response_code(404);
          include('controller/page/'.PAGE404.'.php');
        }
      }elseif(defined('PAGE404') && file_exists('controller/page/'.PAGE404.'.php')){
        http_response_code(404);
        include('controller/page/'.PAGE404.'.php');
      }
      $view->printView();
      if(isset($GLOBALS['_candy']['oneshot'])){
      $_SESSION['_candy']['oneshot'] = $GLOBALS['_candy']['oneshot'];
      }else{
        unset($_SESSION['_candy']['oneshot']);
      }
    }
  public static function cron($controller,$array='*'){
    return Cron::controller($controller);
  }
  public static function authPage($page,$controller,$else=''){
    if(Mysql::userCheck(false)){
      Route::page($page,$controller);
    }elseif($else!=''){
      Route::page($page,$else);
    }
  }
  public static function authGet($page,$controller,$else='',$check='',$t=true){
    if(Mysql::userCheck(false)){
      Route::get($page,$controller,$check,$t);
    }elseif($else!=''){
      Route::get($page,$else,$check,$t);
    }
  }
  public static function authPost($page,$controller,$else='',$check='',$t=true){
    if(Mysql::userCheck(false)){
      Route::post($page,$controller,$check,$t);
    }elseif($else!=''){
      Route::post($page,$else,$check,$t);
    }
  }
  private static function checkRequest($route,$page){
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
            $url .= $chk_page;
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
  public static function print(){
    $route = new Route;
    $directory = 'controller';
    $import = array_diff(scandir($directory), array('..', '.','page','post','get','cron'));
    foreach ($import as $key){
      if(substr($key,-4)=='.php'){
        include('controller/'.$key);
      }
    }
    $arr_subs = explode('.',$_SERVER['HTTP_HOST']);
    $domain = '';
    $routefile = 'www';
    foreach ($arr_subs as $key){
      $domain .= $key.'.';
      if(file_exists('route/'.substr($domain,0,-1).'.php')){
        $routefile = substr($domain,0,-1);
      }
    }
    require_once('route/'.$routefile.'.php');
    if(isset($_GET['_candy']) && $_GET['_candy']!=''){
      switch($_GET['_candy']){
        case 'token':
          if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'])['host']==$_SERVER['HTTP_HOST']){
            header("Content-Type: application/json; charset=UTF-8");
            echo Candy::token('json');
          }else{self::printPage();}
          break;
        case 'cron':
          if(defined('CRON_JOBS') && CRON_JOBS===true){
              if((substr($_SERVER['SERVER_ADDR'],0,8)=='192.168.') || ($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'])){
                if($_SERVER['HTTP_USER_AGENT']=='candyPHP-cron'){
                  function set($p,$v){
                    global $candy;
                    $candy->set($p,$v);
                  }
                  foreach ($GLOBALS['cron'] as $key => $value){
                    if($value){
                        include('controller/cron/'.$key.'.php');
                    }
                  }
                }else{self::printPage();}
              }else{self::printPage();}
            }
          break;
        default:
          self::printPage();
      }
    }else{
      self::printPage();
    }
  }
}

$route = new Route();
