<?php
class Mail
{
    private static $_arr = [];

    public static function view($v){
      self::$_arr['view'] = $v;
      return new static();
    }
    public static function to($v){
      self::$_arr['to'] = $v;
      return new static();
    }
    public static function subject($v){
      self::$_arr['subject'] = $v;
      return new static();
    }
    public static function from($email,$name = ''){
      if($name!=''){
        self::$_arr['from'] = ['mail' => $email, 'name' => $name];
      }else{
        self::$_arr['from'] = $email;
      }
      return new static();
    }
    public static function send(){
      if(isset(self::$_arr['to']) && isset(self::$_arr['view'])){
        if(!function_exists('get')){
          function get($v){
            return Candy::get($v);
          }
        }
        ob_start();
        include(View::cacheView('mail/'.self::$_arr['view'].'.php'));
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
