<?php

uses ("core.log.LogFacility");
uses ("core.base.Singleton");

function do_log($message, $level = "INFO", $qclass = null) {
    if (!\core\base\Singleton::exists('\core\log\LogFacility')) {
        if ($level == "ERROR")  file_put_contents("php://stderr", $message);
        return;
    }

    $log = \core\base\Singleton::getInstance('\core\log\LogFacility'); 
    $t = debug_backtrace();
    switch(count($t)) {
        case 1: $trace = $t[0]; break;
        case 2: $trace = $t[1]; break;
        default: $trace = $t[2]; break;
    }

	trigger_error("$message");

    $log->add($message, $level, $qclass, $trace);
}

function applog($message, $qclass = null) {
    do_log($message, "INFO", $qclass);
}

function trace($message, $qclass = null) {
    do_log($message, "TRACE", $qclass);
}

function debug($message, $qclass = null) {
    do_log($message, "DEBUG", $qclass);    
}

function error($message, $qclass = null) {
    do_log($message, "ERROR", $qclass);
    trigger_error($message, E_USER_ERROR);   
}

function warn($message, $qclass = null) {
    do_log($message, "WARN", $qclass);
    trigger_error($message, E_USER_ERROR);
}

function info($message, $qclass = null) {
    do_log($message, "INFO", $qclass);
}

?>
