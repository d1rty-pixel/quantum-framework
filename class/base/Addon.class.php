<?php

namespace core\base;

uses (
    "core.base.Quobject",
    "core.base.Singleton"
);

abstract class Addon extends \core\base\Quobject {

    protected $model            = null;

    protected $model_method     = null;

    protected $args             = null;

    public $controller          = null;

    protected $supported_vars   = array("model", "model_method");

    public function __construct($args = null, $model_ds = null) {
        parent::__construct();
        $this->args = $args;
        $this->configure();
        $this->controller->runController();
    }

    public function configure() {
        $controller         = str_replace("Model", "Controller", $this->model);
        $controller_name    = str_replace(".", "\\", $controller);
        $view               = str_replace("Model", "View", $this->model);

        uses ($controller, $this->model, $view);
        
        trace(sprintf("Creating controller %s for addon %s", $controller_name, $this->getQFClassName()), $this);

        $this->controller = new $controller_name(
            array("model"       => $this->model),
            false
        );

        $this->controller->configure($this->args);
    }

}

?>
