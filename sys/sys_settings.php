<?php
class Config {
  public $backupdirectory = '../backup/';

  public function displayError($b = true){
    $b ? ini_set('display_errors', 1) : ini_set('display_errors', 0);
  }
  public function mysqlServer($s){
    define('MYSQL_SERVER',$s);
  }
  public function mysqlDatabase($s){
    define('MYSQL_DB',$s);
  }
  public function mysqlUsername($s){
    define('MYSQL_USER',$s);
  }
  public function mysqlPassword($s){
    define('MYSQL_PASS',$s);
  }
  public function mysqlConnection($b = true){
    define('MYSQL_CONNECT',$b);
  }
  public function languageDetect($b  = true){
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
      $langg = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
      if(file_exists("lang/lang_{$langg}.php")){
        require_once "lang/lang_{$langg}.php";
        Lang::setArray($lang);
      }elseif(file_exists("lang/lang.php")){
        require_once "lang/lang.php";
        Lang::setArray($lang);
      }
    }else{
      if(file_exists("lang/lang.php")){
        require_once "lang/lang.php";
        Lang::setArray($lang);
      }
    }
  }
  public function cronJobs($b = true){
    define('CRON_JOBS',$b);
    $command = '* * * * * curl -L -A candyPHP-cron '.$_SERVER['SERVER_NAME'].'/?_candy=cron';
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
  public function autoBackup($b = true,$directory = 'backup/'){
    global $conn;
    global $backupdirectory;
    $backupdirectory = $directory;
    define('AUTO_BACKUP',$b);
    set_time_limit(1000);
    if($b && (date("Hi")=='0000' || true) && $_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'] && isset($_GET['_candy']) && $_GET['_candy']=='cron'){
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
}
$config = new Config();
include('config.php');
