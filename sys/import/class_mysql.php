<?php
class Mysql {

  public function connect($db=MYSQL_DATABASE,$user=MYSQL_USER,$pass=MYSQL_PASS,$server='127.0.0.1'){
    $conn = mysqli_connect($server, $user, $pass, $db);
    if (!$conn) {
        echo "Mysql bağlantı hatası" . PHP_EOL;
        exit;
    }
    return $conn;
  }
  
}
global $mysql;
$mysql = new Candy();
