<?php
set_time_limit(0);
ini_set('memory_limit', '4G');
$base = 'https://raw.githubusercontent.com/CandyPack/CandyPHP/master/';
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
}else $version_current = 0;
foreach($arr_get as $new){
  if(substr($new,0,1)=='#'){
    $params_new = explode(':',str_replace('#','',$new));
    switch ($params_new[0]) {
      case 'version':
        if($params_new[1]>$version_current) $update = true;
        else return false;
        break;
      case 'delete':
        if(file_exists($params_new[1])) unlink($params_new[1]);
        break;
    }
  }elseif(trim($new)!='') $arr_update[] = trim($new);
}
if($update){
  foreach ($arr_update as $key){
    if(strpos($key, '/') !== false){
      $arr_dir = explode('/',$key);
      $makedir = '';
      for ($i=0; $i < count($arr_dir) - 1; $i++) $makedir .= $arr_dir[$i].'/';
      $makedir = substr($makedir,0,-1);
      if(!file_exists($makedir)) mkdir($makedir, 0777, true);
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
