<?php
class Cron
{
    private $_var = [];
    private $_run = true;

    function __construct($_var=[],$_run=true){
      $this->_var = $_var;
      $this->_run = $_run;
    }

    function controller($controller){
      $this->_var['controller'] = $controller;
      $this->_var['date'] = getdate();
      $this->_var['time'] = [];
      $this->_var['time']['minutes'] = time() / 60;
      $this->_var['time']['hours'] = $this->_var['time']['minutes'] / 60;
      $this->_var['time']['days']  = $this->_var['time']['hours'] / 24;
      $this->_run = true;
      return new static($this->_var,$this->_run);
    }

    function run(){
      if($this->_run && isset($this->_var['controller'])){
        if(defined('CRON_JOBS') && CRON_JOBS){
          if(file_exists('cron/cron_'.$this->_var['controller'].'.php')){
            include('cron/cron_'.$this->_var['controller'].'.php');
          }
        }
      }
      return $this->_run;
    }

    function minute($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['minutes']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function hour($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['hours']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function day($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['mday']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function weekDay($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['wday']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function month($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['mon']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function year($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['year']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function yearDay($val){
      if($this->_run){
        $this->_run = intval($this->_var['date']['yday']) == intval($val);
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyMinute($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['time']['minutes'])%intval($val) == 0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyHour($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['time']['hours'])%intval($val)==0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyDay($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['time']['days'])%intval($val)==0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyWeekDay($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['date']['wday'])%intval($val)==0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyMonth($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['date']['mon'])%intval($val) == 0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyYear($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['date']['year'])%intval($val)==0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }

    function everyYearDay($val=1){
      if($this->_run){
        $this->_run = intval($this->_var['date']['yday'])%intval($val)==0;
      }
      $GLOBALS['cron'][$this->_var['controller']] = $this->_run;
      return new static($this->_var,$this->_run);
    }
}
