<?php

namespace core\log;

uses (
    "core.base.Quobject",
    "core.base.Timing",
    "core.log.LogLevel",
    "core.log.LogMessage",
    "core.log.LogFormat",
    "core.filesystem.File"
);

class LogFacility extends \core\base\Quobject {
    
    protected $level          = "TRACE";

    private $file           = null;

    protected $filename       = null;

    protected $enabled        = false;

    private $timer          = null;

    private $timer_start    = null;

    private $logs           = array();
   
    protected $supported_vars = array(
        "level", "filename", "enabled"
    );
 
    public function __construct() {
        parent::__construct();    

        $this->timer        = new \core\base\Timing();
        $this->timer_start  = $this->timer->elapsed();

        $this->add("Quantum QLogNG+ started - Note that this log may contain sensitive data. Make sure the debug level is lower than 'DEBUG' when this software is used for productive purposes.", 
            "TRACE",
            $this, 
            array('function' => '\core\log\LogFacility->__construct')
        );

        try {
            $this->file = new \core\filesystem\File($this->filename, FMODE_APPEND);
##            print_r($this);
#            $this->file->fs->chmod(0777);
        } catch (\Exception $e) {
            print "exception $e\n";
        }
    }

    public function add($message, $level, $qclass, $trace) {
        if (is_object($qclass))
            $classname = (method_exists($qclass, "getClassName")) ? $qclass->getClassName() : get_class($qclass);
        else if (is_string($qclass))
            $classname = $qclass;
        else
            $classname = "static/undefined";    
    
        array_push($this->logs, new \core\log\LogMessage($message, array(
            "level"     => $level,
            "elapsed"   => $this->timer->elapsed(),
            "qclass"    => $classname,
            "caller"    => $trace,
        )));
    }
    
    public function getLogs() {
        return ($this->enabled) ? $this->logs : null;
    }
    
    public function getMessages() {
        $messages = "";
    
        $format = new \core\log\LogFormat($this->timer_start, $this->timer->elapsed());
        $prev_elapsed = 0;
        foreach ($this->logs as $id => $log) {
            if (LogLevel::name($log->getLevel()) <= LogLevel::name($this->level)) {        
                $messages .= $format->toString($log->getMessage(), $log->getDetail(), $prev_elapsed);
            }
            $prev_elapsed = $log->getElapsed();
        }
        return $messages;
    }

    public function writeLog() {
        if ( (is_bool($this->enabled) && $this->enabled === TRUE) || ($this->enabled == "true") || ($this->enabled == 1) ) {

            if ($this->file instanceof \core\filesystem\File) {
                $this->file->write($this->getMessages());
                return true;
            }
        }

        return false;
    }
    
}

?>
