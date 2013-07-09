<?php

uses ("core.base.mvc.Controller");

class CsstabController extends Controller {

    private $data = null;

    public function configure($data) {
        $this->data = $data;
    }

    public function init() {
    }

    public function onGet() {
        $this->view->display($this->data);
    }

    public function onPost() {
        $this->onGet();
    }

}

?>
