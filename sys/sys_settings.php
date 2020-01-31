<?php
class Config {
  public $backupdirectory = '../backup/';

  public static function displayError($b = true){
    if($b){
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);
    }else{
      ini_set('display_errors', 0);
    }
  }
  public static function mysqlServer($s){
    define('MYSQL_SERVER',$s);
  }
  public static function mysqlDatabase($s){
    define('MYSQL_DB',$s);
  }
  public static function mysqlUsername($s){
    define('MYSQL_USER',$s);
  }
  public static function mysqlPassword($s){
    define('MYSQL_PASS',$s);
  }
  public static function mysqlConnection($b = true){
    define('MYSQL_CONNECT',$b);
  }
  public static function languageDetect($b  = true){
    function returnLang($l){
      return require_once "lang/{$l}.php";
    }
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
      $langg = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
      if(file_exists("lang/{$langg}.php")){
        Lang::setArray(returnLang($langg));
      }elseif(file_exists("lang/lang.php")){
        Lang::setArray(returnLang('lang'));
      }
    }else{
      if(file_exists("lang/lang.php")){
        Lang::setArray(returnLang('lang'));
      }
    }
  }
  public static function cronJobs($b = true){
    define('CRON_JOBS',$b);
    $command = '* * * * * curl -L -A candyPHP-cron '.str_replace('www.','',$_SERVER['SERVER_NAME']).'/?_candy=cron';
    exec('crontab -l', $crontab);
    $append = true;
    $is_override = false;
    if(isset($crontab) && is_array($crontab)){
      foreach ($crontab as $key) {
        if($key==$command){
          $is_override = !$append;
          $append = false;
        }
      }
      if($append || $is_override){
        if($is_override){
          exec('crontab -r ');
          foreach ($crontab as $key) {
            if($key!='' && $key!=$command){
              exec('echo -e "`crontab -l`\n'.$key.'" | crontab -', $output);
            }
          }
        }
        exec('echo -e "`crontab -l`\n'.$command.'" | crontab -', $output);
      }
    }
  }
  public static function autoBackup($b = true,$directory = 'backup/'){
    define('AUTO_BACKUP',$b);
    define('BACKUP_DIRECTORY',$directory);
  }
  public static function runBackup(){
    global $conn;
    global $backupdirectory;

    set_time_limit(10000);
    $b = defined('AUTO_BACKUP') && AUTO_BACKUP;
    if($b && date("Hi")=='0000' && ((substr($_SERVER['SERVER_ADDR'],0,8)=='192.168.') || ($_SERVER['SERVER_ADDR']==$_SERVER['REMOTE_ADDR'])) && isset($_GET['_candy']) && $_GET['_candy']=='cron'){
      $directory = BACKUP_DIRECTORY;
      $backupdirectory = $directory;
      if (!file_exists($backupdirectory.'mysql/')) {
        mkdir($backupdirectory.'mysql/', 0777, true);
      }
      if (!file_exists($backupdirectory.'www/')) {
        mkdir($backupdirectory.'www/', 0777, true);
      }
      $tables = array();
      $result = mysqli_query($conn,"SHOW TABLES");
      while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
      }
      $return = '';
      foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT * FROM ".$table);
        $num_fields = mysqli_num_fields($result);
        $return .= 'DROP TABLE '.$table.';';
        $row2 = mysqli_fetch_row(mysqli_query($conn, 'SHOW CREATE TABLE '.$table));
        $return .= "\n\n".$row2[1].";\n\n";
        for ($i=0; $i < $num_fields; $i++){
          while ($row = mysqli_fetch_row($result)){
            $return .= 'INSERT INTO '.$table.' VALUES(';
            for ($j=0; $j < $num_fields; $j++){
              $row[$j] = addslashes($row[$j]);
              if(isset($row[$j])){
                $return .= '"'.$row[$j].'"';
              }else{
                $return .= '""';
              }
              if($j<$num_fields-1){
                $return .= ',';
              }
            }
            $return .= ");\n";
          }
        }
        $return .= "\n\n\n";
      }
      $handle = fopen($backupdirectory.'mysql/'.date("Y-m-d").'-backup.sql', 'w+');
      fwrite($handle, $return);
      fclose($handle);
      $rootPath = realpath('./');
      $zip = new ZipArchive();
      $zip->open($backupdirectory.'www/'.date("Y-m-d").'-backup.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
      /** @var SplFileInfo[] $files */
      $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
      );
      foreach ($files as $name => $file)
      {
        if (!$file->isDir())
        {
          $filePath = $file->getRealPath();
          $relativePath = substr($filePath, strlen($rootPath) + 1);
          $zip->addFile($filePath, $relativePath);
        }
      }
      $zip->close();
    }
  }
  public static function autoUpdate($b = true){
    set_time_limit(1000);
    if($b && date("Hi")=='0010' && $_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'] && isset($_GET['_candy']) && $_GET['_candy']=='cron'){
      $base = 'https://raw.githubusercontent.com/emredv/Candy-PHP/master/';
      $get = file_get_contents($base.'update.txt');
      $arr_get = explode("\n",$get);
      $now = getdate();
      $params = array();
      $update = false;
      if(file_exists('update.txt')){
        $current = file_get_contents('update.txt', FILE_USE_INCLUDE_PATH);
        $arr_current = explode("\n",$current);
        foreach($arr_current as $current){
          if(substr($current,0,1)=='#'){
            $params_current = explode(':',str_replace('#','',$current));
            switch ($params_current[0]) {
              case 'version':
              $version_current = $params_current[1];
              break;
            }
          }
        }
      }else{
        $version_current = 0;
      }
      foreach($arr_get as $new){
        if(substr($new,0,1)=='#'){
          $params_new = explode(':',str_replace('#','',$new));
          switch ($params_new[0]) {
            case 'version':
            if($params_new[1]>$version_current){
              $update = true;
            }
            break;
          }
        }else{
          if(trim($new)!=''){
            $arr_update[] = trim($new);
          }
        }
      }
      if($update){
      foreach ($arr_update as $key){
        if(strpos($key, '/') !== false){
          $arr_dir = explode('/',$key);
          $makedir = '';
          for ($i=0; $i < count($arr_dir) - 1; $i++) {
            $makedir .= $arr_dir[$i].'/';
          }
          $makedir = substr($makedir,0,-1);
          if (!file_exists($makedir)) {
            mkdir($makedir, 0777, true);
          }
        }
        $content = '';
        $content = file_get_contents($base.$key);
        if(trim($content)!=''){
          $file = fopen($key, "w") or die("Unable to open file!");
          fwrite($file, $content);
          fclose($file);
        }
      }
    }
    }
  }

  public static function masterMail($s=''){
    if(!defined('MASTER_MAIL') && $s!=''){
      define('MASTER_MAIL',$s);
    }
  }

  public static function check($v){
    $return = true;
    $arr_var = explode(',',$v);
    foreach ($arr_var as $key){
      if($key!=''){
        if(!defined($key)){
          $return = false;
        }else{
          if(is_bool(constant($key)) && !constant($key)){
            $return = false;
          }elseif(!is_bool(constant($key)) && (is_numeric(constant($key)) || constant($key)!='')){

          }
        }
      }
    }
    return $return;
  }

  public static function backupClear(){
    $arr = ['www','mysql'];
    foreach ($arr as $key){
      $dir = substr(BACKUP_DIRECTORY,-1)=='/' ? BACKUP_DIRECTORY.$key.'/' : BACKUP_DIRECTORY.'/'.$key.'/';
      if(file_exists($dir)){
        $dh  = opendir($dir);
        while(false !== ($filename = readdir($dh))){
          if($filename!='.' && $filename!='..'){
            $filemtime = filemtime($dir.$filename);
            $diff = time()-$filemtime;
            $days = round($diff/86400);
            $dayofweek = date('w', $filemtime);
            $dayofmonth = date('d', $filemtime);
            $dayofyear = date('md', $filemtime);
            if($days>7){
              if($dayofweek!=1 || $days>30){
                if($dayofmonth!=1 || $days>365){
                  if($dayofyear!='0101'){
                    unlink($dir.$filename);
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}

$config = new Config();
include('config.php');
