<?php
namespace Candy;

class Plugin{
  private $plugin;
  private $dir;
  private $name;

  public function plugin($name){
    if(!is_dir("plugin")) mkdir('plugin');
    $this->dir = "plugin/".$name;
    $this->plugin = $name;
    $arr_name = explode('/',$name);
    $this->name = end($arr_name);
    if(!file_exists("$this->dir/candy_loader.php")) self::get($name);
    return file_exists("$this->dir/candy_loader.php") ? include("$this->dir/candy_loader.php") : false;
  }

  private function get($name){
    ini_set('memory_limit', '-1');
    if((strpos($name, '/')!==false)) return self::github($name);
    return self::candy($name);
  }

  private function candy($name, $b=false){
    if($b){
      $list = file_get_contents('plugin/list.txt', FILE_USE_INCLUDE_PATH);
    }else{
      if(file_exists('plugin/list.txt')) if(self::candy($name,true)===true) return true;
      $list = \Candy::curl("https://gist.githubusercontent.com/emredv/fd84e69233f6bd4ee41544335fc12b9f/raw/CandyPHP-Plugins");
      file_put_contents('plugin/list.txt',$list);
    }
    $plugins = explode("\n",$list);
    foreach($plugins as $key){
      $plugin = explode('|',$key);
      if(strtolower($plugin[0])==strtolower($name)){
        $this->name = $plugin[0];
        $this->dir = "plugin/".$plugin[0];
        if(file_exists("$this->dir/candy_loader.php")) return true;
        self::download($plugin[1]);
        $plug_dir = $this->dir;
        if(file_exists("$plug_dir/git.zip")) unlink("$plug_dir/git.zip");
        $dirs = array_diff(scandir($plug_dir),['.','..','candy_loader.php']);
        foreach($dirs as $dir) $loader_dir = is_dir($plug_dir."/".$dir) ? "/".$dir : "";
        if(!isset($GLOBALS["_candy"])) $GLOBALS["_candy"] = [];
        if(!isset($GLOBALS["_candy"]["plugin"])) $GLOBALS["_candy"]["plugin"] = [];
        $loader = "<?php \n";
        $loader .= '$_plug = "'.$plugin[0].'";'."\n";
        $loader .= 'if(isset($GLOBALS["_candy"]["plugin"][$_plug])) return $GLOBALS["_candy"]["plugin"][$_plug];'."\n";
        $arr_loader = $plugin;
        unset($arr_loader[0]);
        unset($arr_loader[1]);
        foreach($arr_loader as $key){
          if(substr($key,0,1)=='#'){
            $loader .= '$reflection = new ReflectionClass($_plug);'."\n";
            $loader .= '$params = $reflection->getConstructor()->getParameters();'."\n";
            $loader .= '$GLOBALS["_candy"]["plugin"][$_plug] = count($params)>0 ? true : new '.substr($key,1)."();\n";
            $loader .= 'return count($params)>0 ? true : $GLOBALS["_candy"]["plugin"][$_plug];'."\n";
          }else{
            $loader .= "include (__DIR__.'$loader_dir/$key');\n";
          }
        }
        file_put_contents("$plug_dir/candy_loader.php", $loader);
        return true;
      }
    }
  }

  private function github($name){
    $download = self::download("https://github.com/$name/archive/master.zip");
    if($download === false) return false;
    $plug_dir = $this->dir;
    $json = self::getJson($name);
    if($json === false) return false;
  }

  private function getJson($name){
    $json = \Candy::curl("https://raw.githubusercontent.com/$name/master/candy.json");
    if($json=='404: Not Found') $json = \Candy::curl("https://raw.githubusercontent.com/$name/master/composer.json");
    if($json=='404: Not Found') return false;
    $plug_dir = $this->dir;
    $obj = json_decode($json);
    $src = $this->src($obj->autoload);
    $loader = [];
    foreach($src as $key){
      $loader = array_merge($loader,(is_dir($key) ? \Candy::dirContents($key) : [$key]));
    }
    $loader_php = "<?php \n";
    $loader_php .= '$_plug = "'.$this->name.'";'."\n";
    $loader_php .= 'if(isset($GLOBALS["_candy"]["plugin"][$_plug])) return $GLOBALS["_candy"]["plugin"][$_plug];'."\n";
    foreach($loader as $key) if(strtolower(substr($key,-4))=='.php') $loader_php .= "include (BASE_PATH.'".str_replace(BASE_PATH,'',$key)."');\n";
    $loader_php .= '$GLOBALS["_candy"]["plugin"][$_plug] = true;'."\n";
    $loader_php .= 'return true;';
    file_put_contents("$plug_dir/candy_loader.php", $loader_php);
  }

  private function download($url){
    $zip = \Candy::curl($url);
    if($zip=='Not Found') return false;
    $plug_dir = $this->dir;
    $dirs = explode('/',$plug_dir);
    $mkdir = [];
    foreach($dirs as $key){
      $mkdir[] = $key;
      if(!is_dir(implode('/',$mkdir))) mkdir(implode('/',$mkdir));
    }
    if(file_exists("$plug_dir/git.zip")) unlink("$plug_dir/git.zip");
    $save = file_put_contents("$plug_dir/git.zip",$zip);
    return self::extract("$plug_dir/git.zip");
  }

  private function extract($path){
    $zip = new \ZipArchive;
    if($zip->open($path) !== TRUE) return false;
    $zip->extractTo("$this->dir/");
    $zip->close();
    unlink($path);
    return true;
  }

  private function src($autoload){
    $result = [];
    if(is_array($autoload) || is_object($autoload)){
      foreach($autoload as $key) $result = array_merge($result, $this->src($key));
    }else{
      $result = array_merge($result, ["$this->dir/$this->name-master/$autoload"]);
    }
    return $result;
  }
}
