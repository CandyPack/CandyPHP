<?php
class Candy {
  public $var;
  public static $ajax_var;
  public static $imported = [];
  public $token;
  public $postToken;
  public $getToken;
  public $tokenCheck = '';

  public static function import($class){
    if(!in_array($class,self::$imported)){
      self::$imported[] = $class;
      include(BASE_PATH."/sys/import/$class.php");
    }
  }

  public static function get($p){
    global $var;
    return isset($var->$p) ? $var->$p : null;
  }

  public static function set($p,$v=null,$ajax=false){
    global $var;
    if(empty($var)) $var = new \stdClass();
    if(empty(self::$ajax_var)) self::$ajax_var = new \stdClass();
    if(is_array($p) && ($v===null || is_bool($v))){
      foreach ($p as $key => $value){
        $var->$key = $value;
        if($v === true) self::$ajax_var->$key = $value;
      }
    }else{
      $var->$p = $v;
      if($ajax != false) self::$ajax_var->$p = $ajax===true ? $v : $ajax;
    }
    if(!isset(self::$ajax_var->candy->token)) {
      self::$ajax_var->candy = new \stdClass();
      self::$ajax_var->candy->token = self::token(null,true);
      self::$ajax_var->candy->page = self::page();
    }
    setcookie('candy',json_encode(self::$ajax_var),0,"/",false,true);
  }

  public static function configCheck(){
    header('X-POWERED-BY: Candy PHP');
    register_shutdown_function(function(){
      $error = error_get_last();
      if(!empty($error)){
        $types = [
          1    => 'Fatal',
          2    => 'Warning',
          4    => 'Syntax',
          8    => 'Notice',
          256  => 'User',
          512  => 'User Warning',
          1024 => 'User Notice',
          2048 => 'Strictly'
        ];
        $type = 'PHP '.(isset($types[$error["type"]]) ? $types[$error["type"]] : 'Unknown');
        Config::errorReport($type,$error["message"],$error["file"],$error["line"]);
      }
    });
    if(defined('MYSQL_CONNECT') && MYSQL_CONNECT==true) Mysql::connect();
    Config::runBackup();
    Config::runUpdate();
    Config::backupClear();
    if(!defined('CANDY_COMPOSER') || (defined('CANDY_COMPOSER') && CANDY_COMPOSER)){
      if(defined('CANDY_COMPOSER_DIRECTORY')){
        include(CANDY_COMPOSER_DIRECTORY);
      }elseif(file_exists('../vendor/autoload.php')){
        include('../vendor/autoload.php');
      }elseif(file_exists('vendor/autoload.php')){
        include('vendor/autoload.php');
      }
    }
    if(intval(date('d'))==2 && intval(date('H'))<=2) foreach(glob(BASE_PATH."/storage/cache/*") as $key) if(!is_dir($key) && filemtime($key)+10000 < time()) unlink($key);
  }

  public static function token($check = null, $force = false){
    global $token;
    global $tokenCheck;
    if($check === null || $check==='input' || $check==='json' || $check==='echo'){
      if($token=='' || $force){
        $token = md5(uniqid(mt_rand(), true));
        if(isset($_SESSION['_token']) && is_array($_SESSION['_token'])){
          $sess = $_SESSION['_token'];
          array_unshift($sess,$token);
          array_splice($sess,60);
        }else{
          $sess = [$token];
        }
        $_SESSION['_token'] = $sess;
      }
      if($check==='input'){
        echo '<input name="token" value="'.$token.'" hidden="">';
      }elseif($check==='json'){
        return json_encode(['token' => $token]);
      }elseif($check==='echo'){
        echo $token;
      }
      return $token;
    }else{
      if(strpos($tokenCheck, ','.$check.',') !== false){
        return true;
      }elseif(isset($_SESSION['_token']) && is_array($_SESSION['_token']) && in_array($check,$_SESSION['_token'])){
        $_SESSION['_token'] = array_diff($_SESSION['_token'], [$check]);
        $tokenCheck .= ','.$check.',';
        return true;
      }else{
        return false;
      }
    }
  }

