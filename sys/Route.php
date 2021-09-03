<?php
class Route {
  protected static $is_cron = false;
  protected static $request = [];

  public static function all($controller,$type = 'page'){
    $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
    if(is_callable($controller)){
      $return = $controller();
      if(!empty($return) && $return !== 1) Candy::return($return);
      $GLOBALS['_candy']['route']['page'] = false;
      $GLOBALS['_candy']['route']['method'] = 'page';
    }else{
      $GLOBALS['_candy']['route']['page'] = $controller;
      $GLOBALS['_candy']['route']['method'] = 'page';
      self::$request['page'] = $get_page;
    }
  }

  public static function page($page,$controller,$type = 'page'){
    $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
    $page = substr($page,0,1) == '/' ? substr($page,1) : $page;
    if(self::checkRequest($page,$get_page)){
      if(is_callable($controller)){
        $return = $controller();
        if(!empty($return) && $return !== 1) Candy::return($return);
        $GLOBALS['_candy']['route']['page'] = false;
        $GLOBALS['_candy']['route']['method'] = 'page';
      }else{
        $GLOBALS['_candy']['route']['page'] = $controller;
        $GLOBALS['_candy']['route']['method'] = 'page';
      }
    }
  }
  public static function get($page,$controller,$check='',$t=true){
    $arr_get = $_GET;
    $page = substr($page,0,1) == '/' ? substr($page,1) : $page;
    unset($arr_get['_page']);
    if(Candy::getCheck($check,$t)){
      $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
      if(self::checkRequest($page,$get_page) && isset($_GET)){
        $GLOBALS['_candy']['route']['page'] = $controller;
        $GLOBALS['_candy']['route']['method'] = 'get';
      }
    }
  }
  public static function post($page,$controller,$check='',$t=true){
    $page = substr($page,0,1) == '/' ? substr($page,1) : $page;
    if(Candy::postCheck($check,$t)){
      $get_page = isset($_GET['_page']) ? $_GET['_page'] : '';
      if(self::checkRequest($page,$get_page) && isset($_POST)){
        $GLOBALS['_candy']['route']['page'] = $controller;
        $GLOBALS['_candy']['route']['method'] = 'post';
      }
    }
  }
  public static function error($code, $page){
    if(is_callable($page)){
      $page();
    }else{
      if(!isset($GLOBALS['_candy'])) $GLOBALS['_candy'] = [];
      if(!isset($GLOBALS['_candy']['route'])) $GLOBALS['_candy']['route'] = [];
      if(!isset($GLOBALS['_candy']['route']['error'])) $GLOBALS['_candy']['route']['error'] = [];
      if(!isset($GLOBALS['_candy']['route']['error']['controller'])) $GLOBALS['_candy']['route']['error']['controller'] = [];
      if(strpos($page, '.') !== false){
        $controller = str_replace('.','/',$page);
        $controller = preg_replace("((.*)\/)", "$1/error/", $controller);
      }else{
        $controller = $page;
      }
      $GLOBALS['_candy']['route']['error'][$code] = $controller;
      $GLOBALS['_candy']['route']['error']['controller'][$code] = $page;
    }
  }
  public static function page404($controller){
    if(!isset($GLOBALS['_candy']['route']['error']['404'])) $GLOBALS['_candy']['route']['error']['404'] = 'page/'.$controller;
  }
  public static function printPage(){
    global $view;
    global $candy;
    global $conn;
    Config::checkBruteForce();
    if(!defined('CANDY_REQUESTS')) define('CANDY_REQUESTS',self::$request);
    if(!function_exists('set')){
      function set($p,$v=null,$a=null){
        return Candy::set($p,$v,$a);
      }
    }
    if(isset($GLOBALS['_candy']['route']['method']) && $GLOBALS['_candy']['route']['method'] == 'page'){
      Candy::$ajax_var = new stdClass();
      Candy::$ajax_var->candy = new \stdClass();
      Candy::$ajax_var->candy->token = Candy::token(null,true);
      Candy::$ajax_var->candy->page = Candy::page();
      setcookie('candy',json_encode(Candy::$ajax_var),0,"/",false,true);
    }
    if(!function_exists('request')){
      function request($v=null,$method=null){
        if($v==null){
          $rec = file_get_contents('php://input');
          $json = json_decode($rec);
          if(json_last_error() == JSON_ERROR_NONE){
            return $json;
          }
          $xml = @simplexml_load_string($rec);
          if($xml){
            return new SimpleXMLElement($rec);;
          }else{
            return false;
          }
        }elseif($method==null){
          if(isset(CANDY_REQUESTS[$v])){
            return is_string(CANDY_REQUESTS[$v]) ? trim(CANDY_REQUESTS[$v]) : CANDY_REQUESTS[$v];
          }elseif(isset($_POST[$v])){
            return is_string($_POST[$v]) ? trim($_POST[$v]) : $_POST[$v];
          }elseif(isset($_GET[$v])){
            return is_string($_GET[$v]) ? trim($_GET[$v]) : $_GET[$v];
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
        function __(){
          $arr = func_get_args();
          $arr[0] = Lang::get($arr[0]);
          return call_user_func_array("sprintf", $arr);
        }
      }
      if(isset($GLOBALS['_candy']['route']['page'])){
        $page = $GLOBALS['_candy']['route']['method'].'/'.$GLOBALS['_candy']['route']['page'];
      if(strpos($GLOBALS['_candy']['route']['page'], '.') !== false){
        $page = str_replace('.','/',$GLOBALS['_candy']['route']['page']);
        $page = preg_replace("((.*)\/)", "$1/".$GLOBALS['_candy']['route']['method'].'/', $page);
      }
    }else{
      http_response_code(404);
      $GLOBALS['_candy']['route']['page'] = isset($GLOBALS['_candy']['route']['error']['controller']['404']) ? $GLOBALS['_candy']['route']['error']['controller']['404'] : '';
      $page = isset($GLOBALS['_candy']['route']['error']['404']) ? $GLOBALS['_candy']['route']['error']['404'] : die();
    }
    header('X-Candy-Page: '.(isset($GLOBALS['_candy']['route']['page']) ? $GLOBALS['_candy']['route']['page'] : ''));
    if(file_exists('controller/'.$page.'.php')){
      if(!defined('PAGE')) define('PAGE', $page);
      $return = include 'controller/'.$page.'.php';
      if(!empty($return) && $return !== 1) Candy::return($return);
    }
    $view->printView();
    if(isset($GLOBALS['_candy']['oneshot'])){
      $_SESSION['_candy']['oneshot'] = $GLOBALS['_candy']['oneshot'];
    }else{
      unset($_SESSION['_candy']['oneshot']);
    }
  }
  public static function cron($controller,$array='*'){
    $cron = new Cron();
    return $cron->controller($controller);
  }
  public static function authPage($page,$controller,$else=''){
    if(isset($GLOBALS['_candy']['auth']['status']) && $GLOBALS['_candy']['auth']['status'] ? Auth::check() : Mysql::userCheck(false)){
      Route::page($page,$controller);
    }elseif($else!=''){
      Route::page($page,$else);
    }
  }
  public static function authGet($page,$controller,$else='',$check='',$t=true){
    if(isset($GLOBALS['_candy']['auth']['status']) && $GLOBALS['_candy']['auth']['status'] ? Auth::check() : Mysql::userCheck(false)){
      Route::get($page,$controller,$check,$t);
    }elseif($else!=''){
      Route::get($page,$else,$check,$t);
    }
  }
  public static function authPost($page,$controller,$else='',$check='',$t=true){
    if(isset($GLOBALS['_candy']['auth']['status']) && $GLOBALS['_candy']['auth']['status'] ? Auth::check() : Mysql::userCheck(false)){
      Route::post($page,$controller,$check,$t);
    }elseif($else!=''){
      Route::post($page,$else,$check,$t);
    }
  }
  private static function checkRequest($route,$page){
    if(isset($GLOBALS['_candy']['route']['page'])) return false;
    if($route==$page){
      self::$request = [];
      return true;
    }
    if(strpos($route, '{')===false || strpos($route, '}')===false || $route=='') return false;
      $continue = true;
      $var = [];
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
        self::$request = $var;
      }else{
        $return = false;
      }
      return $return;
  }
  public static function print(){
    global $conn;
    Config::devmodeVersion();
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
    $GLOBALS['_candy']['route']['name'] = $routefile;
    if (php_sapi_name() == "cli" && !empty($_SERVER['argv'])) {
      if($_SERVER['argv'][1] != 'candy') self::printPage();
      switch ($_SERVER['argv'][2]) {
        case 'cron':
          if(!defined('CRON_JOBS') || CRON_JOBS !== true) self::printPage();
          $GLOBALS['cron'] = [];
          if(file_exists("route/".$_SERVER['argv'][3])){
            $routefile = $_SERVER['argv'][3];
            include("route/$routefile");
          }
          $now = date('Y-m-d H:i');
          $storage = Candy::storage('sys')->get('cron');
          $storage->route = isset($storage->route) ? $storage->route : new \stdClass;
          $storage->route->$routefile = isset($storage->route->$routefile) ? $storage->route->$routefile : new \stdClass;
          if(!isset($storage->route->$routefile->run)) $storage->route->$routefile->run = '0000-00-00 00:00';
          if($storage->route->$routefile->run == $now) return false;
          $storage->route->$routefile->run = $now;
          Candy::storage('sys')->set('cron',$storage);
          function set($p,$v=null,$a=null){
            return Candy::set($p,$v,$a);
          }
          if(isset($GLOBALS['cron'])){
            foreach ($GLOBALS['cron'] as $key => $value){
              if($value){
                $cron = 'cron/'.$key;
                if(strpos($key, '.') !== false){
                  $cron = str_replace('.','/',$key);
                  $cron = preg_replace("((.*)\/)", "$1/".'cron'.'/', $cron);
                }
                Candy::async(function($cron){
                  include('controller/'.$cron.'.php');
                },$cron);
              }
            }
          }
          break;
      }
    }else if(isset($_GET['_candy']) && $_GET['_candy']!=''){
      switch($_GET['_candy']){
        case 'token':
          if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'])['host']==$_SERVER['HTTP_HOST']){
            header('Access-Control-Allow-Origin: '.($_SERVER['SERVER_PORT']==443 ? 'https://' : 'http://').$_SERVER['HTTP_HOST']);
            header("Content-Type: application/json; charset=UTF-8");
            Candy::return([
              'page' => isset($GLOBALS['_candy']['route']['page']) ? $GLOBALS['_candy']['route']['page'] : $GLOBALS['_candy']['route']['error']['controller']['404'],
              'token' => Candy::token()
            ]);
          }else{self::printPage();}
          break;
        case 'cron':
          if(defined('CRON_JOBS') && CRON_JOBS===true){
              if((substr($_SERVER['SERVER_ADDR'],0,8)=='192.168.') || ($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'])){
                if($_SERVER['HTTP_USER_AGENT']=='candyPHP-cron'){
                  $now = date('Y-m-d H:i');
                  $storage = Candy::storage('sys')->get('cron');
                  $storage->route = isset($storage->route) ? $storage->route : new \stdClass;
                  $storage->route->$routefile = isset($storage->route->$routefile) ? $storage->route->$routefile : new \stdClass;
                  if(!isset($storage->route->$routefile->run)) $storage->route->$routefile->run = '0000-00-00 00:00';
                  if($storage->route->$routefile->run == $now) return false;
                  $storage->route->$routefile->run = $now;
                  Candy::storage('sys')->set('cron',$storage);
                  function set($p,$v=null,$a=null){
                    return Candy::set($p,$v,$a);
                  }
                  if(isset($GLOBALS['cron'])){
                    foreach ($GLOBALS['cron'] as $key => $value){
                      if($value){
                        $cron = 'cron/'.$key;
                        if(strpos($key, '.') !== false){
                          $cron = str_replace('.','/',$key);
                          $cron = preg_replace("((.*)\/)", "$1/".'cron'.'/', $cron);
                        }
                        Candy::async(function($cron){
                          include('controller/'.$cron.'.php');
                        },$cron);
                      }
                    }
                  }
                }else{self::printPage();}
              }else{self::printPage();}
            }
          break;
        case 'async':
          if(!isset($_GET['hash'])) break;
          if(!((substr($_SERVER['SERVER_ADDR'],0,8)=='192.168.') || ($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']))) return self::printPage();
          $storage = Candy::storage('sys')->get('cache');
          $hash = $_GET['hash'];
          if(!file_exists('storage/cache/async_'.$_GET['hash'].'.php')) return self::printPage();
          function set($p,$v=null,$a=null){
            return Candy::set($p,$v,$a);
          }
          ignore_user_abort(true);
          $GLOBALS['_candy_async'] = $_GET['hash'];
          $storage = Candy::storage("cache/async/".$_GET['async_data'])->get('data');
          $f = BASE_PATH.'/storage/cache/async_'.$_GET['hash'].'.php';
          $GLOBALS['_candy']['cached'][$f]['file'] = $storage->file;
          $GLOBALS['_candy']['cached'][$f]['line'] = $storage->line;
          include($f);
          break;
        default:
          self::printPage();
      }
    }else{
      self::printPage();
    }
    if(defined('MYSQL_CONNECT') && MYSQL_CONNECT==true) Mysql::closeAll();
  }
}

$route = new Route();
