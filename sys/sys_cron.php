<?php
class Cron
{
    private static $_var = [];
    private static $_run = true;

    function __construct($_var=[],$_run=true){
      self::$_var = $_var;
      self::$_run = $_run;
    }

    public static function controller($controller){
        self::$_var['controller'] = $controller;
        self::$_var['date'] = getdate();
        self::$_run = true;
        return new static(self::$_var,self::$_run);
    }

    public static function run(){
      if(self::$_run && isset(self::$_var['controller'])){
        if(defined('CRON_JOBS') && CRON_JOBS){
          if(file_exists('cron/cron_'.self::$_var['controller'].'.php')){
            include('cron/cron_'.self::$_var['controller'].'.php');
          }
        }
      }
      return self::$_run;
    }

    public static function minute($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['minutes']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);
    }

    public static function hour($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['hours']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function day($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['mday']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function weekDay($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['wday']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function month($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['mon']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function year($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['year']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function yearDay($val){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['yday']) == intval($val);
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyMinute($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['minutes'])%intval($val) == 0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyHour($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['hours'])%intval($val)==0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyDay($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['mday'])%intval($val)==0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyWeekDay($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['wday'])%intval($val)==0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyMonth($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['mon'])%intval($val) == 0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyYear($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['year'])%intval($val)==0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

    public static function everyYearDay($val=1){
      if(self::$_run){
        self::$_run = intval(self::$_var['date']['yday'])%intval($val)==0;
      }
      $GLOBALS['cron'][self::$_var['controller']] = self::$_run;
      return new static(self::$_var,self::$_run);

    }

}
