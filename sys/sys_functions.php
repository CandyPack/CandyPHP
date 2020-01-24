<?php
class Candy {
  public $var;
  public $imported;
  public $token;
  public $postToken;
  public $getToken;
  public $tokenCheck = '';

  public function hello(){
    echo 'Hi, World !';
  }

  public function import($class){
    global $imported;
    if(!(strpos($imported, '_'.$class.'_') !== false)){
      $imported .= '_'.$class.'_';
      include('import/class_'.$class.'.php');
    }
  }

  public function userCheck(){
    return false;
  }

  public function get($p){
    global $var;
    return isset($var->$p) ? $var->$p : null;
  }

  public function set($p,$v){
    global $var;
    if(empty($var)){
      $var = new \stdClass();
    }
    $var->$p = $v;
  }

  public function configCheck(){
    header('X-POWERED-BY: Candy PHP');
    if(defined('MYSQL_CONNECT') && MYSQL_CONNECT==true){
      self::import('mysql');
      Mysql::connect();
    }
    if(defined('AUTO_BACKUP') && AUTO_BACKUP==true){
      Config::runBackup();
      Config::backupClear();
    }
  }

  public function token($check = 0){
    global $token;
    global $tokenCheck;
    if($check===0 || $check==='input' || $check==='json' || $check==='echo'){
      if($token==''){
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

  public function postCheck($post='',$t=true){
    global $postToken;
    $count = 0;
    $arr_post = explode(',',$post);
    if($post!=''){
      foreach ($arr_post as $key) {
        if($key!='' && isset($_POST[$key]) && $_POST[$key]!=''){
          $count++;
        }
      }
    }elseif($_SERVER['REQUEST_METHOD']==='POST'){
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

  public function getCheck($get='',$t=true){
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

  public function isNumeric($v,$method='post'){
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

  public function direct($link=0){
    if($link===404){
      if(!defined('DIRECT_404')){
        define('DIRECT_404',true);
      }
    }else{
      $url = $link!==0 ? $link : $_SERVER['HTTP_REFERER'];
      header('Location: '.$url);
    }
    return new class{function with($v){
      if(is_array($GLOBALS['_candy']['oneshot'])){
        $GLOBALS['_candy']['oneshot'] = array_merge($GLOBALS['_candy']['oneshot'],$v);
      }else{
        $GLOBALS['_candy']['oneshot']=$v;
      }
      return new static();}};
  }

  public function uploadImage($postname="upload",$target = "uploads/",$filename='0',$maxsize=500000){

    $result = new \stdClass();
    $result->success = false;
    $target_dir = $target;
    $target_file = $filename=='0' ? $target_dir . basename($_FILES[$postname]["name"]) : $target_dir.$filename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $check = (!file_exists($_FILES[$postname]['tmp_name']) || !is_uploaded_file($_FILES[$postname]['tmp_name'])) ? false : getimagesize($_FILES[$postname]["tmp_name"]);

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

    if ($_FILES[$postname]["size"] > $maxsize) {
      $result->message = "Sorry, your file is too large.";
      $uploadOk = 0;
    }

    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
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

  public function dateFormatter($date = '0', $format = 'd / m / Y'){
    $date = str_replace('/','-',$date);
    $date = new DateTime($date);
    $date = $date->format($format);
    return $date;
  }

  public function getJs($path,$b=true){
    $minify = false;
    $file_raw = 'assets/js/'.$path;
    $file_min = str_replace('.js','.min.js',$file_raw);
    if(file_exists($file_raw)){
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
        file_put_contents($file_min, $js_min);
      }
      echo $b ? '/'.$file_min.'?_v='.$date_min : '';
      return $file_min.'?_v='.$date_min;
    }else{
      echo $b ? '/'.$path : '';
      return $path;
    }
  }

  public function getCss($path, $b=true){
    $minify = false;
    $file_raw = 'assets/css/'.$path;
    $file_min = str_replace('.css','.min.css',$file_raw);
    if(file_exists($file_raw)){
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
        $css_min = self::cssMinifier($css_raw);
        file_put_contents($file_min, $css_min);
      }
      echo $b ? '/'.$file_min.'?_v='.$date_min : '';
      return $file_min.'?_v='.$date_min;
    }else{
      echo $b ? '/'.$path : '';
      return $path;
    }
  }

  public function jsMinifier($js){
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

  public function cssMinifier($css){
    $url = 'https://cssminifier.com/raw';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_POSTFIELDS => http_build_query([ "input" => $css ])
    ]);
    $minified = curl_exec($ch);
    curl_close($ch);
    return $minified;
  }

  public function uploadCheck($name=null){
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

  public function mail($view){
    self::import('mail');
    return Mail::view($view);
  }

  public function quickMail($to,$message,$subject = '',$from = ''){
    if(is_array($from)){
      $from_name = '<'.$from['name'].'>';
      $from = $from['mail'];
      $from_mail = $from['mail'];
    }else{
      $from_name = '';
      $from_mail = $from;
    }
    if($from=='' && defined('MASTER_MAIL')){
      $from = MASTER_MAIL;
    }
    if($subject==''){
      $subject = $_SERVER['SERVER_NAME'];
    }

    $headers = "From: ".$from_name . strip_tags($from_mail) . "\r\n";
    $headers .= "Reply-To: ". strip_tags($from_mail) . "\r\n";
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

    return mail($to, $subject, $message, $headers);
  }

  public function storage($s){
    return Storage::select($s);
  }

  public function strFormatter($str,$format){
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

  public function validator($v = null){
    self::import('validation');
    $validation = new Validation();
    return $validation->validator($v);
  }

  public function session($key,$val=null){
    if($val===null){
      return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }else{
      $_SESSION[$key] = $val;
      return true;
    }
  }

  public function return($v){
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($v);
  }

  public function hash($v,$h=null){
    $options = ['cost' => 11];
    if($h==null){
      return password_hash($v, PASSWORD_BCRYPT, $options);
    }else{
      return password_verify($v, $h);
    }
  }
}
$candy = new Candy();
