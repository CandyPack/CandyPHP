<?php
class View {
  public function setHead($v){
    define('VIEW_HEAD',$v);
  }
  public function setHeader($v){
    define('VIEW_HEADER',$v);
  }
  public function setSidebar($v){
    define('VIEW_SIDEBAR',$v);
  }
  public function setContent($v){
    define('VIEW_CONTENT',$v);
  }
  public function setFooter($v){
    define('VIEW_FOOTER',$v);
  }
  public function setScript($v){
    define('VIEW_SCRIPT',$v);
  }
  public function setSkeleton($v){
    define('VIEW_SKELETON',$v);
  }
  public function printView(){
    global $candy;
    global $route;
    global $conn;

    function get($v){
      global $candy;
      return $candy->get($v);
    }

    if(defined('VIEW_SKELETON')){
    $skeleton = defined('VIEW_SKELETON') ? 'skeleton/'.VIEW_SKELETON.'.skeleton' : 'skeleton/page.skeleton';
    if(defined('VIEW_HEAD')){
    $skeleton = explode('{{ HEAD }}',file_get_contents($skeleton, FILE_USE_INCLUDE_PATH));
    print($skeleton[0]);
      include('view/head_'.VIEW_HEAD.'.php');
    }
    if(defined('VIEW_HEADER')){
    $skeleton = explode('{{ HEADER }}',$skeleton[1]);
    print($skeleton[0]);
      include('view/header_'.VIEW_HEADER.'.php');
    }
    if(defined('VIEW_SIDEBAR')){
    $skeleton = explode('{{ SIDEBAR }}',$skeleton[1]);
    print($skeleton[0]);
      include('view/sidebar_'.VIEW_SIDEBAR.'.php');
    }
    if(defined('VIEW_CONTENT')){
    $skeleton = explode('{{ CONTENT }}',$skeleton[1]);
    print($skeleton[0]);
      include('view/content_'.VIEW_CONTENT.'.php');
    }
    if(defined('VIEW_FOOTER')){
    $skeleton = explode('{{ FOOTER }}',$skeleton[1]);
    print($skeleton[0]);
      include('view/footer_'.VIEW_FOOTER.'.php');
    }
    if(defined('VIEW_SCRIPT')){
    $skeleton = explode('{{ SCRIPT }}',$skeleton[1]);
    print($skeleton[0]);
      include('view/script_'.VIEW_SCRIPT.'.php');
    }
    print($skeleton[1]);
  }
}
}

$view = new View();
