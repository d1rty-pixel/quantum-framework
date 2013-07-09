<?php

namespace core\log;

class LogMessage {
    
    private $message            = null;
    private $detail             = null;
    
    public function __construct($message, $detail) {
        $this->message              = $message;
        $this->detail               = $detail;
    }
   
    public function getMessage() {
        return $this->message;
    }
    
    public function getDetail() {
        return $this->detail;
    }

    public function getLevel() {
        return $this->detail["level"];
    }

    public function getElapsed() {
        return $this->detail["elapsed"];
    }   
}

?>