  public static function postCheck($post='',$t=true){
    global $postToken;
    $count = 0;
    $arr_post = explode(',',$post);
    if($post!=''){
      foreach ($arr_post as $key) {
        if($key!='' && isset($_POST[$key]) && $_POST[$key]!=''){
          $count++;
        }
      }
    }elseif(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==='POST'){
      $count = 1;
    }
    if($t){
      if(isset($_POST['token']) && self::token($_POST['token']) && count($arr_post)==$count){
        if(!$t || parse_url($_SERVER['HTTP_REFERER'])['host'] == $_SERVER['HTTP_HOST']){
          return true;
        }else{
          return false;
        }
      }else{
        return false;
      }
    }else{
      if(!$t || parse_url($_SERVER['HTTP_REFERER'])['host'] == $_SERVER['HTTP_HOST']){
        return count($arr_post)==$count;
      }else{
        return false;
      }
    }
  }

  public static function getCheck($get='',$t=true){
    if($get!=''){
      $count = 0;
      $arr_get = explode(',',$get);
      foreach ($arr_get as $key) {
        if($key!='' && isset($_GET[$key]) && $_GET[$key]!=''){
          $count++;
        }
      }
      if($t){
        return isset($_GET['token']) && self::token($_GET['token']) && count($arr_get)==$count;
      }else{
        return count($arr_get)==$count;
      }
    }else{
      $arr_get = isset($_GET) ? $_GET : array();
      if($t){
        if(isset($_GET['token'])){
          return self::token($_GET['token']) && count($arr_get)>0;
        }else{
          return false;
        }
      }else{
        return count($arr_get)>0;
      }
    }
  }

  public static function isNumeric($v,$method='post'){
    $count = 0;
    $arr_get = explode(',',$v);
    foreach ($arr_get as $key) {
      if($key!='' && strtolower($method)=='get' && isset($_GET[$key]) && is_numeric($_GET[$key])){
        $count++;
      }elseif($key!='' && strtolower($method)=='post' && isset($_POST[$key]) && is_numeric($_POST[$key])){
        $count++;
      }
    }
    return count($arr_get)==$count;
  }

  public static function direct($link=null){
    if($link===404){
      self::abort(404);
    }else{
      $url = $link!==null ? $link : $_SERVER['HTTP_REFERER'];
      header('Location: '.$url);
      die();
    }
    return new class{function with($v){
      if(is_array($GLOBALS['_candy']['oneshot'])){
        $GLOBALS['_candy']['oneshot'] = array_merge($GLOBALS['_candy']['oneshot'],$v);
      }else{
        $GLOBALS['_candy']['oneshot']=$v;
      }
      return new static();}};
  }

  public static function uploadImage($postname="upload",$target = "uploads/",$filename='0',$maxsize=500000){
    $result = new \stdClass();
    $result->success = false;
    $target_dir = $target;
    $target_file = $filename=='0' ? $target_dir . basename($_FILES[$postname]["name"]) : $target_dir.$filename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo(basename($_FILES[$postname]["name"]),PATHINFO_EXTENSION));
    if(isset($_FILES[$postname])){
      $check = (!file_exists($_FILES[$postname]['tmp_name']) || !is_uploaded_file($_FILES[$postname]['tmp_name'])) ? false : getimagesize($_FILES[$postname]["tmp_name"]);
    }else{
      $check = false;
    }

    if($check !== false) {
      $result->message = "File is an image - " . $check["mime"] . ".";
      $uploadOk = 1;
    } else {
        $result->message = "File is not an image.";
        $uploadOk = 0;
    }

    if (file_exists($target_file)) {
      $result->message = "Sorry, file already exists.";
      $uploadOk = 0;
    }

    if ($check !== false && $_FILES[$postname]["size"] > $maxsize) {
      $result->message = "Sorry, your file is too large.";
      $uploadOk = 0;
    }

    if($check !== false && $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
      $result->message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
      $uploadOk = 0;
    }

