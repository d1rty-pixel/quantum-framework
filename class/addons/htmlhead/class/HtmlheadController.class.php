<?php

uses ("core.base.mvc.Controller");

class HtmlheadController extends Controller {

    private $config     = null;
    private $type       = null;

    public function configure() {
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
