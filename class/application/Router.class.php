<?php

namespace core\application;

uses(
    "core.base.Quobject",
    "core.base.Singleton",
    "core.config.Config",
    "core.exceptions.IllegalArgumentException"
);

class Router extends \core\base\Quobject {

    private $parameter              = "qf_controller";

    private $parameter_value        = "index";

    private $method                 = "get";

    private $config                 = null;

    protected $supported_vars       = array("parameter", "parameter_value", "method");

    public function __construct($config) {
        $this->config = $config;
        parent::__construct();
        $this->determineRequestParameter();

        $this->config->import(array(
            "request.parameter"     => $this->parameter,
        ));

        trace(sprintf("Detected best suiting request parameter '%s' with value '%s'", $this->parameter, $this->parameter_value), $this);
    }

    private function determineRequestParameter() {
        if (!\Request::isEmpty("qf_controller")) {       // use qf_controller
            $this->parameter = "qf_controller";
        } else if (!\Request::isEmpty("qf_module")) {    // use qf_module
            $this->parameter = "qf_module";
        } else if (!\Request::isEmpty("qf_template")) {
            $this->parameter = "qf_template";
        } else {                                        // use the configuration default
            $this->parameter = $this->config->get("default.parameter");
        }

#        if (is_null($this->parameter) || $this->parameter == "")
#            throw new \core\exceptions\IllegalArgumentException("Parameter is still undefined");

        $this->parameter_value = \Request::getArgument($this->parameter);
    }

}

?>
