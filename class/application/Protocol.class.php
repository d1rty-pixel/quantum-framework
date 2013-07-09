<?php

namespace core\application;

uses(
    "core.base.Quobject",
    "core.exceptions.ProtocolException"
);

final class Protocol extends \core\base\Quobject {

    private $protocol   = null;

    private $secure     = false;

    private $sslport    = 443;

    protected $supported_vars = array("sslport");


    public function __construct() {
        parent::__construct();

        $this->protocol = (php_sapi_name() == "cli") ? "cli" : null;

        if (is_null($this->protocol)) {
            $this->determineSecureConnection();
        }

        if ($this->protocol == "https") {
            $this->secure = true;
        }

        debug(sprintf("Determined protocol '%s' with secure connection turned %s", $this->protocol, ($this->secure) ? "on" : "off"), $this);
    }


    private function determineSecureConnection() {
        if (!isset($_SERVER["SERVER_NAME"]) || !$_SERVER["SERVER_NAME"]) {
            if (!isset($_ENV["SERVER_NAME"])) {
                // Set to env server_name
                $_SERVER["SERVER_NAME"] = $_ENV["SERVER_NAME"];
            }
        }

        if (!$_SERVER["SERVER_NAME"]) {
            throw new \core\exceptions\ProtocolException("Cannot determine server name");
        }

        if (!isset($_SERVER["SERVER_PORT"]) || !$_SERVER["SERVER_PORT"]) {
            if (!isset($_ENV["SERVER_PORT"])) {
                $_SERVER["SERVER_PORT"]=$_ENV["SERVER_PORT"];
            }
        }

        if (!$_SERVER["SERVER_PORT"]) {
            throw new \core\exceptions\ProtocolException("Cannot determine server port");
        }

        $this->protocol = isset($_SERVER["HTTPS"]) ?
            (($_SERVER["HTTPS"]==="on" || $_SERVER["HTTPS"] === 1 || $_SERVER["SERVER_PORT"] === $this->sslport) ? "https" : "http") : 
            (($_SERVER["SERVER_PORT"] === $this->sslport) ? "https" : "http");
    }


    public function getProtocol() {
        return $this->protocol;
    }


    public function isSecure() {
        return $this->secure;
    }

}

?>
