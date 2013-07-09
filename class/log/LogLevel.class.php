<?php

namespace core\log;

uses ("core.exceptions.IllegalArgumentException");

class LogLevel {

    const
        INFO    = 0x0001,  
        WARN    = 0x0002,
        ERROR   = 0x0004,
        DEBUG   = 0x0008,
        TRACE   = 0x0016;

    const
        NONE    = 0x0000,
        ALL     = 0x001F;

    public static function name($name = "INFO") {
        static $map = array(
            'INFO'  => self::INFO,
            'WARN'  => self::WARN,
            'ERROR' => self::ERROR,
            'DEBUG' => self::DEBUG,
            'TRACE' => self::TRACE,
            'ALL'   => self::ALL,
            'NONE'  => self::NONE,
        );
        $key = strtoupper($name);
        if (!isset($map[$key])) {
            throw new \core\exceptions\IllegalArgumentException("No loglevel '".$name."'");
        }
        return $map[$key];
    }
    
    public static function nameOf($level) {
        static $map = array(
            self::INFO  => 'INFO',
            self::WARN  => 'WARN',
            self::ERROR => 'ERROR',
            self::DEBUG => 'DEBUG',
            self::TRACE => 'TRACE',
        );

        if (!isset($map[$level])) {
            throw new \core\exceptions\IllegalArgumentException("No loglevel '".$name."'");
        }
        return $map[$level];
    }

}

?>
