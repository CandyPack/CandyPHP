<?php
class View {
  public static function head($v){
    return self::set('head',$v);
  }
  public static function header($v){
    return self::set('header',$v);
  }
  public static function sidebar($v){
    return self::set('sidebar',$v);
  }
  public static function content($v){
    return self::set('content',$v);
  }
  public static function footer($v){
    return self::set('footer',$v);
  }
  public static function script($v){
    return self::set('script',$v);
  }
  public static function skeleton($v){
    $GLOBALS['_candy']['view']['part']['skeleton'] = $v;
    return new static();
  }
  public static function all($v){
    $GLOBALS['_candy']['view']['part']['all'] = $v;
    return new static();
  }
  public static function set($c,$v){
    if(!isset($GLOBALS['_candy'])) $GLOBALS['_candy'] = [];
    if(!isset($GLOBALS['_candy']['view'])) $GLOBALS['_candy']['view'] = [];
    if(!isset($GLOBALS['_candy']['view']['parts'])) $GLOBALS['_candy']['view']['parts'] = [];
    $GLOBALS['_candy']['view']['parts'][strtoupper($c)] = $v;
    return new static();
  }
  public static function printView(){
    global $candy;
    global $route;
    global $conn;
    if(!function_exists('get')){
      function get($v){
        global $candy;
        return $candy->get($v);
      }
    }
    $ajaxcheck = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'])['host']==$_SERVER['HTTP_HOST'];
    $ajaxcheck = $ajaxcheck && isset($_SERVER['HTTP_X_CANDY']) && $_SERVER['HTTP_X_CANDY']=='ajaxload' && isset($_SERVER['HTTP_X_CANDY_LOAD']);
    if($ajaxcheck){
      $output = [];
      $load_content = null;
      $contents = explode(',',$_SERVER['HTTP_X_CANDY_LOAD']);
      foreach($contents as $content){
        if(isset($GLOBALS['_candy']['view']['part']['all'])) $load_content = $GLOBALS['_candy']['view']['part']['all'];
        if(isset($GLOBALS['_candy']['view']['parts'][strtoupper(trim($content))])) $load_content = $GLOBALS['_candy']['view']['parts'][strtoupper(trim($content))];
        if($load_content !== null){
          $v_exp = explode('.',$load_content);
          if(count($v_exp)>0){
            $vdir = $v_exp;
            unset($vdir[count($vdir)-1]);
            $vdir = implode('/',$vdir).'/';
          }else{
            $vdir = "";
          }
          $vfile = $vdir.strtolower(trim($content)).'/'.end($v_exp).'.blade.php';
          if(file_exists('view/'.$vfile)){
            ob_start();
            include(self::cacheView($vfile));
            $output[$content] = ob_get_clean();
          }
        }
      }
      Candy::return([
        'output' => $output,
        'variables' => Candy::$ajax_var
      ]);
    }
    if((isset($GLOBALS['_candy']['view']['part']['skeleton']) || isset($GLOBALS['_candy']['view']['part']['all'])) && !$ajaxcheck){
    if(isset($GLOBALS['_candy']['view']['part']['all'])) $skeleton = 'skeleton/'.$GLOBALS['_candy']['view']['part']['all'].'.skeleton';
    $skeleton = isset($GLOBALS['_candy']['view']['part']['skeleton']) ? 'skeleton/'.$GLOBALS['_candy']['view']['part']['skeleton'].'.skeleton' : 'skeleton/page.skeleton';
    $skeleton = file_get_contents($skeleton, FILE_USE_INCLUDE_PATH);
    $arr_test = explode('{{', $skeleton);
      foreach ($arr_test as $key) {
        $load_content = null;
        if(strpos($key, '}}') !== false){
          $arr_key = explode('}}',$key);
          $load_key = strtoupper(trim($arr_key[0]));
          if(isset($GLOBALS['_candy']['view']['part']['all'])) $load_content = $GLOBALS['_candy']['view']['part']['all'];
          if(isset($GLOBALS['_candy']['view']['parts'][$load_key])) $load_content = $GLOBALS['_candy']['view']['parts'][$load_key];
          if($load_content !== null){
            $v_exp = explode('.', $load_content);
            if(count($v_exp)>0){
              $vdir = $v_exp;
              unset($vdir[count($vdir)-1]);
              $vdir = implode('/',$vdir).'/';
            }else{
              $vdir = "";
            }
            $vfile = $vdir.strtolower($load_key).'/'.end($v_exp).'.blade.php';
            if(file_exists('view/'.$vfile)){
              include(self::cacheView($vfile));
            }
          }else{
            print($key);
          }
          if(isset($arr_key[1])){
            print($arr_key[1]);
          }
        }else{
          print($key);
        }
      }
    }
  }
  public static function cacheView($v){
    $cache = false;
    $filepath = defined('DEV_VERSION') ? 'zip://' . DEV_VERSION . '#view/'.$v  : 'view/'.$v;
    if(!file_exists('storage/cache/')){
      if(!file_exists('storage/')){
        mkdir('storage/', 0777, true);
      }
      mkdir('storage/cache/', 0777, true);
    }

    $storage = Candy::storage('sys')->get('cache');
    $storage->view = isset($storage->view) && is_object($storage->view) ? $storage->view : new \stdClass;
    $storage->view->$v = isset($storage->view->$v) && is_object($storage->view->$v) ? $storage->view->$v : new \stdClass;
    $php_time = defined('DEV_VERSION') ? 0 : filemtime($filepath);
    if(defined('DEV_VERSION')){
      $cache = true;
    }elseif(!isset($storage->view->$v)){
      $cache = true;
    }elseif(!isset($storage->view->$v->time) ||  $php_time>$storage->view->$v->time){
      $cache = true;
    }elseif(!isset($storage->view->$v->file) || !file_exists($storage->view->$v->file)){
      $cache = true;
    }
    if($cache){
      if(isset($storage->view->$v->file) && !defined('DEV_VERSION')){
        if(file_exists($storage->view->$v->file)){
          unlink($storage->view->$v->file);
        }
      }
      $php_raw = file_get_contents($filepath, FILE_USE_INCLUDE_PATH);
      $regex = [
        '/@if *\((.*)\)/',
        '/@foreach *\((.*)\)/',
        '/@for *\((.*)\)/',
        '/@while *\((.*)\)/',
        '/@elseif *\((.*)\)/',
        '/@candy::(.*);/',
        '/@candy::\(?(.*)\)/'
      ];
      $replace = [
        '<?php if($1){ ?>',
        '<?php foreach($1){ ?>',
        '<?php for($1){ ?>',
        '<?php while($1){ ?>',
        '<?php }elseif($1){ ?>',
        '<?php Candy::$1; ?>',
        '<?php Candy::$1); ?>'
      ];
      $php_raw = preg_replace($regex, $replace, $php_raw);
      $str = [
        '{{--',
        '--}}',
        '{{',
        '}}',
        '@php',
        '@endphp',
        '{!!',
        '!!}',
        '@endif',
        '@endforeach',
        '@endfor',
        '@endwhile',
        '@else',
        '@end',
      ];
      $rpl = [
        '<?php /*',
        '*/ ?>',
        '<?php echo htmlentities(',
        ') ?>',
        '<?php',
        '?>',
        '<?php echo ',
        ' ?>',
        '<?php } ?>',
        '<?php } ?>',
        '<?php } ?>',
        '<?php } ?>',
        '<?php }else{ ?>',
        '<?php } ?>'
      ];
      $php_raw = str_replace($str,$rpl,$php_raw);
      $php_cache = defined('DEV_VERSION') ? 'storage/cache/dev_'.md5($v).time().'.php' : 'storage/cache/'.md5($v).time().'.php';
      $unlk = file_exists($php_cache) ? unlink($php_cache) : false;
      file_put_contents($php_cache, $php_raw);
      if(!defined('DEV_VERSION')){
        $storage->view->$v->file = $php_cache;
        $storage->view->$v->time = $php_time;
        Candy::storage('sys')->set('cache',$storage);
      }
    }else{
      $php_cache = $storage->view->$v->file;
    }
    return $php_cache;
  }
}
$view = new View();
