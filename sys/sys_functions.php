<?php
class Candy {
  public $var;
  public $imported;
  public $token;
  public $import;
  public $postToken;
  public $getToken;

  public function hello(){
    echo 'Hi, World !';
  }

  public function import($class){
    global $imported;
    global $import;
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
    return $var->$p;
  }

  public function set($p,$v){
    global $var;
    if(empty($var)){
      $var = new \stdClass();
    }
    $var->$p = $v;
  }

  public function configCheck(){
    global $imported;
    global $import;
    if(defined('MYSQL_CONNECT') && MYSQL_CONNECT==true){
      include('import/class_mysql.php');
      $imported .= '_mysql_';
      $mysql->connect();
    }
  }

  public function token($check = '0'){
    global $token;
    if($check=='0' || $check=='input'){
      if($token==''){
        $token = md5(rand(10000,99999));
        $_SESSION['token'] = $token;
      }
      if($check==='input'){
        echo '<input name="token" value="'.$token.'" hidden="">';
      }
      return $token;
    }else{
      if($_SESSION['token']==$token){
        unset($_SESSION['token']);
        return true;
      }else{
        return false;
      }
    }
  }

  public function postCheck($post,$t=true,$r=true){
    global $postToken;
    $postToken = is_null($postToken) ? isset($_POST['token']) && isset($_SESSION['token']) && $_SESSION['token']==$_POST['token'] : $postToken;
    unset($_SESSION['token']);

    $count = 0;
    $arr_post = explode(',',$post);
    foreach ($arr_post as $key) {
      if($key!='' && isset($_POST[$key]) && $_POST[$key]!=''){
        $count++;
      }
    }
    if($t){
      if($postToken && count($arr_post)==$count){
        if(!$r || parse_url($_SERVER['HTTP_REFERER'])['host'] == $_SERVER['HTTP_HOST']){
          return true;
        }else{
          return false;
        }
      }else{
        return false;
      }
    }else{
      if(!$r || parse_url($_SERVER['HTTP_REFERER'])['host'] == $_SERVER['HTTP_HOST']){
        return count($arr_post)==$count;
      }else{
        return false;
      }
    }
  }

  public function getCheck($get,$t=true){
    $count = 0;
    $arr_get = explode(',',$get);
    foreach ($arr_get as $key) {
      if($key!='' && isset($_GET[$key]) && $_GET[$key]!=''){
        $count++;
      }
    }
    if($t){
      return isset($_GET['token']) && isset($_SESSION['token']) && $_SESSION['token']==$_GET['token'] && count($arr_get)==$count;
    }else{
      return count($arr_get)==$count;
    }
  }

  public function isNumeric($v,$method='another'){
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
    $url = $url!=0 ? $url : $_SERVER['HTTP_REFERER'];
    header('Location: '.$url);
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
      $result->message = "Sorry, your file was not uploaded.";
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
  public static function slugify($text)
  {
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
}
$candy = new Candy();
