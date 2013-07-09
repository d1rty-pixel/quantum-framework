<?php

namespace core\log;

class LogFormat {
    
    private $start;
    private $end;
    private $bar_width          = 20;
    private $time_format        = "%f";
    private $percentage_symbols = array(
        0.5 => ".",
        1.5 => "|",
        2   => "┐",
        3   => ">",
    );
    private $fill_symbol        = "=";
    private $display            = null;
    
    public function __construct($start, $end) {
        $this->start    = $start;
        $this->end      = $end;
        
        $this->display  = new \StdClass;
        $this->display->timebar     = true;
        $this->display->percent     = true;
        $this->display->file        = false;
        $this->display->line        = false;
        $this->display->call        = false;
        $this->display->args        = false;
        $this->display->qclass      = true;
    }
    
    public function setTimeFormat($format) {
        $this->time_format  = $format;
    }
    
    public function setBarWidth($width) {
        $this->bar_width    = $width;
    }
    
    public function setDisplay($what, Bool $bool) {
        $this->display->$what = $bool;
    }
    
    private function getChangeSymbol($change_rate) {
        $symbol = ".";
        foreach ($this->percentage_symbols as $p => $s) {
            if ($change_rate > $p) $symbol = $s;
        }
        return $symbol;
    }
    
    private function displayProgressBar($elapsed, $prev_elapsed) {
        if ( (!$this->bar_width) || (!$this->display->timebar) ) return;
        $percent        = (Float) (100 / $this->end * $elapsed);
        $position       = (Int) ($this->bar_width * $percent / 100);
        $change_rate    = (Float) $percent - ( (Float) (100 / $this->end * $prev_elapsed) );
        $prev_position  = (Int) ($this->bar_width * ((Float) (100 / $this->end * $prev_elapsed)) / 100);
        
        $bar = " ";
        if ($this->display->percent) {
            $bar .= round($percent, 2)."%";
            for ($i=strlen($bar); $i<=6; $i++) {    # an end of 7 may be "correct", but we'll never reach 100% here
                $bar .= " ";
            }
            $bar .= " ";
        }
        
        $bar .= "[";
        for ($i=0; $i<$this->bar_width; $i++) {
            $bar .= ($position == $i) ? $this->getChangeSymbol($change_rate) : (($i >= $prev_position && $i < $position) ? "=" : " ");
        }
        return $bar."]";
    }
    
    private function _loglevel($detail) {
        $fill = "";
        for ($i=strlen($detail["level"]); $i<5; $i++) {
            $fill .= " ";
        }
        return $detail["level"].$fill;
    }
    
    private function _file($detail) {
        if (!$this->display->file) return;
        return (empty($detail["caller"]["file"])) ? "main" : $detail["caller"]["file"];
    }
    
    private function _line($detail) {
        return (empty($detail["caller"]["line"])) ? 0      : $detail["caller"]["line"];
    }
    
    private function _fileline($detail) {
        if ( (!$this->display->file) && (!$this->display->line) ) return;
        return " [".$this->_file($detail).(($this->display->line) ? (($this->display->file) ? ":" : "").$this->_line($detail) : "")."]";
    }
    
    private function _class($detail) {
        if (@is_null($detail["caller"]["class"])) return;
        return $detail["caller"]["class"];
    }
    
    private function _calltype($detail) {
        if (@is_null($detail["caller"]["type"])) return;
        return $detail["caller"]["type"];      
    }
    
    private function _function($detail) {
        if (@is_null($detail["caller"]["function"])) return;
        return $detail["caller"]["function"];        
    }
        
    private function _args($detail) {
        if (!$this->display->args) {
            if (!$this->display->call) {
                return ""; 
            } else { 
                return "()";
            }
        }
        if ( (@is_null($detail["caller"]["args"])) || (count($detail["caller"]["args"]) == 0) ) return;
        return "('".implode("','", array_values($detail["caller"]["args"]))."')";
    }
    
    private function _call($detail) {
        if (!$this->display->call) return;
        return " ".$this->_class($detail).$this->_calltype($detail).$this->_function($detail).$this->_args($detail);
    }
    
    private function _qclass($detail) {
        if (!$this->display->qclass) return;
        return " [".$detail["qclass"]."]";
    }
    
    public function toString($message, $detail, $prev_elapsed) {
        return sprintf("%s%s ".$this->time_format."s%s%s%s: %s\n",
            $this->_loglevel($detail), 
            $this->displayProgressBar($detail["elapsed"], $prev_elapsed),
            $detail["elapsed"],
            $this->_qclass($detail),
            $this->_fileline($detail),
            $this->_call($detail),
            $message
        );
    }    
}

?>
