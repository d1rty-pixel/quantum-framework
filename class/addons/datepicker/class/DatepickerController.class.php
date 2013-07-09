<?php

uses ("core.base.mvc.Controller");

class DatepickerController extends Controller {

    public function init() { 
    }

    /**
     * configure
     * 
     * array can contain the following keys
     *   interval:  sth between 1 day and n years or fixed time (seconds)
     *   buttons:   true or false
     *   start_ts:  default start_ts will be overwritten from request
     *   end_ts:    default end_ts will be overwritten from request
     *   parameters: bla
     *   action_path_script: blubb
     */
    public function configure($config = array()) {
        $this->type = "DatePicker";

        foreach ($config as $key => $val) {
            $this->model->set($key, $val);
        }

        $this->model->setDefaults();
        $this->model->applyRequestParameters();
    }

    public function onGet() {
        $config = $this->model->getConfig();
        Request::implant("start_ts",    $config->start_ts);
        Request::implant("end_ts",      $config->end_ts);
        $this->view->display($this->type, (Array) $config);
    }

    public function OnPost() {
        $this->onGet();
    }

}

?>