    if ($uploadOk == 0) {
      if(!isset($result->message)){
        $result->message = "Sorry, your file was not uploaded.";
      }
    } else {
      if (move_uploaded_file($_FILES[$postname]["tmp_name"], $target_file)) {
        $result->message = "The file ". basename( $_FILES[$postname]["name"]). " has been uploaded.";
        $result->success = true;
      } else {
        $result->message = "Sorry, there was an error uploading your file.";
      }
    }
    return $result;
  }

  public static function slugify($text){
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
      return '';
    }
    return $text;
  }

  public static function generateFilename($filename,$extension,$path = ''){
    $loop = 0;
    $loop_file = '';
    while(file_exists($path.$filename.$loop_file.$extension)){
      $loop++;
      $loop_file = '-'.$loop;
    }
    return $filename.$loop_file.$extension;
  }

  public static function arrayElementDelete($array,$element){
    if(($key = array_search($element, $array)) !== false) {
      unset($array[$key]);
    }
    return $array;
  }

  public static function dateFormatter($date = '0', $format = 'd / m / Y'){
    $date = str_replace('/','-',$date);
    $date = new DateTime($date);
    $date = $date->format($format);
    return $date;
  }

  public static function getJs($path, $min = true){
    $minify = false;
    $file_raw = 'assets/js/'.$path;
    $file_min = str_replace('.js','.min.js',$file_raw);
    if($min && file_exists($file_raw)){
      $date_min = '1';
      if(file_exists($file_min)){
        $date_raw = filemtime($file_raw);
        $date_min = filemtime($file_min);
        if($date_raw>$date_min){
          $minify = true;
        }
      }else{
        $minify = true;
      }
      if($minify){
        $js_raw = file_get_contents($file_raw, FILE_USE_INCLUDE_PATH);
        $js_min = self::jsMinifier($js_raw);
        if(empty($js_min)) $js_min = "console.error('Candy JS', 'Error: $path file could not be minimized')\n".$js_raw;
        if(substr_count($js_min,"\n") == 2){
          $arr_errors = explode("\n",$js_min);
          $error = count($arr_errors)==3;
          $error = $error && substr($arr_errors[0],0,11) == '// Error : ';
          $error = $error && substr($arr_errors[1],0,11) == '// Line  : ';
          $error = $error && substr($arr_errors[2],0,11) == '// Col   : ';
          if($error) $js_min = "console.error('Candy JS Error','\\n','Error:','".substr($arr_errors[0],11)."','\\n','JS:','$path file could not be minimized.','\\n','Line:','".substr($arr_errors[1],11)."', '\\n','Col:','".substr($arr_errors[2],11)."');";
        }
        file_put_contents($file_min, $js_min);
      }
      echo '/'.$file_min.'?_v='.$date_min;
      return $file_min.'?_v='.$date_min;
    }else{
      echo '/assets/js/'.$path;
      return '/assets/js/'.$path;
    }
  }

  public static function getCss($path, $min = true){
    $minify = false;
    $file_raw = 'assets/css/'.$path;
    $file_min = str_replace('.css','.min.css',$file_raw);
    if($min && file_exists($file_raw)){
      $date_min = '1';
      if(file_exists($file_min)){
        $date_raw = filemtime($file_raw);
        $date_min = filemtime($file_min);
        if($date_raw>$date_min){
          $minify = true;
        }
      }else{
        $minify = true;
      }
      if($minify){
        $css_raw = file_get_contents($file_raw, FILE_USE_INCLUDE_PATH);
        self::import('Minifier');
        $minifier = new \Candy\Minifier();
        $css_min = $minifier->css($css_raw);
        file_put_contents($file_min, $css_min);
      }
      echo '/'.$file_min.'?_v='.$date_min;
      return '/'.$file_min.'?_v='.$date_min;
    }else{
      echo '/assets/css/'.$path;
      return '/assets/css/'.$path;
    }
  }

  public static function jsMinifier($js){
    $url = 'https://javascript-minifier.com/raw';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_POSTFIELDS => http_build_query([ "input" => $js ])
    ]);
    $minified = curl_exec($ch);
    curl_close($ch);
    return $minified;
  }

  public static function uploadCheck($name=null){
    if(isset($_FILES) && count($_FILES)>0){
      if($name==null){
        foreach ($_FILES as $key) {
          if($key['size']>0){
            return true;
          }
        }
      }else{
        $result = true;
        $arr_name = explode(',',$name);
        foreach ($arr_name as $key) {
          if($key!=''){
            if(isset($_FILES[$key]) && count($_FILES[$key])>0){
              if($_FILES[$key]['size']>0){
                $result = !$result ? false : true;
              }else{
                $result = false;
              }
            }else{
              $result = false;
            }
          }
        }
        return $result;
      }
    }else{
      return false;
    }
  }

  public static function mail($view){
    self::import('Mail');
    $mail = new \Candy\Mail();
    return $mail->view($view);
  }

  public static function quickMail($to,$message,$subject = '',$from = ''){
    if(is_array($from)){
      $from_name = $from['name'];
      $from_mail = $from['mail'];
    }else{
      $from_name = '';
      $from_mail = $from;
    }
    if($subject==''){
      $subject = $_SERVER['SERVER_NAME'];
    }

    $headers = "From: $from_name <$from_mail>" . "\r\n";
    $headers .= "Reply-To: ". $from_mail . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if(!(strpos($message, '<html>') !== false)){
      if(!(strpos($message, '<head>') !== false)){
        if(!(strpos($message, '<body>') !== false)){
          $message = '<body>'.$message.'</body>';
        }
        $message = '<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>'.$subject.'</title>
        <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
        <meta name="viewport" content="width=device-width">
        <meta name="robots" content="noindex,nofollow">
        <meta property="og:title" content="'.$subject.'">
        '.$message;
      }
      $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <!--[if IE]>
      <html xmlns="http://www.w3.org/1999/xhtml" class="ie">
      <![endif]-->
      <!--[if !IE]><!-->
      <html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml">
      <!--<![endif]-->'.$message.'</html>';
    }

    return !empty($from_mail) ? mail($to, $subject, $message, $headers, "-f $from_mail") : mail($to, $subject, $message, $headers);
  }

  public static function storage($s){
    return Storage::select($s);
  }

  public static function strFormatter($str,$format){
    $output = '';
    $letter = 0;
    for ($i=0; $i < strlen($format); $i++) {
      if(substr($format,$i,1)=='?'){
        $output .= substr($str,$letter,1);
        $letter = $letter + 1;
      }elseif(substr($format,$i,1)=='*'){
        $output .= substr($str,$letter);
        $letter = $letter + strlen(substr($str,$letter));
      }else{
        $output .= substr($format,$i,1);
      }
    }
    return $output;
  }

  public static function validator($v = null){
    self::import('Validation');
    $validation = new Validation();
    return $validation->validator($v);
  }

  public static function session($key){
    if(count(func_get_args())==1){
      return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }else{
      if(func_get_args()[1] !== null){
        $_SESSION[$key] = func_get_args()[1];
      }else{
        unset($_SESSION[$key]);
      }
      return true;
    }
  }

  public static function return($v){
    header("Content-Type: application/json; charset=UTF-8");
    $v = is_array($v) || is_object($v) ? $v : ['result' => $v];
    echo json_encode($v);
    die();
  }

  public static function hash($v,$h=null){
    $options = ['cost' => 11];
    if($h==null) return password_hash($v, PASSWORD_BCRYPT, $options);
    else return password_verify($v, $h);
  }

  public static function getImage($path,$size=null,$b = true){
      $resize = false;
      $file_raw = 'assets/img/'.$path;
      $arr_extension = explode('.',$file_raw);
      $sizes = explode('x',$size);
      $extension = '.'.end($arr_extension);
      $type = $b ? '0' : '1';
      $min_ext = function_exists('imagewebp') ? ".webp" : $extension;
      $file_min = ($size!==null) ? str_replace($extension,"-$type-$size".$min_ext,$file_raw) : str_replace($extension,'.min'.$min_ext,$file_raw);
      if(file_exists($file_raw)){
        $date_min = '1';
        if(file_exists($file_min)){
          $date_raw = filemtime($file_raw);
          $date_min = filemtime($file_min);
          if($date_raw>$date_min) $resize = true;
        }else $resize = true;
        if($resize){
          self::import('ResizeImage');
          $image_size = getimagesize($file_raw);
          $resize = new ResizeImage($file_raw);
          if($size!==null){
            if(isset($sizes[1])) $type = $sizes[0]>$sizes[1] ? 'maxWidth' : 'maxHeight';
            else {
              $sizes[1] = $sizes[0];
              $type = $image_size[0]<$image_size[1] ? 'maxWidth' : 'maxHeight';
            }
            $type = $b ? 'exact' : $type;
            $resize->resizeTo($sizes[0], $sizes[1], $type);
          }
          $resize->saveImage($file_min);
        }
        echo '/'.$file_min.'?_v='.$date_min;
        return '/'.$file_min.'?_v='.$date_min;
      }else{
        echo '/assets/img/'.$path;
        return '/assets/img/'.$path;
      }
  }

  public static function abort($exc=500,$msg='',$die=true){
    $status = [
      100 => 'Continue',  101 => 'Switching Protocols', 102 => 'Processing',
      200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information',
      204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content',
      207 => 'Multi-Status', 300 => 'Multiple Choices', 301 => 'Moved Permanently',
      302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy',
      307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized',
      402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found',
      405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required',
      408 => 'Request Timeout', 409 => 'Conflict',   410 => 'Gone', 411 => 'Length Required',
      412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Long',
      415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable', 417 => 'Expectation Failed',
      422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 426 => 'Upgrade Required',
      500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable',
      504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported', 506 => 'Variant Also Negotiates',
      507 => 'Insufficient Storage', 509 => 'Bandwidth Limit Exceeded', 510 => 'Not Extended'
    ];
    if(isset($status[$exc])){
      header($_SERVER['SERVER_PROTOCOL'].' '.$exc.' '.$status[$exc]);
    }else{
      http_response_code(intval($exc));
    }
    Config::checkBruteForce(10);
    if($die){
      if(isset($GLOBALS['_candy']['route']['error'][$exc]) && file_exists('controller/'.$GLOBALS['_candy']['route']['error'][$exc].'.php')){
        $GLOBALS['_candy']['route']['page'] = $GLOBALS['_candy']['route']['error']['controller'][$exc];
        header('X-Candy-Page: '.(isset($GLOBALS['_candy']['route']['page']) ? $GLOBALS['_candy']['route']['page'] : ''));
        include('controller/'.$GLOBALS['_candy']['route']['error'][$exc].'.php');
        View::printView();
      }
      die($msg);
    }
  }

  public static function curl($url,$params=null,$header=null,$method=null,$conf=[]){
    if(is_array($params)){
      $postData = http_build_query($params);
    }else{
      $postData = $params;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    if($method=='post' || $postData!=''){ curl_setopt($ch, CURLOPT_POST, 1); }
    if($postData!=''){ curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); }
    if($header!==null){ curl_setopt($ch, CURLOPT_HTTPHEADER, $header); }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
    foreach($conf as $key => $val) {
      curl_setopt($ch, constant($key),$val);
    }
    $result = curl_exec($ch);
    if(curl_errno($ch)) return curl_error($ch);
    curl_close ($ch);
    return $result;
  }

  public static function async($method, $data=null){
    if(isset($GLOBALS['_candy_async']) && $GLOBALS['_candy_async']!=null){
      unset($GLOBALS['_candy_async']);
      if(!isset($GLOBALS['_candy']['cached'])) $GLOBALS['_candy']['cached'] = [];
      $data_id = isset($_GET['async_data']) ? $_GET['async_data'] : $_SERVER['argv'][4];
      $storage = Candy::storage("cache/async/$data_id")->get('data');
      $func = new ReflectionFunction($method);
      $f = $func->getFileName();
      $GLOBALS['_candy']['cached'][$f]['file'] = $storage->file;
      $GLOBALS['_candy']['cached'][$f]['line'] = $storage->line;
      $data = ($storage->array==1) ? ((array)($storage->data)) : $storage->data;
      if(file_exists(BASE_PATH."/storage/cache/async/$data_id.json")) unlink(BASE_PATH."/storage/cache/async/$data_id.json");
      $method($data);
      $GLOBALS['_candy_async'] = null;
      return die();
    }
    $func = new ReflectionFunction($method);
    $f = $func->getFileName();
    $start_line = $func->getStartLine() - 1;
    $end_line = $func->getEndLine();
    $length = $end_line - $start_line;
    $source = file($f);
    $source = implode('', array_slice($source, 0, count($source)));
    $source = preg_split("/".PHP_EOL."/", $source);
    $body = '';
    for($i=$start_line; $i<$end_line; $i++){
      if($i + 1 == $end_line && substr_count($source[$i],'}')==1 && substr(trim($source[$i]),0,1)=='}'){
        $body .= '});';
        continue;
      }
      $body .= "{$source[$i]}\n";
    }
    $body = trim($body);
    $func_hash = md5($body);
    $file = BASE_PATH.'/storage/cache/async_'.$func_hash.'.php';
    if(!file_exists($file) || (filemtime($file) + 80000) < time()){
      if(file_exists($file)) unlink($file);
      if(substr($body,0,1)=='$'){
        $body = preg_replace('/'.preg_quote('$', '/').'/', '/*', $body, 1);
        $body = preg_replace('/'.preg_quote('=', '/').'/', '*/', $body, 1);
        if(substr($body,-1)==';') $body = 'Candy::async('.substr($body,0,-1).');';
      }else{
        if(strpos($body,'Candy::async(') !== false) $body = explode('Candy::async(',$body,2)[1];
        if(substr($body,0,8)=='function') $body = 'Candy::async('.$body;
        if(substr($body,-1)=='}') $body = $body.');';
      }
      if(substr($body,0,5)=='<?php') $body = preg_replace('/'.preg_quote('<?php', '/').'/', '', $body, 1);
      $body = '<?php '.$body;
      $split = str_split($body);
      $begin = null;
      $result = '';
      $escape = false;
      foreach($split as $key) {
        $result .= $key;
        if($escape === false){
          if($key == '{') $begin = $begin !== null ? $begin + 1 : 1;
          if($key == '}') $begin -= 1;
        }
        if($key == '"') $escape = $key;
        elseif($key == "'") $escape = $key;
        elseif($key == $escape) $escape = false;
        if($begin === 0) break;
      }
      $result = trim($result);
      if(Candy::string($result)->isBegin('<?php Candy::async') && !Candy::string($result)->isEnd(');')) $result .= ');';
      $body = $result;
      file_put_contents($file, $body);
      $storage = Candy::storage('sys')->get('cache');
      $storage->async = isset($storage->async) && is_object($storage->async) ? $storage->async : new \stdClass;
      $storage->async->$func_hash = isset($storage->async->$func_hash) && is_object($storage->async->$func_hash) ? $storage->async->$func_hash : new \stdClass;
      $storage->async->$func_hash = '1';
      Candy::storage('sys')->set('cache',$storage);
    }
    $datas = ['hash' => $func_hash, 'data' => $data, 'array' => is_array($data) ? 1 : 0];
    $date = date('YmdH');
    $data_id = mt_rand().time().rand(100,999);
    $storage = ['hash' => $func_hash, 'data' => $data, 'array' => is_array($data) ? 1 : 0, 'file' => $f, 'line' => $start_line];
    Candy::storage("cache/async/$data_id")->set('data',$storage);
    if(isset($_SERVER['SERVER_NAME'])){
      $curl = self::curl(str_replace('www.','',$_SERVER['SERVER_NAME'])."/?_candy=async&hash=$func_hash&async_data=$data_id",
                 $datas,
                 null,
                 'post',
                 ['CURLOPT_TIMEOUT' => 1,
                  'CURLOPT_FRESH_CONNECT' => true,
                  'CURLOPT_FOLLOWLOCATION' => true,
                  'CURLOPT_RETURNTRANSFER' => true,
                  'CURLOPT_POST' => true,
                  'CURLOPT_SSL_VERIFYHOST' => 0]);
    } else exec("php ".BASE_PATH."/index.php candy async $func_hash $data_id > /dev/null 2>&1 &");
  }

  public static function isDev($f = null){
    if(defined('CANDY_DEVMODE') && CANDY_DEVMODE){
      return is_callable($f) ? $f() : true;
    }else{
      return false;
    }
  }

  public static function encrypt($v, $key = null, $stage = null){
    $key = $key===null ? (defined('ENCRYPT_KEY') ? ENCRYPT_KEY : 'candy') : $key;
    $stage = $stage===null ? (defined('ENCRYPT_STAGE') ? ENCRYPT_STAGE : 5) : $stage;
    $alphas = range('a', 'z');
    $encrypted = base64_encode($v);
    $md5 = $key;
    for($i=0; $i < $stage; $i++){
      $md5 = md5($md5);
      $arr = array_unique(array_filter(str_split($md5)));
      $loop = 0;
      foreach($arr as $key){
        if(is_numeric($key)){
          $encrypted = substr($encrypted,$key).substr($encrypted,0,$key);
        }else{
          $encrypted = str_replace([strtolower($key),strtoupper($key)], ["{-}","{--}"], $encrypted);
          $encrypted = str_replace([strtolower($alphas[$loop]),strtoupper($alphas[$loop])], [strtolower($key),strtoupper($key)], $encrypted);
          $encrypted = str_replace(["{-}","{--}"], [strtolower($alphas[$loop]),strtoupper($alphas[$loop])], $encrypted);
        }
        $loop++;
      }
    }
    $encrypted = str_replace(['==','='],['_','-'],$encrypted);
    return $encrypted;
  }

  public static function decrypt($v, $key = null, $stage = null){
    $key = $key===null ? (defined('ENCRYPT_KEY') ? ENCRYPT_KEY : 'candy') : $key;
    $stage = $stage===null ? (defined('ENCRYPT_STAGE') ? ENCRYPT_STAGE : 5) : $stage;
    $decrypted = str_replace(['_','-'],['==','='],$v);
    $alphas = range('a', 'z');
    $keys = [];
    for($i=0; $i < $stage; $i++){
      $key = md5($key);
      $keys[] = $key;
    }
    $keys = array_reverse($keys);
    foreach($keys as $md5) {
      $arr =  array_reverse(array_unique(array_filter(str_split($md5))));
      $loop = 1;
      foreach($arr as $key){
        if(is_numeric($key)){
          $decrypted = substr($decrypted,($key * -1)).substr($decrypted,0,($key * -1));
        }else{
          $count = count($arr) - $loop;
          $decrypted = str_replace([strtolower($key),strtoupper($key)], ["{-}","{--}"], $decrypted);
          $decrypted = str_replace([strtolower($alphas[$count]),strtoupper($alphas[$count])], [strtolower($key),strtoupper($key)], $decrypted);
          $decrypted = str_replace(["{-}","{--}"], [strtolower($alphas[$count]),strtoupper($alphas[$count])], $decrypted);
        }
        $loop++;
      }
    }
    return base64_decode($decrypted);
  }

  public static function dirContents($dir, &$results = []){
    $result = [];
    if(is_array($dir)){
      foreach($dir as $key) self::dirContents($key, $results);
    }else{
      $files = scandir($dir);
      foreach($files as $key => $value){
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if(!is_dir($path)){
          $results[] = $path;
        }elseif($value != "." && $value != "..") {
          self::dirContents($path, $results);
          $results[] = $path;
        }
      }
    }
    return $results;
  }

  public static function plugin($name,$includes=null){
    self::import('Plugin');
    $plugin = new \Candy\Plugin();
    return $plugin->plugin($name,$includes);
  }

  public static function page($page=null){
    if($page==null && isset($GLOBALS['_candy']['route']['page'])) return $GLOBALS['_candy']['route']['page'];
    if(isset($GLOBALS['_candy']['route']['page'])) return $page == $GLOBALS['_candy']['route']['page'];
    if(defined('PAGE')) return $page==PAGE;
    return false;
  }

  public static function string($string){
    self::import('Variable');
    $str = new \Candy\Variable($string);
    return $str;
  }

  public static function var($var){
    self::import('Variable');
    $str = new \Candy\Variable($var);
    return $str;
  }

  public static function auth($val=null){
    self::import('Auth');
    $auth = new \Candy\Auth($val);
    return $auth;
  }
}
$candy = new Candy();
