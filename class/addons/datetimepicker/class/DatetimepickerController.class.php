<?php

uses ("core.base.mvc.Controller");

class DatetimepickerController extends Controller {

    private $config     = null;
    private $type       = null;

    private $prototypes = array(
        "datepicker"        => array(),
        "datetimepicker"    => array(),
        "timepicker"        => array(),
    );

    public function configure($type = "datepicker", $config = null) {
        $this->type     = strtolower($type);
        $this->config   = xjson_encode($config);
    }

    private function getDefaultPrototype() {
        if ($this->type == "datepicker") {

        } else if ($this->type == "datetimepicker")
    }

    public function init() {
    }

    public function onGet() {
        $this->view->display($this->type, $this->config);
    }

    public function onPost() {
        $this->onGet();
    }

}

?>
