<?php
class View {
  public function setHead($v){
    define('VIEW_HEAD',$v);
  }
  public function setHeader($v){
    define('VIEW_HEADER',$v);
  }
  public function setBody($v){
    define('VIEW_BODY',$v);
  }
  public function setFooter($v){
    define('VIEW_FOOTER',$v);
  }
  public function setScript($v){
    define('VIEW_SCRIPT',$v);
  }
  public function printView(){
    $skeleton = explode('{{ PRINT }}',file_get_contents('skeleton/page.skeleton', FILE_USE_INCLUDE_PATH));
    print($skeleton[0]);
    if(null !== VIEW_HEAD){
      include('view/head_'.VIEW_HEAD.'.php');
    }
    if(null !== VIEW_HEADER){
      include('view/header_'.VIEW_HEADER.'.php');
    }
    if(null !== VIEW_BODY){
      include('view/body_'.VIEW_BODY.'.php');
    }
    if(null !== VIEW_FOOTER){
      include('view/footer_'.VIEW_FOOTER.'.php');
    }
    if(null !== VIEW_SCRIPT){
      include('view/script_'.VIEW_SCRIPT.'.php');
    }
    print($skeleton[1]);
  }
}

$view = new View();
