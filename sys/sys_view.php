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
    $skeleton = defined('VIEW_SKELETON') ? 'skeleton/'.VIEW_SKELETON.'.skeleton' : 'skeleton/page.skeleton';
    $skeleton = explode('{{ HEAD }}',file_get_contents($skeleton, FILE_USE_INCLUDE_PATH));
    print($skeleton[0]);
    if(null !== VIEW_HEAD){
      include('view/head_'.VIEW_HEAD.'.php');
    }
    $skeleton = explode('{{ HEADER }}',$skeleton[1]);
    print($skeleton[0]);
    if(null !== VIEW_HEADER){
      include('view/header_'.VIEW_HEADER.'.php');
    }
    $skeleton = explode('{{ SIDEBAR }}',$skeleton[1]);
    print($skeleton[0]);
    if(null !== VIEW_SIDEBAR){
      include('view/sidebar_'.VIEW_SIDEBAR.'.php');
    }
    $skeleton = explode('{{ CONTENT }}',$skeleton[1]);
    print($skeleton[0]);
    if(null !== VIEW_CONTENT){
      include('view/content_'.VIEW_CONTENT.'.php');
    }
    $skeleton = explode('{{ FOOTER }}',$skeleton[1]);
    print($skeleton[0]);
    if(null !== VIEW_FOOTER){
      include('view/footer_'.VIEW_FOOTER.'.php');
    }
    $skeleton = explode('{{ SCRIPT }}',$skeleton[1]);
    print($skeleton[0]);
    if(null !== VIEW_SCRIPT){
      include('view/script_'.VIEW_SCRIPT.'.php');
    }
    print($skeleton[1]);
  }
}

$view = new View();
