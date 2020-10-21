<?php
class Mail
{
    private static $_arr = [];

    function __construct($_arr=[]){
      self::$_arr = $_arr;
    }

    public static function view($v){
      self::$_arr['view'] = $v;
      return new static(self::$_arr);
    }
    public static function to($v){
      self::$_arr['to'] = $v;
      return new static(self::$_arr);
    }
    public static function subject($v){
      self::$_arr['subject'] = $v;
      return new static(self::$_arr);
    }
    public static function from($email,$name = ''){
      if($name!=''){
        self::$_arr['from'] = ['mail' => $email, 'name' => $name];
      }else{
        self::$_arr['from'] = $email;
      }
      return new static(self::$_arr);
    }
    public static function send(){
      if(isset(self::$_arr['to']) && isset(self::$_arr['view'])){
        if(!function_exists('get')){
          function get($v){
            return Candy::get($v);
          }
        }
        if(!file_exists('view/mail/'.self::$_arr['view'].'.blade.php')){
          $storage = Candy::storage('sys')->get('mail');
          $storage->error = isset($storage->error) && is_object($storage->error) ? $storage->error : new \stdClass;
          $storage->error->info = isset($storage->error->info) && is_object($storage->error->info) ? $storage->error->info : new \stdClass;
          if(Config::check('MASTER_MAIL') && (!isset($storage->error->info->date) || $storage->error->info->date!=date('d/m/Y'))){
            Candy::quickMail(MASTER_MAIL,
            '<b>Date</b>: '.date("Y-m-d H:i:s").'<br />
            <b>Message</b>: Couldn\'t send mail <br />mail/'.self::$_arr['view'].'.blade.php <b>Not Exists File</b><br /><br />
            MAIL:
            <pre>'.print_r(self::$_arr,true).'</pre><br /><br />
            <b>Details</b>: <br />
            SERVER:
            <pre>'.print_r($_SERVER,true).'</pre>
            SESSION:
            <pre>'.print_r($_SESSION,true).'</pre>
            COOKIE:
            <pre>'.print_r($_COOKIE,true).'</pre>
            POST:
            <pre>'.print_r($_POST,true).'</pre>
            GET:
            <pre>'.print_r($_GET,true).'</pre>',
            $_SERVER['SERVER_NAME'].' - INFO',
            ['mail' => 'candyphp@'.$_SERVER['SERVER_NAME'], 'name' => 'Candy PHP']);
          $storage->error->info->date = date('d/m/Y');
          Candy::storage('sys')->set('mail',$storage);
        }
          return false;
        }
        ob_start();
        include(View::cacheView('mail/'.self::$_arr['view'].'.blade.php'));
        $message = ob_get_clean();
        $to = self::$_arr['to'];
        $subject = isset(self::$_arr['subject']) ? self::$_arr['subject'] : '';
        if(isset(self::$_arr['from'])){
          $from = self::$_arr['from'];
        }else{
          if(defined('INFO_MAIL') && false){
            $from = INFO_MAIL;
          }else{
            return false;
          }
        }
        if(is_array($from)){
          $from_name = ''.$from['name'].' ';
          $from_mail = '<'.$from['mail'].'>';
        }else{
          $from_name = '';
          $from_mail = $from;
        }
        if($subject==''){
          $subject = $_SERVER['SERVER_NAME'];
        }

        $headers = "From: ".$from_name . $from_mail . "\r\n";
        $headers .= "Reply-To: ". $from_mail . "\r\n";
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
        //-----------------------------------------------------------------------
      }else{
        return false;
      }
    }

}
